<?php

namespace DealerLedger\DataAccess\Tables\Mhcdynad;

use DealerLedger\DataAccess\Entities\Mhcdynad\MhcSfids as MhcSfidsEntity;
use DealerLedger\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'mhcdyna::mhc_sfids'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class MhcSfids extends MhcdynadDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'mhc_sfids';

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
     * @return MhcSfidsEntity
     */
    public static function createEntity(array $data = array())
    {
        return new MhcSfidsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param MhcSfidsEntity $entity
     * @param array          $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(MhcSfidsEntity $entity, array $fields = array())
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
     * @param MhcSfidsEntity $entity
     * @param array          $criteria The update criteria. An associative array of
     *                                 fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(MhcSfidsEntity $entity, array $fields, array $criteria)
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
