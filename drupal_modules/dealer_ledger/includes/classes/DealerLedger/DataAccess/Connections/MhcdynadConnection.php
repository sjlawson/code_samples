<?php

namespace DealerLedger\DataAccess\Connections;

use Database;

/**
 * Database connection class to 'mhcdyna'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class MhcdynadConnection extends AbstractConnection
{
    const DRUPAL_CONNECTION_NAME = 'mhcdynad';
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
