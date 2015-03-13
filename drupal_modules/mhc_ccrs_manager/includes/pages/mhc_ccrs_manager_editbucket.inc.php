<?php
/**
 * Page callback for page "editbucket"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-26
 */

namespace MHCCcrsManager;

use MHCCcrsManager\Presenters\Pages\CcrsManagerPresenter;
use MHCCcrsManager\DependencyInjection\Pages\EditbucketDependencyContainer;
use MHCCcrsManager\Presenters\Pages\EditbucketPresenter;

/**
 * Page callback for "editbucket"
 *
 * @return array The render array
 */
function _mhc_ccrs_manager_editbucket()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new EditbucketDependencyContainer();
    $editbucketPresenter = $dependencyContainer['EditbucketPresenter'];

    // Set devMode parameters.
    if ($editbucketPresenter->getDevMode()) {
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
        get_module_path() . '/js/mhc_ccrs_manager_editbucket.js'
    );

    $urlParams = $editbucketPresenter->getBucketFormDefaults();
    $bucketReceivablesTable = _mhc_ccrs_manager_bucket_receivables_table($editbucketPresenter, $urlParams);
    $bucketPayablesTable = _mhc_ccrs_manager_bucket_payables_table($editbucketPresenter, $urlParams);

    $pageRenderArray = array(
        '#EditbucketForm' => drupal_get_form('MHCCcrsManager\_mhc_ccrs_manager_editbucket_form', $editbucketPresenter, $urlParams),
        '#BucketReceivablesTable' => array(
            array(
                '#empty' => t('No results'),
                '#header' => $bucketReceivablesTable['header'],
                '#rows' => $bucketReceivablesTable['rows'],
                '#theme' => 'table'
                ),
            array(
                '#theme' => 'pager',
                '#element' => 0
            )
        ),
        '#BucketPayablesTable' => array(
            array(
                '#empty' => t('No results'),
                '#header' => $bucketPayablesTable['header'],
                '#rows' => $bucketPayablesTable['rows'],
                '#theme' => 'table'
            ),
            array(
                '#theme' => 'pager',
                '#element' => 1
            )
        ),
        '#theme' => 'mhc_ccrs_manager_editbucket_page',
        '#bucketID' => ( array_key_exists('bucketID', $urlParams) ? $urlParams['bucketID'] : false )
    );

    return $pageRenderArray;
}

/**
 * Table builder for this bucket's payables
 *
 */
function _mhc_ccrs_manager_bucket_payables_table(EditBucketPresenter $presenter, array $urlParams)
{
    return array(
        'header' => $presenter->getBucketPayablesTableHeader(),
        'rows' => $presenter->getBucketPayablesTableRows($urlParams,
                  _mhc_ccrs_manager_bucket_payables_pager($presenter, $urlParams))
        );
}

/**
 * Payables Pager Builder
 *
 */
function _mhc_ccrs_manager_bucket_payables_pager(EditBucketPresenter $presenter, array $urlParams)
{
    // Initialize Pager
    $limit['rowCount'] = variable_get('mhc_ccrs_manager_payables_num_per_page', 10);
    $page = pager_default_initialize($presenter->getBucketPayablesCount($urlParams), $limit['rowCount'], 1);
    $limit['offset'] = $limit['rowCount'] * $page;

    return $limit;
}

/**
 * Table builder for this bucket's receivables
 * @return array
 */
function _mhc_ccrs_manager_bucket_receivables_table(EditBucketPresenter $presenter, array $urlParams)
{
    return array(
        'header' => $presenter->getBucketReceivablesTableHeader(),
        'rows' => $presenter->getBucketReceivablesTableRows($urlParams,
                  _mhc_ccrs_manager_bucket_receivables_pager($presenter, $urlParams)),
        );
}

/**
 * Receivables Pager Builder
 * @return int $limit
 */
function _mhc_ccrs_manager_bucket_receivables_pager(EditBucketPresenter $presenter, array $urlParams)
{
    // Initialize Pager
    $limit['rowCount'] = variable_get('mhc_ccrs_manager_receivables_num_per_page', 10);
    $page = pager_default_initialize($presenter->getBucketReceivablesCount($urlParams), $limit['rowCount'], 0);
    $limit['offset'] = $limit['rowCount'] * $page;

    return $limit;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param EditbucketPreseneter $presenter
 *
 * @return array
 */
function _mhc_ccrs_manager_editbucket_form($form, &$form_state, EditbucketPresenter $presenter, array $urlParams)
{
    // We are removing form caching, since closures (and hence our DI containers
    // and therefore our presenters) are not serializable.  This will also stop
    // annoying "Serialization of 'Closure'" exceptions in Drupal.
    $form_state['no_cache'] = true;

    $bucketData = (array_key_exists('bucketID', $urlParams) && $urlParams['bucketID'] > 0 ) ? $presenter->getCcrsBucketsBucketData($urlParams['bucketID']) : null;
    $editMode = ( @$urlParams['e'] == 1 || !$bucketData ) ? true : false;
    return array(

        /* DEBUG - view bucket data
        'bucket_data' => array(
                    '#title' => t('Bucket Data'),
                    '#type' => 'textarea',
                    '#cols' => 60,
                    '#rows' => 5,
                    '#default_value' => $bucketData ? json_encode($bucketData) : '',
                    '#attributes' => array('disabled' => true)
                ),
        */
        'ccrs_manager_form_bucket_fieldset' => array(
            '#type' => 'fieldset',
            '#title' => ($editMode ? ( $bucketData ? t('Edit ') : t('Create ') ) : t('View ') ) . t('Bucket'),
            'enableEdit' => ($bucketData && !$editMode )? array(
                '#type' => 'submit',
                '#value' => 'Click to edit',
                '#submit' => array('MHCCcrsManager\enable_edit_fields')
            ) : array(),
            'bucketID' => array(
                '#title' => t('Bucket ID'),
                '#type' => 'hidden',
                '#size' => 5,
                '#default_value' => $bucketData ? $bucketData['bucketID'] : '',
            ),
            'term' => array(
                '#title' => t('Term'),
                '#field_suffix' => t('Month'),
                '#type' => 'select',
                '#options' => array('0' => '0', '12'=>'12', '24'=>'24'),
                '#attributes' => !$editMode ? array('disabled' => true) : '',
                '#default_value' => $bucketData ? $bucketData['term'] : '',
            ),
            'isM2M' => array(
                '#title' => t('Month-to-Month'),
                '#type' => 'checkbox',
                '#attributes' => !$editMode ? array('disabled' => true) : '',
                '#default_value' => ($bucketData
                                    &&  (
                                        strpos(strtoupper($bucketData['shortDescription']), 'M2M') !== false
                                        || (array_key_exists('isM2M', $bucketData) && $bucketData['isM2M'])
                                        )
                                    ) ? true : '',
            ),
            'clearbothb' => array(
            '#type' => 'markup',
            '#markup' => '<div class="clearfix"></div>'
            ),
            'bucketCategoryID' => array(
                '#title' => t('Bucket Category'),
                '#type' => 'select',
                '#options' => $presenter->getBucketCategoriesOptionsArray(),
                '#default_value' => $bucketData ? $bucketData['bucketCategoryID'] : '',
                '#empty_option' => '- Select -',
                '#multiple' => false,
                '#attributes' => !$editMode ? array('disabled' => true) : '',
            ),
            'contractTypeID' => array(
                '#title' => t('Contract Type'),
                '#type' => 'select',
                '#options' => $presenter->getBucketContractTypesOptionsArray(),
                '#default_value' => $bucketData ? $bucketData['contractTypeID'] : '',
                '#empty_option' => '- Select -',
                '#multiple' => false,
                '#attributes' => !$editMode ? array('disabled' => true) : '',
            ),
            'actTypeID' => array(
                '#title' => t('Activation Type'),
                '#type' => 'select',
                '#options' => $presenter->getActivationTypesOptionsArray(),
                '#default_value' => $bucketData ? $bucketData['actTypeID'] : '',
                '#empty_option' => '- Select -',
                '#multiple' => false,
                '#attributes' => !$editMode ? array('disabled' => true) : '',
            ),
            'isNE2' => array(
                '#title' => t('NE2'),
                '#type' => 'checkbox',
                '#default_value' => ($bucketData && $bucketData['isNE2']) ? $bucketData['isNE2'] : '',
                '#attributes' => !$editMode ? array('disabled' => true) : '',
            ),
            'isEdge' => array(
                '#title' => t('Edge'),
                '#type' => 'checkbox',
                '#default_value' => ($bucketData && $bucketData['isEdge']) ? $bucketData['isEdge'] : '',
                '#attributes' => !$editMode ? array('disabled' => true) : '',
            ),
            'shortDescription' => array(
                '#title' => t('Short Description'),
                '#type' => 'textfield',
                '#default_value' => $bucketData ? $bucketData['shortDescription'] : '',
                '#size' => 20,
                '#attributes' => !$editMode ? array('disabled' => true) : '',
            ),
            'description' => array(
                '#title' => t('Description'),
                '#type' => 'textfield',
                '#size' => 45,
                '#attributes' => !$editMode ? array('disabled' => true) : '',
                '#default_value' => $bucketData ? $bucketData['description'] : '',
            ),
            'clearboth' => array(
                '#type' => 'markup',
                '#markup' => '<div class="clearfix"></div>'
            ),

        ),
        'cancel' => array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
            '#submit' => array('MHCCcrsManager\_mhc_ccrs_manager_editbucket_form_cancel'),
            '#limit_validation_errors' => array()
        ),
        'submit' => array(
            '#type' => 'submit',
            '#value' => t('Submit'),
        ),
        'clearboth' => array(
            '#type' => 'markup',
            '#markup' => '<div class="clearfix"></div>'
        ),

    );
}

function _mhc_ccrs_manager_editbucket_form_cancel($form, &$form_state)
{
    $form_state['redirect'] = array(CcrsManagerPresenter::getDrupalMenuRouterPath() );
}

function enable_edit_fields($form, &$form_state)
{
    $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(),
                                    array('query' => array('bucketID'=>$form_state['input']['bucketID'] , 'e'=>1))
                                    );
}

/**
 * Form Validator
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_editbucket_form_validate($form, &$form_state)
{
    $presenter = $form_state['build_info']['args'][0];

    $bucketExists = $presenter->checkBucketExists($form_state['input'] );

    if( $bucketExists ) {
        form_set_error('description', t('Identical bucket already exists, please check the list.') );
    }
}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_editbucket_form_submit($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];
    $result = $presenter->pushToBucket($form_state['input']);
    if($result) {
        drupal_set_message(t('Save successful'), 'status');
    } else {
        drupal_set_message(t('Save Error'), 'error');
    }

    // if INSERT, $result returns the last insert ID otherwise it's true for a successful update, or else false if anything went wrong
    $redirectQueryValue = is_bool($result) ? $form_state['input']['bucketID'] : $result;

    // Redirect the user.

    $form_state['redirect'] = array(EditbucketPresenter::getDrupalMenuRouterPath(), array('query' => array('bucketID'=>$redirectQueryValue)));
}
