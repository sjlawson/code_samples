<?php
/**
 * Page callback for page "Edit Bucket Category"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-04-01
 */

namespace MHCCcrsManager;

use MHCCcrsManager\Presenters\Pages\ListBucketCategoriesPresenter;
use MHCCcrsManager\DependencyInjection\Pages\ListBucketCategoriesDependencyContainer;
use MHCCcrsManager\DependencyInjection\Pages\EditBucketCategoryDependencyContainer;
use MHCCcrsManager\Presenters\Pages\EditBucketCategoryPresenter;

/**
 * Page callback for "Edit Bucket Category"
 *
 * @return array The render array
 */
function _mhc_ccrs_manager_edit_bucket_category()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new EditBucketCategoryDependencyContainer();
    $editBucketCategoryPresenter = $dependencyContainer['EditBucketCategoryPresenter'];

    // Set devMode parameters.
    if ($editBucketCategoryPresenter->getDevMode()) {
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
        
    $urlParams = $editBucketCategoryPresenter->getBucketCategoryFormDefaults();

    $pageRenderArray = array(
        '#EditBucketCategoryForm' => drupal_get_form('MHCCcrsManager\_mhc_ccrs_manager_edit_bucket_category_form', $editBucketCategoryPresenter, $urlParams),
        '#theme' => 'mhc_ccrs_manager_edit_bucket_category_page',
    );

    return $pageRenderArray;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param EditBucketCategoryPreseneter $presenter
 *
 * @return array
 */
function _mhc_ccrs_manager_edit_bucket_category_form($form, &$form_state, EditBucketCategoryPresenter $presenter, array $urlParams)
{
    // We are removing form caching, since closures (and hence our DI containers
    // and therefore our presenters) are not serializable.  This will also stop
    // annoying "Serialization of 'Closure'" exceptions in Drupal.
    $form_state['no_cache'] = true;
    $bucketCategoryID = array_key_exists('bucketCategoryID', $urlParams) ? $urlParams['bucketCategoryID'] : null;
    $data = $bucketCategoryID ? $presenter->getBucketCategoryData($bucketCategoryID) : null;

    return array(
      'form_fieldset' => array(
          '#type' => 'fieldset', 
          '#title' => 'Bucket Category', 
          'bucketCategoryID' => array(
              '#type' => 'textfield',
              '#title' => 'Category ID',
              '#default_value' => $data ? $data['bucketCategoryID'] : '',
              '#size' => 4,
              '#required' => true
          ),
          'description' => array(
              '#type' => 'textfield',
              '#title' => 'Description',
              '#default_value' => $data ? $data['description'] : '',
              '#size' => 80,
              '#required' => true
          ),
          'isNew' => array(
          	  '#type' => 'hidden',
              '#value' => $data ? false : true,
          )
      ),
        'cancel' => array(
            '#type' => 'submit',
            '#value' => t('Cancel'),
        	'#limit_validation_errors' => array(),
        	'#submit' => array('MHCCcrsManager\_mhc_ccrs_manager_editbucketCategory_form_cancel')
        ),
        'submit' => array(
            '#type' => 'submit',
            '#value' => t('Submit'),
        ),
    );
}

function _mhc_ccrs_manager_editbucketCategory_form_clear($form, &$form_state)
{
	$form_state['redirect'] = array(EditBucketCategoryPresenter::getDrupalMenuRouterPath());
}

function _mhc_ccrs_manager_editbucketCategory_form_cancel($form, &$form_state)
{
	$form_state['redirect'] =  array(ListBucketCategoriesPresenter::getDrupalMenuRouterPath());
}
/**
 * Form Validator
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_edit_bucket_category_form_validate($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];
}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_edit_bucket_category_form_submit($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];
	
    $result = $presenter->saveBucketCategory($form_state['input'], $form_state['input']['isNew'] );
    $bucketCategoryID = $form_state['input']['bucketCategoryID'];
    
	if($result) {
    	drupal_set_message(t('Save successful'), 'status');
    } else {
    	drupal_set_message(t('Save Error'), 'error');
    }
    
    // Redirect the user.
    $form_state['redirect'] = array(ListBucketCategoriesPresenter::getDrupalMenuRouterPath(), array('query'=>array('bucketCategoryID'=>$bucketCategoryID)));
}
