<?php

namespace DealerLedger\DataAccess\Tables;

/**
 * Database table interface for table classes.
 *
 * @date 2014-06-19
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 */
interface DatabaseTableInterface
{
    /**
     * Getter for the database table name.
     *
     * @return string
     */
    public static function getTableName();

    /**
     * Entity factory.
     */
    public static function createEntity(array $data = array());
}
