<?php

namespace IconicsInvoicing\DataAccess\Database\Configuration\Connections;

use Database;
use PDO;

class DrupalDatabaseConnectionInformation implements DatabaseConnectionInformationInterface
{
    /** @var string */
    protected $key;

    /** @var string */
    protected $target;

    /**
     * Constructor
     */
    public function __construct($key, $target)
    {
        $this->key = $key;
        $this->target = $target;
    }

    public function getDsn()
    {
        $connectionInfo = $this->getConnectionInfo();
        if (isset($connectionInfo['dsn'])) {
            return $connectionInfo['dsn'];
        }

        $connectionDsn = 'mysql:host=' . $connectionInfo['host'] .
            ';dbname=' . $connectionInfo['database'];

        if (isset($connectionInfo['port']) &&
            is_numeric($connectionInfo['port'])) {
            $connectionDsn .= ';port=' . $connectionInfo['port'];
        }

        return $connectionDsn;
    }

    public function getOptions()
    {
        return array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
    }

    public function getPassword()
    {
        $connectionInfo = $this->getConnectionInfo();
        if (!array_key_exists('password', $connectionInfo)) {
            throw new \RuntimeException('Drupal connection: no password.');
        }

        return $connectionInfo['password'];
    }

    public function getUsername()
    {
        $connectionInfo = $this->getConnectionInfo();
        if (!array_key_exists('username', $connectionInfo)) {
            throw new \RuntimeException('Drupal connection: no username.');
        }

        return $connectionInfo['username'];
    }

    /**
     * Gets the connection info from Drupal.
     *
     * @return array
     */
    protected function getConnectionInfo()
    {
        $connectionInfo = Database::getConnectionInfo($this->key);

        return $connectionInfo[$this->target];
    }
}
