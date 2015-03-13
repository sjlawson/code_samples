<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use PDO;
use MHCCcrsManager\DataAccess\Entities\Ccrs2\BucketCategories as BucketCategoriesEntity;
use MHCCcrsManager\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::bucket_categories'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-25
 */
class BucketCategories extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'bucket_categories';

    /**
     * Will return the full table name.
     *
     * @return string
     */
    public static function getTableName()
    {
        return self::getDatabaseName() . '.' . self::NAME;
    }

    /**
     * Entity factory.
     *
     * @return BucketCategoriesEntity
     */
    public static function createEntity(array $data = array())
    {
        return new BucketCategoriesEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketCategoriesEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(BucketCategoriesEntity $entity, array $fields = array())
    {       
		// Check if exists
		$stmt = $this->getBucketCategoryById($entity->getBucketCategoryID());
		if ( count($stmt->fetchAll(PDO::FETCH_ASSOC)) ) {
			drupal_set_message("That bucket category ID already exists",'error');
			return false;
		}

        return $this->connection->insert(self::getTableName(), $entity->toArray());
    }

    /**
     * Helper function to build and execute simple update statements.
     *
     * @param BucketCategoriesEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketCategoriesEntity $entity, array $fields, array $criteria)
    {
        return $this->connection->update(self::getTableName(), $entity->toArray($fields),
                                         $criteria);
    }

    /**
     * Helper function to build and execute simple delete statements.
     *
     * @param array $criteria The delete criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function delete(array $criteria)
    {
        return $this->connection->delete(self::getTableName(), $criteria);
    }
    
    /**
     *Get list of bucket categories for use in option selector
     *
     *@return PDOStatement
     */
    public function getBucketCategories() 
    {
        $query = "
            SELECT
                bc.bucketCategoryID AS bucketCategoryID,
                bc.description AS description
            FROM ".self::getTableName()." AS bc
            ORDER BY bucketCategoryID ASC";
            
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }
    
    /**
     * 
     * get BucketCategory By Id
     * @param $bucketCategoryID
     * @return PDOStatement
     */
    public function getBucketCategoryById($bucketCategoryID)
    {
        $query = "
            SELECT
                bucketCategoryID,
                description
            FROM ".self::getTableName()."
        	WHERE bucketCategoryID=:bucketCategoryID";
            
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute(array(':bucketCategoryID'=>$bucketCategoryID));

        return $stmt;	
    }
}
