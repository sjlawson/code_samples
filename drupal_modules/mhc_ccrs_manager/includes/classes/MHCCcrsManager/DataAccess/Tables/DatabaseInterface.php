<?php

namespace MHCCcrsManager\DataAccess\Tables;

/**
 * Database interface for table classes.
 *
 * @date 2014-03-21
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 */
interface DatabaseInterface
{
    /**
     * Getter for the database name.
     *
     * @return string
     */
    static public function getDatabaseName();
}
