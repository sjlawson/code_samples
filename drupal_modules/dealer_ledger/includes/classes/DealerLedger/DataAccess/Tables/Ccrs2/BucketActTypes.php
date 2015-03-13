<?php

namespace DealerLedger\DataAccess\Tables\Ccrs2;

use DealerLedger\DataAccess\Entities\Ccrs2\BucketActTypes as BucketActTypesEntity;
use DealerLedger\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::bucket_act_types'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class BucketActTypes extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'bucket_act_types';

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
     * @return BucketActTypesEntity
     */
    public static function createEntity(array $data = array())
    {
        return new BucketActTypesEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketActTypesEntity $entity
     * @param array                $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(BucketActTypesEntity $entity, array $fields = array())
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
     * @param BucketActTypesEntity $entity
     * @param array                $criteria The update criteria. An associative array of
     *                                       fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketActTypesEntity $entity, array $fields, array $criteria)
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
}
