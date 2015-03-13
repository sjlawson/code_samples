<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveApi;

use ReviveUsageData\DataAccess\Connections\DatabaseConnectionInterface;
use ReviveUsageData\DataAccess\Connections\ReviveApiConnection;
use ReviveUsageData\DataAccess\Tables\DatabaseInterface;

/**
 * The database table class for 'revive_api'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-06
 */
abstract class ReviveApiDatabaseTable implements DatabaseInterface
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

        return $databases[ReviveApiConnection::DRUPAL_CONNECTION_NAME][ReviveApiConnection::DRUPAL_CONNECTION_TARGET]['database'];
    }
}
