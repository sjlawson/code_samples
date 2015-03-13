<?php
/**
 * Page callback for page "list_bucket_categories"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-04-01
 */

namespace MHCCcrsManager;

use MHCCcrsManager\DependencyInjection\Pages\ListBucketCategoriesDependencyContainer;
use MHCCcrsManager\Presenters\Pages\ListBucketCategoriesPresenter;
use MHCCcrsManager\Presenters\Pages\EditBucketCategoryPresenter;

/**
 * Page callback for "list_bucket_categories"
 *
 * @return array The render array
 */
function _mhc_ccrs_manager_list_bucket_categories()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new ListBucketCategoriesDependencyContainer();
    $listBucketCategoriesPresenter = $dependencyContainer['ListBucketCategoriesPresenter'];

    // Set devMode parameters.
    if ($listBucketCategoriesPresenter->getDevMode()) {
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

	$bucketCategoriesTable = _mhc_ccrs_manager_list_bucket_categories_table($listBucketCategoriesPresenter);
    $pageRenderArray = array(
        '#ListBucketCategoriesForm' => drupal_get_form('MHCCcrsManager\_mhc_ccrs_manager_list_bucket_categories_form', $listBucketCategoriesPresenter),
    	'#bucketCategoriesTable' => array(
	            '#empty' => t('No results'),
	            '#header' => $bucketCategoriesTable['header'],
	            '#rows' => $bucketCategoriesTable['rows'],
	            '#theme' => 'table'
	            ),
        '#theme' => 'mhc_ccrs_manager_list_bucket_categories_page',
    );

    return $pageRenderArray;
}

function _mhc_ccrs_manager_list_bucket_categories_table($presenter)
{
	return array(
        'header' => $presenter->getBucketCategoriesTableHeader(),
        'rows' => $presenter->getBucketCategoriesTableRows()
        );	
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param ListBucketCategoriesPreseneter $presenter
 *
 * @return array
 */
function _mhc_ccrs_manager_list_bucket_categories_form($form, &$form_state, ListBucketCategoriesPresenter $presenter)
{
    // We are removing form caching, since closures (and hence our DI containers
    // and therefore our presenters) are not serializable.  This will also stop
    // annoying "Serialization of 'Closure'" exceptions in Drupal.
    $form_state['no_cache'] = true;

    return array(
        'addnew' => array(
            '#type' => 'markup',
            '#markup' => '<h2>Bucket Categories <a class="link_addnew" title="Add new bucket category" href="' . base_path() . EditBucketCategoryPresenter::getDrupalMenuRouterPath() . '">+</a></h2>'
        )
    );
}

/**
 * Form Validator
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_list_bucket_categories_form_validate($form, &$form_state)
{

}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _mhc_ccrs_manager_list_bucket_categories_form_submit($form, &$form_state)
{
    // Redirect the user.
    $form_state['redirect'] = array(
        EditBucketCategoryPresenter::getDrupalMenuRouterPath(), 
        array('query'=>array('bucketCategoryID' => $form_state['input']['bucketCategoryID']) )
    );
}

function _mhc_ccrs_manager_list_bucket_categories_form_addnew($form, &$form_state)
{
	// Redirect the user.
    $form_state['redirect'] = array(EditBucketCategoryPresenter::getDrupalMenuRouterPath());	
}
