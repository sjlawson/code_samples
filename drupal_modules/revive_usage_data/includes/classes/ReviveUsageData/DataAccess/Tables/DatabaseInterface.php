<?php

namespace ReviveUsageData\DataAccess\Tables;

/**
 * Database interface for table classes.
 *
 * @date 2014-05-06
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 */
interface DatabaseInterface
{
    /**
     * Getter for the database name.
     *
     * @return string
     */
    public static function getDatabaseName();
}
