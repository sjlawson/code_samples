<?php

namespace IconicsInvoicing\DataAccess\Database;

use IconicsInvoicing\DataAccess\Database\Configuration\Connections\DatabaseConnectionInformationInterface;
use PDO;
use PDOStatement;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Connection implements LoggerAwareInterface
{
    /** @var DatabaseConnectionInformationInterface */
    protected $connectionInfo;

    /** @var LoggerInterface|null */
    protected $logger;

    /** @var PDO|null */
    protected $pdoConnection;

    /**
     * Constructor
     */
    public function __construct(DatabaseConnectionInformationInterface $connectionInfo)
    {
        $this->connectionInfo = $connectionInfo;
        $this->logger = null;
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
            $this->pdoConnection = new PDO($this->connectionInfo->getDsn(),
                                           $this->connectionInfo->getUsername(),
                                           $this->connectionInfo->getPassword(),
                                           $this->connectionInfo->getOptions());

            if ($this->logger !== null) {
                $this->logger->debug('Connected to database: ' . $this->connectionInfo->getDsn());
            }
        }

        return $this->pdoConnection;
    }

    /**
     * Sets a logger instance.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /*
     *  Helper functions
     */

    /**
     * Helper function to execute a simple query.
     *
     * @param string $sql
     * @param array  $parameters (optional)
     * @param array  $types      (optional)
     *
     * @return PDOStatement
     */
    public function execute($sql, array $parameters = array(), array $types = array())
    {
        $this->log($sql, $parameters, $types);
        $pdoStatement = $this->prepare($sql, false);
        $this->bindValues($pdoStatement, $parameters, $types);
        $pdoStatement->execute();

        return $pdoStatement;
    }

    /**
     * Helper function to execute a simple query and return all results.
     *
     * @param string $sql
     * @param array  $parameters
     * @param int    $fetchType
     *
     * @return array
     */
    public function executeAndFetchAllResults($sql, array $parameters = array(), array $types = array(), $fetchType = PDO::FETCH_ASSOC)
    {
        return $this->execute($sql, $parameters, $types)
                    ->fetchAll($fetchType);
    }

    /**
     * Helper function to execute a simple query and return a column of the first result.
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return mixed
     */
    public function executeAndFetchSingleColumnResult($sql, array $parameters = array(), array $types = array(), $column = 0)
    {
        return $this->execute($sql, $parameters, $types)
                    ->fetchColumn($column);
    }

    /**
     * Helper function to execute a simple query and return an array of a single column.
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return mixed
     */
    public function executeAndFetchAllColumnResults($sql, array $parameters = array(), array $types = array(), $column = 0)
    {
        return $this->execute($sql, $parameters, $types)
                    ->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    public function executeAndFetchAllUniqueColumnResults($sql, array $parameters = array(), array $types = array(), $column = 0)
    {
        return $this->execute($sql, $parameters, $types)
                    ->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE, $column);
    }

    /**
     * Helper function to execute a simple query and return a single result.
     *
     * @param string $sql
     * @param array  $parameters
     * @param int    $fetchType
     *
     * @return array
     */
    public function executeAndFetchSingleResult($sql, array $parameters = array(), array $types = array(), $fetchType = PDO::FETCH_ASSOC)
    {
        return $this->execute($sql, $parameters, $types)
                    ->fetch($fetchType);
    }

    /**
     * Helper function to execute a simple query and return the last insert id.
     *
     * @param string $sql
     * @param array  $parameters
     * @param string $name
     *
     * @return string
     */
    public function executeAndGetLastInsertId($sql, array $parameters = array(), array $types = array(), $name = null)
    {
        $connection = $this->getConnection();
        $this->execute($sql, $parameters, $types);

        return $connection->lastInsertId($name);
    }

    /**
     * Helper function to execute a simple query and return the number of rows effected.
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return int
     */
    public function executeAndGetRowCount($sql, array $parameters = array(), array $types = array())
    {
        return $this->execute($sql, $parameters, $types)
                    ->rowCount();
    }

    /**
     * Helper function to bind values.
     */
    public function bindValues(PDOStatement $pdoStatement, array $parameters, array $types = array())
    {
        if (!empty($parameters)) {
            // Check whether parameters are positional or named.
            // Mixing positional and named parameters is not allowed in PDO.
            if (is_int(key($parameters))) {
                // Positional parameters.
                // Need to handle both zero-based and one-based indicies.
                for ($index = 0; $index <= count($parameters); ++$index) {
                    if (isset($parameters[$index])) {
                        if (isset($types[$index])) {
                            $pdoStatement->bindValue($index, $parameters[$index], $types[$index]);
                        } else {
                            $pdoStatement->bindValue($index, $parameters[$index]);
                        }
                    }
                }
            } else {
                // Named parameters
                foreach ($parameters as $name => $value) {
                    if (isset($types[$name])) {
                        $pdoStatement->bindValue($name, $value, $types[$name]);
                    } else {
                        $pdoStatement->bindValue($name, $value);
                    }
                }
            }
        }
    }

    /**
     * Helper function to prepare a query.
     *
     * @param string $sql
     *
     * @return PDOStatement
     */
    public function prepare($sql, $log = true)
    {
        if ($log) {
            $this->log($sql);
        }

        return $this->getConnection()->prepare($sql);
    }

    /**
     * Helper function to start a transaction.
     *
     * @return boolean True if transaction successful.
     */
    public function beginTransaction()
    {
        if ($this->logger !== null) {
            $this->logger->debug('Beginning transaction.');
        }

        return $this->getConnection()->beginTransaction();
    }

    /**
     * Helper function to commit a transaction.
     *
     * @return boolean True if commit successful.
     */
    public function commit()
    {
        if ($this->logger !== null) {
            $this->logger->debug('Committing transaction.');
        }

        return $this->getConnection()->commit();
    }

    /**
     * Helper function to roll back a transaction.
     *
     * @return boolean True if roll back successful.
     */
    public function rollBack()
    {
        if ($this->logger !== null) {
            $this->logger->debug('Rolling back transaction.');
        }

        return $this->getConnection()->rollBack();
    }

    /**
     * Helper function to determine if currently in a transaction.
     *
     * @return boolean True if inside a transaction.
     */
    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Helper function to get the error information from a failed query.
     *
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->getConnection()->getErrorInfo();
    }

    /**
     * Log queries, parameters, and types.
     *
     * @param string $sql
     * @param array  $parameters (optional)
     * @param array  $types      (optional)
     */
    protected function log($sql, array $parameters = array(), array $types = array())
    {
        if ($this->logger !== null) {
            $logMessageArray = array();
            $logMessageArray[] = 'Query: ' . preg_replace('/\s+/', ' ', $sql);
            if (!empty($parameters)) {
                $logMessageArray[] = 'Parameters: ' . json_encode($parameters);

                if (!empty($types)) {
                    $logMessageArray[] = 'Types: ' . json_encode($types);
                }
            }

            $this->logger->debug(implode("\n", $logMessageArray));
        }
    }
}