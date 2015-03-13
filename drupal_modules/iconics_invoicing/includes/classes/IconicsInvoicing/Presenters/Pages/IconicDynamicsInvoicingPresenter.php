<?php

namespace IconicsInvoicing\Presenters\Pages;

use IconicsInvoicing\Environments\EnvironmentAware;
use IconicsInvoicing\DataAccess\Repositories\OrderRepository;
use IconicsInvoicing\DataAccess\Repositories\InvoiceRepository;

/**
 * "Iconic Dynamics Invoicing" page presenter.
 *
 * The environment is injected after instantiation from the DI container
 * using setter injection.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-12-15
 */
class IconicDynamicsInvoicingPresenter extends EnvironmentAware
{
    const DRUPAL_MENU_ROUTER_PATH = 'apps/warehouse/iconic_invoicing';

    protected $orderRepository;
    protected $invoiceRepository;

    /**
     * Constructor
     */
    public function __construct(OrderRepository $orderRepository, InvoiceRepository $invoiceRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function getUrlparams($queryParameters)
    {
        $defaultParams = array(
            'startDate' => null,
            'invoiceStatus' => null,
            'searchOrderID' => null,
            'jsonAction' => null,
            'jsonActionData' => null,
        );
        $urlParams = array_merge($defaultParams, $queryParameters);

        return $urlParams;
    }

    /**
     * Will return the drupal path for this page.
     *
     * @return string
     */
    public function getDrupalMenuRouterPath()
    {
        return self::DRUPAL_MENU_ROUTER_PATH;
    }


    public function getInvoiceByOrderNum($orderApprovalNum)
    {
        list($orderNum, $approvalNum) = explode('-',$orderApprovalNum);
        $orderEntity = $this->orderRepository->createOrderFromOrderKeys($orderNum, $approvalNum);
        $invoiceEntity = $this->invoiceRepository->getInvoiceFromOrder($orderEntity);
        $customerEntity = $this->invoiceRepository->createCustomerEntityFromInvoice($invoiceEntity);
        $invoiceLineEntities = $this->invoiceRepository->getInvoiceLinesFromInvoice($invoiceEntity);

        $invoiceLines = array();

        foreach ($invoiceLineEntities as $invoiceLine) {
            $invoiceLines[] = $invoiceLine->toArray();
        }

        return array(
            'customerData' => $customerEntity->toArray(),
            'invoiceData' => $invoiceEntity->toArray(),
            'invoiceLines' => $invoiceLines,
        );
    }

    /**
     * Retrieve order data, orderlines to invoice
     *
     * @param $orderApprovalNum
     * @return array( CustomerEntity, InvoiceEntity, InvoiceLineEntities )
     */
    public function prepareInvoice($orderApprovalNum)
    {
        list($orderNum, $approvalNum) = explode('-',$orderApprovalNum);

        $orderEntity = $this->orderRepository->createOrderFromOrderKeys($orderNum, $approvalNum);

        $existingInvoiceCheck = $this->invoiceRepository->getInvoiceFromOrder($orderEntity);
        if ($existingInvoiceCheck !== false) {
            return array('error' => 'An invoice with that order-approval ID already exists');
        }

        $invoiceEntity = $this->invoiceRepository->createInvoiceFromOrder($orderEntity);
        $customerEntity = $this->orderRepository->createCustomerFromLocationCode(
        $orderEntity->getVerizonIconicLocationCode());
        $products = $this->orderRepository->createProductEntitiesFromOrder($orderEntity);

        $mergedProducts = $this->invoiceRepository->mergeLikeProductsByItemNumber($products);
        $invoiceLines = array();
        foreach ($mergedProducts as $mergedProduct) {
            $invoiceLines[] = $this->invoiceRepository->createInvoiceLineFromMergedProducts(
                $mergedProduct['productEntity'], $mergedProduct['itemCount']);
        }

        return array(
            'customerEntity' => $customerEntity,
            'invoiceEntity' => $invoiceEntity,
            'invoiceLineEntities' => $invoiceLines,
        );

    }

    public function displayOrderToInvoice($orderApprovalNum)
    {
        $preparedInvoiceData = $this->prepareInvoice($orderApprovalNum);

        $invoiceLines = array();

        foreach ($preparedInvoiceData['invoiceLineEntities'] as $invoiceLine) {
            $invoiceLines[] = $invoiceLine->toArray();
        }

        return array(
            'customerData' => $preparedInvoiceData['customerEntity']->toArray(),
            'invoiceData' => $preparedInvoiceData['invoiceEntity']->toArray(),
            'invoiceLines' => $invoiceLines,
        );

    }

    /**
     * Process orders to invoices from selected checkboxes
     *
     * @param array [(orderID:nnnnn-mmmmmmmmm , invoiceDate:mm/dd/yyyy), ... ]
     * @return true | error:msg on failure
     */
    public function processSelectedOrders($ordersData)
    {
        foreach ($ordersData as $orderData) {

            if($orderData['orderID']) {
                $ret = $this->processInvoice($orderData['orderID'], $orderData['invoiceDate']);
                if($ret !== true) {
                    return $ret;
                }
            }
        }

        return true;
    }

    public function processInvoice($orderApprovalNum, $overrideDate = null)
    {
        $preparedInvoiceData = $this->prepareInvoice($orderApprovalNum);
        $overrideDate = $overrideDate ? date('Y-m-d', strtotime($overrideDate)) : null;
        $calculatedInvoiceDate = date('Y-m-d', strtotime($preparedInvoiceData['invoiceEntity']->getInvoiceDate()));

        /*
         * Turn this back on when they decide they want to validate override date
         *
        if( $overrideDate && $calculatedInvoiceDate != $overrideDate) {
            $minDfillDate = date('Y-m-d', strtotime($this->orderRepository->getMinDfillForOrder($orderApprovalNum)));
            if($overrideDate < $minDfillDate) {

                return array('error' => 'Given invoice date cannot be earlier than ' .
                    date('m-d-Y', strtotime($minDfillDate))
                    . ' for order ' . $orderApprovalNum
                );
            }
        }
        */

        if (!$preparedInvoiceData['customerEntity']) {

            list($mhcOrderNum, $approvalNum) = explode('-', $orderApprovalNum);

            $cstTable =  "<div class='customer-view'>
            <table class='invoice-view-customerdata'>"
            . "<tr><th>Customer ID</th><td> <span class='error'>empty</span> </td></tr>"
            . "<tr><th>Customer PO Num</th><td>DF" . $mhcOrderNum . "</td></tr>"
            . "<tr><th>Customer Name</th><td> <span class='error'>empty</span> </td></tr>"
            . "<tr><th>Address</th><td> <span class='error'>empty</span> </td></tr>
                  </table></div>";
            $invoiceData = $preparedInvoiceData['invoiceEntity']->toArray();
            $cstTable .= "<div class='invoice-view'>".
             "<table class='invoice-view-invoicedata'>".
             "<tr><th>Order Number</th><td>" . $invoiceData['orderNumber'] . "</td></tr>".
             "<tr><th>Invoice Date</th><td>" . $invoiceData['invoiceDate'] . "</td></tr>".
             "<tr><th>BatchID</th><td>" . $invoiceData['batchID'] . "</td><tr>".
             "<tr><th>SiteID</th><td>". $invoiceData['siteID'] . "</td></tr>".
             "</table></div><div style='clear: both;'></div><br />";

            return array('error' => 'Customer data missing', 'entityTable' => $cstTable);
        }

        if (!$this->validateInvoiceData($preparedInvoiceData['invoiceEntity'])) {
            return array('error' => 'Empty invoice');
        }

        if (!$this->validateInvoiceLines($preparedInvoiceData['invoiceLineEntities'])) {
            return array('error' => 'Missing invoice line data');
        }

        $this->invoiceRepository->processInvoice($preparedInvoiceData, $overrideDate);

        return true;
    }

    private function validateInvoiceData(\IconicsInvoicing\DataAccess\Entities\InvoiceEntity $invoiceEntity)
    {
        $invoArray = array_values($invoiceEntity->toArray());
        if(empty($invoArray)) {
            return false;
        }

        return true;
    }

    private function validateInvoiceLines(array $invoiceLines)
    {
        if(empty($invoiceLines)) {
            return false;
        }

        return true;
    }

    public function getInvoiceLinesTableHeader()
    {
        return array(
            'itemCode' => 'Item Code',
            'itemNumer' => 'Item Number',
            'itemQuantity' => 'Invoice Quantity',
            'unitPrice' => 'Unit Price',
            'extendedPrice' => 'Extended Price',
        );
    }

    public function getOrderLinesTableHeader()
    {
        return array(
            'dfillDate' => 'DFill Date',
            'itemEsn' => 'Item ESN',
            'itemLine' => 'Item Line',
            ''
        );
    }

    public function getIconicInvoiceTableHeader()
    {
        return array(
            'select' => 'Select',
            'iconicLocationCode' => 'Location',
            'mhcLocation' => 'POS',
            'orderApprovalNum' => 'Order Number-Approval Number',
            'iconicOrderItemsOrderedCount' => 'Items Ordered',
            'iconicOrderItemsDeliveredCount' => 'Items Shipped',
            'invoiceDate' => 'DFill Date',
            'actualInvoiceDate' => 'Invoice Date',
            'action' => 'Action'
        );
    }

    public function getIconicInvoiceTableDataRows($filters = array(), $limit = array())
    {

        $startDate = (isset($filters['startDate']) && !empty($filters['startDate']))
            ? $filters['startDate']
            : date('Y-m-d', (strtotime('now') - 86400 * 14) );

        $endDate = date('Y-m-d', strtotime($startDate) + 86400 * 14);

        $invoices = $this->invoiceRepository->getInvoiceNumbersByDateRange($startDate, $endDate );
        $filteredOrders = $this->orderRepository->getFilteredOrders($filters, $limit, false, $invoices);

        // drupal_set_message("InvoiceNums:<pre>" . print_r($invoices, true));
        for ( $i=0; $i < count($filteredOrders); $i++) {

            $actualInvoice = $this->invoiceRepository->getInvoiceByOrderNum($filteredOrders[$i]['orderApprovalNum']);

            $customerEntity = $this->orderRepository->createCustomerFromLocationCode($filteredOrders[$i]['iconicLocationCode']);
            $isDisabled = $customerEntity === false ? 'disabled' : '';

            if($actualInvoice) {
                $filteredOrders[$i]['actualInvoiceDate'] = date('m/d/Y',
                                                           strtotime($actualInvoice->getInvoiceDate()));

                if($filteredOrders[$i]['actualInvoiceDate'] != date('m/d/Y',
                        strtotime( $filteredOrders[$i]['invoiceDate']))) {
                    $filteredOrders[$i]['actualInvoiceDate'] = "<span class='error'>"
                        . $filteredOrders[$i]['actualInvoiceDate']
                        . "</span>";
                }
            } else {
                $filteredOrders[$i]['actualInvoiceDate'] = 'Not Processed';
            }

            $action = '';
            if(!$customerEntity) {
                $action = "<a class='button small viewProblem' rel='"
                    . $filteredOrders[$i]['orderApprovalNum']
                    . "' >View Problem</a>";

                $filteredOrders[$i] = array('select' => "<input type='checkbox' disabled />") + $filteredOrders[$i];
                //$filteredOrders[$i]['select'] = "<input type='checkbox' disabled />";

                $filteredOrders[$i]['invoiceDate'] =
                    "<input class='invoicedate-table-entry' $isDisabled type='text' id='invoiceDate_"
                    . $filteredOrders[$i]['orderApprovalNum']
                    . "' name='invoiceDate_"
                    . $filteredOrders[$i]['orderApprovalNum']."' "
                    . "value='" . date('m/d/Y', strtotime( $filteredOrders[$i]['invoiceDate'])) . "' />";

            } elseif($filteredOrders[$i]['iconicOrderItemsOrderedCount'] ==
                $filteredOrders[$i]['iconicOrderItemsDeliveredCount']
                && !$actualInvoice) {

                $filteredOrders[$i] =  array('select' =>
                                          "<input type='checkbox' class='process-invoice-checkbox' value='".$filteredOrders[$i]['orderApprovalNum']."' name='process_".$filteredOrders[$i]['orderApprovalNum']."' />") +
                    $filteredOrders[$i];

                $action = "<a class='button small previewInvoice' rel='"
                    . $filteredOrders[$i]['orderApprovalNum']
                    . "' >View Order</a>";

                $filteredOrders[$i]['invoiceDate'] =
                    "<input class='invoicedate-table-entry' $isDisabled type='text' id='invoiceDate_"
                    . $filteredOrders[$i]['orderApprovalNum']
                    . "' name='invoiceDate_"
                    . $filteredOrders[$i]['orderApprovalNum']."' "
                    . "value='" . date('m/d/Y', strtotime( $filteredOrders[$i]['invoiceDate'])) . "' />";

            } elseif ($filteredOrders[$i]['iconicOrderItemsOrderedCount'] ==
                    $filteredOrders[$i]['iconicOrderItemsDeliveredCount']
                    && !empty($actualInvoice)) {
                $filteredOrders[$i] = array('select' => '') + $filteredOrders[$i];

                    $filteredOrders[$i]['invoiceDate'] = date('m/d/Y',
                                                         strtotime( $filteredOrders[$i]['invoiceDate']));
                    $action = "<a class='button small viewOrder' rel='"
                        .$filteredOrders[$i]['orderApprovalNum']
                        ."' >View Invoice</a>";
                } else {
                    $action = "<a href='#' class='button small forceInvoice $isDisabled' rel='"
                        . $filteredOrders[$i]['orderApprovalNum']
                        . "'>Force Invoice</a>";

                    $filteredOrders[$i] = array('select' => '') + $filteredOrders[$i];

                    $filteredOrders[$i]['invoiceDate'] =
                        "<input class='invoicedate-table-entry $isDisabled' type='text' id='invoiceDate_"
                        . $filteredOrders[$i]['orderApprovalNum']
                        . "' name='invoiceDate_"
                        . $filteredOrders[$i]['orderApprovalNum']."' "
                        . "value='" . date('m/d/Y', strtotime( $filteredOrders[$i]['invoiceDate'])) . "' />";
                }

            $filteredOrders[$i]['action'] = $action;
        }

        return $filteredOrders;
    }

    public function getIconicInvoiceTableDataRowCount($filters = array(), $limit = array())
    {
        $startDate = (isset($filters['startDate']) && !empty($filters['startDate']))
            ? $filters['startDate']
            : date('Y-m-d', (strtotime('now') - 86400 * 14) );

        $endDate = date('Y-m-d', strtotime($startDate) + 86400 * 14);

        $invoices = $this->invoiceRepository->getInvoiceNumbersByDateRange($startDate, $endDate );

        return $this->orderRepository->getFilteredOrders($filters, $limit, true, $invoices);
    }

    /**
     * The best debugging method ever conceived!
     */
    public function getStupid()
    {
        /* $query = "SELECT COUNT(*) FROM " . $this->environment['Database']['warehouse']['verizon_locations']; */
        /* $connection = $this->environment['Connection']; */

        /* $customerEntity = $this->orderRepository->createCustomerFromLocationCode('A78'); */
        $orderEnt = $this->orderRepository->createOrderFromOrderKeys('2120','132886561');
        /* $orderLines = $this->orderRepository->createOrderLinesFromOrder($orderEnt); */
        $anInvoice = $this->invoiceRepository->getInvoiceFromOrder($orderEnt);

        // $stuff = $this->invoiceRepository->

        echo "<pre>".print_r($anInvoice, true); // print_r($orderLines, true);
    }

}
