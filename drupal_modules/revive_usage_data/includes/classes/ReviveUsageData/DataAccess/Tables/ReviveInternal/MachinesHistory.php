<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveInternal;

use PDO;
use ReviveUsageData\DataAccess\Entities\ReviveInternal\MachinesHistory as MachinesHistoryEntity;
use ReviveUsageData\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'revive_internal::machines_history'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-07-17
 */
class MachinesHistory extends ReviveInternalDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'machines_history';

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
     * @return MachinesHistoryEntity
     */
    public static function createEntity(array $data = array())
    {
        return new MachinesHistoryEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param MachinesHistoryEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(MachinesHistoryEntity $entity, array $fields = array())
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
     * @param MachinesHistoryEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(MachinesHistoryEntity $entity, array $fields, array $criteria)
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
     * Select and return decoded json column by machineID and date closest after that which was passed
     * @param $machineID
     * @param $processID
     * @return array
     */
    public function findHistoryForDate($machineID, $processID)
    {
        $query = "SELECT * FROM " . self::getTableName() ." h "
            . " WHERE h.`json` LIKE '%" . $machineID . "%'
                AND h.`dateTimeModified` > FROM_UNIXTIME(" . $processID . ")
                ORDER BY h.`dateTimeModified` ASC
                LIMIT 1";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return json_decode($row['json'], true);
    }

}
