<?php
/**
 * Page callback for page "Edit Payable"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-28
 */

namespace MHCCcrsManager;

use MHCCcrsManager\DependencyInjection\Pages\EditPayableDependencyContainer;
use MHCCcrsManager\Presenters\Pages\EditPayablePresenter;
use MHCCcrsManager\Presenters\Pages\EditbucketPresenter;
use MHCCcrsManager\Presenters\Pages\CcrsManagerPresenter;

/**
 * Page callback for "Edit Payable"
 *
 * @return array The render array
 */
function _mhc_ccrs_manager_edit_payable()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new EditPayableDependencyContainer();
    $editPayablePresenter = $dependencyContainer['EditPayablePresenter'];

    // Set devMode parameters.
    if ($editPayablePresenter->getDevMode()) {
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
        get_module_path() . '/js/mhc_ccrs_manager_edit_payable.js'
    );
    $urlParams = $editPayablePresenter->getPayableFormDefaults();

    $pageRenderArray = array(
        '#EditPayableForm' => drupal_get_form('MHCCcrsManager\_mhc_ccrs_manager_edit_payable_form', $urlParams),
        '#theme' => 'mhc_ccrs_manager_edit_payable_page',
    );

    return $pageRenderArray;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param EditPayablePreseneter $presenter
 *
 * @return array
 */
function _mhc_ccrs_manager_edit_payable_form($form, &$form_state, array $urlParams)
{
    // This form uses Ajax
    // $form_state['no_cache'] = true;
    load_resources();
    $dependencyContainer = new EditPayableDependencyContainer();
    $presenter = $dependencyContainer['EditPayablePresenter'];

    $payoutBucketID = !empty($urlParams['payoutBucketID']) ? $urlParams['payoutBucketID'] : null;
    $formData = $payoutBucketID ? $presenter->getPayable($payoutBucketID) : null;
    if($payoutBucketID) {
        $formData['begDate'] = @$formData['begDate']  ? date('m/d/Y', strtotime($formData['begDate'] )) : '';
        $formData['endDate'] = @$formData['endDate']  ? date('m/d/Y', strtotime($formData['endDate'] )) :  '';
    }
    $bucketID = $payoutBucketID ? $formData['bucketID'] : $urlParams['bucketID'];

    // This page is only available if either bucketID or commissionBucketID is set
    if(!$payoutBucketID && !$bucketID) {
        drupal_goto(CcrsManagerPresenter::getDrupalMenuRouterPath());
    }

    $editAction = $payoutBucketID ? t('Edit Payable') : t('Add New Payable');
    $payableCount = 0;
    if($_GET['q']=='system/ajax'){
        $payoutScheduleID = !empty($form_state['values']['payoutScheduleID'])
            ? $form_state['values']['payoutScheduleID'] : '';
    } else {
        $payoutScheduleID = $formData ? $formData['payoutScheduleID'] : null;
    }

    if($payoutScheduleID) {
        $payableCount = $presenter->getPayablesCountForSchedule(
            array(
                'bucketID' => $bucketID,
                'payoutScheduleID' => $payoutScheduleID,
            ));;
    }

    return array(
        'editPayableFieldset' => array(
            '#type' => 'fieldset',
            '#title' => $editAction,
            '#id' => 'editPayableFieldset',

            'payoutBucketID' => array(
                '#type' => 'hidden',
                '#value' => $payoutBucketID,
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
                '#type' => 'textfield',
                '#size' => 25,
                '#default_value' => $formData ? $formData['begDate'] : '',
                '#required' => ($payableCount ? true : false),
                '#description' => ($payableCount ? t('Required: payable exists for the selected Payout Schedule') : ''),
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
            'payoutScheduleID' =>array(
                '#title' => t('Payout Schedule'),
                '#type' => 'select',
                '#default_value' => $formData ?  $formData['payoutScheduleID'] : null,
                '#empty_value' => '- select -',
                '#required' => true,
                '#options' => $presenter->getPayoutScheduleOptionsArray(),
                '#ajax' => array(
                    'callback' => 'MHCCcrsManager\mhc_ccrs_manager_edit_payable_form_payoutSchedule_callback',
                    'wrapper' => 'editPayableFieldset',
                    'method' => 'replaceWith',
                    'effect' => 'fade'
                )
            ),
            'empSpiff' => array(
                '#type' => 'hidden',
                '#value' => $formData['empSpiff'] ,
        )
    ), // end fieldset
        'cancel' => array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#limit_validation_errors' => array(),
            '#submit' => array('MHCCcrsManager\mhc_ccrs_manager_edit_payable_form_cancel')
        ),
        'submit' => array(
            '#type' => 'submit',
            '#value' => t('Submit'),
        ),
    );
}

function mhc_ccrs_manager_edit_payable_form_payoutSchedule_callback($form, &$form_state)
{
    return $form['editPayableFieldset'];
}

/**
 *
 * Redirect back to Bucket edit page
 * @param unknown_type $form
 * @param unknown_type $form_state
 */
function mhc_ccrs_manager_edit_payable_form_cancel($form, &$form_state)
{
    $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(), array('query' => array('bucketID' => $form_state['input']['bucketID'])));
}


/**
 * Form Validator
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_edit_payable_form_validate($form, &$form_state)
{
    $form_state['input']['amount'] = preg_replace('/\$?([\d]{1,}\.\d{2})/', '$1', $form_state['input']['amount']);
    $form_state['input']['adSpiff'] = preg_replace('/\$?([\d]{1,}\.\d{2})/', '$1', $form_state['input']['adSpiff']);

    /* for the moment, disable the 30-day limit rule. Uncomment to re-instate disallow editing if begDate is earlier than 30 prior to now
    // Ascertain that begDate (if not null) is not more than 30 days prior to now
    $begDt = new \DateTime($form_state['input']['begDate'], new \DateTimeZone('EST'));
    $nowDt = new \DateTime('now', new \DateTimeZone('EST'));
    $nowLess30d = $nowDt->sub(new \DateInterval('P30D'))->format('Y-m-d');

    $goodDate = ($begDt->format('Y-m-d') < $nowLess30d) ? false : true; // for clarity
    if(!empty($form_state['input']['begDate']) && !$goodDate) {
        form_set_error('begDate', 'Begin date cannot be more than 30 days before today: '.$begDt->format('Y-m-d').' < '.$nowLess30d);

    } else
    */

    if($form_state['input']['begDate'] > $form_state['input']['endDate']
        && !(empty($form_state['input']['begDate']) || empty($form_state['input']['endDate']) ) ) {

        form_set_error('begDate', t('Begin Date must be < End Date, or either one must be empty'));

    } else {
        // Get the presenter.
        load_resources();
        $dependencyContainer = new EditPayableDependencyContainer();
        $presenter = $dependencyContainer['EditPayablePresenter'];

        $validateDates = $presenter->validatePayable($form_state['input']);
        if(is_array($validateDates)) {
            form_set_error('begDate', t($validateDates['message']));
        }
    }
}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_edit_payable_form_submit($form, &$form_state)
{
    // Get the presenter.
    load_resources();
    $dependencyContainer = new EditPayableDependencyContainer();
    $presenter = $dependencyContainer['EditPayablePresenter'];

    $bucketID = $form_state['input']['bucketID'];
    $payoutBucketID = $form_state['input']['payoutBucketID'];


    $errRedirectQuery = $payoutBucketID ? array('query'=>array('payoutBucketID'=>$form_state['input']['payoutBucketID']))
            : array('query'=>array('bucketID'=>$form_state['input']['bucketID']));

    $result = $presenter->savePayable($form_state['input']);

    if(is_array($result)) {
        drupal_set_message(t($result['message']), $result['result']);
        $form_state['redirect'] = array(EditPayablePresenter::getDrupalMenuRouterPath(), $errRedirectQuery);
    } elseif($result) {
        drupal_set_message(t('Payable Save Success'), 'status');
        $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(),
                                                        array('query'=>array('bucketID'=>$form_state['input']['bucketID'])));
    } else {
        drupal_set_message(t('Save Error'), 'error');
    }
}
