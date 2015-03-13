<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use PDO;
use MHCCcrsManager\DataAccess\Entities\Ccrs2\Buckets as BucketsEntity;
use MHCCcrsManager\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::buckets'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class Buckets extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'buckets';

    /**
     * Will return the full table name.
     *
     * @return string
     */
    static public function getTableName()
    {
        return self::getDatabaseName() . '.' . self::NAME;
    }

    /**
     * Entity factory.
     *
     * @return BucketsEntity
     */
    static public function createEntity(array $data = array())
    {
        return new BucketsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketsEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(BucketsEntity $entity, array $fields = array())
    {
        $data = $entity->toArray();
        if(array_key_exists('bucketID', $data)) unset($data['bucketID']);
        if(array_key_exists('addedOn', $data)) unset($data['addedOn']);

        $fields = array_keys($data);

        $placeholders = array();
        foreach ($fields as $field) {
            $placeholders[] = ":$field";
        }

        $query = 'INSERT INTO ' . $this->getTableName() .
                 ' ( ' . implode(',', $fields) . ', addedOn )
                 VALUES
                 ( '.implode(',', $placeholders) . ', NOW() )';

        $stmt = $this->connection->prepareQuery($query);
        $this->bindVals($stmt, $data);

        if(!$stmt->execute()) {
            return false;
        } else {
            return $this->connection->getLastInsertId();
        }

    }

    /**
     * Helper function to build and execute update statements... adapted from AbstractConnection
     *
     * @param BucketsEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs... should be bucketID = n
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketsEntity $entity, array $criteria)
    {
        $data = $entity->toArray();
        if(array_key_exists('bucketID', $data)) unset($data['bucketID']);
        if(array_key_exists('addedOn', $data)) unset($data['addedOn']);

        // Build parameter array.
        $parameterArray = array();
        $setArray = array();
        foreach ($data as $field => $value) {
            $field = $field[0] == ':' ? substr($field, 1) : $field;
            $parameterArray['set_' . $field] = $value;
            $setArray[] = $field . ' = :set_' . $field;
        }

        //TODO: comment if addedOn value from creation should be preserved
        $setArray[] = 'addedOn = NOW()';

        // Build criteria array and parameter array.
        $criteriaArray = array();
        foreach ($criteria as $field => $value) {
            $field = $field[0] == ':' ? substr($field, 1) : $field;
            $parameterArray['criteria_' . $field] = $value;
            $criteriaArray[] = $field . ' = :criteria_' . $field;
        }

        // Build the query.
        $query = 'UPDATE ' . $this->getTableName() . ' SET ' .
            implode(', ', $setArray) . ' WHERE ' .
            implode(' AND ', $criteriaArray);

        $stmt = $this->connection->prepareQuery($query);
        $this->bindVals($stmt, $parameterArray);

        return $stmt->execute();
    }

    /**
     *
     * Bind params to stmt with correct types
     * @param $stmt
     * @param $params
     */
    public function bindVals(\PDOStatement &$stmt, array $params = array())
    {
        foreach ($params as $field => $value) {
            $key = $field[0] == ':' ? $field : ':' . $field;
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
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
     * Will return a list of Buckets as an array formatted for a 'select' form control
     *
     * @return PDOStatement
     */
    public function getBucketsOptionsArray()
    {
        $query = "
            SELECT
                b.bucketID AS id,
                b.description AS fieldText
            FROM ".self::getTableName()." AS b
            ORDER BY b.bucketCategoryID, actTypeID, term";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Retrieve all buckets
     * @return PDOStatement
     */
    public function getAllBuckets()
    {
        $query = "
            SELECT *
            FROM ".self::getTableName()."
            ORDER BY description ASC";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     *Return single Bucket with full left join data
     *
     * @return PDOStatement
     */
    public function getBucketById($bucketId)
    {
        $query = "
            SELECT
                b.*,
                cat.description AS categoryDescription,
                con.description AS contractDescription,
                act.type AS actType
            FROM " . self::getTableName() . " AS b
                LEFT JOIN " . BucketCategories::getTableName() . " cat ON cat.bucketCategoryID = b.bucketCategoryID
                LEFT JOIN " . BucketContractTypes::getTableName() . " con ON con.contractTypeID = b.contractTypeID
                LEFT JOIN " . BucketActTypes::getTableName() . " act ON act.actTypeID = b.actTypeID
            WHERE
                b.bucketID = :bucketID
                ";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $bucketId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

/**
  * Try to SELECT a bucket from given name:value arrays
  * and optional 'isNot' pairs
  *
  * @param array $data name:value
  * @param array $isNot name:value
  * @return PDOStatement
  */
    public function getBucketFromData( array $data , array $isNot = array() ) {
        $fields = array_keys($data);
        $query = "SELECT * FROM " . self::getTableName() . " WHERE 1";

        foreach( $fields as $field ) {
            $query .= " AND " . $field . " = :" . $field;
        }

        foreach(array_keys( $isNot ) as $isNotKey) {
            $query .= " AND " . $isNotKey . " != :" . $isNotKey;
        }

        $stmt = $this->connection->prepareQuery($query);
        foreach($data as $dataField => $value) {
            $stmt->bindValue(':'.$dataField, $value,
                            ( is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR )
                            );
        }

        foreach($isNot as $isNotField => $isNotValue) {
            $stmt->bindValue(':'.$isNotField, $isNotValue,
                            ( is_int($isNotValue) ? PDO::PARAM_INT : PDO::PARAM_STR )
                            );
        }

        $stmt->execute();

        return $stmt;
    }

    public function getCSVData()
    {
        /*
         * disabled fields:
         cs.id AS 'Commission Schedule ID',
         b.bucketID AS 'Bucket ID',
         cr.commissionBucketID AS 'Recievable ID',
         cp.payoutBucketID AS 'Payable ID',
         */
        $setQuery = "SET @date = CURDATE()";
        $stmt = $this->connection->prepareQuery($setQuery);
        $stmt->execute();

        $query = "
        SELECT
            cs.schedule AS 'Commission Schedule',
            b.bucketCategoryID AS 'Bucket Category',
            ct.description AS 'Contract Type',
            act.type AS 'Activation Type',
            b.term AS 'Contract Length',
            b.description AS 'Contract Description',
            b.shortDescription AS 'Contract Code',
            cr.amount AS 'Commission Receivable',
            cr.adSpiff AS 'Advanced Device Receivable',
            cp.amount AS 'Commission Payable',
            cp.adSpiff AS 'Advanced Device Payable',
            cp.empSpiff AS 'Employee Payable'
        FROM " . self::getTableName() . " b
        INNER JOIN " . BucketActTypes::getTableName() . " act ON act.actTypeID = b.actTypeID
        INNER JOIN " . BucketContractTypes::getTableName() . " ct ON ct.contractTypeID = b.contractTypeID
        INNER JOIN " . BucketCommissionBuckets::getTableName() . " cr
            ON cr.bucketID = b.bucketID
            AND @date BETWEEN IFNULL(cr.begDate, '1900-01-01') AND IFNULL(cr.endDate, '9999-01-01')
        INNER JOIN " . BucketCommissionPayoutBuckets::getTableName() . " cp
            ON cp.bucketID = b.bucketID
            AND @date BETWEEN IFNULL(cp.begDate, '1900-01-01') AND IFNULL(cp.endDate, '9999-01-01')
        INNER JOIN " . EstimatorCommissionSchedules::getTableName() . " cs ON cs.id = cp.payoutScheduleID
        WHERE cs.id IN (1,11, 8, 10)
            AND @date BETWEEN IFNULL(cr.begDate, '1900-01-01') AND IFNULL(cr.endDate, '9999-01-01')
        ORDER BY
            cs.schedule,
            b.bucketCategoryID,
            b.actTypeID,
            b.term,
            cs.id
        ";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }
}
