<?php

namespace MHCCcrsManager\DataAccess\Tables\Ccrs2;

use MHCCcrsManager\DataAccess\Connections\DatabaseConnectionInterface;
use MHCCcrsManager\DataAccess\Connections\Ccrs2Connection;
use MHCCcrsManager\DataAccess\Tables\DatabaseInterface;

/**
 * The database table class for 'ccrs2'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
abstract class Ccrs2DatabaseTable implements DatabaseInterface
{
    protected $connection;

    /**
     * Constructor
     */
    public function __construct(DatabaseConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    static public function getDatabaseName()
    {
        global $databases;
        return $databases[Ccrs2Connection::DRUPAL_CONNECTION_NAME][Ccrs2Connection::DRUPAL_CONNECTION_TARGET]['database'];
    }
}
