<?php

namespace DealerLedger\DataAccess\Tables\Ccrs2;

use DealerLedger\DataAccess\Connections\DatabaseConnectionInterface;
use DealerLedger\DataAccess\Connections\Ccrs2Connection;
use DealerLedger\DataAccess\Tables\DatabaseInterface;

/**
 * The database table class for 'ccrs2'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
abstract class Ccrs2DatabaseTable implements DatabaseInterface
{
    protected $connection;

    const COOPCOMMISSION = 28;
    const COOPDEACTCOMMISSION = 29;

    /**
     * Constructor
     */
    public function __construct(DatabaseConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public static function getDatabaseName()
    {
        global $databases;

        return $databases[Ccrs2Connection::DRUPAL_CONNECTION_NAME][Ccrs2Connection::DRUPAL_CONNECTION_TARGET]['database'];
    }
}
