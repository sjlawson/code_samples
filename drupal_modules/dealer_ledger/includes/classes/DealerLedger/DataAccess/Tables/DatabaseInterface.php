<?php

namespace DealerLedger\DataAccess\Tables;

/**
 * Database interface for table classes.
 *
 * @date 2014-06-19
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
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
