<?php

namespace MHCCcrsManager\DataAccess\Connections;

use Closure;
use Exception;
use PDO;
use PDOStatement;

/**
 * Abstract base class for database connections.
 *
 * Inspiration:
 * https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Connection.php
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
abstract class AbstractConnection implements DatabaseConnectionInterface
{
    /** @var string */
    protected $connectionDsn;

    /** @var string */
    protected $connectionUsername;

    /** @var string */
    protected $connectionPassword;

    /** @var array */
    protected $connectionOptions;

    /** @var PDO|null */
    protected $pdoConnection;

    /**
     * Constructor
     */
    public function __construct(array $connectionInfo)
    {
        // Build the DSN.
        if (isset($connectionInfo['dsn'])) {
            $this->connectionDsn = $connectionInfo['dsn'];
        }
        else {
            $this->connectionDsn = 'mysql:host=' . $connectionInfo['host'] .
                ';dbname=' . $connectionInfo['database'];

            if (isset($connectionInfo['port']) &&
                is_numeric($connectionInfo['port'])) {
                $this->connectionDsn .= ';port=' . $connectionInfo['port'];
            }
        }

        $this->connectionUsername = $connectionInfo['username'];
        $this->connectionPassword = $connectionInfo['password'];
        $this->connectionOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

        // Lazy-load the database connection.
        $this->pdoConnection = null;
    }

    /**
     * Will return a connection to this database.
     *
     * @return PDO
     */
    public function getConnection()
    {
        if ($this->pdoConnection === null) {
            $this->pdoConnection = new PDO($this->connectionDsn,
                                           $this->connectionUsername,
                                           $this->connectionPassword,
                                           $this->connectionOptions);
        }

        return $this->pdoConnection;
    }

    /**
     * Will prepare a PDO statement.
     *
     * @param string $query
     *
     * @return PDOStatement
     */
    public function prepareQuery($query)
    {
        return $this->getConnection()->prepare($query);
    }

    /**
     * Will bind values and execute a PDO statement.
     *
     * Typical usage:
     * $stmt = $connection->prepareQuery($query);
     * if ($connection->executeQuery($stmt, $parameters)) {
     *     $allResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
     * }
     *
     * @param PDOStatement $stmt
     * @param array        $params An associative array of fieldname:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function executeQuery(PDOStatement &$stmt, array $params = array())
    {
        foreach ($params as $field => $value) {
            $key = $field[0] == ':' ? $field : ':' . $field;
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        return $stmt->execute();
    }

    /**
     * Executes a function in a transaction.
     *
     * The function gets passed this Connection instance as an (optional) parameter.
     *
     * If an exception occurs during execution of the function or transaction commit,
     * the transaction is rolled back and the exception re-thrown.
     *
     * @param closure $func The function to execute transactionally.
     */
    public function transactional(Closure $func)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $func($this);
            $connection->commit();
        }
        catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * Returns the id of the last inserted row.
     *
     * @param string|null $seqName Name of the sequence object from which the ID should be returned.
     *
     * @return string The row ID of the last row that was inserted into the database.
     */
    public function getLastInsertId($seqName = null)
    {
        return $this->getConnection()->lastInsertId($seqName);
    }

    /**
     * Will build and execute a simple insert statement.
     *
     * @param string $tableName The name of the table to insert into.
     * @param array  $data      An associative array of fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function insert($tableName, array $data = array())
    {
        if (empty($data)) {
            return $this->executeQuery('INSERT INTO ' . $tableName .
                                       ' () VALUES ()');
        }

        // Build fields array.
        $fieldsArray = array();
        foreach ($data as $field => $value) {
            $fieldsArray[] = $field[0] == ':' ? $field : ':' . $field;
        }

        // Build the query.
        $query = 'INSERT INTO ' . $tableName . ' (' .
            implode(', ', array_keys($data)) . ') VALUES (' .
            implode(', ', $fieldsArray) . ')';

        $stmt = $this->prepareQuery($query);
        return $this->executeQuery($stmt, $data);
    }

    /**
     * Will build and execute a simple update statement.
     *
     * @param string $tableName The name of the table to insert into.
     * @param array  $data      The data to be updated. An associative array of fieldName:value pairs.
     * @param array  $criteria  The update criteria. An associative array of fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function update($tableName, array $data, array $criteria)
    {
        // Build parameter array.
        $parameterArray = array();
        $setArray = array();
        foreach ($data as $field => $value) {
            $field = $field[0] == ':' ? substr($field, 1) : $field;
            $parameterArray['set_' . $field] = $value;
            $setArray[] = $field . ' = :set_' . $field;
        }

        // Build criteria array and parameter array.
        $criteriaArray = array();
        foreach ($criteria as $field => $value) {
            $field = $field[0] == ':' ? substr($field, 1) : $field;
            $parameterArray['criteria_' . $field] = $value;
            $criteriaArray[] = $field . ' = :criteria_' . $field;
        }

        // Build the query.
        $query = 'UPDATE ' . $tableName . ' SET ' .
            implode(', ', $setArray) . ' WHERE ' .
            implode(' AND ', $criteriaArray);

        $stmt = $this->prepareQuery($query);
        return $this->executeQuery($stmt, $parameterArray);
    }

    /**
     * Will build and execute a simple delete statement.
     *
     * @param string $tableName The name of the table to insert into.
     * @param array  $criteria  The delete criteria. An associative array of fieldName:value pairs.
     *
     * @return boolean True on success or false on failure.
     */
    public function delete($tableName, array $criteria)
    {
        // Build criteria array and parameter array.
        $parameterArray = array();
        $criteriaArray = array();
        foreach ($criteria as $field => $value) {
            $field = $field[0] == ':' ? substr($field, 1) : $field;
            $parameterArray[$field] = $value;
            $criteriaArray[] = $field . ' = :' . $field;
        }

        // Build the query.
        $query = 'DELETE FROM ' . $tableName . ' WHERE ' .
            implode(' AND ', $criteriaArray);

        $stmt = $this->prepareQuery($query);
        return $this->executeQuery($stmt, $parameterArray);
    }
}
