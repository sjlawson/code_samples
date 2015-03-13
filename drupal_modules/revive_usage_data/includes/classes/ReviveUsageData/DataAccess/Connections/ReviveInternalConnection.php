<?php

namespace ReviveUsageData\DataAccess\Connections;

use Database;

/**
 * Database connection class to 'revive_internal'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-06
 */
class ReviveInternalConnection extends AbstractConnection
{
    const DRUPAL_CONNECTION_NAME = 'revive_internal';
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
