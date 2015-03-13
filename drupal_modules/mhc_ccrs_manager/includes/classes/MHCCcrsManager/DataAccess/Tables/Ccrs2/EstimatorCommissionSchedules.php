<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use PDO;
use MHCCcrsManager\DataAccess\Entities\Ccrs2\EstimatorCommissionSchedules as EstimatorCommissionSchedulesEntity;
use MHCCcrsManager\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'ccrs2::estimator_commission_schedules'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-27
 */
class EstimatorCommissionSchedules extends Ccrs2DatabaseTable implements DatabaseTableInterface
{
    const NAME = 'estimator_commission_schedules';

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
     * @return EstimatorCommissionSchedulesEntity
     */
    public static function createEntity(array $data = array())
    {
        return new EstimatorCommissionSchedulesEntity($data);
    }

    /**
     * Helper function to build and execute simple insert statements.
     *
     * @param EstimatorCommissionSchedulesEntity $entity
     * @param array $fields Optional array of field names to insert.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert(EstimatorCommissionSchedulesEntity $entity, array $fields = array())
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
     * @param EstimatorCommissionSchedulesEntity $entity
     * @param array $criteria The update criteria. An associative array of
     *                        fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update(EstimatorCommissionSchedulesEntity $entity, array $fields, array $criteria)
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
     * 
     * Simple get all query
     * @return PDOStatement
     */
    public function getPayoutSchedules()
    {
    	$activePayoutSchedules = array(1,8,10,11);
    	$query = "SELECT * FROM " . self::getTableName() . "
    		WHERE id IN (" . implode(',', $activePayoutSchedules) . ")
    		ORDER BY schedule ASC
    	";
		
    	$stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt;
    }
    
}
