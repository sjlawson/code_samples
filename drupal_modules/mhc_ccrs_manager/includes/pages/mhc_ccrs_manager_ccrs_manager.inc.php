<?php
/**
 * Page callback for page "CCRS Manager"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */

namespace MHCCcrsManager;

use MHCCcrsManager\Presenters\Pages\EditbucketPresenter;

use MHCCcrsManager\DependencyInjection\Pages\CcrsManagerDependencyContainer;
use MHCCcrsManager\Presenters\Pages\CcrsManagerPresenter;

/**
 * Page callback for "CCRS Manager"
 *
 * @return array The render array
 */
function _mhc_ccrs_manager_ccrs_manager()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new CcrsManagerDependencyContainer();
    $ccrsManagerPresenter = $dependencyContainer['CcrsManagerPresenter'];

    // Set devMode parameters.
    if ($ccrsManagerPresenter->getDevMode()) {
        ini_set('html_errors', 'On');
        ini_set('xdebug.var_display_max_depth', 5);
    }

    // Load css.
    drupal_add_css(
        get_module_path() . '/css/mhc_ccrs_manager_styles.css',
        array(
            'type' => 'file',
            'group' => CSS_DEFAULT,
            'every_page' => false)
        );
    
    drupal_add_js(
        get_module_path() . '/js/mhc_ccrs_manager_ccrs_manager.js'    
    );

    $urlParams = $ccrsManagerPresenter->getCcrsFormDefaults();
    
    $pageRenderArray = array(
        '#CcrsManagerForm' => drupal_get_form('MHCCcrsManager\_mhc_ccrs_manager_ccrs_manager_form', $urlParams),
        '#theme' => 'mhc_ccrs_manager_ccrs_manager_page',
    );

    return $pageRenderArray;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param CcrsManagerPreseneter $presenter
 *
 * @return array
 */
function _mhc_ccrs_manager_ccrs_manager_form($form, &$form_state, array $urlParams)
{
    // $form_state['no_cache'] = true;  // Ajax is here
    load_resources();

    // Build composition root.
    $dependencyContainer = new CcrsManagerDependencyContainer();
    $presenter = $dependencyContainer['CcrsManagerPresenter'];
	
	$selectedBucketID = !empty($form_state['values']['buckets']) ? $form_state['values']['buckets'] : '';
	
	$receivablesTable = build_ajax_receivables_table($presenter, $selectedBucketID);
	$payablesTable = build_ajax_payables_table($presenter, $selectedBucketID);
	$previewPanel = '<div id="ccrs_manager_preview">
            		<label>Receivables:</label>'.render($receivablesTable).'
            		<label>Payables:</label>'.render($payablesTable).' 
            		</div>';
	
	$previewPanel = $selectedBucketID ? $previewPanel : '<div id="ccrs_manager_preview"></div>' ;	
    
    return array(
        'ccrs_manager_form_bucket_fieldset' => array(
            '#type' => 'fieldset',
            '#title' => 'List Buckets',
    		'preview' => array(
            	'#type' => 'markup',
            	'#markup' => $previewPanel,
         	),
            'buckets' => array(
                '#title' => t('Select a bucket to view/edit'),
                '#type' => 'select',
                '#options' => $presenter->getCcrsBucketsOptionsArray(), 
                '#empty_option' => '- Select -',
                '#multiple' => false,
                '#size' => 7,
            	'#ajax' => array(
            		'callback' => 'MHCCcrsManager\_mhc_ccrs_manager_ccrs_manager_form_preview_callback',
            		'wrapper' => 'ccrs_manager_preview',
            		'method' => 'replaceWith',
            		'effect' => 'fade'
            	)
            )           
        ),
        'add_new' => array(
            '#type' => 'submit',
            '#value' => t('New Bucket'),
            '#submit' => array('MHCCcrsManager\_mhc_ccrs_manager_ccrs_manager_form_add_new')
        ),
		'submit' => array(
            '#type' => 'submit',
            '#value' => t('View/Edit Bucket'),
        ),
        'export_csv' => array(
        	'#type' => 'submit',
        	'#value' => 'Export CSV',
        	'#submit' => array('MHCCcrsManager\_mhc_ccrs_manager_ccrs_manager_form_export_csv')
        )
    );
}

function _mhc_ccrs_manager_ccrs_manager_form_preview_callback($form, &$form_state)
{
	return $form['ccrs_manager_form_bucket_fieldset']['preview'];	
}

/**
 * 
 * Callback for csv export
 * @param $form
 * @param $form_state
 */
function _mhc_ccrs_manager_ccrs_manager_form_export_csv($form, &$form_state)
{
	do_csv_export();
	
	$form_state['redirect'] = array(CcrsManagerPresenter::getDrupalMenuRouterPath());
}

/**
 * 
 * terminal function for csv export callback 
 */
function do_csv_export()
{
	$fileName = 'crss_buckets_export.csv';
	drupal_add_http_header('Content-Type', 'text/csv; utf-8');
	drupal_add_http_header('Content-Disposition', 'attachment; filename = '.$fileName);
	drupal_add_http_header('Pragma', 'public');
    drupal_add_http_header('Expires', '0');
    drupal_add_http_header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
    
	$dependencyContainer = new CcrsManagerDependencyContainer();
	$presenter = $dependencyContainer['CcrsManagerPresenter'];
	
	$presenter->buildMasterCSV();
	drupal_exit();
}

/**
 * Form Validator
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_ccrs_manager_form_validate($form, &$form_state)
{

}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_ccrs_manager_form_submit($form, &$form_state)
{
    $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(), array('query' => array('bucketID' => $form_state['values']['buckets'])));
}

/**
 *Handler for 'Add New' button
 *
 */
function _mhc_ccrs_manager_ccrs_manager_form_add_new($form, &$form_state) {
	$form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath());
}

/**
 * 
 * Receivable table builder for preview panel
 * @param $presenter
 * @param $bucketID
 * @return array
 */
function build_ajax_receivables_table(CcrsManagerPresenter $presenter, $bucketID)
{
	$previewRowCount = 5;
	return array(
            '#empty' => t('No results'),
            '#header' => $presenter->getBucketReceivablesTableHeader(),
            '#rows' => $presenter->getBucketReceivablesTableRows(array('bucketID' => $bucketID), array('rowCount'=>$previewRowCount,'offset'=>0)),
            '#theme' => 'table'
            );
}

/**
 * 
 * Payable table builder for preview panel
 * @param CcrsManagerPresenter $presenter
 * @param unknown_type $bucketID
 * @return array
 */
function build_ajax_payables_table(CcrsManagerPresenter $presenter, $bucketID)
{
	$previewRowCount = 5;
	return array(
            '#empty' => t('No results'),
            '#header' => $presenter->getBucketPayablesTableHeader(),
            '#rows' => $presenter->getBucketPayablesTableRows(array('bucketID' => $bucketID), array('rowCount'=>$previewRowCount,'offset'=>0)),
            '#theme' => 'table'
            );
}
