<?php

namespace DealerLedger\DataAccess\Tables\Mhcdynad;

use DealerLedger\DataAccess\Connections\DatabaseConnectionInterface;
use DealerLedger\DataAccess\Connections\MhcdynadConnection;
use DealerLedger\DataAccess\Tables\DatabaseInterface;

/**
 * The database table class for 'mhcdyna'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
abstract class MhcdynadDatabaseTable implements DatabaseInterface
{
    protected $connection;

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

        return $databases[MhcdynadConnection::DRUPAL_CONNECTION_NAME][MhcdynadConnection::DRUPAL_CONNECTION_TARGET]['database'];
    }
}
