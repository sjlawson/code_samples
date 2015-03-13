<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveInternal;

use ReviveUsageData\DataAccess\Connections\DatabaseConnectionInterface;
use ReviveUsageData\DataAccess\Connections\ReviveInternalConnection;
use ReviveUsageData\DataAccess\Tables\DatabaseInterface;

/**
 * The database table class for 'revive_internal'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-06
 */
abstract class ReviveInternalDatabaseTable implements DatabaseInterface
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

        return $databases[ReviveInternalConnection::DRUPAL_CONNECTION_NAME][ReviveInternalConnection::DRUPAL_CONNECTION_TARGET]['database'];
    }
}
