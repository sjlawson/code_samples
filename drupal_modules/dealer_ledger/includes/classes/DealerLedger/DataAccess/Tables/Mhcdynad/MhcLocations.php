<?php

namespace DealerLedger\DataAccess\Tables\Mhcdynad;

use DealerLedger\DataAccess\Entities\Mhcdynad\MhcLocations as MhcLocationsEntity;
use DealerLedger\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'mhcdyna::mhc_locations'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class MhcLocations extends MhcdynadDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'mhc_locations';

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
     * @return MhcLocationsEntity
     */
    public static function createEntity(array $data = array())
    {
        return new MhcLocationsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param MhcLocationsEntity $entity
     * @param array              $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(MhcLocationsEntity $entity, array $fields = array())
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
     * @param MhcLocationsEntity $entity
     * @param array              $criteria The update criteria. An associative array of
     *                                     fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(MhcLocationsEntity $entity, array $fields, array $criteria)
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
     * Export locations for account
     * @param $accountID
     * @return mixed PDOStatement |string error message
     */
    public function exportLocations($accountID)
    {
        $SQL = "
            SELECT
                locationID,
                colocationID,
                sfid,
                name,
                address,
                city,
                (SELECT stateName FROM mhcdynad.mhc_states WHERE id = stateID) AS state,
                zipCode,
                phoneNumber,
                faxNumber,
                openDate
            FROM " . self::getTableName()  . " l
            LEFT JOIN " . MhcSfids::getTableName() . " sf ON sf.instanceID = l.id
            WHERE accountID = '" . $accountID . "'
            ORDER BY colocationID ";

        $stmt = $this->connection->prepareQuery($SQL);
        $stmt->execute();

        if ($stmt->execute()) {
            return $stmt;

        } else {
            $error = $stmt->errorInfo();

            return $error[2];
        }
    }
}
