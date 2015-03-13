<?php

namespace IconicsInvoicing\DataAccess\Repositories;

use IconicsInvoicing\Environments\EnvironmentAware;
use IconicsInvoicing\DataAccess\Entities;
use PDO;

define('INVOICES_TABLE','SOP10100');
define('INVOICE_LINE_TABLE','SOP10200');
define('INVOICE_COMMENT_TABLE', 'SOP10202');

/**
 * The Invoice repository.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-12-30
 */
class InvoiceRepository extends EnvironmentAware
{
    /**
     * @param $storeNumber
     * @return CustomerEntity
     */
    public function createCustomerFromStoreNumber($storeNumber)
    {
        $query = "SELECT
          `mhcLocationID` AS storeNumber,
          `verizonLocationCode` AS locationCode,
          `locationName` as name,
          CONCAT(`locationAddress`,',\n',
          `locationCity`,',\n',
          `locationState`,',\n',
          `locationZip`) AS address
        FROM
          `warehouse`.`verizon_locations` vl
        WHERE
          vl.`mhcLocationID` = :storeNumber ";

        $customerData = $this->environment['Connection']
            ->executeAndFetchSingleResult($query, array(':storeNumber' => $storeNumber));

        $customer = $customerData === false ? false : Entities\CustomerEntity::create($customerData);

        return $customer;
    }

    /**
     * Grab mhcLocation (customer) data from invoice relationships
     *
     * @param InvoiceEntity
     * @return CustomerEntity
     */
    public function createCustomerEntityFromInvoice(Entities\InvoiceEntity $invoice)
    {
        list($orderNum, $approvalNum) = explode('-', $invoice->getInvoiceNumber());
        $query = "SELECT
          vl.`mhcLocationID` AS storeNumber,
          vl.`verizonLocationCode` AS locationCode,
          vl.`locationName` as name,
          CONCAT(vl.`locationAddress`,',\n',
          vl.`locationCity`,',\n',
          vl.`locationState`,',\n',
          vl.`locationZip`) AS address
        FROM
        `warehouse`.`verizon_iconic_orders` vio
            INNER JOIN `warehouse`.`verizon_locations` vl
                ON vl.verizonIconicLocationCode = vio.location
        WHERE
        vio.orderNum = :orderNum
        AND vio.approvalNum = :approvalNum";

        $customerData = $this->environment['Connection']
            ->executeAndFetchSingleResult($query, array(':orderNum' => $orderNum, ':approvalNum' => $approvalNum));

        if(!$customerData) $customerData = array();

        $customer = Entities\CustomerEntity::create($customerData);

        return $customer;
    }

    /**
     * Given startDate and endDate return invoice numbers for reference
     * @param $startDate
     * @param $endDate
     * @return array(num1, num2, ...)
     */
    public function getInvoiceNumbersByDateRange($startDate, $endDate)
    {
        // return array('13625-136855718','12637-549173064', '28425-449643888', '2120-132886561' , '8780-457641343');

        $query = "SELECT i.SOPNUMBE
        FROM " . INVOICES_TABLE . " i
        WHERE i.INVODATE BETWEEN :startDate AND :endDate";

        $stmt = $this->environment['ConnectionMSSQL']->execute($query,
                array(':startDate' => $startDate, ':endDate' => $endDate )
        );

        $numList = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // field length is constant, so trim
        for ($i=0 ; $i < count($numList); $i++ ) {
            $numList[$i] = trim($numList[$i]);
        }

        return $numList;
    }

    /**
     * @param OrderEntity $order
     * @return InvoiceEntity
     */
    public function createInvoiceFromOrder(Entities\OrderEntity $order)
    {
        $invoiceDate = $this->deriveInvoiceDateFromOrder($order);
        $notes = $this->deriveInvoiceNotesFromOrder($order);
        $orderNumber = $this->deriveOrderNumberFromOrder($order);

        $invoiceData = array(
            'batchID' => 'I-'.date('Ymd'),
            'invoiceDate' => $invoiceDate,
            'invoiceNotes' => $notes,
            'orderNumber' => $orderNumber,
            'siteID' => '0201',
        );

        $invoiceEntity = Entities\InvoiceEntity::create($invoiceData);

        return $invoiceEntity;
    }

    /**
     * @param array $likeProducts
     * @param integer $quantity
     * @return InvoiceLineEntity
     */
    public function createInvoiceLineFromMergedProducts(Entities\ProductEntity $product, $quantity)
    {
        $invoiceLineData = array(
            'itemNumber' => $product->getItemNumber(),
            'extendedPrice' => $this->deriveExtendedPriceFromMergedProducts($product, $quantity),
            'unitPrice' => $product->getUnitPrice(),
            'itemQuantity' => $quantity
        );

        return Entities\InvoiceLineEntity::create($invoiceLineData);
    }

    /**
     * @param $itemNumber invoice::itemNumber (dynamics_ID)
     * @return ProductEntity
     */
    public function createProductFromItemNumber($itemNumber)
    {
    }

    /**
     * @param ProductEntity $product
     * @param integer $quantity
     * @return float
     */
    public function deriveExtendedPriceFromMergedProducts(Entities\ProductEntity $product, $quantity)
    {
        return $product->getUnitPrice() * $quantity;
    }

    /**
     * @param OrderEntity $order
     * @return date
     */
    private function deriveInvoiceDateFromOrder(Entities\OrderEntity $order)
    {
        $connection = $this->environment['Connection'];

        $query = "SELECT
          MAX(vsd.`sofieDocumentDatetime`) AS invoiceDate
        FROM
          `verizon_sofie_dfill` vsd
        WHERE vsd.`verizonIconicOrderNum` = :orderNum
          AND vsd.`verizonIconicOrderApprovalNum` = :approvalNum";

        $params = array(
            ':orderNum' => $order->getOrderNum(),
            ':approvalNum' => $order->getApprovalNum(),
        );

        return $connection->executeAndFetchSingleColumnResult($query, $params);
    }

    /**
     * @param OrderEntity $order
     * @return string
     */
    private function deriveInvoiceNotesFromOrder(Entities\OrderEntity $order)
    {
        $shippingDetails = $this->createOrderShippingDetailsFromOrder($order);

        $params = array(
            ':orderNum' => $order->getOrderNum(),
            ':approvalNum' => $order->getApprovalNum(),
        );

        $dfillDataQuery = "
        SELECT
            vsd.verizonItemCode AS itemCode,
            vsd.esn AS esn
        FROM "
            . $this->environment['Database']['warehouse']['verizon_sofie_dfill'] . " vsd
        WHERE
            vsd.verizonIconicOrderNum = :orderNum AND
            vsd.verizonIconicOrderApprovalNum = :approvalNum";

        $dfillData = $this->environment['Connection']
            ->executeAndFetchAllResults($dfillDataQuery, $params);

        $noteString = "\nDevice Details: ";
        foreach ($dfillData as $dfill) {
            $noteString .= $dfill['esn'] . '(' . $dfill['itemCode'] . '), ';
        }

        $noteString .= "\nTracking Details: ";
        foreach ($shippingDetails as $shippingDetail) {
            $noteString .= $shippingDetail->getTrackingNumber() . '(' . $shippingDetail->getCarrier() . '), ';
        }

        return $noteString;
    }

    /**
     * @param OrderEntity
     * @return OrderShippingDetailEntity
     */
    private function createOrderShippingDetailsFromOrder(Entities\OrderEntity $order)
    {
        $params = array(
            ':orderNum' => $order->getOrderNum(),
            ':approvalNum' => $order->getApprovalNum(),
        );

        $shippingDetailQuery = "
        SELECT
            vsdt.`shippingCarrier` AS carrier,
            vsdt.`trackingNumber` AS trackingNumber
        FROM "
            . $this->environment['Database']['warehouse']['verizon_sofie_dfill_tracking'] . " vsdt
          WHERE vsdt.`verizonIconicOrderApprovalNum` = :approvalNum
            AND vsdt.`verizonIconicOrderNum` = :orderNum";

        $shippingDetailData = $this->environment['Connection']
            ->executeAndFetchAllResults($shippingDetailQuery, $params);

        $shippingDetails = array();
        foreach ($shippingDetailData as $shippingRow) {
            $shippingDetails[] = Entities\OrderShippingDetailEntity::create($shippingRow);
        }


        return $shippingDetails;
    }

    /**
     * @param OrderEntity $order
     * @return string
     */
    private function deriveOrderNumberFromOrder(Entities\OrderEntity $order)
    {
        return $order->getOrderNum().'-'.$order->getApprovalNum() ; // this is stupid
    }

    /**
     * Basic GET invoice method
     *
     * @param string order-approval
     * @return InvoiceEntity
     */
    public function getInvoiceByOrderNum($orderApprovalNum)
    {
        $query =
        "SELECT i.SOPNUMBE AS orderNumber,
                i.BACHNUMB AS batchID,
                i.INVODATE AS invoiceDate,
                i.LOCNCODE AS siteID
         FROM " . INVOICES_TABLE . " i
         WHERE i.SOPNUMBE = :orderNum";

        $invoiceData = $this->environment['ConnectionMSSQL']->executeAndFetchSingleResult($query,
                   array(':orderNum' => $orderApprovalNum));
        if ($invoiceData === false) {
            return false;
        }

        return Entities\InvoiceEntity::create($invoiceData);
    }

    /**
     * @param OrderEntity $order
     * @return InvoiceEntity
     */
    public function getInvoiceFromOrder(Entities\OrderEntity $order)
    {
        $query =
        "SELECT i.SOPNUMBE AS orderNumber,
                i.BACHNUMB AS batchID,
                i.INVODATE AS invoiceDate,
                ic.CMMTTEXT AS invoiceNotes,
                i.LOCNCODE AS siteID
         FROM " . INVOICES_TABLE . " i
             LEFT JOIN " . INVOICE_COMMENT_TABLE . " ic ON i.SOPNUMBE = ic.SOPNUMBE
         WHERE i.SOPNUMBE = :orderNum";

        $invoiceData = $this->environment['ConnectionMSSQL']->executeAndFetchSingleResult($query,
                   array(':orderNum' => $this->deriveOrderNumberFromOrder($order) ));
        if ($invoiceData === false) {
            return false;
        }

        return Entities\InvoiceEntity::create($invoiceData);
    }

    /**
     * @param InvoiceEntity $invoice
     * @return array( InvoiceLineEntity[] )
     */
    public function getInvoiceLinesFromInvoice(Entities\InvoiceEntity $invoice)
    {
        $invoiceNumber = $invoice->getInvoiceNumber();
        $query = "SELECT
        il.SOPNUMBE AS invoiceNumber,
        il.UNITPRCE AS unitPrice,
        il.XTNDPRCE AS extendedPrice,
        il.QUANTITY AS itemQuantity,
        il.ITEMNMBR AS itemNumber
        FROM " . INVOICE_LINE_TABLE . " il
        WHERE il.SOPNUMBE = :invoiceNumber";

        $invLines = $this->environment['ConnectionMSSQL']->execute($query,
                    array(':invoiceNumber' => $invoice->getInvoiceNumber() ));

        $invoiceLineEntities = array();

        while ($line = $invLines->fetch(PDO::FETCH_ASSOC)) {
            $invoiceLineEntities[] = Entities\InvoiceLineEntity::create($line);
        }

        return $invoiceLineEntities;
    }

    /**
     * @param $overrideDate
     *
     * @return boolean
     */
    public function overrideInvoiceDate($overrideDate)
    {
    }

    /**
     * @param array $products : ProductEntity[]
     * @return array('productEntity' => ProductEntity, itemCount => quantity)
     */
    public function mergeLikeProductsByItemNumber(array $products)
    {
        $mergedArray = array();
        foreach ($products as $productEntity) {
            $itemNumber = $productEntity->getItemNumber();
            if ( !array_key_exists($itemNumber, $mergedArray) ) {
                $mergedArray[$itemNumber] = array(
                    'productEntity' => $productEntity,
                    'itemCount' => 1,
                );
            } else {
                $mergedArray[$itemNumber]['itemCount'] += 1;
            }
        }

        $finalArray = array_values($mergedArray);

        return $finalArray;
    }

    /**
     * @param array $preparedInvoiceData( 'invoiceEntity', 'invoiceLineEntities', 'customerEntity' )
     * @return null
     */
    public function processInvoice(array $preparedInvoiceData, $overrideDate = null)
    {
        $invoice = $preparedInvoiceData['invoiceEntity'];
        $invoiceLines = $preparedInvoiceData['invoiceLineEntities'];
        $customer = $preparedInvoiceData['customerEntity'];
        $invoiceNumber = $invoice->getInvoiceNumber();
        list($mhcOrderNum, $approvalNum) = explode('-',$invoiceNumber);

        $invoiceQuery = "
        SET XACT_ABORT ON
        BEGIN TRANSACTION

        INSERT INTO " . INVOICES_TABLE . "
            (
                SOPTYPE,
                SOPNUMBE,
                DOCID,
                DOCDATE,
                INVODATE,
                LOCNCODE,
                BACHNUMB,
                CUSTNMBR,
                CUSTNAME,
                CSTPONBR,
                ShipToName,
                ADDRESS1,
                CITY,
                STATE,
                ZIPCODE
            ) VALUES (
                3,
                '$invoiceNumber',
                'IN',
                '" . date('Y-m-d') . "',
                '" .
            ($overrideDate ? date('Y-m-d', strtotime($overrideDate)) : date('Y-m-d', strtotime($invoice->getInvoiceDate())))
            . "',
                '" . $invoice->getSiteID() . "',
                '" . $invoice->getBatchID() . "',
                '". $customer->getStoreNumber() ."',
                '". $customer->getName() ."',
                'DF". $mhcOrderNum  ."',
                '".  $customer->getName() ."',
                '". $customer->getAddress() ."',
                '". $customer->getCity() ."',
                '". $customer->getState() ."',
                '". $customer->getZipcode() ."'
            )
        ";

        $invoiceQuery .= "

        INSERT INTO " . INVOICE_COMMENT_TABLE . "
           (
                SOPNUMBE,
                SOPTYPE,
                CMMTTEXT
           ) VALUES (
                '" . $invoiceNumber . "',
                3,
                '" . $invoice->getInvoiceNotes() . "'
           )";

        $sequenceNumber = 0;
        foreach ($invoiceLines as $iLine) {
            $sequenceNumber++;
            $invoiceQuery .= "

        INSERT INTO " . INVOICE_LINE_TABLE . "
           (
                SOPTYPE,
                SOPNUMBE,
                CMPNTSEQ,
                LNITMSEQ,
                ITEMNMBR,
                QUANTITY,
                UNITPRCE,
                XTNDPRCE
           ) VALUES (
                3,
                '" . $invoiceNumber . "',
                $mhcOrderNum,
                $sequenceNumber,
                '" . $iLine->getItemNumber() . "',
                " . $iLine->getItemQuantity() . ",
                '" .$iLine->getUnitPrice() . "',
                '" .$iLine->getExtendedPrice() . "'
           )

           ";
        }

        $invoiceQuery .= "
        COMMIT TRANSACTION
        ";

        $this->environment['ConnectionMSSQL']->execute($invoiceQuery);
    }

}