<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveInternal;

use PDO;
use ReviveUsageData\DataAccess\Entities\ReviveInternal\Configurations as ConfigurationsEntity;
use ReviveUsageData\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'revive_internal::configurations'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-30
 */
class Configurations extends ReviveInternalDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'configurations';

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
     * @return ConfigurationsEntity
     */
    public static function createEntity(array $data = array())
    {
        return new ConfigurationsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param ConfigurationsEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(ConfigurationsEntity $entity, array $fields = array())
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
     * @param ConfigurationsEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(ConfigurationsEntity $entity, array $fields, array $criteria)
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
     * Query a list of configurations
     *
     * @return PDOStatement
     */
    public function getMachineConfigurations()
    {
        $query = "SELECT * FROM " . self::getTableName() . " ORDER BY `name`";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }

}
