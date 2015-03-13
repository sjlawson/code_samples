<?php

namespace DealerLedger\DataAccess\Tables\Ccrs2;

use PDO;
use DealerLedger\DataAccess\Entities\Ccrs2\DealerLedger as DealerLedgerEntity;
use DealerLedger\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::dealer_ledger'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class DealerLedger extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'dealer_ledger';

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
     * @return DealerLedgerEntity
     */
    public static function createEntity(array $data = array())
    {
        return new DealerLedgerEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param DealerLedgerEntity $entity
     * @param array              $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(DealerLedgerEntity $entity, array $fields = array())
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
     * @param DealerLedgerEntity $entity
     * @param array              $criteria The update criteria. An associative array of
     *                                     fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(DealerLedgerEntity $entity, array $fields, array $criteria)
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
     * Get account customers
     * @param $accountID
     * @param $monthYear
     * @return PDOStatement
     */
    public function getCustomerAssociations($accountID, $monthYear)
    {
        $SQL = "
            SELECT DISTINCT
                accountNumber,
                phone
            FROM " . self::getTableName() . "
            WHERE accountID = :accountID
                AND monthYear = :monthYear

            UNION DISTINCT

            SELECT DISTINCT
                accountNumber,
                originalPhone AS phone
            FROM " . self::getTableName() . "
            WHERE accountID = :accountID
                AND monthYear = :monthYear
                AND originalPhone IS NOT NULL

            ORDER BY accountNumber, phone
        ";
        $stmt = $this->connection->prepareQuery($SQL);
        $stmt->bindValue(':accountID', $accountID, PDO::PARAM_STR);
        $stmt->bindValue(':monthYear', $monthYear, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Return rows for account, date, with paidON as NULL
     *
     * @param $accountID
     * @param $monthYear
     * @param $excludeCoop - boolean switch, controls query option to exclude column types
     * @return PDOStatement
     */
    public function getEstimates($accountID, $monthYear, $excludeCoop)
    {
        $SQL = "
            SELECT
                dl.associationID,
                dl.accountID,
                dl.locationID,
                dl.sfid,
                dl.monthYear,
                dl.accountNumber,
                dl.customerName,
                dl.originalPhone,
                dl.phone,
                dl.deviceCategory,
                dl.deviceID,
                dl.bucketID,
                REPLACE(b.shortDescription, ' ', '_') AS bucketDescription,
                dl.contractDate,
                dl.deactDate,
                dl.daysOfService,
                dl.visionCode,
                dl.contractLength,
                dl.pricePlan,
                dl.upgradeType,
                dl.description,
                ct.commissionType,
                dl.columnTypeID,
                ct.type AS columnType,
                payable,
                paidOn
            FROM " . self::getTableName() . " dl
            INNER JOIN " . Buckets::getTableName() . " b ON b.bucketID = dl.bucketID
            INNER JOIN " . Rq4ReconColumnTypes::getTableName()  . " ct ON ct.id = dl.columnTypeID
            WHERE accountID = :accountID
                AND monthYear = :monthYear
                AND paidOn IS NULL ";

        if ($excludeCoop) {
            $SQL .= "AND columnTypeID NOT IN (" . self::COOPCOMMISSION . "," . self::COOPDEACTCOMMISSION . ")";
        }

        $SQL .= " ORDER BY dl.accountID, dl.locationID, dl.associationID, dl.columnTypeID ";

        $stmt = $this->connection->prepareQuery($SQL);
        $stmt->bindValue(':accountID', $accountID, PDO::PARAM_STR);
        $stmt->bindValue(':monthYear', $monthYear, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Get Dealer Ledger records by id, having paidOn as NOT null ensures that it is an actual payment record
     *
     * @param $accountID
     * @param $monthYear
     * @return array PDO::FETCH_ASSOC
     */
    public function getFinalPayments($accountID, $monthYear)
    {
        $SQL = "
            SELECT
                dl.associationID,
                dl.accountID,
                dl.locationID,
                dl.sfid,
                dl.monthYear,
                dl.accountNumber,
                dl.customerName,
                dl.originalPhone,
                dl.phone,
                dl.deviceCategory,
                dl.deviceID,
                dl.bucketID,
                REPLACE(b.shortDescription, ' ', '_') AS bucketDescription,
                dl.contractDate,
                dl.deactDate,
                dl.daysOfService,
                dl.visionCode,
                dl.contractLength,
                dl.pricePlan,
                dl.upgradeType,
                dl.description,
                ct.commissionType,
                dl.columnTypeID,
                ct.type AS columnType,
                payable,
                paidOn
            FROM " . self::getTableName() . " dl
            INNER JOIN " . Buckets::getTableName() . " b ON b.bucketID = dl.bucketID
            INNER JOIN " . Rq4ReconColumnTypes::getTableName() . " ct ON ct.id = dl.columnTypeID
            WHERE accountID = :accountID
                AND monthYear = :monthYear
                AND paidOn IS NOT NULL
                AND columnTypeID NOT IN (" . self::COOPCOMMISSION . "," . self::COOPDEACTCOMMISSION . ")
            ORDER BY dl.accountID, dl.locationID, dl.associationID, dl.columnTypeID ";

        $stmt = $this->connection->prepareQuery($SQL);
        $stmt->bindValue(':accountID' , $accountID, PDO::PARAM_STR);
        $stmt->bindValue(':monthYear' , $monthYear, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Get rows from dealer_ledger that have column_types as COOPCOMMISSION(28) or COOPDEACTCOMMISSION(29)
     *
     * @param $accountID
     * @param $monthYear
     * @return array PDO::FETCH_ASSOC
     */
    public function getCoopPayments($accountID, $monthYear)
    {
        $SQL = "
            SELECT *
            FROM (
                SELECT
                    locationID,
                    sfid,
                    phone,
                    monthYear,
                    customerName,
                    contractdate,
                    deactdate,
                    IF(contractDate < '2012-7-1', payable,
                        IF(deviceCategory IN ('BPN', 'HPC', 'HFN', 'IPD', 'IPH', 'TAB'), 0.00,
                            IF(columnTypeID = " . self::COOPCOMMISSION . ", 15.00, -15.00)
                        )
                    ) AS payable
                FROM " . self::getTableName() . "
                WHERE accountID = :accountID
                    AND columnTypeID IN (" . self::COOPCOMMISSION . "," . self::COOPDEACTCOMMISSION . ")
                    AND monthYear = :monthYear
            ) AS t1
            WHERE t1.payable <> 0.00";

        $stmt = $this->connection->prepareQuery($SQL);
        $stmt->bindValue(':accountID', $accountID, PDO::PARAM_STR);
        $stmt->bindValue(':monthYear', $monthYear, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

}
