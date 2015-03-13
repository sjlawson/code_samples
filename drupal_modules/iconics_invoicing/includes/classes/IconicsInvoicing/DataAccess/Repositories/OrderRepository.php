<?php

namespace IconicsInvoicing\DataAccess\Repositories;

use IconicsInvoicing\Environments\EnvironmentAware;
use IconicsInvoicing\DataAccess\Entities;
use PDO;

/**
 * The Order repository.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-12-30
 */
class OrderRepository extends EnvironmentAware
{
    /**
     * @param array $filters
     * @param array $limit
     * @param boolean $returnCount
     *
     * @return OrderEntity[]
     */
    public function getFilteredOrders(array $filters = array(), $limit = array(), $returnCount = false, $invoiceNumbers = array())
    {
        $params = $this->generateFilterParams($filters);

        if ($returnCount) {
            $limitClause = '';
        } else {

            $limitClause = (isset($limit['rowCount']) && isset($limit['offset']))
                ? " LIMIT " . $limit['offset'] . "," . $limit['rowCount']
                : ' LIMIT 0,20 ';
        }

        $havings = '';

        // This is a logic mess
        $havingNotArray = array();
        if (!$filters['invoiceStatus']['notReady']) {
            // do not inclue rows in which shipped != ordered
            $havingNotArray[] = "iconicOrderItemsDeliveredCount = iconicOrderItemsOrderedCount";
        }

        if (!$filters['invoiceStatus']['ready']) {
            $havingNotArray[] = "iconicOrderItemsDeliveredCount != iconicOrderItemsOrderedCount";
        }

        if (!$filters['invoiceStatus']['invoiced']) {
            $havings .= " AND orderApprovalNum NOT IN ('" . implode("','",$invoiceNumbers) . "') ";
        } elseif ($filters['invoiceStatus']['invoiced'] == 'invoiced' && !$filters['invoiceStatus']['ready'] && !$filters['invoiceStatus']['notReady'])  {
            $havings .= " AND orderApprovalNum IN ('" . implode("','",$invoiceNumbers) . "') ";
        } elseif(($filters['invoiceStatus']['ready'] == 'ready' || $filters['invoiceStatus']['notReady'] == 'notReady' ) && $filters['invoiceStatus']['invoiced'] == 'invoiced') {

            if( $filters['invoiceStatus']['ready'] == 'ready' ) {
                $havingNotArray[] = " iconicOrderItemsDeliveredCount = iconicOrderItemsOrderedCount ";
            }

            if( $filters['invoiceStatus']['notReady'] == 'notReady' ) {
                $havingNotArray[] = " iconicOrderItemsDeliveredCount != iconicOrderItemsOrderedCount ";
            }

            $havingNotArray[] = "orderApprovalNum IN ('" . implode("','",$invoiceNumbers) . "') ";
        } else {
            $havingNotArray[] = "orderApprovalNum IN ('" . implode("','",$invoiceNumbers) . "') ";
        }

        $havingNotString = empty($havingNotArray) ? '' : " AND ( " . implode(' OR ', $havingNotArray) . " ) ";

        $connection = $this->environment['Connection'];
        $query = "SELECT
            vio.location AS 'iconicLocationCode',
            vl.mhcLocationID AS 'mhcLocation',
            CONCAT(vio.orderNum, '-', vio.approvalNum) AS orderApprovalNum,
            MAX(vio.line) AS 'iconicOrderItemsOrderedCount',
            SUM(IF(ISNULL(vsd.verizonSofieDfillID), 0, 1)) AS 'iconicOrderItemsDeliveredCount',
            MAX(vsd.sofieDocumentDatetime) AS invoiceDate
            FROM "
                . $this->environment['Database']['warehouse']['verizon_iconic_orders'] . " vio " . "
            LEFT JOIN "
                . $this->environment['Database']['warehouse']['verizon_sofie_dfill'] . " vsd "
            ."ON vsd.verizonIconicOrderNum = vio.orderNum
                AND vsd.transactionLineNumber = vio.line
                AND vsd.verizonIconicOrderApprovalNum = vio.approvalNum
            INNER JOIN "
            . $this->environment['Database']['warehouse']['verizon_locations'] . " vl
                ON vl.verizonIconicLocationCode = vio.location
            WHERE
                vio.`status` = 'C'
                AND vsd.esn NOT LIKE ('8914%')
                AND vl.locationType = 'DLR' ";

        if(!empty($filters['searchOrderID'])) {
            $searchList = explode(',',str_replace('-',',', $filters['searchOrderID']));
            $query .= " AND (";

            $searchArray = array();
            foreach ($searchList as $searchString) {
                $searchArray[] = "( vl.mhcLocationID = '$searchString' OR vio.orderNum LIKE '%".$searchString."%' OR vio.approvalNum LIKE '%".$searchString."%' )";
            }

            $query .= implode(' OR ',$searchArray);

            $query .= ")";
        }

        $query .= "
            GROUP BY vio.orderNum , vio.confirmationID , vio.approvalNum ";

        if(isset($params[':startDate'])) {
            $query .= "
                HAVING invoiceDate BETWEEN :startDate AND :endDate
                ";
        } else {
            $query .= " HAVING 1 ";
        }

        $query .= "
                $havings
                $havingNotString
            ORDER BY invoiceDate DESC
        " . $limitClause ;

        // drupal_set_message($query);

        if($returnCount) {
            return $connection->executeAndGetRowCount($query, $params);
        } else {
            return $connection->executeAndFetchAllResults($query, $params);
        }
    }

    /**
     * format parameter array
     *
     * @param array $filters
     * @return array $params
     */
    private function generateFilterParams($filters)
    {
        $params = array();

        /* $startDate = (!isset($filters['startDate']) || empty($filters['startDate'])) ? '' : */
        /*     date('Y-m-d 00:00:00', strtotime($filters['startDate'])); */
        if(!isset($filters['startDate']) || empty($filters['startDate'])) {
            ;
        } else {
            $startDate = date('Y-m-d 00:00:00', strtotime($filters['startDate']));
            $params[':startDate'] = $startDate;
            $params[':endDate'] = date('Y-m-d 23:59:59', strtotime($startDate) + 86400 * 14 );
        }

        return $params;
    }

    /**
     * @param $verizonIconicLocationCode
     * @return CustomerEntity
     */
    public function createCustomerFromLocationCode($verizonIconicLocationCode)
    {
        $connection = $this->environment['Connection'];
        $query = "SELECT
          `mhcLocationID` AS storeNumber,
          `verizonIconicLocationCode` AS locationCode,
          `locationName` AS name,
          `locationAddress` AS address,
          `locationCity` AS city,
          `locationState` AS state,
          `locationZip` AS zipcode
        FROM
          `warehouse`.`verizon_locations` vl
        WHERE
          vl.`verizonIconicLocationCode` = :locationCode ";

        $customerData = $connection->executeAndFetchSingleResult($query,
                        array(':locationCode' => $verizonIconicLocationCode));

        $customer = !$customerData ? false : Entities\CustomerEntity::create($customerData);

        return $customer;
    }


    /**
     * @param $orderNumber
     * @param $approvalNumber
     *
     * @return OrderEntity
     */
    public function createOrderFromOrderKeys($orderNumber, $approvalNumber)
    {
        $query = "SELECT
  vio.`approvalNum`,
  vio.`orderNum`,
  vio.`confirmationID`,
  vio.`location` AS verizonIconicLocationCode,
  MAX(vsd.sofieDocumentDatetime) AS invoiceDate,
  MAX(vio.line) AS 'itemsOrdered',
  SUM(
    IF(
      ISNULL(vsd.verizonSofieDfillID),
      0,
      1
    )
  ) AS 'itemsShipped'
FROM
  `warehouse`.`verizon_iconic_orders` vio
  LEFT JOIN `warehouse`.`verizon_sofie_dfill` vsd
    ON vsd.verizonIconicOrderNum = vio.orderNum
    AND vsd.transactionLineNumber = vio.line
    AND vsd.verizonIconicOrderConfirmationID = vio.confirmationID
    AND vsd.verizonIconicOrderApprovalNum = vio.approvalNum
WHERE vio.`orderNum` = :orderNum
  AND vio.`approvalNum` = :approvalNum";

        $connection = $this->environment['Connection'];
        $orderData = $connection->executeAndFetchSingleResult($query, array(':orderNum' => $orderNumber, ':approvalNum' => $approvalNumber) );
        $order = Entities\OrderEntity::create($orderData);

        return $order;
    }

    /**
     * @param OrderEntity $order
     *
     * @return OrderLineEntity[]
     */
    public function createOrderLinesFromOrder(Entities\OrderEntity $order)
    {
        $query = "SELECT
          vsd.`sofieDocumentDatetime` AS dfillDate,
          vsd.`esn` AS itemEsn,
          vsd.`transactionLineNumber` AS itemLine,
        FROM
          warehouse.`verizon_sofie_dfill` vsd
        WHERE vsd.`verizonIconicOrderNum` = :orderNum
          AND vsd.`verizonIconicOrderApprovalNum` = :approvalNum
          AND  vsd.esn NOT LIKE ('8914%') ";
        $params = array(
            ':orderNum' => $order->getOrderNum(),
            ':approvalNum' => $order->getApprovalNum(),
        );

        $connection = $this->environment['Connection'];
        $orderLinesData = $connection->executeAndFetchAllResults($query, $params);

        $orderLines = array();
        foreach( $orderLinesData as $line ) {
            // var_dump($line);

            $orderLines[] = Entities\OrderLineEntity::create($line);
        }

        return $orderLines;

    }

    /**
     * @param $orderNumber
     * @param $approvalNumber
     *
     * @return OrderLineEntity
     */
    protected function createOrderLineFromOrderKeys($orderNumber, $approvalNumber)
    {

    }

    /**
     * @param $orderNumber
     * @param $approvalNumber
     *
     * @return OrderShippingDetailEntity
     */
    protected function createOrderShippingDetailFromOrderKeys($orderNumber, $approvalNumber)
    {
        $query = "SELECT
        vsdt.`shippingCarrier`,
        vsdt.`trackingNumber`
        FROM
        `warehouse`.`verizon_sofie_dfill_tracking` vsdt
        WHERE
        vsdt.`verizonIconicOrderApprovalNum` = :approvalNum
        AND vsdt.`verizonIconicOrderNum` = :orderNum
        ";
        $params = array(':approvalNum' => $approvalNumber, ':orderNum' => $orderNumber);
        $shippingDetailData = $this->environment['Connection']->executeAndFetchSingleResult($query, $params);

        return Entities\OrderShippingDetailEntity::create($shippingDetailData);
    }

    /**
     * @param OrderEntity $order
     * @return OrderShippingDetailEntity[]
     */
    public function createOrderShippingDetailsFromOrder(Entities\OrderEntity $order)
    {
        $orderNum = $order->getOrderNum();
        $approvalNum = $order->getApprovalNum();

        return $this->createOrderShippingDetailFromOrderKeys($orderNum, $approvalNum);
    }

    /**
     * @param  $itemCode order::itemCode : string
     * @return ProductEntity
     */
    public function createProductFromItemCode($itemCode)
    {
    }

    /**
     * @param Entities\OrderEntity $order
     * @return ProductEntity[]
     */
    public function createProductEntitiesFromOrder(Entities\OrderEntity $order)
    {
        $query = "SELECT
          vio.`itemCode` AS itemCode,
          o.`dynamics_id` AS itemNumber,
          o.`verizon_price` AS unitPrice
        FROM
          warehouse.`verizon_iconic_orders` vio
          INNER JOIN mhcdynad.`ordersheet` o
            ON vio.`itemCode` = o.`dymax_code`
        WHERE vio.`orderNum` = :orderNum
          AND vio.`approvalNum` = :approvalNum
          AND vio.`status` = 'C'";

        $params = array(':orderNum' => $order->getOrderNum(), ':approvalNum' => $order->getApprovalNum());

        $productEntities = array();
        $stmt = $this->environment['Connection']->execute($query, $params);

        while($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $productEntities[] = Entities\ProductEntity::create($item);
        }

        return $productEntities;
    }

    /**
     * get min dfill domument date for invoice date validation
     *
     * @param string $orderApprovalNum
     * @return mixed false|string
     */
    public function getMinDfillForOrder($orderApprovalNum)
    {
        list($orderNum, $approvalNum) = explode('-',$orderApprovalNum);

        $query = "SELECT
          MIN(vsd.`sofieDocumentDatetime`)
        FROM
          warehouse.`verizon_sofie_dfill` vsd
        WHERE vsd.verizonIconicOrderApprovalNum = :approvalNum
          AND vsd.`verizonIconicOrderNum` = :orderNum";

        $params = array(':orderNum' => $orderNum, ':approvalNum' => $approvalNum);

        return $this->environment['Connection']->executeAndFetchSingleColumnResult($query, $params);
    }

}