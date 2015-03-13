<?php

namespace IconicsInvoicing;

use IconicsInvoicing\DependencyInjection\Pages\IconicDynamicsInvoicingDependencyContainer;
use IconicsInvoicing\Presenters\Pages\IconicDynamicsInvoicingPresenter;

/**
 * Page callback for "Iconic Dynamics Invoicing"
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-12-15
 *
 * @return array The render array
 */
function _iconics_invoicing_iconic_dynamics_invoicing()
{
    load_resources();
    global $base_url;

    // Build composition root.
    $dependencyContainer = new IconicDynamicsInvoicingDependencyContainer();
    $presenter = $dependencyContainer['IconicDynamicsInvoicingPresenter'];
    $urlParams = $presenter->getUrlParams($dependencyContainer['QueryParameters']);

    if(isset($urlParams['jsonAction']) && method_exists( $presenter,$urlParams['jsonAction'])) {

        $invoiceDate = isset($urlParams['invoiceDate'])
            ? date('Y-m-d', strtotime($urlParams['invoiceDate']))
            : null;

        $ordersData = isset($_POST['ordersData']) ? $_POST['ordersData'] : null;

        echo _iconics_invoicing_handle_ajax(
            $urlParams['jsonAction'],
            $urlParams['jsonActionData'],
            $presenter,
            $invoiceDate,
            $ordersData
        );

        exit();
    }

    // Set title.
    drupal_set_title('Iconic Dynamics Invoicing');

    // Load css.
    drupal_add_css(
        get_module_path() . '/css/iconics_invoicing_iconic_dynamics_invoicing.css',
        array(
            'type'       => 'file',
            'group'      => CSS_DEFAULT,
            'every_page' => false
        )
    );

    // inject correct ajax view path into javascript

    drupal_add_js(
        'var DRUPAL_PATH="' . $base_url . '/' . $presenter->getDrupalMenuRouterPath() . '";',
        'inline'
    );

    drupal_add_js(
        'var URL_QUERY="' . $_SERVER['QUERY_STRING']  . '";',
        'inline'
    );

    // Load js.
    drupal_add_js(
        get_module_path() . '/js/iconics_invoicing_iconic_dynamics_invoicing.js',
        array(
            'type'       => 'file',
            'group'      => JS_DEFAULT,
            'every_page' => false
        )
    );

    $iconicsInvoiceTable = _iconics_invoicing_invoice_order_table($presenter, $urlParams);

    // Build the render array.
    $renderArray = array(
        '#btnProcessSelected' => array(
            '#type' => 'markup',
            '#markup' => "<a id='btn-process-selected-orders' class='button'>Process Selected Orders</a>",
        ),
        '#iconicsInvoiceTable' => array(
            array(
                '#header' => $iconicsInvoiceTable['header'],
                '#rows' => $iconicsInvoiceTable['rows'],
                '#theme' => 'table',
                '#empty' => t('No results'),
            ),
            array(
                '#theme' => 'pager',
                '#element' => 0
            ),
        ),
        '#iconicDynamicsInvoicingForm' => drupal_get_form('IconicsInvoicing\_iconics_invoicing_iconic_dynamics_invoicing_form', $presenter, $urlParams),
        '#theme' => 'iconics_invoicing_iconic_dynamics_invoicing_page',
    );

    return $renderArray;
}

function _iconics_invoicing_invoice_order_table($presenter, $urlParams)
{
    return array(
        'header' => $presenter->getIconicInvoiceTableHeader(),
        'rows' => $presenter->getIconicInvoiceTableDataRows(
            $urlParams,
            _iconics_invoicing_orders_pager($presenter, $urlParams)
        ),
    );
}

function _iconics_invoicing_orders_pager($presenter, $urlParams)
{
    $limit['rowCount'] = 50;
    $page = pager_default_initialize(
        $presenter->getIconicInvoiceTableDataRowCount($urlParams),
        $limit['rowCount'], 0);
    $limit['offset'] = $limit['rowCount'] * $page;

    return $limit;
}

/**
 * Form Builder
 *
 * @return array
 */
function _iconics_invoicing_iconic_dynamics_invoicing_form($form, &$form_state, IconicDynamicsInvoicingPresenter $presenter, $urlParams)
{
    return array(
        'iconics_invoice_filter_fieldset' => array(
            '#type' => 'fieldset',
            '#title' => t('Filter'),
            '#collapsible' => true,
            'container1' => array(
                '#type' => 'container',
                '#attributes' => array('class'=>array('iconicFilterControl')),

                'startDate' => array(
                    '#title' => t('Start Date'),
                    '#description' => t(' - (System polls in two week intervals)'),
                    '#default_value' => !empty($urlParams['startDate']) ? $urlParams['startDate'] : '', // date('Y-m-d', strtotime('now') - 86400 * 14),
                    '#type' => 'textfield',
                    '#size' => 30,
                ),
            ),

            'container2' => array(
                '#type' => 'container',
                '#attributes' => array('class'=>array('iconicFilterControl')),
                'invoiceStatus' => array(
                    '#type' => 'checkboxes',
                    '#title' => t('Invoice Status'),
                    '#options' => array(
                        'notReady' => t('Not Ready'),
                        'ready' => t('Ready'),
                        'invoiced' => t('Invoiced'),
                    ),
                    '#default_value' => !empty($urlParams['invoiceStatus']) ? $urlParams['invoiceStatus'] : array('notReady'=>'notReady', 'ready'=>'ready','invoiced'=>'invoiced'),
                ),
            ),
            'clearfixtag' => array(
                '#type' => 'markup',
                '#markup' => '<div class="clearfix"></div>'
            ),
            'searchOrderID' => array(
                '#type' => 'textfield',
                '#title' => t('Search by Order ID / POS'),
                '#description' => t('- Enter full or partial Invoice ID/POS'),
                '#default_value' => !empty($urlParams['searchOrderID']) ? $urlParams['searchOrderID'] : '',
                '#size' => 30,
            ),

            'iconics_invoice_filter_button_container' => array(
                '#type' => 'container',
                '#attributes' => array('class'=>array('iconics-filter-button-container')),

                'submit' => array(
                    '#type'  => 'submit',
                    '#value' => t('Filter'),
                ),
                'reset' => array(
                    '#type' => 'submit',
                    '#value' => t('Reset'),
                    '#submit' => array('\IconicsInvoicing\_iconics_invoicing_filter_form_reset'),
                    '#limit_validation_errors' => array(),
                ),
            )
        ),
    );
}

/**
 * Form Validator
 */
function _iconics_invoicing_iconic_dynamics_invoicing_form_validate($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];
}

/**
 * Form Submit Handler
 */
function _iconics_invoicing_iconic_dynamics_invoicing_form_submit($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];

    // Redirect the user.
    $form_state['redirect'] = array($presenter->getDrupalMenuRouterPath(),
                              array('query' => $form_state['values'])
    );
}

function _iconics_invoicing_filter_form_reset($form, &$form_state)
{
    $presenter = $form_state['build_info']['args'][0];

    $form_state['redirect'] = array($presenter->getDrupalMenuRouterPath(),
                              array('query' => array())
    );
}

function _iconics_invoicing_handle_ajax($action, $data, $presenter, $invoiceDate = null, $ordersData = null)
{
    setlocale(LC_MONETARY, 'en_US');
    switch ($action) {
        case "getInvoiceByOrderNum" :

            $data = $presenter->getInvoiceByOrderNum($data);
            _create_invoice_view_from_data($data);

            break;
        case "displayOrderToInvoice" :
            $data = $presenter->displayOrderToInvoice($data);
            _create_invoice_view_from_data($data);
            //dump($data);
            break;
        case "processSelectedOrders" :
            $ret = $presenter->processSelectedOrders($ordersData);
            if(is_array($ret) && array_key_exists('error', $ret)) {

                echo "<h3>There was a problem processing your request</h3><p>" . $ret['error'] . "</p>";
                if (array_key_exists('entityTable', $ret)) {
                    echo $ret['entityTable'];
                }

            } else {
                drupal_set_message("Invoice(s) processed successfully!");
                echo 'true';
            }

            break;
        case "processInvoice" :
            $ret = $presenter->processInvoice($data, $invoiceDate);
            if(is_array($ret) && array_key_exists('error', $ret)) {

                echo "<h3>There was a problem processing your request</h3><p>" . $ret['error'] . "</p>";
                if (array_key_exists('entityTable', $ret)) {
                    echo $ret['entityTable'];
                }
            } else {
                drupal_set_message("Invoice processed successfully!");
                echo 'true';
            }

            break;
    }

    exit();
}

function _create_invoice_view_from_data($data)
{
    list($mhcOrderNum, $approvalNum) = explode('-', $data['invoiceData']['orderNumber']);

    echo "<div class='customer-view'>
                  <table class='invoice-view-customerdata'>";
    echo "<tr><th>Customer ID</th><td>" . $data['customerData']['storeNumber'] . "</td></tr>";
    echo "<tr><th>Customer PO Num</th><td>DF" . $mhcOrderNum . "</td></tr>";
    echo "<tr><th>Customer Name</th><td>". $data['customerData']['name'] . "</td></tr>";
    echo "<tr><th>Address</th><td>".$data['customerData']['address'] . "</td></tr>
                  </table></div>";

    echo "<div class='invoice-view'>";
    echo "<table class='invoice-view-invoicedata'>";
    echo "<tr><th>Order Number</th><td>" . $data['invoiceData']['orderNumber'] . "</td></tr>";
    echo "<tr><th>Invoice Date</th><td>" . date('m/d/Y', strtotime($data['invoiceData']['invoiceDate'])) . "</td></tr>";
    echo "<tr><th>BatchID</th><td>" . $data['invoiceData']['batchID'] . "</td><tr>";
    echo "<tr><th>SiteID</th><td>". $data['invoiceData']['siteID'] . "</td></tr>";

    echo "</table></div><div style='clear: both;'></div><br />";

    if(!empty($data['invoiceLines'])) {
        echo "<table class='invoice-view-invoicelines'><tr>";
        //echo "<th>Invoice Number</th>";
        echo "<th>Item Number</th>
              <th>Item Quantity</th>
              <th>Unit Price</th>
              <th>Extended Price</th></tr>";
        $total = 0;
        foreach ($data['invoiceLines'] as $invoiceLine) {
            echo "<tr>";
            //echo "<td>" . $invoiceLine['invoiceNumber'] . "</td>";
            echo "<td>" . $invoiceLine['itemNumber'] . "</td>";
            echo "<td>" . $invoiceLine['itemQuantity'] . "</td>";
            echo "<td> $" . money_format('%i', $invoiceLine['unitPrice']) . "</td>";
            echo "<td> $" . money_format('%i', $invoiceLine['extendedPrice']) . "</td>";
            echo "</tr>";
            $total += $invoiceLine['extendedPrice'];
        }

        echo "</table>";
        echo "<div style='text-align: right; margin: 15px'>TOTAL: $" . money_format('%i', $total) . "</div>";

        echo "<br /><div class='invoice-notes'><label>Invoice Notes:</label><p id='invoice-notes-display'><pre>";
        echo $data['invoiceData']['invoiceNotes'];
        echo "</pre></p></div>";

    }

}