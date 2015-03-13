<?php

namespace IconicsInvoicing\DataAccess\Database\Configuration;

use ArrayAccess;

class Database implements ArrayAccess
{
    /** @var string */
    protected $commonDatabaseName;

    /** @var string */
    protected $databaseName;

    /** @var array */
    protected $tableNames;

    /**
     * Constructor
     *
     * The $commonDatabaseName should be common across all companies.
     * The $databaseName should be specific to each company.
     *
     * For example: RQ4 replication
     *     - MHC:  $commonDatabaseName = 'RQ4'; $databaseName = 'MooreheadComm'
     *     - Viva: $commonDatabaseName = 'RQ4'; $databaseName = 'VivaMovil'
     *
     * @param string $commonDatabaseName Cross-company db name. (Ex: RQ4)
     * @param string $databaseName       Actual db name (Ex: MooreheadComm)
     */
    public function __construct($commonDatabaseName, $databaseName)
    {
        $this->commonDatabaseName = $commonDatabaseName;
        $this->databaseName = $databaseName;
        $this->tableNames = array();
    }

    public function getCommonDatabaseName()
    {
        return $this->commonDatabaseName;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    public function offsetSet($key, $value)
    {
        $this->tableNames[$key] = $value;
    }

    public function offsetGet($key)
    {
        if (!array_key_exists($key, $this->tableNames)) {
            throw new \InvalidArgumentException(sprintf('Table name "%s" is not defined.', $key));
        }

        return '`' . $this->databaseName . '`.`' . $this->tableNames[$key] . '`';
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->tableNames);
    }

    public function offsetUnset($key)
    {
        unset($this->tableNames[$key]);
    }
}
