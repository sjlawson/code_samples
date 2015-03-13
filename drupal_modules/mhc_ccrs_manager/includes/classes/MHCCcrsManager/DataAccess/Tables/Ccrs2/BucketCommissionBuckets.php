<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use PDO;
use MHCCcrsManager\DataAccess\Entities\Ccrs2\BucketCommissionBuckets as BucketCommissionBucketsEntity;
use MHCCcrsManager\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::bucket_commission_buckets'. A.K.A 'Receivables'
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class BucketCommissionBuckets extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'bucket_commission_buckets';

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
     * @return BucketCommissionBucketsEntity
     */
    static public function createEntity(array $data = array())
    {
        return new BucketCommissionBucketsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketCommissionBucketsEntity $entity
     *
     * @return boolean false on failure, insert ID on success
     */
    public function insert(BucketCommissionBucketsEntity $entity)
    {
        $data = $entity->toArray();
        if(array_key_exists('commissionBucketID', $data)) unset($data['commissionBucketID']);
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
     * @param BucketCommissionBucketsEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketCommissionBucketsEntity $entity, array $fields, array $criteria)
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
     *
     */
    public function getBucketReceivablesCount(array $filters)
    {
        $bucketID = array_key_exists('bucketID', $filters) ? $filters['bucketID'] : 0;
        $commissionBucketID = array_key_exists('commissionBucketID', $filters) ? $filters['commissionBucketID'] : 0;

        $query = "
            SELECT COUNT(r.commissionBucketID)
            FROM ".self::getTableName() . " r
            WHERE r.bucketID = :bucketID";

        if($commissionBucketID) {
            $query .= " AND r.commissionBucketID != :commissionBucketID";
        }

         // This should be optional
        if(array_key_exists('begDate', $filters)) {
            $query .= " AND begDate < :begDate";
        }
        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $bucketID, PDO::PARAM_INT);

        if($commissionBucketID) {
            $stmt->bindValue(':commissionBucketID', $commissionBucketID, PDO::PARAM_INT);
        }

        if(array_key_exists('begDate', $filters)) {
            $stmt->bindValue(':begDate', $filters['begDate'], PDO::PARAM_STR);
        }

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
    public function getBucketReceivablesRecords(array $filters, array $limit)
    {

        $params = array(':bucketID' => @$filters['bucketID'] ? $filters['bucketID'] : 0);

        if(isset($limit['offset'])) {
            $params[':offset'] = intval($limit['offset']);
        }

        if(isset($limit['rowCount'])) {
            $params[':rowCount'] = intval($limit['rowCount']);
        }

        $query = "
            SELECT r.*
            FROM " . self::getTableName() . " r
            WHERE r.bucketID = :bucketID
            ORDER BY r.begDate "
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
     * Get most recent receivable - with null endDate beging most recent
     * @param $bucketID
     */
    public function getBucketReceivablesByBucketAndEndDate($bucketID)
    {
        $query = "
        SELECT *
        FROM " . self::getTableName() . "
        WHERE bucketID = :bucketID
        ORDER BY endDate IS NULL DESC, endDate DESC
        LIMIT 1";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':bucketID', $bucketID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     *
     * Simple SELECT single record
     * @param $commissionBucketID
     * @return PDOStatement
     */
    public function getBucketCommissionBucketsReceivableById($commissionBucketID)
    {
        $query = "SELECT r.* FROM " . $this->getTableName() . " r
            WHERE r.commissionBucketID = :commissionBucketID";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':commissionBucketID', $commissionBucketID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
    * Find records where dates overlap with proposed input,
    * expected to be used for UPDATEs since INSERTs have endDate altering logic built-in
    * @param array $formInput
    * @return PDO::PDOStatement
    */
    public function findReceivableDateConflict(array $formInput)
    {
        $isUpdate = false;
        $endDate = $formInput['endDate'] == null ? '9999-12-31' : date('Y-m-d',strtotime($formInput['endDate']));
        $query = "SELECT commissionBucketID, begDate, endDate FROM " . $this->getTableName() . "
            WHERE bucketID = :bucketID ";

        // to eliminate conflict with oneself is to find enlightenment
        if(array_key_exists('commissionBucketID',$formInput)
            && !empty($formInput['commissionBucketID'])) {
            $isUpdate = true;
            $query .= "AND commissionBucketID != :commissionBucketID ";
        }

        $query .= "AND (
                   (begDate <= :begDate AND endDate > :begDate) OR
                   (begDate < :endDate AND endDate > :endDate) OR
                   (endDate > :begDate AND endDate < :endDate)
                   )";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':begDate', date('Y-m-d',strtotime($formInput['begDate'])), PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindValue(':bucketID', $formInput['bucketID'], PDO::PARAM_INT);
        if($isUpdate) {
            $stmt->bindValue(':commissionBucketID', $formInput['commissionBucketID'], PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt;
    }

}
