<?php

namespace MHCCcrsManager\DataAccess\Tables;

/**
 * Database table interface for table classes.
 *
 * @date 2014-03-21
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 */
interface DatabaseTableInterface
{
    /**
     * Getter for the database table name.
     *
     * @return string
     */
    static public function getTableName();

    /**
     * Entity factory.
     */
    static public function createEntity(array $data = array());
}
