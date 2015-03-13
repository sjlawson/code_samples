<?php

namespace DealerLedger\DataAccess\Connections;

use Closure;
use PDOStatement;

/**
 * Database connection interface for connection classes.
 *
 * @date 2014-06-19
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 */
interface DatabaseConnectionInterface
{
    public function getConnection();
    public function prepareQuery($query);
    public function executeQuery(PDOStatement &$stmt, array $params = array());
    public function transactional(Closure $func);
    public function getLastInsertId($seqName = null);
    public function insert($tableName, array $data = array());
    public function update($tableName, array $data, array $criteria);
    public function delete($tableName, array $criteria);
}
