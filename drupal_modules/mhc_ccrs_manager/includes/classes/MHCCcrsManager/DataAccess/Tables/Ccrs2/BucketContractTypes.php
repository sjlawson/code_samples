<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use PDO;
use MHCCcrsManager\DataAccess\Entities\Ccrs2\BucketContractTypes as BucketContractTypesEntity;
use MHCCcrsManager\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::bucket_contract_types'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-25
 */
class BucketContractTypes extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'bucket_contract_types';

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
     * @return BucketContractTypesEntity
     */
    public static function createEntity(array $data = array())
    {
        return new BucketContractTypesEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketContractTypesEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(BucketContractTypesEntity $entity, array $fields = array())
    {
        // If fields not given, submit default fields.
        if (empty($fields)) {
            $fields = array_diff($entity->getNonNullAndNullableFieldNamesArray(),
                                 $entity->getPrimaryKeyFieldNamesArray());
        }

        return $this->connection->insert(self::getTableName(), $entity->toArray($fields));
    }

    /**
     * Helper function to build and execute simple update statements.
     *
     * @param BucketContractTypesEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketContractTypesEntity $entity, array $fields, array $criteria)
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
     * Get contract types 
     * @return PDOStatement
     */
    public function getBucketContractTypes()
    {
    	$query = "
            SELECT
                bct.contractTypeID AS contractTypeID,
                bct.description AS description
            FROM ".self::getTableName()." AS bct
            ORDER BY description ASC";
            
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }
    
}
