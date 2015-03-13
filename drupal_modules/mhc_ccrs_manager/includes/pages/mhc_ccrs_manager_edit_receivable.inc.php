<?php
/**
 * Page callback for page "Edit Receivable"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-28
 */

namespace MHCCcrsManager;

use MHCCcrsManager\Presenters\Pages\CcrsManagerPresenter;
use MHCCcrsManager\DependencyInjection\Pages\EditReceivableDependencyContainer;
use MHCCcrsManager\Presenters\Pages\EditReceivablePresenter;
use MHCCcrsManager\Presenters\Pages\EditbucketPresenter;

/**
 * Page callback for "Edit Receivable"
 *
 * @return array The render array
 */
function _mhc_ccrs_manager_edit_receivable()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new EditReceivableDependencyContainer();
    $editReceivablePresenter = $dependencyContainer['EditReceivablePresenter'];

    // Set devMode parameters.
    if ($editReceivablePresenter->getDevMode()) {
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
        get_module_path() . '/js/mhc_ccrs_manager_edit_receivable.js'
    );

    $urlParams = $editReceivablePresenter->getReceivableFormDefaults();

    $pageRenderArray = array(
        '#editReceivableForm' => drupal_get_form('MHCCcrsManager\_mhc_ccrs_manager_edit_receivable_form', $editReceivablePresenter, $urlParams),
        '#theme' => 'mhc_ccrs_manager_edit_receivable_page',
    );

    return $pageRenderArray;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param EditReceivablePreseneter $presenter
 *
 * @return array
 */
function _mhc_ccrs_manager_edit_receivable_form($form, &$form_state, EditReceivablePresenter $presenter, array $urlParams)
{
    // We are removing form caching, since closures (and hence our DI containers
    // and therefore our presenters) are not serializable.  This will also stop
    // annoying "Serialization of 'Closure'" exceptions in Drupal.
    $form_state['no_cache'] = true;

    $commissionBucketID = !empty($urlParams['commissionBucketID']) ? $urlParams['commissionBucketID'] : null;
    $formData = $commissionBucketID ? $presenter->getCcrsBuceketCommissionBucketsRecord($commissionBucketID) : null;
    if($commissionBucketID) {
       $formData['begDate'] = $formData['begDate']  ? date('m/d/Y', strtotime($formData['begDate'] )) : '';
       $formData['endDate'] = $formData['endDate']  ? date('m/d/Y', strtotime($formData['endDate'] )) :  '';
    }

    $bucketID = $commissionBucketID ? $formData['bucketID'] : $urlParams['bucketID'];

    // This page is only available if either bucketID or commissionBucketID is set
    if(!$commissionBucketID && !$bucketID) {
        drupal_goto(CcrsManagerPresenter::getDrupalMenuRouterPath());
    }

    $editAction = $commissionBucketID ? t('Edit receivable') : t('Add new receivable');

    //check if other receivables exist for this bucket, if so, begDate is a required field
    $receivableCount = $presenter->getBucketReceivablesCount(
        array('bucketID' => $bucketID, 'commissionBucketID' => $commissionBucketID )
    );

    return array(
        'editReceivableFieldset' => array(
            '#type' => 'fieldset',
            '#title' => $editAction,

            'commissionBucketID' => array(
                '#type' => 'hidden',
                '#value' => $commissionBucketID,
            ),
            'bucketID' => array(
                '#type' => 'hidden',
                '#value' => $bucketID,
            ),
                        'addedOn' => array(
                                    '#type' => 'hidden',
                                    '#value' => $formData ? $formData['addedOn'] : null
                        ),
            'begDate' => array(
                '#title' => t('Begin Date'),
                '#description' => $receivableCount ? t('Required when another receivable exists') : '',
                '#type' => 'textfield',
                '#size' => 25,
                '#default_value' => $formData ? $formData['begDate'] : '',
                '#required' => $receivableCount ? true : false,
                '#prefix' => '<div class="datepicker_field">',
                '#suffix' => '</div>'
            ),
            'endDate' => array(
                '#title' => t('End Date'),
                '#type' => 'textfield',
                '#size' => 25,
                '#default_value' => $formData ? $formData['endDate'] : '',
                '#prefix' => '<div class="datepicker_field">',
                '#suffix' => '</div>'
            ),
            'amount' => array(
                '#title' => t('Amount'),
                '#type' => 'textfield',
                '#field_prefix' => '$',
                '#size' => 25,
                '#default_value' => $formData ? money_format('%i', $formData['amount']) : '0.00',
            ),
            'adSpiff' => array(
                '#title' => t('AD-Spiff'),
                '#type' => 'textfield',
                '#field_prefix' => '$',
                '#size' => 25,
                '#default_value' => $formData ? money_format('%i', $formData['adSpiff']) : '0.00',
            ),
        ),
        'cancel' => array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors' => array(),
            '#submit' => array('MHCCcrsManager\mhc_ccrs_manager_edit_receivable_form_cancel')
        ),
        'submit' => array(
            '#type' => 'submit',
            '#value' => t('Submit'),
        ),
    );
}

/**
 *
 * Redirect back to Bucket edit page
 * @param unknown_type $form
 * @param unknown_type $form_state
 */
function mhc_ccrs_manager_edit_receivable_form_cancel($form, &$form_state)
{
    $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(), array('query' => array('bucketID' => $form_state['input']['bucketID'])));
}

/**
 * Form Validator
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_edit_receivable_form_validate($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];

    $form_state['input']['amount'] = preg_replace('/\$?([\d]{1,}\.\d{2})/', '$1', $form_state['input']['amount']);
    $form_state['input']['adSpiff'] = preg_replace('/\$?([\d]{1,}\.\d{2})/', '$1', $form_state['input']['adSpiff']);

    /* for the moment, disable the 30-day limit rule
    // Ascertain that begDate (if not null) is not more than 30 days prior to now
    $begDt = new \DateTime($form_state['input']['begDate'], new \DateTimeZone('EST'));
    $nowDt = new \DateTime('now', new \DateTimeZone('EST'));
    $nowLess30d = $nowDt->sub(new \DateInterval('P30D'))->format('Y-m-d');

    $goodDate = ($begDt->format('Y-m-d') < $nowLess30d) ? false : true; // for clarity
    if(!empty($form_state['input']['begDate']) && !$goodDate) {
        form_set_error('begDate', 'Begin date cannot be more than 30 days before today: '.$begDt->format('Y-m-d').' < '.$nowLess30d);

    } elseif...
    */

    if($form_state['input']['begDate'] > $form_state['input']['endDate']
        && !(empty($form_state['input']['begDate']) || empty($form_state['input']['endDate']) ) ) {
                form_set_error('begDate', t('Begin Date must be < End Date, or either one must be empty'));
    } elseif(empty($form_state['input']['begDate'])) {
        // maybe redundant
        $receivableCount = $presenter->getBucketReceivablesCount( array(
                               'bucketID' => $form_state['input']['bucketID'],
                               'commissionBucketID' => $form_state['input']['commissionBucketID']
                           )
        );
        if($receivableCount > 0) {
            form_set_error('begDate', t('Begin Date Required if recevable exists for bucket'));
        }
    } else {
        // Check that proposed entry begDate does not match an existing entry begDate
        $validationCheck = $presenter->validateReceivable($form_state['input']);
        if(is_array($validationCheck)) {
            form_set_error('begDate', t($validationCheck['message']) );
        }
    }
}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_edit_receivable_form_submit($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];
    $bucketID = $form_state['input']['bucketID'];
    $commissionBucketID = $form_state['input']['commissionBucketID'];

    $errRedirect = $commissionBucketID ? array('query'=>array('commissionBucketID'=>$form_state['input']['commissionBucketID']))
            : array('query'=>array('bucketID'=>$form_state['input']['bucketID']));

    $result = $presenter->saveReceivable($form_state['input']);

    if(is_array($result)) {
        drupal_set_message(t($result['message']), $result['result']);
        $form_state['redirect'] = array(EditReceivablePresenter::getDrupalMenuRouterPath(), $errRedirect);
    } elseif($result) {
        drupal_set_message(t('Receivable Save Success'), 'status');
        $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(),
                                                        array('query'=>array('bucketID'=>$form_state['input']['bucketID'])));
    } else {
        drupal_set_message(t('Save Error'), 'error');
    }

}

