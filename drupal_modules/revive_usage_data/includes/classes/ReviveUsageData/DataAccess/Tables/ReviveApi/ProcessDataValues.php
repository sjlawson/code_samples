<?php

namespace ReviveUsageData\DataAccess\Tables\ReviveApi;

use PDO;
use ReviveUsageData\DataAccess\Entities\ReviveApi\ProcessDataValues as ProcessDataValuesEntity;
use ReviveUsageData\DataAccess\Tables\DatabaseTableInterface;

/**
 * Table model for 'revive_api::process_data_values'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-20
 */
class ProcessDataValues extends ReviveApiDatabaseTable implements DatabaseTableInterface
{
    const NAME = 'process_data_values';

    /**
     * Will return the full table name.
     *
     * @return string
     */
    public static function getTableName()
    {
        return self::getDatabaseName() . '.' . self::NAME;
    }

    /**
     * Entity factory.
     *
     * @return ProcessDataValuesEntity
     */
    public static function createEntity(array $data = array())
    {
        return new ProcessDataValuesEntity($data);
    }

    /* C-U-D methods removed, backed up on local machine */
    /* _saved-revive-ProcessDataValues-CUD-methods.php */

    /**
     * Select keys with names like param.
     * @param $likeString
     * @return array PDO::FETCH_ASSOC
     */
    public function getKeyTypes($likeString)
    {
        $query = "SELECT
              *
            FROM " .
            self::getTableName() . " p
            WHERE `processName` LIKE :likeString ";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':likeString', '%'.$likeString.'%', PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get list of business keys by returning DISTINCT process names
     * @return array
     */
    public function getBusinessKeys()
    {
        $query = "SELECT p.`processName`, p.* FROM " .
            self::getTableName() . " p
                GROUP BY p.`processName`
                ORDER BY p.`processName`";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Select method, specifically for process i/o export
     * @param $filters - input from form
     * @return PDOStatement
     */
    public function getIOforExport($filters)
    {
        // filter the filter
        $reviveDataFilters = $this->formatDataFilter($filters);
        $params = array();
        $whereClause = $this->buildWhereClause($reviveDataFilters, $params);

        $query = "SELECT
                      xp.`processID`,
                      xp.`processDatetime`,
                      pdv.`processName`,
                      pdv.`processValue`
                  FROM " . Processes::getTableName() . " xp "
            . $this->buildJoinsClause()
                  . $whereClause .
                  " AND (pdv.`processName` LIKE 'REDS_IN%' OR pdv.`processName` LIKE 'REDS_OUT%')
                    GROUP BY pdv.`processName`, pdv.`processValue`
                    ORDER BY xp.`processID`, pdv.`processTimestamp` ASC ";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute($params);

        return $stmt;
    }


    /**
     * Main select method
     * @param $filters - input from form
     * @param $limit - Drupal paging limit array
     * @return array PDO::FETCH_ASSOC
     */
    public function getUsageData($filters, array $limit)
    {
        // filter the filter
        $reviveDataFilters = $this->formatDataFilter($filters);
        $params = array();
        $whereClause = $this->buildWhereClause($reviveDataFilters, $params);
        $havingClause = $this->buildHavingClause($reviveDataFilters, $params);
        $limitClause = (isset($limit['rowCount']) && isset($limit['offset'])) ? " LIMIT " .
            $limit['offset'] . "," . $limit['rowCount'] : '';

        $query = "SELECT
                  xp.*,
                  ril.`name` AS locationName
                FROM "
            . Processes::getTableName() ." xp "
            . $this->buildJoinsClause()
            . $whereClause
            . " GROUP BY xp.`processID` ORDER BY xp.`processDateTime` DESC "
            . $limitClause;

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Get count based on filter input
     * @param  array $filters
     * @return int
     */
    public function getUsageDataCount(array $filters)
    {
        $reviveDataFilters = $this->formatDataFilter($filters);
        $params = array();
        $whereClause = $this->buildWhereClause($reviveDataFilters, $params);
        $havingClause = $this->buildHavingClause($reviveDataFilters, $params);

        $query = "SELECT
                  COUNT(DISTINCT(xp.`processID`))
                FROM "
            . Processes::getTableName() ." xp "
            . $this->buildJoinsClause()
            . $whereClause;


        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * SELECT method for export full data for processID
     * @param $filters
     * @return array PDO:FETCH_ASSOC
     */
    public function getUsageDataForExport($filters, $limitInt = 0)
    {
        $reviveDataFilters = $this->formatDataFilter($filters);
        $params = array();
        $whereClause = $this->buildWhereClause($reviveDataFilters, $params);

        $query = "SELECT
                        ril.`name` AS locationName,
                        xp.`machineID`,
                        xp.`processID`,
                        xp.`processName`,
                        xp.`processValue`
                  FROM " . Processes::getTableName() . " xp "
                  . $this->buildJoinsClause()
                  . $whereClause .
                  " ORDER BY locationName, xp.`processID`, xp.`machineID`, xp.`processName` ";

        if ($limitInt) {
            $query .= " LIMIT " . $limitInt;
        }

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Same joins used in count & display
     * No logic, just one convenient join clause that can be used in multiple SELECT methods
     *
     * @access private
     * @return string
     */
    private function buildJoinsClause()
    {
        $joinClause = "
            LEFT JOIN "
            . self::getTableName()
                . " pdv USING (`processID`)
            LEFT JOIN "
            . \ReviveUsageData\DataAccess\Tables\ReviveInternal\Locations::getTableName()
                . " ril USING (`locationsID`) ";

        return $joinClause;
    }

    /**
     * Build where clause from url filters
     * @param $reviveDataFilters -- name->value OR name->array
     *          if name->value, the sting is built like: " AND fieldname = :fieldname
     *          special rules applied if the field is startDate or endDate
     *          if name->array, use implode to make csv list for use in
     *                  " AND field IN ( ... , ... , ... )
     * @param &$params
     *          params passed by ref,
     *          when data is not array, $params is populated with data values for PDO
     *          since it is pass-by-ref, the values are available to the calling method
     *          Handles table-field notation (e.g.: ril.`locationName`,
     *          by replacing '.' with '_' only in the parameter
     *          array ('.' in the param name breaks PDO)
     *
     * @return string
     * @access private
     */
    private function buildWhereClause($reviveDataFilters, &$params = null)
    {
        $whereClause = "WHERE 1 ";

        // Take care of business keys first
        if (!empty($reviveDataFilters['key_filters']) ) {
                // this is a special filter for the business key queue

            $keyNameValues = $reviveDataFilters['key_filters'];
            $whereClause .= " AND xp.`processID` IN ( SELECT p.`processID` FROM "
                . self::getTableName() . " p ";

            $subQueryJoins = "";
            $subQueryWhere = " WHERE 1 ";
            for ($i=0; $i < count($keyNameValues); $i++ ) {

                    $keyPair = explode('<>',$keyNameValues[$i]);
                    $keyName = $keyPair[0];
                    $keyValue = $keyPair[1];
                    $subQueryJoins .= "INNER JOIN " . self::getTableName() . " p".$i . " USING (processID)
";
                    $subQueryWhere .= "AND p".$i.".`processName` = '" . $keyName
                        . "' AND p".$i.".`processValue` = '" . $keyValue . "' ";
                }
                $whereClause .= $subQueryJoins . $subQueryWhere . " ) ";
            }

        //now get the rest
        foreach ($reviveDataFilters as $field => $value) {
            if (!empty($value)  && $field != 'key_filters' ) {
                if (is_array($value) ) {
                    $whereClause .= " AND " . $field . " IN ( '" . implode("','",$value) . "' ) ";
                } elseif ($field == 'start_date') {
                    $whereClause .= " AND xp.`processDatetime` >= :startDate ";
                    $params[':startDate'] = date('Y-m-d H:i:s', $value);
                } elseif ($field == 'end_date') {
                    $whereClause .= " AND xp.`processDatetime` <= :endDate ";
                    $params[':endDate'] = date('Y-m-d H:i:s', $value);
                } else {
                    $whereClause .= " AND " . $field . " = :".str_replace('.','_',$field);
                    if (is_array($params)) {
                        $params[':'.str_replace('.','_',$field)] = $value;
                    }

                }
            }
        }


        return $whereClause;
    }

    /**
     * Probable do not need
     *
     */
    private function buildHavingClause($reviveDataFilters, &$params = null)
    {
        $havingFields = array('runLocationsID');
        $havings = "";
        foreach ($reviveDataFilters as $field => $value) {
            if (!empty($value)  && $field != 'key_filters' && in_array($field, $havingFields) ) {
                if (is_array($value)) {
                    $havings .= " AND " .$field . " IN ( '" . implode("','", $value) . "' ) ";
                } else {
                    $havings .= " AND " . $field . " = :".$field;
                    $params[':'.$field] = $value;
                }
            }
        }

        if (!empty($havings)) {
            return " HAVING " . substr($havings, 5);
        } else {
            return '';
        }
    }

    /**
     * Crete filter to be used for building WHERE clause
     * @param  array $filters
     * @return array $returnFilter
     *                       @access private
     */
    private function formatDataFilter($filters)
    {
        $returnFilter = array();

        if (!empty($filters['key_filters'])) {
            $returnFilter['key_filters'] = $filters['key_filters'];
        }
        //the alias 'xp' is for the process data actually being returned or eXported
        if (!empty($filters['processID'])) {
            $returnFilter['xp.processID'] = $filters['processID'];
        }

        if (!empty($filters['machineID'])) {
            $returnFilter['xp.machineID'] = $filters['machineID'];
        }

        /* if (!empty($filters['processValue'])) { */
        /*     $returnFilter['pdv.processValue'] = $filters['processValue']; */
        /* } */

        if(!empty($filters['configurationsID'])) {
            $returnFilter['xp.configurationsID'] = $filters['configurationsID'];
        }

        /* if (!empty($filters['processName'])) { */
        /*     $returnFilter['pdv.processName'] = $filters['processName']; */
        /* } */

        if (!empty($filters['locationID'])) {
            $returnFilter['xp.locationsID'] = $filters['locationID'];
        }

        if (!empty($filters['start_date'])) {
            $returnFilter['start_date'] = strtotime($filters['start_date'] . ' 00:00:00');
        }

        if (!empty($filters['end_date'])) {
            $returnFilter['end_date'] = strtotime($filters['end_date'] . ' 23:59:59');
        }

        return $returnFilter;
    }

    /**
     * Find distinct values from key name
     * @param keyname
     * @return array PDO::FETCH_ASSOC
     */
    public function getDistinctValuesFromKeyName($keyName)
    {
        $query = "SELECT DISTINCT
                      (p.`processValue`), p.`processName`
                  FROM " . self::getTableName() . " p " .
                  "WHERE p.`processName` = :keyName
                  GROUP BY p.`processValue`";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':keyName', $keyName, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Key-value pairs for process data 'Business Key' names
     * @param $processID
     * @param $limit (default 0)
     * @return array PDO::FETCH_ASSOC
     */
    public function getProcessKeyValuePairs($processID, $limitInt = 0, array $optionalFilter = array())
    {
        $optionalFilterClause = "";

        foreach ($optionalFilter as $processName) {
            if(empty($optionalFilterClause)) {
                $optionalFilterClause = " AND ( `processName` = '$processName' ";
            } else {
                $optionalFilterClause .= " OR `processName` = '$processName' ";
            }
        }

        $optionalFilterClause = empty($optionalFilterClause) ? '' : $optionalFilterClause . ' ) ';

        $query = "SELECT DISTINCT(p.`processName`), p.`processValue`
                  FROM " . self::getTableName() . " p
                  WHERE p.`processID` = :processID "
                        . $optionalFilterClause .
                  "ORDER BY p.`processName` ";

        if ($limitInt) {
            $query .= " LIMIT " . $limitInt;
        }

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':processID', $processID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLimitedSet($processID)
    {
        $query = "SELECT
                  `machineID`,
                  `processID`,
                  `processName`,
                  `processValue`,
                  `processTimestamp`
                FROM " . self::getTableName() . "
                WHERE processID = :processID
                  AND (
                        processName = 'REDS_PROCESS_TableCycles_TableTime_TemperaturePlaten'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_TemperatureDessicant'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_TemperatureInjection'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_RHChamber'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_RHAmbient'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_ModeType'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_Pressure'
                        OR processName = 'REDS_PROCESS_TableCycles_TableTime_Current'
                      )
                ORDER BY `processTimestamp` ASC";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':processID', $processID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

}
