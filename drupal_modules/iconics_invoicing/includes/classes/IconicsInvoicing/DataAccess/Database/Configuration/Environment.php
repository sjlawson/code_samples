<?php

namespace IconicsInvoicing\DataAccess\Database\Configuration;

use IconicsInvoicing\DataAccess\Database\Configuration\Database;
use IconicsInvoicing\DataAccess\Database\Connection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Environment implements \ArrayAccess, LoggerAwareInterface
{
    /** @var string */
    protected $name;

    /** @var Connection */
    protected $connection;

    /** @var array[Database] */
    protected $databases;

    /**
     * Constructor
     */
    public function __construct($name, Connection $connection)
    {
        $this->name = $name;
        $this->connection = $connection;
        $this->databases = array();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a logger instance.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->connection->setLogger($logger);
    }

    public function offsetSet($key, $value)
    {
        if (!($value instanceof Database)) {
            throw new \InvalidArgumentException('Only "Database" objects can be set.');
        }

        $this->databases[$key] = $value;
    }

    public function offsetGet($key)
    {
        if (!array_key_exists($key, $this->databases)) {
            throw new \InvalidArgumentException(sprintf('Database "%s" is not defined.', $key));
        }

        return $this->databases[$key];
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->databases);
    }

    public function offsetUnset($key)
    {
        unset($this->databases[$key]);
    }
}
