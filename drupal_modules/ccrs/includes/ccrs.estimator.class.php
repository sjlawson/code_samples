<?php

/**
 * estimates the vzw receivable amount
 */
class ccrsEstimator extends ccrs
{

    public $monthYear;

    public function __construct($options, $table, $monthYear)
    {
        $this->setDB();
        $this->options = $options;
        $this->table = $table;
        $this->monthYear = date('Y-m-1', strtotime($monthYear));
    }

    public function determineCopyEstimates()
    {
        //if current month, or last months file but not past the 2nd; copy the estimates to the actual columns
        // return (($this->monthYear==$this->t->getDate($this->t->getDate('now', time::FIRST))) || ($this->monthYear==$this->t->getDate($this->t->getDate('last month', time::FIRST)) && date('d')<3));
        return (($this->monthYear == date("Y-m-1", time())) || ($this->monthYear == date("Y-m-1", strtotime('last month')) && date('d') < 3));
    }

    public function estimateCommissions()
    {
        $this->addUpdateLog('', 0, 'Beginning Estimator.');

        switch ($this->table) {
            case self::ACTS:
            case self::DEACTS:
            case self::REACTS:
            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $error = $this->estimateAdvanceDeviceCommissions();
                if ($error)
                    return $error;

                $error = $this->estimateNonCommissionables();
                if ($error)
                    return $error;

                $error = $this->estimateIphones();
                if ($error)
                    return $error;

                $error = $this->estimateHomePhoneConnects();
                if ($error)
                    return $error;

                $error = $this->estimateALPHPCbase();
                if ($error)
                    return $error;

                $error = $this->estimateALPHPCadditional();
                if ($error)
                    return $error;

                $error = $this->estimateHomeFusions();
                if ($error)
                    return $error;

                $error = $this->estimateALPHFbase();
                if ($error)
                    return $error;

                $error = $this->estimateALPHFadditional();
                if ($error)
                    return $error;

                $error = $this->estimateALPbase();
                if ($error)
                    return $error;

                $error = $this->estimateALPadditional(true);
                if ($error)
                    return $error;

                $error = $this->estimateALPadditional(false);
                if ($error)
                    return $error;

                break;

            default:
                return;
        }

        switch ($this->table) {
            case self::ACTS:
            case self::DEACTS:
            case self::REACTS:
                $error = $this->markEligibleForTierBonus();
                if ($error)
                    return $error;

                $error = $this->estimateTierBonus();
                if ($error)
                    return $error;

                $error = $this->estimatePrepaidActivations();
                if ($error)
                    return $error;

                $error = $this->estimateRegularPostpayActivations();
                if ($error)
                    return $error;

                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:

                $error = $this->estimateALPadditionalNE2(true);
                if ($error)
                    return $error;

                $error = $this->estimateALPadditionalNE2(false);
                if ($error)
                    return $error;

                $error = $this->estimateRegularPostpayUpgrades();
                if ($error)
                    return $error;

                break;
        }

        $error = $this->copyEstimatedCommissions();
        if ($error)
            return $error;
        $this->addUpdateLog('', 0, 'Finished Estimator.');
    }

    public function copyEstimatedCommissions()
    {
        $this->addUpdateLog('', 0, 'Determining whether to copy estimated commissions to actual.');

        if ($this->determineCopyEstimates()) {
            switch ($this->table) {
                case self::ACTS:
                case self::DEACTS:
                case self::REACTS:
                    $planTable = "estimator_activation_plans";
                    break;

                case self::UPGRADES:
                case self::UPGRADE_DEACTS:
                    $planTable = "estimator_upgrade_plans";
                    break;
            }

            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table}
                (id, commissionAmount)
                SELECT *
                FROM(
                 SELECT c.id, IFNULL(p.commission,0) AS commission
                 FROM {$this->options->_dbName}.{$this->table} c
                 LEFT JOIN {$this->options->_dbName}.{$planTable} p ON p.id=c.estimatorID
                 WHERE monthYear='{$this->monthYear}' AND visionCode NOT LIKE '%E%'
                ) AS t1
                ON DUPLICATE KEY
                UPDATE commissionAmount=commission
            ";
            try {
                $result = $this->_db->query($SQL);
            } catch (exception $e) {
                return "There was an error during estimate copying. " . $e->getMessage();
            }
            $this->addUpdateLog('', $result->rowCount(), 'Copied estimated commissions to actual.');
        } else {
            $this->addUpdateLog('', 0, 'Declined to copy estimated commissions to actual.');
        }
    }

    public function estimatePrepaidActivations()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::PREPAID . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.estimator_activation_plans
               WHERE isPrepaid AND (pricePlan BETWEEN begPrice AND endPrice) AND (contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND ((!contractLength AND !pricePlan) OR (visionCode IN (SELECT * FROM {$this->options->_dbName}.estimator_activation_prepaid_vision_codes))) AND estimatorID IS NULL AND visionCode NOT LIKE '%E%'
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID=eID, contractTypeID=contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during prepaids. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated Prepaid Activations.');
    }

    public function estimateRegularPostpayActivations()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, contractType
            FROM(
             SELECT *,
             (IF(visionCode IN (SELECT * FROM {$this->options->_dbName}.estimator_activation_family_share_vision_codes),
             (
              /* Family Share */
              SELECT id
              FROM {$this->options->_dbName}.estimator_activation_plans
              WHERE isPostpaid AND isFamilyShare AND (pricePlan BETWEEN begPrice AND endPrice) AND (contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength),
              /* Regular Postpay */
              (
              SELECT id
              FROM {$this->options->_dbName}.estimator_activation_plans
              WHERE isPostpaid AND !isFamilyShare AND (pricePlan BETWEEN begPrice AND endPrice) AND (contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength)
              )
             ) AS eID,
             IF(visionCode IN (SELECT * FROM {$this->options->_dbName}.estimator_activation_family_share_vision_codes)," . self::LLPFAMILYSHARE . "," . self::LLP . ") AS contractType
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND visionCode NOT LIKE '%i%' AND pricePlan AND estimatorID IS NULL AND visionCode NOT LIKE '%E%'
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID=eID, contractTypeID=contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during postpaid acts. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated Regular Postpaid Activations.');
    }

    public function estimateRegularPostpayUpgrades()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, contractType
            FROM(
             SELECT *,
              (IF(visionCode IN (SELECT * FROM {$this->options->_dbName}.estimator_upgrade_family_share_vision_codes),

               /* Family Share */
               IF(upgradeType IN ('NE1','NE2'),
                /* NE2 */
                (
                 SELECT id
                 FROM {$this->options->_dbName}.estimator_upgrade_plans
                 WHERE isPostpaid AND isFamilyShare AND isNE2 AND (pricePlan BETWEEN begPrice AND endPrice) AND (new_esn_contractdate_start BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
                 ),
                /* Regular Family Share */
                (
                 SELECT id
                 FROM {$this->options->_dbName}.estimator_upgrade_plans
                 WHERE isPostpaid AND isFamilyShare AND !isNE2 AND (pricePlan BETWEEN begPrice AND endPrice) AND (new_esn_contractdate_start BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
                 )
                ),
               /* Non-Family Share Postpay */
               IF(upgradeType IN ('NE1','NE2'),
               /* NE2 */
               (
                SELECT id
                FROM {$this->options->_dbName}.estimator_upgrade_plans
                WHERE isPostpaid AND !isFamilyShare AND isNE2 AND (pricePlan BETWEEN begPrice AND endPrice) AND (new_esn_contractdate_start BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
               ),
               /* Regular Postpay */
               (
                SELECT id
                FROM {$this->options->_dbName}.estimator_upgrade_plans
                WHERE isPostpaid AND !isFamilyShare AND !isNE2 AND (pricePlan BETWEEN begPrice AND endPrice) AND (new_esn_contractdate_start BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
               ))
              )
             ) AS eID,
             IF(visionCode IN (SELECT * FROM {$this->options->_dbName}.estimator_upgrade_family_share_vision_codes),
              IF(upgradeType IN ('NE1','NE2'), " . self::LLPNE2FAMILYSHARE . ", " . self::LLPFAMILYSHARE . ")
             ,
              IF(upgradeType IN ('NE1','NE2'), " . self::LLPNE2 . ", " . self::LLP . ")
             ) AS contractType
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND visionCode NOT LIKE '%i%' AND pricePlan AND estimatorID IS NULL
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID=eID, contractTypeID=contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during postpaid ups. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated Regular Postpaid Upgrades.');
    }

    public function estimateAdvanceDeviceCommissions()
    {
        $this->addUpdateLog('', 0, 'Begin Advanced Device Commission.');
        $prepaid = '';

        switch ($this->table) {
            case self::ACTS:
            case self::DEACTS:
            case self::REACTS:
                $contractDate = 'contractDate';
                $nonCommTable = 'estimator_activation_non_commissonable_vision_codes';
                $alpLimitedTable = 'estimator_activation_alp_limited_vision_codes';
                $alpUnLimitedTable = 'estimator_activation_alp_unlimited_vision_codes';
                $prepaid = "
                    UNION

                    SELECT *
                    FROM {$this->options->_dbName}.estimator_activation_prepaid_vision_codes
                ";
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $contractDate = 'new_esn_contractdate_start';
                $nonCommTable = 'estimator_upgrade_non_commissonable_vision_codes';
                $alpLimitedTable = 'estimator_upgrade_alp_limited_vision_codes';
                $alpUnLimitedTable = 'estimator_upgrade_alp_unlimited_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, additionalCommissionEstimatorID)
            SELECT c.id, p.bucketID
            FROM {$this->options->_dbName}.{$this->table} c
            LEFT JOIN {$this->options->_dbName}.ccrs_pda p ON p.phoneDescription=c.phoneDescription AND ($contractDate BETWEEN begDate AND IFNULL(endDate,NOW()))
            WHERE monthyear='{$this->monthYear}' AND isPDA AND contractLength NOT IN (0, 12) AND !wasPreviouslyActivated AND visionCode NOT LIKE '%E%' AND visionCode NOT IN (
                SELECT *
                FROM {$this->options->_dbName}.{$nonCommTable}

                UNION

                SELECT *
                FROM {$this->options->_dbName}.{$alpLimitedTable}

                UNION

                SELECT *
                FROM {$this->options->_dbName}.{$alpUnLimitedTable}

                $prepaid
            )
            ON DUPLICATE KEY
            UPDATE additionalCommissionEstimatorID = p.bucketID
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during advanced devices. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated Advanced Device Commission.');

        if ($this->determineCopyEstimates()) {
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table}
                (id, additionalCommission)
                SELECT *
                FROM(
                 SELECT c.id, IFNULL(p.commission,0) AS commission
                 FROM {$this->options->_dbName}.{$this->table} c
                 LEFT JOIN {$this->options->_dbName}.estimator_advanced_device_buckets p ON p.id=c.additionalCommissionEstimatorID
                 WHERE monthYear='{$this->monthYear}'
                ) AS t1
                ON DUPLICATE KEY
                UPDATE additionalCommission = commission
            ";
            try {
                $result = $this->_db->query($SQL);
            } catch (exception $e) {
                return "There was an error during advanced device copying. " . $e->getMessage();
            }
            $this->addUpdateLog($this->table, $result->rowCount(), 'Advanced Device Estimates Copied to actual.');

            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table}
                (id, spiff)
                SELECT *
                FROM(
                 SELECT id, spiff-additionalCommission AS newSpiff
                 FROM {$this->options->_dbName}.{$this->table}
                 WHERE monthYear>='2011-10-1' AND spiff>=additionalCommission AND spiff /* Date is hardcoded beg date*/
                ) AS t1
                ON DUPLICATE KEY
                UPDATE spiff = newSpiff
            ";
            try {
                $result = $this->_db->query($SQL);
            } catch (exception $e) {
                return "There was an error during spiff removal. " . $e->getMessage();
            }
            $this->addUpdateLog($this->table, $result->rowCount(), 'Advanced Device Estimates Removed from Spiff.');
        } else {
            //actual vz numbers
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table}
                (id, spiff, additionalCommission)
                SELECT *
                FROM(
                 SELECT t1.id, adb.commission, spiff-commission AS newSpiff
                 FROM {$this->options->_dbName}.{$this->table} t1
                 LEFT JOIN {$this->options->_dbName}.estimator_advanced_device_buckets adb ON adb.id=t1.additionalCommissionEstimatorID
                 WHERE monthYear='{$this->monthYear}' AND adb.commission IS NOT NULL AND spiff>=adb.commission
                ) AS t1
                ON DUPLICATE KEY
                UPDATE spiff = t1.newSpiff, additionalCommission = t1.commission
            ";
            try {
                $result = $this->_db->query($SQL);
            } catch (exception $e) {
                return "There was an error during additional from spiff move. " . $e->getMessage();
            }
            $this->addUpdateLog($this->table, $result->rowCount(), 'Advanced Device Moved to Additional from Spiff.');
        }
    }

    public function estimateTierBonus()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, tierBonusEstimatorID)
            SELECT *
            FROM(
            SELECT id,
             45 /* (SELECT id FROM {$this->options->_dbName}.estimator_activation_tier_bonus WHERE scheduleID=5 AND ((eligibleActs-eligibleDeacts) BETWEEN MIN AND MAX) AND ('{$this->monthYear}' BETWEEN begDate AND IFNULL(endDate,'9999-1-1'))) */ AS tbeID
            FROM(
             SELECT *,
              ((
               SELECT COUNT(*)
               FROM {$this->options->_dbName}.ccrs_activations c
               WHERE monthyear='{$this->monthYear}' AND isTierBonusEligible
              )+(
               SELECT COUNT(*)
               FROM {$this->options->_dbName}.ccrs_reactivations c
               WHERE monthyear='{$this->monthYear}' AND isTierBonusEligible
              )) AS eligibleActs,
              (
               SELECT COUNT(*)
               FROM {$this->options->_dbName}.ccrs_deactivations c
               WHERE monthyear='{$this->monthYear}' AND isTierBonusEligible
              ) AS eligibleDeacts
             FROM(
              SELECT id
              FROM {$this->options->_dbName}.{$this->table} c
              WHERE monthyear='{$this->monthYear}' AND isTierBonusEligible AND visionCode NOT LIKE '%E%'
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE tierBonusEstimatorID = tbeID
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during tier estimate. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated Tier Bonus');

        $this->addUpdateLog('', 0, 'Determining whether to copy estimated tier bonus to actual.');
        if ($this->determineCopyEstimates()) {
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table}
                (id, tierBonus)
                SELECT *
                FROM(
                 SELECT c.id, IFNULL(t.amount,0) AS amount
                 FROM {$this->options->_dbName}.{$this->table} c
                 LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus t ON t.id=c.tierBonusEstimatorID
                 WHERE monthYear='{$this->monthYear}'
                ) AS t1
                ON DUPLICATE KEY
                UPDATE tierBonus = amount
            ";
            try {
                $result = $this->_db->query($SQL);
            } catch (exception $e) {
                return "There was an error during copy of tier. " . $e->getMessage();
            }
            $this->addUpdateLog('', $result->rowCount(), 'Copied estimated Tier Bonus onto Actual.');
        } else {
            $this->addUpdateLog('', 0, 'Declined to copy estimated commissions to actual.');
        }
    }

    /**
     * ALP both Acts and Ups HPC Only
     *
     * @return type
     */
    public function estimateALPHPCbase()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::HPC . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isHomePhoneConnect AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND visionCode='99999' AND estimatorID IS NULL AND phoneDescription IN (
               SELECT phoneDescription FROM {$this->options->_dbName}.ccrs_hpc
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during ALP HPC Base estimates. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated ALP HPC Base.');
    }

    public function estimateALPHPCadditional()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $visionTable1 = 'estimator_activation_alp_limited_vision_codes';
                $visionTable2 = 'estimator_activation_alp_unlimited_vision_codes';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $visionTable1 = 'estimator_upgrade_alp_limited_vision_codes';
                $visionTable2 = 'estimator_upgrade_alp_unlimited_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::HPC . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isNonCommissionable AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND phoneDescription IN (
               SELECT phoneDescription FROM {$this->options->_dbName}.ccrs_hpc
              )
              AND visionCode IN(
               SELECT visionCode
               FROM {$this->options->_dbName}.$visionTable1

               UNION

               SELECT visionCode
               FROM {$this->options->_dbName}.$visionTable2
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = t1.eID, contractTypeID = t1.contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during ALP HPC Additional. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'ALP HPC Additional');
    }

    /**
     * ALP both Acts and Ups non-self::HPC
     *
     * @return type
     */
    public function estimateALPbase()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::ALPBASE . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isALPbase AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE  monthYear='{$this->monthYear}' AND visionCode='99999' AND estimatorID IS NULL
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during ALP Base estimate. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated ALP BASE');
    }

    public function estimateALPadditional($isLimited)
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $isNE2 = '';
                $upgradeType = '';

                if ($isLimited) {
                    $visionTable = 'estimator_activation_alp_limited_vision_codes';
                    $limitedFlag = 'isALPlimited';
                    $contractTypeID = self::ALPLIMITED;
                } else {
                    $visionTable = 'estimator_activation_alp_unlimited_vision_codes';
                    $limitedFlag = '!isALPlimited';
                    $contractTypeID = self::ALPUNLIMITED;
                }
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $isNE2 = " AND !isNE2 ";
                $upgradeType = " AND upgradeType NOT IN ('NE1', 'NE2') ";
                if ($isLimited) {
                    $visionTable = 'estimator_upgrade_alp_limited_vision_codes';
                    $limitedFlag = 'isALPlimited';
                    $contractTypeID = self::ALPLIMITED;
                } else {
                    $visionTable = 'estimator_upgrade_alp_unlimited_vision_codes';
                    $limitedFlag = '!isALPlimited';
                    $contractTypeID = self::ALPUNLIMITED;
                }
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, {$contractTypeID} AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isALP AND !isALPbase AND $limitedFlag $isNE2 AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE  monthYear='{$this->monthYear}' $upgradeType AND estimatorID IS NULL AND visionCode IN (
               SELECT *
               FROM {$this->options->_dbName}.$visionTable
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = t1.eID, contractTypeID = t1.contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during ALP Additional Estimate. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated ALP Additional');
    }

    public function estimateALPadditionalNE2($isLimited)
    {
        $planTable = 'estimator_upgrade_plans';
        $contractDate = 'new_esn_contractdate_start';
        $isNE2 = " AND isNE2 ";
        $upgradeType = " AND upgradeType IN ('NE1', 'NE2') ";

        if ($isLimited) {
            $visionTable = 'estimator_upgrade_alp_limited_vision_codes';
            $limitedFlag = 'isALPlimited';
            $contractTypeID = self::ALPNE2LIMITED;
        } else {
            $visionTable = 'estimator_upgrade_alp_unlimited_vision_codes';
            $limitedFlag = '!isALPlimited';
            $contractTypeID = self::ALPNE2UNLIMITED;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, {$contractTypeID} AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isALP AND $limitedFlag $isNE2 AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE  monthYear='{$this->monthYear}' $upgradeType AND estimatorID IS NULL AND visionCode IN (
               SELECT *
               FROM {$this->options->_dbName}.$visionTable
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during ALP Additional NE2. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated ALP Additional NE2.');
    }

    /**
     * iphone both acts and ups
     *
     * @return type
     */
    public function estimateIphones()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $familyShareTable = 'estimator_activation_family_share_vision_codes';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $familyShareTable = 'estimator_upgrade_family_share_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, contractType
            FROM(
             SELECT *,
              (IF(visionCode IN (SELECT * FROM {$this->options->_dbName}.$familyShareTable),
              (
               /* Family Share */
               SELECT id
               FROM {$this->options->_dbName}.$planTable e
               WHERE isIphone AND isFamilyShare AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength),
               /* Regular Postpay */
               (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isIphone AND !isFamilyShare AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength)
               )
              ) AS eID,
              IF(visionCode IN (SELECT * FROM {$this->options->_dbName}.$familyShareTable)," . self::IPHONEFAMILYSHARE . "," . self::IPHONE . ") AS contractType
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND visionCode LIKE '%i%' AND estimatorID IS NULL
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during Iphone Estimate. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated iPhones.');
    }

    /**
     * home phone connect both acts and ups
     *
     * @return type
     */
    public function estimateHomePhoneConnects()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $visionTable = 'estimator_activation_home_phone_connect_vision_codes';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $visionTable = 'estimator_upgrade_home_phone_connect_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::HPC . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isHomePhoneConnect AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND estimatorID IS NULL AND visionCode IN (
               SELECT visionCode
               FROM {$this->options->_dbName}.$visionTable
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during LLP HPC Estimate. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated LLP HPC.');
    }

    /**
     * Home Fusion
     *
     * @return type
     */
    public function estimateHomeFusions()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $visionTable = 'estimator_activation_home_fusion_vision_codes';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $visionTable = 'estimator_upgrade_home_fusion_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::HF . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isHomeFusion AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND estimatorID IS NULL AND visionCode IN (
               SELECT visionCode
               FROM {$this->options->_dbName}.$visionTable
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during Estimated LLP HF. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated LLP HF.');
    }

    public function estimateALPHFbase()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::HF . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isHomeFusion AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND visionCode='99999' AND estimatorID IS NULL AND phoneDescription IN (
               SELECT phoneDescription FROM {$this->options->_dbName}.ccrs_hf
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during Estimated ALP HF Base. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated ALP HF Base.');
    }

    public function estimateALPHFadditional()
    {
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $visionTable1 = 'estimator_activation_alp_limited_vision_codes';
                $visionTable2 = 'estimator_activation_alp_unlimited_vision_codes';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $visionTable1 = 'estimator_upgrade_alp_limited_vision_codes';
                $visionTable2 = 'estimator_upgrade_alp_unlimited_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::HF . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isNonCommissionable AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE monthYear='{$this->monthYear}' AND phoneDescription IN (
               SELECT phoneDescription FROM {$this->options->_dbName}.ccrs_hf
              )
              AND visionCode IN(
               SELECT visionCode
               FROM {$this->options->_dbName}.$visionTable1

               UNION

               SELECT visionCode
               FROM {$this->options->_dbName}.$visionTable2
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = t1.eID, contractTypeID = t1.contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during ALP HF Additional. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'ALP HF Additional');
    }

    /**
     * non-commissionable both acts and ups
     *
     * @return type
     */
    public function estimateNonCommissionables()
    {
        $this->addUpdateLog('', 0, 'Begin Non-Comms.');
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $planTable = 'estimator_activation_plans';
                $contractDate = 'contractDate';
                $visionTable = 'estimator_activation_non_commissonable_vision_codes';
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planTable = 'estimator_upgrade_plans';
                $contractDate = 'new_esn_contractdate_start';
                $visionTable = 'estimator_upgrade_non_commissonable_vision_codes';
                break;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatorID, contractTypeID)
            SELECT *
            FROM(
            SELECT id, eID, " . self::NONCOMM . " AS contractType
            FROM(
             SELECT *,
              (
               SELECT id
               FROM {$this->options->_dbName}.$planTable
               WHERE isNonCommissionable AND (pricePlan BETWEEN begPrice AND endPrice) AND ($contractDate BETWEEN begDate AND IFNULL(endDate,'9999-1-1')) AND contractLength=t1.contractLength
              ) AS eID
             FROM(
              SELECT *
              FROM {$this->options->_dbName}.{$this->table}
              WHERE  monthYear='{$this->monthYear}' AND visionCode IN (
               SELECT *
               FROM {$this->options->_dbName}.$visionTable
              )
             ) AS t1
            ) AS t1
            ) AS t1
            ON DUPLICATE KEY
            UPDATE estimatorID = eID, contractTypeID = contractType
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during Non-Comm Estimate. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Estimated Non-Comm.');
    }

    public function markEligibleForTierBonus()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id)
            SELECT id
            FROM {$this->options->_dbName}.{$this->table} c
            WHERE
            monthyear='{$this->monthYear}'
            AND phoneDescription NOT IN (SELECT phoneDescription FROM {$this->options->_dbName}.ccrs_hpc)
            AND (
            (visionCode='99999' AND contractLength=24)
            OR
            (
             pricePlan
             AND contractLength
             AND visionCode NOT LIKE '%i%'
             AND visionCode NOT LIKE '%E%'
             AND visionCode NOT IN (
                SELECT *
                FROM {$this->options->_dbName}.estimator_activation_home_phone_connect_vision_codes

                UNION

                SELECT *
                FROM {$this->options->_dbName}.estimator_activation_home_fusion_vision_codes

                UNION

                SELECT visionCode
                FROM {$this->options->_dbName}.estimator_activation_non_commissonable_vision_codes

                UNION

                SELECT visionCode
                FROM {$this->options->_dbName}.estimator_activation_alp_limited_vision_codes

                UNION

                SELECT visionCode
                FROM {$this->options->_dbName}.estimator_activation_alp_unlimited_vision_codes

                UNION

                SELECT visionCode
                FROM {$this->options->_dbName}.estimator_activation_prepaid_vision_codes
             )
            )
            )
            ON DUPLICATE KEY
            UPDATE isTierBonusEligible = 1
        ";
        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during Tier Bonus Marking. " . $e->getMessage();
        }
        $this->addUpdateLog('', $result->rowCount(), 'Marked Phones Eligible for Tier Bonus.');
    }

}
