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
               pbk.processName, p.*
            FROM " .
            self::getTableName() . " p
            INNER JOIN revive_api.process_business_keys pbk on (pbk.processBusinessKeysId=p.processBusinessKeysId)
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
        $query = "SELECT p.`processName`, p.* FROM  revive_api.process_business_keys p
                ORDER BY p.`processName`";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * get array of processBusinessKeysID limited to REDS_IN and REDS_OUT
     *
     * @return string csv
     */
    public function getInOutBusinessKeysString()
    {
        $query = "
        SELECT GROUP_CONCAT(pbk.`processBusinessKeysID`)
        FROM
          process_business_keys pbk
        WHERE
          ( pbk.`processName` LIKE 'REDS_IN%' OR pbk.`processName` LIKE 'REDS_OUT%')
        ";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get an array of processID with current filter
     *
     * @param array $filters
     * @return string csv
     */
    public function getProcessIDstringFromFilter($filters)
    {
        // filter the filter
        $reviveDataFilters = $this->formatDataFilter($filters);
        $params = array();
        $whereClause = $this->buildWhereClause($reviveDataFilters, $params);

        // first get list of filtered process IDs
        $query = "
        SELECT
          GROUP_CONCAT(DISTINCT(p.processID))
        FROM "
            . Processes::getTableName() . " p "
            . $whereClause;
        ;

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * Select method, specifically for process i/o export
     * @param $filters - input from form
     * @return PDOStatement
     */
    public function getIOforExport($filters)
    {
        $processIDList = $this->getProcessIDstringFromFilter($filters);

        $inOutBusinessKeys = $this->getInOutBusinessKeysString();

        $query = "
        SELECT
          pdv.`processID`,
          pdv.`datetimeAdded`,
          pbk.`processName`,
          pdv.`processValue`
        FROM
          process_data_values pdv
          INNER JOIN process_business_keys pbk USING (`processBusinessKeysID`)
        WHERE
          pdv.`processID` IN (
          " . $processIDList . "
          )
          AND pdv.`processBusinessKeysID` IN (
          " . $inOutBusinessKeys . "
          )
          ORDER BY pdv.`processID`, pdv.`datetimeAdded`";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute();

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

        $limitClause = (isset($limit['rowCount']) && isset($limit['offset'])) ? " LIMIT " .
            $limit['offset'] . "," . $limit['rowCount'] : '';

        $query = "SELECT
                  p.*
                FROM "
            . Processes::getTableName() ." p "
            . $whereClause
            . " GROUP BY p.`processID` ORDER BY p.`processDateTime` DESC "
            . $limitClause;

        // echo "<pre>$query</pre>";

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

        $query = "
                SELECT
                COUNT(DISTINCT(p.`processID`))
                FROM "
            . Processes::getTableName() ." p "

            . $whereClause;

        // echo "<pre>$query</pre>";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * Get a process businesskey ID from name
     *
     * @param $bkName string
     * @return processBusinessKeysId
     */
    public function getBusinessKeyIDFromName($bkName)
    {
        $query = "SELECT
                        pbk.processBusinessKeysId
                  FROM revive_api.process_business_keys pbk
                  WHERE
                        pbk.processName = :processName";
        $stmt = $this->connection->prepareQuery($query);
        $stmt->execute(array(':processName' => $bkName));

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
                        pbk.`processName`,
                        xp.`processValue`
                  FROM " . Processes::getTableName() . " xp "
            . "LEFT JOIN "
            . \ReviveUsageData\DataAccess\Tables\ReviveInternal\Locations::getTableName()
                . " ril USING (`locationsID`) "
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
        $joinClause = "";

        /* $joinClause = " */
        /*     LEFT JOIN " */
        /*     . self::getTableName() */
        /*     . " pdv USING (`processID`) "; */

        /* $joinClause .= " */
        /*     LEFT JOIN " */
        /*     . \ReviveUsageData\DataAccess\Tables\ReviveInternal\Locations::getTableName() */
        /*         . " ril USING (`locationsID`) "; */

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
        $whereClause = "WHERE 1";

        $processListQuery = "";

        $processListQuery = "";
        $procListQueryJoins = "";
        $procListQueryWhere = " WHERE procSummary.`processID` IS NOT NULL
                ";

        // Take care of business keys first
        if (!empty($reviveDataFilters['key_filters']) ) {
            // this is a special filter for the business key queue
            $keyNameValues = $reviveDataFilters['key_filters'];


            for ($i=0; $i < count($keyNameValues); $i++ ) {

                $keyPair = explode('<>',$keyNameValues[$i]);
                $keyName = $keyPair[0];
                $keyValue = $keyPair[1];
                $procListQueryJoins .= " INNER JOIN " . self::getTableName() . " p".$i . "
                ON (p.`processID` = p$i.`processID`)
                ";
                $procListQueryWhere .= " AND p".$i.".`processBusinessKeysID` = '" . $keyName
                    . "' AND p".$i.".`processValue` = '" . $keyValue . "' ";
            }
        }

        $ljClause = " LEFT JOIN " . Processes::getTableName() . " procSummary ON (
                procSummary.`processID` = p.`processID`
                ";
        foreach ($reviveDataFilters as $field => $value) {
            if (!empty($value)  && $field != 'key_filters' ) {
                if (is_array($value) ) {
                    $ljClause .= " AND " . $field . " IN ( '" . implode("','",$value) . "' ) ";
                } elseif ($field == 'start_date') {
                    $ljClause .= " AND procSummary.`processDatetime` >= '".date('Y-m-d H:i:s', $value)."' ";
                    // $params[':startDate'] = date('Y-m-d H:i:s', $value);
                } elseif ($field == 'end_date') {
                    $ljClause .= " AND procSummary.`processDatetime` <= '".date('Y-m-d H:i:s', $value)."' ";
                    // $params[':endDate'] = date('Y-m-d H:i:s', $value);
                } else {
                    $ljClause .= " AND " . $field . " = :".str_replace('.','_',$field);
                    if (is_array($params)) {
                        $params[':'.str_replace('.','_',$field)] = $value;
                    }
                }
            }
        }
        $ljClause .= " )
        ";

        $whereClause = str_replace('1 AND', '', $ljClause . $procListQueryJoins . $procListQueryWhere);

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
            $returnFilter['p.processID'] = $filters['processID'];
        }

        if (!empty($filters['machineID'])) {
            $returnFilter['p.machineID'] = $filters['machineID'];
        }

        /* if (!empty($filters['processValue'])) { */
        /*     $returnFilter['pdv.processValue'] = $filters['processValue']; */
        /* } */

        if(!empty($filters['configurationsID'])) {
            $returnFilter['p.configurationsID'] = $filters['configurationsID'];
        }

        /* if (!empty($filters['processName'])) { */
        /*     $returnFilter['pdv.processName'] = $filters['processName']; */
        /* } */

        if (!empty($filters['locationID'])) {
            $returnFilter['p.locationsID'] = $filters['locationID'];
        }

        if (!empty($filters['start_date'])) {
            $returnFilter['start_date'] = strtotime($filters['start_date'] . ' 00:00:00');
        }

        if (!empty($filters['end_date'])) {
            $returnFilter['end_date'] = strtotime($filters['end_date'] . ' 23:59:59');
        }

        if( !empty($filters['reviveSuccessful'])) {
            $returnFilter['procSummary.reviveSuccessful'] = intval($filters['reviveSuccessful']);
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
                      (p.`processValue`), p.`processBusinessKeysID`
                  FROM " . self::getTableName() . " p " .
                  "WHERE p.`processBusinessKeysID` = :keyName
                  GROUP BY p.`processValue`";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':keyName', $keyName, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find distinct values from key name
     * @param keyname
     * @return array PDO::FETCH_ASSOC
     */
    public function getDistinctValuesFromKeyID($keyID)
    {
        $query = "SELECT DISTINCT
                      (p.`processValue`), p.`processBusinessKeysID`
                  FROM " . self::getTableName() . " p " .
                  "WHERE p.`processBusinessKeysID` = :keyID
                  GROUP BY p.`processValue`";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':keyID', $keyID, PDO::PARAM_STR);
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
                $optionalFilterClause = " AND ( pbk.`processName` = '$processName' ";
            } else {
                $optionalFilterClause .= " OR pbk.`processName` = '$processName' ";
            }
        }

        $optionalFilterClause = empty($optionalFilterClause) ? '' : $optionalFilterClause . ' ) ';

        $query = "SELECT
                        pbk.`processName`,
                        p.`processValue`,
                        p.`processTimestamp`
                  FROM " . self::getTableName() . " p
                  INNER JOIN revive_api.process_business_keys pbk on (pbk.processBusinessKeysId=p.processBusinessKeysId)
                  WHERE p.`processID` = :processID "
                        . $optionalFilterClause .
                  "ORDER BY pbk.`processName`, p.`processTimestamp` ";

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
                  p.`machineID`,
                  p.`processID`,
                  pbk.`processName`,
                  p.`processValue`,
                  p.`processTimestamp`
                FROM " . self::getTableName() . " p
                INNER JOIN revive_api.process_business_keys pbk on (pbk.processBusinessKeysId=p.processBusinessKeysId)
                WHERE p.processID = :processID
                  AND p.processBusinessKeysId IN (172, 170, 171, 169, 167, 165, 166, 164, 256,257,258,259,260,261,262)
                ORDER BY p.`processTimestamp` ASC";

        /*              (
                        pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_TemperaturePlaten'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_TemperatureDessicant'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_TemperatureInjection'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_RHChamber'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_RHAmbient'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_ModeType'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_Pressure'
                        OR pbk.processName = 'REDS_PROCESS_TableCycles_TableTime_Current'
                        )
        */

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':processID', $processID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Find get process business key from key name
     * @param keyname
     * @return array PDO::FETCH_ASSOC
     */
    public function getProcessBusinessKeyFromKeyName($keyName)
    {
        $query = "SELECT  p.`processBusinessKeysID`
                  FROM revive_api.process_business_keys p " .
                      "WHERE p.`processName` = :keyName";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':keyName', $keyName, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find get process business key from key name
     * @param keyname
     * @return array PDO::FETCH_ASSOC
     */
    public function getProcessBusinessKeyNameFromBusinessID($keyID)
    {
        $query = "SELECT  p.`processName`
                  FROM revive_api.process_business_keys p " .
                      "WHERE p.`processBusinessKeysID` = :keyID";

        $stmt = $this->connection->prepareQuery($query);
        $stmt->bindValue(':keyID', $keyID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcessNameAndValueFromProcessesID()
    {

    }
}
