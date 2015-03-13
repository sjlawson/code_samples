<?php

namespace MHCCcrsManager\DataAccess\Connections;

use Database;

/**
 * Database connection class to 'ccrs2'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class Ccrs2Connection extends AbstractConnection
{
    const DRUPAL_CONNECTION_NAME = 'ccrs2';
    const DRUPAL_CONNECTION_TARGET = 'default';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Drupal Database API class
        $connectionInfo = Database::getConnectionInfo(self::DRUPAL_CONNECTION_NAME);
        $connectionInfo = $connectionInfo[self::DRUPAL_CONNECTION_TARGET];

        parent::__construct($connectionInfo);
    }
}
