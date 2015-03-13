<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use PDO;
use MHCCcrsManager\DataAccess\Entities\Ccrs2\BucketCommissionPayoutBuckets as BucketCommissionPayoutBucketsEntity;
use MHCCcrsManager\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::bucket_commission_payout_buckets'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class BucketCommissionPayoutBuckets extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'bucket_commission_payout_buckets';

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
     * @return BucketCommissionPayoutBucketsEntity
     */
    static public function createEntity(array $data = array())
    {
        return new BucketCommissionPayoutBucketsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketCommissionPayoutBucketsEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(BucketCommissionPayoutBucketsEntity $entity, array $fields = array())
    {
        $data = $entity->toArray();
        if(array_key_exists('payoutBucketID', $data)) unset($data['payoutBucketID']);
        if(array_key_exists('addedOn', $data)) unset($data['addedOn']);

        $fields = array_keys($data);

        $placeholders = array();
        foreach ($fields as $field) {
            $placeholders[] = ":$field";
        }

        $query = 'INSERT INTO ' . $this->getTableName() .
               ' ( ' . implode(',', $fields) . ', addedOn )
                 VALUES
                 ( ' . implode(',', $placeholders) . ', NOW() )';

        $stmt = $this->connection->prepareQuery($query);
        if(!$stmt->execute($data)) {
            return false;
        } else {
            return $this->connection->getLastInsertId();
        }
    }

    /**
     * Helper function to build and execute simple update statements.
     *
     * @param BucketCommissionPayoutBucketsEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketCommissionPayoutBucketsEntity $entity, array $fields, array $criteria)
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
     * Return number of rows for a given bucketID
     * @access public
     * @param array $filters
     * @return PDOStatement
     */
    public function getBucketPayablesCount(array $filters) {
        $bucketID = @$filters['bucketID'] ? $filters['bucketID'] : 0;

        $query = "
            SELECT COUNT(p.payoutBucketID)
            FROM ".self::getTableName() . " p
            WHERE p.bucketID = :bucketID
            ORDER BY p.begDate, p.addedOn
        ";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $bucketID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Will return an array of drupal formatted rows
     * @access public
     * @param array $filters
     * @param array $limit['rowCount'] and $limit['offset']
     * @return PDOStatement
     */
    public function getBucketPayablesRecords(array $filters, array $limit) {

        $params = array(':bucketID' => @$filters['bucketID'] ? $filters['bucketID'] : 0);

        if(isset($limit['offset'])) {
            $params[':offset'] = intval($limit['offset']);
        }

        if(isset($limit['rowCount'])) {
            $params[':rowCount'] = intval($limit['rowCount']);
        }

        $query = "
            SELECT p.*, ecs.schedule AS payoutSchedule
            FROM ".self::getTableName() . " p
                LEFT JOIN " . EstimatorCommissionSchedules::getTableName() . " ecs
                    ON ecs.id = p.payoutScheduleID
            WHERE p.bucketID = :bucketID
            ORDER BY p.payoutScheduleID, p.begDate,  p.addedOn "
            .(isset($params[':rowCount']) && isset($params[':offset']) ? " LIMIT :rowCount OFFSET :offset" : '');

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $params[':bucketID'], PDO::PARAM_INT );
        $stmt->bindValue(':offset', $params[':offset'], PDO::PARAM_INT );
        $stmt->bindValue(':rowCount', $params[':rowCount'], PDO::PARAM_INT );
        $stmt->execute();

        return $stmt;

    }

    /**
     *
     * Get single record
     * @param $payoutBucketID
     * @return PDOStatement
     */
    public function getPayableById($payoutBucketID)
    {
        $query = "SELECT * FROM " . $this->getTableName() . "
            WHERE payoutBucketID = :payoutBucketID";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':payoutBucketID', $payoutBucketID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     *
     * Get count of payables having a given bucketID and payoutScheduleID
     * @param array $filters
     * @return PDOStatement | false on improper filter aray
     */
    public function getPayablesCountForSchedule(array $filters)
    {
        $bucketID = @$filters['bucketID'] ? $filters['bucketID'] : 0;
        $payoutScheduleID = @$filters['payoutScheduleID'] ? $filters['payoutScheduleID'] : 0;

        if(!$bucketID || !$payoutScheduleID) {
            return false;
        }

        $query = "
            SELECT COUNT(payoutBucketID)
            FROM ".self::getTableName() . "
            WHERE bucketID = :bucketID
                AND payoutScheduleID = :payoutScheduleID
        ";
        // This should be optional
        if(array_key_exists('begDate', $filters)) {
            $query .= " AND begDate < :begDate";
        }

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $bucketID, PDO::PARAM_INT);
        $stmt->bindValue(':payoutScheduleID', $payoutScheduleID, PDO::PARAM_INT);
        if(array_key_exists('begDate', $filters)) {
            $stmt->bindValue(':begDate', $filters['begDate'], PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt;
    }

    /**
     *
     * Get most recent payable - with null endDate being most recent
     * @param $bucketID
     */
    public function getBucketPayablesByEndDateAndSchedule($bucketID, $payoutScheduleID)
    {
        $query = "
        SELECT *
        FROM " . self::getTableName() . "
        WHERE bucketID = :bucketID
            AND payoutScheduleID = :payoutScheduleID
        ORDER BY endDate IS NULL DESC, endDate DESC
        LIMIT 1";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $bucketID, PDO::PARAM_INT);
        $stmt->bindValue(':payoutScheduleID', $payoutScheduleID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     *
     * Find existing payables with matching bucketID and payoutScheduleID and conflicting dates
     * @param $formInput
     * @return PDOStatement
     */
    public function findPayableDateConflict(array $formInput)
    {
        $endDate = $formInput['endDate'] == null ? '9999-12-31' : date('Y-m-d',strtotime($formInput['endDate']));
        $query = "SELECT payoutBucketID, begDate, endDate FROM " . $this->getTableName() . "
            WHERE
                (
                    bucketID = :bucketID
                    AND
                    payoutScheduleID = :payoutScheduleID
                    AND
                    payoutBucketID != :payoutBucketID
                ) AND (
                (begDate <= :begDate AND endDate > :begDate) OR
                (begDate < :endDate AND endDate > :endDate) OR
                (endDate > :begDate AND endDate < :endDate)
                )";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':begDate', date('Y-m-d',strtotime($formInput['begDate'])), PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindValue(':bucketID', $formInput['bucketID'], PDO::PARAM_INT);
        $stmt->bindValue(':payoutScheduleID', $formInput['payoutScheduleID'], PDO::PARAM_INT);
        $stmt->bindValue(':payoutBucketID', $formInput['payoutBucketID']);
        $stmt->execute();

        return $stmt;
    }

}
