<?php

namespace MHCCcrsManager\Presenters\Pages;

use PDO;
use MHCCcrsManager\Presenters\AbstractPresenter;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;

/**
 * "list_bucket_categories" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-04-01
 */
class ListBucketCategoriesPresenter extends AbstractPresenter
{
    /**
     * Constructor
     */
    public function __construct($devMode,
                                DataAccessDependencyContainer $dataAccessContainer,
                                array $getParameters)
    {
        parent::__construct($devMode, $dataAccessContainer, $getParameters);
    }

    /**
     * Will return the drupal path for this page.
     *
     * @return string
     */
    public static function getDrupalMenuRouterPath()
    {
        return 'apps/accounting/ccrs/manager/list_bucket_categories';
    }

    public function getCategoryOptions()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->getBucketCategories();
        
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$result['bucketCategoryID']] = $result['description'];
        }
        return $options;
    }
    
    public function getBucketCategoriesTableHeader()
    {
    	return array('bucketCategoryID' => 'Category ID', 'description' => 'Description' );
    }
    
    public function getBucketCategoriesTableRows()
    {
    	$stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->getBucketCategories();	
    	$rows = array();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            	// $editLink = $result['endDate'] == null ? '<a class="button" href="edit_payable?payoutBucketID=' . $result['payoutBucketID'] . '" >EDIT</a>' : ' ';
            	
                $rows[] = array(
                    'data' => array(
                        $result['bucketCategoryID'],
                        $result['description']
                        //$editLink
                    )
                );
        }
        return $rows;
    }
    
}
