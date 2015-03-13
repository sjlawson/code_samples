<?php
/**
 * Page callback for page "Revive Usage Data"
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-07
 */

namespace ReviveUsageData;

use ReviveUsageData\DependencyInjection\Pages\ReviveUsageDataDependencyContainer;
use ReviveUsageData\Presenters\Pages\ReviveUsageDataPresenter;

/**
 * Page callback for "Revive Usage Data"
 *
 * @return array The render array
 */
function _revive_usage_data_revive_usage_data()
{
    load_resources();
    global $base_url;

    // Build composition root.
    $dependencyContainer = new ReviveUsageDataDependencyContainer();
    $reviveUsageDataPresenter = $dependencyContainer['ReviveUsageDataPresenter'];

    // Set devMode parameters.
    if ($reviveUsageDataPresenter->getDevMode()) {
        ini_set('html_errors', 'On');
        ini_set('xdebug.var_display_max_depth', 5);
    }

    // Load css.
    drupal_add_css(
        get_module_path() . '/css/revive_usage_data.css',
        array(
            'type' => 'file',
            'group' => CSS_DEFAULT,
            'every_page' => false)
        );

    // inject correct ajax view path into javascript
    drupal_add_js('var DRUPAL_PATH="'
        . $base_url . '/' .
        \ReviveUsageData\Presenters\Pages\ReviveDataAjaxCallbackPresenter::getDrupalMenuRouterPath() . '";
        var MODULE_PATH = "'. $base_url .'/'. drupal_get_path('module','revive_usage_data') . '";'
        ,'inline');

    drupal_add_js(
        get_module_path() . '/js/revive_lightbox.js',
        array(
            'type' => 'file',
            'group' => JS_DEFAULT,
            'every_page' => false
        )
    );

    drupal_add_js(
        get_module_path() . '/js/revive_usage_data.js',
        array(
            'type' => 'file',
            'group' => JS_DEFAULT,
            'every_page' => false
        )
    );

    $urlParams = $reviveUsageDataPresenter->getUsageDataUrlParams();
    $usageDataTable = _revive_get_usage_data_table($reviveUsageDataPresenter, $urlParams);
    $successfulReviveFilterArray = $urlParams;
    $successfulReviveFilterArray['key_filters'][] = 'REDS_OUT_Device_ReviveSuccessful<>1';
    $pageRenderArray = array(
        '#ReviveUsageDataForm' => drupal_get_form(
            'ReviveUsageData\_revive_usage_data_revive_usage_data_form',
            $reviveUsageDataPresenter,
            $urlParams
        ),
        '#reviveUsageDataTable' => array(
            array(
                '#empty' => t('No results'),
                '#header' => $usageDataTable['header'],
                '#rows' => $usageDataTable['rows'],
                '#theme' => 'table'
            ),
            array(
                '#theme' => 'pager',
                '#element' => 0
            ),
        ),
        '#theme' => 'revive_usage_data_revive_usage_data_page',
        '#dataCount' => $reviveUsageDataPresenter->getUsageDataTableCount($urlParams),
        '#successfulRevives' => $reviveUsageDataPresenter->getUsageDataTableCount($successfulReviveFilterArray),
    );

    return $pageRenderArray;
}

/**
 * Fetch data and return formatted for Drupal table
 * @return array { header:array(), rows: array(array()) }
 */
function _revive_get_usage_data_table(ReviveUsageDataPresenter $presenter, array $urlParams)
{
    $table['header'] = $presenter->getUsageDataTableHeaders();
    $table['rows'] = $presenter->getUsageDataTableRows(
        $urlParams,
        _revive_get_usage_data_pager($presenter, $urlParams)
    );

    return $table;
}

/**
 * Usage data table pager builder
 * @return array { rowCount:int, offset:int }
 */
function _revive_get_usage_data_pager(ReviveUsageDataPresenter $presenter, array $urlParams)
{
    // Init pager
    $limit['rowCount'] = 20;
    $page = pager_default_initialize($presenter->getUsageDataTableCount($urlParams), $limit['rowCount'], 0);
    $limit['offset'] = $limit['rowCount'] * $page;

    return $limit;
}

function getUrlParamKeyPairs($urlParams)
{
    $keyPairs = array();
    if (!empty($urlParams['key_filters'])) {
        foreach ($urlParams['key_filters'] as $filterPair) {
            $keyPairs[$filterPair] = $filterPair;
        }
    }

    return $keyPairs;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param ReviveUsageDataPreseneter $presenter
 *
 * @return array
 */
function _revive_usage_data_revive_usage_data_form($form, &$form_state, ReviveUsageDataPresenter $presenter, $urlParams)
{
    // We are removing form caching, since closures (and hence our DI containers
    // and therefore our presenters) are not serializable.  This will also stop
    // annoying "Serialization of 'Closure'" exceptions in Drupal.
    $form_state['no_cache'] = true;

    return array(
        'revive_usage_data_form_container' => array(
            '#type' => 'container',
            '#attributes' => array(
                'class' => array('revive_usage_form_container'),
            ),

            'revive_usage_data_business_key_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('By Business Key')
                        . '<div class="clearbutton-container">
                        <a class="button fieldset-button" id="revive-clear-business-key-button" >'
                        . t('Clear') . '</a></div>',

                'revive_usage_businesskey_filter_container' => array(
                    '#type' => 'container',
                    '#attributes' => array(
                        'class' => array(
                            'businesskey-filter-containter'
                        )
                    ),

                    'revive_business_key' => array(
                        '#type' => 'select',
                        '#title' => 'Key Name',
                        '#options' => $presenter->getBusinessKeysOptionsList(),
                        '#default_value' => isset($urlParams['processName']) ? $urlParams['processName']  : '',
                        '#multiple' => false,
                        '#size' => 7,
                    ),

                    'revive_business_key_value' => array(
                        '#type' => 'select',
                        '#title' => 'Value',
                        '#options' => $presenter->getDistinctBusinessKeyValuesOptionsList($urlParams),
                        '#default_value' => $urlParams['processValue'],
                        '#multiple' => false,
                        '#size' => 7,
                        '#validated' => TRUE,
                        '#suffix' => '<div id="edit-revive-business-key-value-loading"></div>',
                    ),
                ),

                'add_key_pair_to_queue' => array(
                    '#type' => 'markup',
                    '#markup' => '<a class="button queue-add-remove" id="btn-add-selected-to-queue">Add to Queue</a>',
                ),
            ),

            'clear_markup' => array(
                '#type' => 'markup',
                '#markup' => '<div class="clearfix"></div>'
            ),

            'revive_business_key_queue_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('Business Key Filter Queue'),

                'business_key_queue' => array(
                    '#type' => 'select',
                    '#title' => t('&nbsp;'),
                    '#options' => getUrlParamKeyPairs($urlParams),
                    '#multiple' => true,
                    '#size' => 7,
                    '#validated' => true,
                ),

                'remove_key_pair_from_queue' => array(
                    '#type' => 'markup',
                    '#markup' => '<a class="button queue-add-remove" id="btn-remove-selected-from-queue">Remove</a>',
                ),
            ),

            'clear_markup_2' => array(
                '#type' => 'markup',
                '#markup' => '<div class="clearfix"></div>'
            ),

            'revive_usage_data_machine_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('By Machine') .
                        '<div class="clearbutton-container">
                         <a class="button fieldset-button" id="revive-clear-machine-button" >'
                        . t('Clear') . '</a></div>',

                'revive_machine_id' => array(
                    '#type' => 'select',
                    '#title' => t(''),
                    '#options' => $presenter->getMachinesOptionList(),
                    '#default_value' => isset($urlParams['machineID']) ? $urlParams['machineID'] : '',
                    '#multiple' => true,
                    '#size' => 7,
                )
            ),

            'revive_usage_data_location_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('By Location')
                        . '<div class="clearbutton-container">
                        <a class="button fieldset-button" id="revive-clear-location-button" >'
                        . t('Clear').'</a></div>',

                'revive_location_id' => array(
                    '#type' => 'select',
                    '#title' => '',
                    '#options' => $presenter->getLocationsOptionList(),
                    '#default_value' => isset($urlParams['locationID']) ? $urlParams['locationID']  : '',
                    '#multiple' => true,
                    '#size' => 7,
                )
            ),

            'revive_usage_data_date_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('By Date')
                        . '<div class="clearbutton-container">
                        <a class="button fieldset-button" id="revive-clear-dates-button" >'
                        . t('Clear').'</a></div>',

                'revive_start_date' => array(
                    '#type' => 'textfield',
                    '#title' => 'From:',
                    '#size' => 20,
                    '#default_value' => $urlParams['start_date']
                ),

                'revive_end_date' => array(
                    '#type' => 'textfield',
                    '#title' => 'To:',
                    '#size' => 20,
                    '#default_value' => $urlParams['end_date']
                )
            ),

            'revive_usage_data_machine_configuration_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('By Configuration')
                        . '<div class="clearbutton-container">
                        <a class="button fieldset-button" id="revive-clear-configurationsID-button" >'
                        . t('Clear') . '</a></div>',

                'configurationsID' => array(
                    '#type' => 'select',
                    '#options' => $presenter->getConfigurationsOptionsList(),
                    '#default_value' => $urlParams['configurationsID'],
                    '#multiple' => true,
                    '#size' => 7,
                )

            ),

            'clear_markup20' => array(
                '#type' => 'markup',
                '#markup' => '<div class="clearfix"></div>'
            ),

            'ioExport' => array(
                '#type' => 'submit',
                '#value' => t('Export IN/OUT'),
                '#submit' => array('ReviveUsageData\_revive_usage_data_ioExport_submit'),
            ),

            'submit' => array(
                '#type' => 'submit',
                '#value' => t('Filter'),
            ),
        )
    );
}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _revive_usage_data_revive_usage_data_form_submit($form, &$form_state)
{
    $urlParams = getParamFilter($form_state);

    // Redirect the user to same page with updated query parameters
    $form_state['redirect'] = array(ReviveUsageDataPresenter::getDrupalMenuRouterPath(),
                              array('query' => $urlParams ));
}

function _revive_usage_data_ioExport_submit($form, &$form_state)
{
    $presenter = $form_state['build_info']['args'][0];
    $urlParams = getParamFilter($form_state);

    $presenter->exportIOData($urlParams);
}

function getParamFilter(&$form_state)
{
    $urlParams = array();

    if (!empty($form_state['values']['business_key_queue'])) {
        $urlParams['key_filters'] = array_values($form_state['input']['business_key_queue']);
    }

    if (!empty($form_state['input']['revive_machine_id'])) {
        $urlParams['machineID'] = $form_state['input']['revive_machine_id'];
    }

    if (!empty($form_state['input']['revive_business_key'])) {
        $urlParams['processName'] = $form_state['input']['revive_business_key'];
    }

    if (!empty($form_state['input']['revive_business_key_value'])) {
        $urlParams['processValue'] = $form_state['input']['revive_business_key_value'];
    }

    if (!empty($form_state['input']['configurationsID'])) {
        $urlParams['configurationsID'] = $form_state['input']['configurationsID'];
    }

    if (!empty($form_state['input']['revive_location_id'])) {
        $urlParams['locationID'] = $form_state['input']['revive_location_id'];
    }

    if (!empty($form_state['input']['revive_start_date'])) {
        $urlParams['start_date'] = $form_state['input']['revive_start_date'];
    }

    if (!empty($form_state['input']['revive_end_date'])) {
        $urlParams['end_date'] = $form_state['input']['revive_end_date'];
    }

    return $urlParams;
}