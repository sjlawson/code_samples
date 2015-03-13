<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveInternal;

use PDO;
use ReviveUsageData\DataAccess\Entities\ReviveInternal\Locations as LocationsEntity;
use ReviveUsageData\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'revive_internal::locations'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-14
 */
class Locations extends ReviveInternalDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'locations';

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
     * @return LocationsEntity
     */
    public static function createEntity(array $data = array())
    {
        return new LocationsEntity($data);
    }

    /**
     * Simple select all
     * @return PDOStatement
     */
    public function getLocations()
    {
        $query = "SELECT * FROM " . self::getTableName();
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }

}
