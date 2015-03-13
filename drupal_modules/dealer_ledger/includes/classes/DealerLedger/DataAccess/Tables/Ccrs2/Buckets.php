<?php

namespace DealerLedger\DataAccess\Tables\Ccrs2;

use DealerLedger\DataAccess\Entities\Ccrs2\Buckets as BucketsEntity;
use DealerLedger\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::buckets'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class Buckets extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'buckets';

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
     * @return BucketsEntity
     */
    public static function createEntity(array $data = array())
    {
        return new BucketsEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param BucketsEntity $entity
     * @param array         $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(BucketsEntity $entity, array $fields = array())
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
     * @param BucketsEntity $entity
     * @param array         $criteria The update criteria. An associative array of
     *                                fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(BucketsEntity $entity, array $fields, array $criteria)
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
     * Get buckets and relevant fields
     * @return PDOStatement
     */
    public function getBuckets()
    {
        $SQL = "
            SELECT
                b.bucketID,
                b.bucketCategoryID,
                bc.description,
                bat.type,
                b.term,
                b.isNE2,
                b.description,
                b.shortDescription
            FROM " . self::getTableName() . " b
            INNER JOIN " . BucketCategories::getTableName() . " bc
                ON bc.bucketCategoryID = b.bucketCategoryID
            INNER JOIN " . BucketActTypes::getTableName() . " bat
                ON bat.actTypeID = b.actTypeID
            ORDER BY b.bucketCategoryID, b.actTypeID, b.term
        ";
        $stmt = $this->connection->prepareQuery($SQL);
        $stmt->execute();

        return $stmt;
    }

}
