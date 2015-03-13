<?php

namespace MHCCcrsManager\Presenters\Pages;

use PDO;
use MHCCcrsManager\Presenters\AbstractPresenter;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;

/**
 * "Edit Bucket Category" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-04-01
 */
class EditBucketCategoryPresenter extends AbstractPresenter
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
        return 'apps/accounting/ccrs/manager/edit_bucket_category';
    }
    
	/**
     * 
     * @return array
     */
    public function getBucketCategoryFormDefaults() 
    {
        $defaultParams = array();  
        return array_merge($defaultParams, $this->getParameters);
    }
    
    /**
     *
     * @return array 
     */
    public function getBucketCategoryData($bucketCategoryID) 
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->getBucketCategoryById($bucketCategoryID);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    
    /**
     * 
     * Given savedata, uses bool param to decide whether to insert or update
     * @param $savedata
     * @param $newBucketCategory
     */
    public function saveBucketCategory(array $savedata, $newBucketCategory = true)
    {
    	$entity = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->createEntity($savedata);
    	if($newBucketCategory) {
    		drupal_set_message("Inserting");
    		$result = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->insert($entity);
    	} else {
    		drupal_set_message("Updating");
    		$result = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->update($entity, array(), array('bucketCategoryID' => $entity->getBucketCategoryID()));
    	}
    	return $result;
    }
    
}
