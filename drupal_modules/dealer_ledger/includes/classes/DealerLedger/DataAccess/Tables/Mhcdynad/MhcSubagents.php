<?php

namespace DealerLedger\DataAccess\Tables\Mhcdynad;

use PDO;
use DealerLedger\DataAccess\Entities\Mhcdynad\MhcSubagents as MhcSubagentsEntity;
use DealerLedger\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'mhcdyna::mhc_subagents'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-24
 */
class MhcSubagents extends MhcdynadDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'mhc_subagents';

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
     * @return MhcSubagentsEntity
     */
    public static function createEntity(array $data = array())
    {
        return new MhcSubagentsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param MhcSubagentsEntity $entity
     * @param array              $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(MhcSubagentsEntity $entity, array $fields = array())
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
     * @param MhcSubagentsEntity $entity
     * @param array              $criteria The update criteria. An associative array of
     *                                     fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(MhcSubagentsEntity $entity, array $fields, array $criteria)
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
     * DealerLedger.accountID is an unlinked fk to MhcSubagents.id
     *
     * @param $accountID
     * @return array
     */
    public function getSubAgentById($accountID)
    {
        $query = "SELECT
                    *
                FROM " . self::getTableName() . "
                WHERE
                    id = :id
                LIMIT 1";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':id', $accountID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSubagents()
    {
        $query = "
                SELECT DISTINCT
                  (subagents.id),
                  subagents.name
                FROM
                  " . self::getTableName() . " subagents
                  WHERE subagents.`id` IN
                  (SELECT DISTINCT
                    (accountID)
                  FROM
                    " . \DealerLedger\DataAccess\Tables\Ccrs2\DealerLedger::getTableName() . ")
                ORDER BY NAME ";

        /* For additionally filtered content:
                  INNER JOIN " . MhcLocations::getTableName() ." locations
                    ON subagents.id = locations.`accountID`
                WHERE (
                    locations.`closedDate` IS NULL
                    OR locations.`closedDate` = ''
                  )
        */

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }
}
