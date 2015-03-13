<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveInternal;

use PDO;
use ReviveUsageData\DataAccess\Entities\ReviveInternal\Machines as MachinesEntity;
use ReviveUsageData\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'revive_internal::machines'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-14
 */
class Machines extends ReviveInternalDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'machines';

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
     * @return MachinesEntity
     */
    public static function createEntity(array $data = array())
    {
        return new MachinesEntity($data);
    }

    /**
     * SELECT method for all machines
     * @return array PDO::FETCH_ASSOC
     */
    public function getMachines()
    {
        $query = "SELECT * FROM " . self::getTableName();

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
