<?php

/**
 * implements a new estimator object based on the simplified commission model
 */
class ccrsEstimatorSimplified extends ccrs
{

    /**
     *
     * @param type $options
     * @param type $table
     * @param type $monthYear
     */
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
        return (($this->monthYear == date("Y-m-1", time())) || ($this->monthYear == date("Y-m-1", strtotime('last month')) && date('d') < 3));
    }

    /**
     *get everything where bucketID is null
     * update from this table set isUnknownBucket = 1 where bucketID IS NULL for current month-year
     * (is orphaned by default if bucketID is null
     */
    public function setUnknownBucket()
    {
        $SQL = "UPDATE " . $this->options->_dbName . "." . $this->table . " t
                SET t.`isUnknownBucket` = 1
                WHERE t.`bucketID` IS NULL
                AND t.`monthYear` = :monthYear";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear), array()
            );
        } catch (exception $e) {
            return "There was an error during VZW Commissions Estimate Copying. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish unknown bucket assignment.');
    }

    /**
     * method called to estimate all commissions
     *
     * @return type
     */
    public function estimateCommissions()
    {
        $this->addUpdateLog('', 0, 'Beginning Estimator.');

        //every table
        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:

                $error=$this->estimateNonCommissionables();
                if($error) return $error;

                $error = $this->estimatePrepay();
                if ($error) return $error;

                $error = $this->estimatePostpay();
                if ($error) return $error;

                $error = $this->copyEstimateAmounts();
                if ($error) return $error;

                $error = $this->markEligibleForTierBonus();
                if ($error) return $error;

                $error = $this->setUnknownBucket();
                if ($error) return $error;

                break;

            case self::DEACTS:

                $error = $this->estimatePrepay();
                if ($error) return $error;

                $error = $this->estimatePostpay();
                if ($error) return $error;

                $error = $this->copyEstimateAmounts();
                if ($error) return $error;

                $error = $this->estimateDeactCommissions();
                if ($error) return $error;

                $error = $this->reconcileOrphanDeactsWithTransTable();
                if ($error) return $error;

                $error = $this->markEligibleForTierBonus();
                if ($error) return $error;

                //On deacts/upgdeacts - lookup what its bucket should be, if null leave unknown & orphaned
                $error = $this->setUnknownBucket();
                if ($error) return $error;

                break;

            case self::UPGRADES:

                $error = $this->estimateNonCommissionables();
                if ($error) return $error;

                $error = $this->estimateNE2();
                if ($error) return $error;

                $error = $this->estimatePostpay();
                if ($error) return $error;

                $error = $this->copyEstimateAmounts();
                if ($error) return $error;

                $error = $this->setUnknownBucket();
                if ($error) return $error;

                break;

            case self::UPGRADE_DEACTS:

                $error = $this->estimatePostpay();
                if ($error) return $error;

                $error = $this->copyEstimateAmounts();
                if ($error) return $error;

                $error = $this->estimateDeactCommissions();
                if ($error) return $error;

                $error = $this->reconcileOrphanDeactsWithTransTable();
                if ($error) return $error;

                $error = $this->setUnknownBucket();
                if ($error) return $error;

                break;

            default:
                return;
        }

        $this->addUpdateLog('', 0, 'Finished Estimator.');
    }

    public function copyEstimateAmounts()
    {
        if ($this->determineCopyEstimates()) {
            $this->addUpdateLog('', 0, 'Beginning Copy Commissions.');

            $contractDate  = $this->table === self::UPGRADE_DEACTS ? 'c.new_esn_contractdate_start' : 'c.contractDate';

            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table} (id)
                SELECT c.id
                FROM {$this->options->_dbName}.{$this->table} c
                INNER JOIN {$this->options->_dbName}.bucket_commission_buckets cb
                    ON cb.bucketID = c.bucketID
                    AND {$contractDate} BETWEEN IFNULL(cb.begDate, '2012-7-1') AND IFNULL(cb.endDate, '9999-1-1')
                WHERE c.monthYear = :monthYear
                    AND c.bucketID IS NOT NULL
                    AND c.visionCode NOT LIKE ('99999%')
                    AND c.visionCode <> ''
                ON DUPLICATE KEY
                UPDATE
                    commissionAmount = cb.amount,
                    additionalCommission = cb.adSpiff
            ";

            try {
                $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear), array()
                );
            } catch (exception $e) {
                return "There was an error during VZW Commissions Estimate Copying. " . $e->getMessage();
            }

            $this->addUpdateLog('', $result->rowCount(), 'Finish Copy Commissions.');
        }
    }

    /**
     * estimateNE2 function.
     *
     * @access public
     * @return void
     */
    public function estimateNE2()
    {
        $this->addUpdateLog('', 0, 'Begin Estimate NE2 Commissions.');

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.ccrs_upgrades (id)
            SELECT c.id
            FROM {$this->options->_dbName}.ccrs_upgrades c
            INNER JOIN {$this->options->_dbName}.buckets b
                ON b.bucketCategoryID = c.deviceCategory
                AND b.actTypeID = :actType
                AND b.term = c.contractLength
                AND isNE2
                AND NOT isEdge
            WHERE monthYear = :monthYear
                AND NOT c.installmentContract
                AND upgradeType LIKE ('NE%')
                AND c.bucketID IS NULL
            ON DUPLICATE KEY
            UPDATE
                bucketID = b.bucketID,
                contractTypeID = b.contractTypeID
        ";

        try {
            $result = $this->_db->query($SQL, array(
                ':monthYear' => $this->monthYear,
                ':actType' => self::UPGRADETYPE
                ), array()
            );
        } catch (exception $e) {
            return "There was an error during NE2 Estimates. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Estimate NE2 Commissions.');
    }

    /**
     *
     * @return type
     */
    public function estimatePostpay()
    {
        $this->addUpdateLog('', 0, 'Begin Estimate VZW Postpay Commissions.');

        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $actTypeID = self::NEWACTTYPE;
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $actTypeID = self::UPGRADETYPE;
                break;

        }

        //any leftover rows get assigned a bucket
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT id
            FROM {$this->options->_dbName}.{$this->table} c
            INNER JOIN {$this->options->_dbName}.buckets b
                ON b.bucketCategoryID = c.deviceCategory
                AND b.actTypeID = :actTypeID
                AND b.term = c.contractLength
                AND b.isEdge = c.installmentContract
                AND NOT isNE2
            WHERE monthYear = :monthYear
                AND c.bucketID IS NULL
            ON DUPLICATE KEY
            UPDATE
                bucketID = b.bucketID,
                contractTypeID = b.contractTypeID
        ";

        try {
            $result = $this->_db->query($SQL, array(
                ':monthYear' => $this->monthYear,
                ':actTypeID' => $actTypeID
                ), array()
            );
        } catch (exception $e) {
            return "There was an error during VZW Postpay Estimates. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Estimate VZW Postpay Commissions.');
    }

    /**
     *
     * @return type
     */
    public function estimatePrepay()
    {
        $this->addUpdateLog('', 0, 'Begin Estimate VZW Prepay Commissions.');

        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::DEACTS:
                $SQL = "
                    INSERT
                    INTO {$this->options->_dbName}.{$this->table} (id)
                    SELECT id
                    FROM {$this->options->_dbName}.{$this->table} c
                    INNER JOIN {$this->options->_dbName}.buckets b
                        ON b.bucketCategoryID = c.deviceCategory
                        AND b.actTypeID = :actType
                        AND NOT isNE2
                        AND NOT isEdge
                    WHERE monthYear = :monthYear
                        AND visionCode NOT LIKE ('99999%')
                        AND deviceCategory <> ''
                        AND c.bucketID IS NULL
                        AND (
                            visionCode IN (SELECT * FROM {$this->options->_dbName}.estimator_activation_prepaid_vision_codes)
                            OR (contractLength = 0 AND pricePlan = 0)
                        )
                    ON DUPLICATE KEY
                    UPDATE
                        bucketID = b.bucketID,
                        isPrepaid = 1,
                        contractTypeID = b.contractTypeID
                ";

                try {
                    $result = $this->_db->query($SQL, array(
                        ':monthYear' => $this->monthYear,
                        ':actType' => self::PREPAIDTYPE
                        ), array()
                    );
                } catch (exception $e) {
                    return "There was an error during VZW Prepay Estimates. " . $e->getMessage();
                }
                break;

            default:
                return;
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Estimate VZW Prepay Commissions.');
    }

    /**
     *
     * @return type
     */
    public function estimateNonCommissionables()
    {
        $this->addUpdateLog('', 0, 'Begin Estimate VZW Non-Commissionables.');

        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
                $actTypeID = self::NEWACTTYPE;
                $table = 'estimator_activation_non_commissonable_vision_codes';
                break;

            case self::UPGRADES:
                $actTypeID = self::UPGRADETYPE;
                $table = 'estimator_upgrade_non_commissonable_vision_codes';
                break;

            default:
                return;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT c.id
            FROM {$this->options->_dbName}.{$this->table} c
            INNER JOIN {$this->options->_dbName}.buckets b
                ON b.actTypeID = :actTypeID
                AND b.term IS NULL
                AND NOT isNE2
                AND NOT isEdge
            WHERE monthYear = :monthYear
                AND c.bucketID IS NULL
                AND c.visionCode IN (SELECT visionCode FROM {$this->options->_dbName}.{$table})
            ON DUPLICATE KEY
            UPDATE
                bucketID = b.bucketID,
                contractTypeID = b.contractTypeID
        ";

        try {
            $result = $this->_db->query($SQL, array(
                ':monthYear' => $this->monthYear,
                ':actTypeID' => $actTypeID
                ), array()
            );
        } catch (exception $e) {
            return "There was an error during Non-Commissionable Estimates. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Estimate VZW Non-Commissionables.');
    }

    /**
     *
     * @return type
     */
    public function determineDeactBucketIDs()
    {
        $this->addUpdateLog('', 0, 'Begin Determining Deactivation Bucket IDs.');

        switch ($this->table) {
            case self::DEACTS:
                $actTypeID = self::NEWACTTYPE;
                break;

            case self::UPGRADE_DEACTS:
                $actTypeID = self::UPGRADETYPE;
                break;

            default:
                return;
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT c.id
            FROM {$this->options->_dbName}.{$this->table} c
            INNER JOIN {$this->options->_dbName}.buckets b
                ON b.actTypeID = :actTypeID
                AND b.bucketCategoryID = c.deviceCategory
                AND b.term = c.contractLength
                AND NOT isNE2
                AND NOT isEdge
             WHERE monthYear = :monthYear
                 AND c.bucketID IS NULL
            ON DUPLICATE KEY
            UPDATE
                bucketID = b.bucketID,
                contractTypeID = b.contractTypeID
        ";

        try {
            $result = $this->_db->query($SQL, array(
                ':monthYear' => $this->monthYear,
                ':actTypeID' => $actTypeID
                ), array()
            );
        } catch (exception $e) {
            return "There was an error while determining Deactivation Bucket IDs. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Determining Deactivation Bucket IDs.');
    }

    /**
     * Attempt to find matching records for deactivations by checking Tranaction Changes Table
     *
     */
    private function reconcileOrphanDeactsWithTransTable()
    {
        switch ($this->table) {
            case self::DEACTS:
                $join = self::ACTS;
                $process = 'Deacts';
                break;

            case self::UPGRADE_DEACTS:
                $join = self::UPGRADES;
                $process = 'Upgrade Deacts';
                break;

            default:
                return;
        }
        $this->addUpdateLog('', 0, 'Begin reconciling orphan deactivations with transaction changes table ' . $process . '.');

        $query = "
        INSERT
        INTO {$this->options->_dbName}.{$this->table} (id)
        SELECT d.id
        FROM {$this->options->_dbName}.{$this->table}  d
        INNER JOIN {$this->options->_dbName}.ccrs_transaction_changes tc
            ON tc.new_account_number = d.accountNumber
            AND tc.new_phone = d.phone
            AND (tc.new_account_number <> tc.old_account_number OR tc.new_phone <> tc.old_phone)
        INNER JOIN {$this->options->_dbName}.{$join} a
            ON a.deviceID = d.deviceID
            AND a.phone = tc.old_phone
            AND a.accountNumber = tc.old_account_number
            AND LEFT(a.visionCode,5) = LEFT(d.visionCode,5)
        WHERE d.isOrphanDeact
        AND d.monthYear  = :monthYear
                ON DUPLICATE KEY
                UPDATE
                    isOrphanDeact = IF(ISNULL(a.`phone`), 1, 0),
                    " . ($this->determineCopyEstimates() ? "
                    commissionAmount = IFNULL(a.commissionAmount, d.commissionAmount),
                    additionalCommission = IFNULL(a.additionalCommission, d.additionalCommission),
                    spiff = IFNULL(d.spiff, a.spiff),
                    " : "") . "
                    estimatedCommissionPayout = IFNULL(a.estimatedCommissionPayout, d.estimatedCommissionPayout),
                    estimatedSpiffPayout = IFNULL(a.estimatedSpiffPayout, d.estimatedSpiffPayout),
                    estimatedAdditionalCommissionPayout = IFNULL(a.estimatedAdditionalCommissionPayout, d.estimatedAdditionalCommissionPayout),
                    estimatedEmployeePayout = IFNULL(a.estimatedEmployeePayout, d.estimatedEmployeePayout),
                    estimatedPurchasedReceivablePayout = d.purchasedReceivable,
                    estimatedEdgeServiceFeePayout = d.edgeServiceFee,
                    contractTypeID = IFNULL(a.contractTypeID, d.contractTypeID),
                    estimatorID = IFNULL(a.estimatorID, d.estimatorID),
                    bucketID = IFNULL(a.bucketID, d.bucketID)";

        try {
            $result = $this->_db->query($query, array(
                ':monthYear' => $this->monthYear
                ), array()
            );
        } catch (exception $e) {
            return 'There was an error during orphan ' . $process . ' reconciliation. ' . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish reconciling orphan deactivations from transaction changes table ' . $process . '.');
    }

    public function estimateDeactCommissions()
    {
        switch ($this->table) {
            case self::DEACTS:
                $join = self::ACTS;
                $process = 'Deacts';
                break;

            case self::UPGRADE_DEACTS:
                $join = self::UPGRADES;
                $process = 'Upgrade Deacts';
                break;

            default:
                return;
        }
        $this->addUpdateLog('', 0, 'Begin Estimating ' . $process . '.');

        //test for using estimates
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table} (id)
                SELECT c.id
                FROM {$this->options->_dbName}.{$this->table} c
                LEFT JOIN {$this->options->_dbName}.$join c2
                    ON c2.deviceID = c.deviceID
                    AND c2.phone = c.phone
                    AND c2.accountNumber = c.accountNumber
                    AND LEFT(c2.visionCode,5) = LEFT(c.visionCode,5)
                WHERE c.monthYear = :monthYear
                ON DUPLICATE KEY
                UPDATE
                    isOrphanDeact = IF(ISNULL(c2.`phone`), 1, 0),
                    " . ($this->determineCopyEstimates() ? "
                    commissionAmount = IFNULL(c2.commissionAmount, c.commissionAmount),
                    additionalCommission = IFNULL(c2.additionalCommission, c.additionalCommission),
                    spiff = IFNULL(c2.spiff, c.spiff),
                    " : "") . "
                    estimatedCommissionPayout = IFNULL(c2.estimatedCommissionPayout, c.estimatedCommissionPayout),
                    estimatedSpiffPayout = IFNULL(c2.estimatedSpiffPayout, c.estimatedSpiffPayout),
                    estimatedAdditionalCommissionPayout = IFNULL(c2.estimatedAdditionalCommissionPayout, c.estimatedAdditionalCommissionPayout),
                    estimatedEmployeePayout = IFNULL(c2.estimatedEmployeePayout, c.estimatedEmployeePayout),
                    estimatedPurchasedReceivablePayout = c.purchasedReceivable,
                    estimatedEdgeServiceFeePayout = c.edgeServiceFee,
                    contractTypeID = IFNULL(c2.contractTypeID, c.contractTypeID),
                    estimatorID = IFNULL(c2.estimatorID, c.estimatorID),
                    bucketID = IFNULL(c2.bucketID, c.bucketID)
            ";

        try {
            $result = $this->_db->query($SQL, array(
                ':monthYear' => $this->monthYear
                ), array()
            );
        } catch (exception $e) {
            return 'There was an error during  ' . $process . ' Estimates. ' . $SQL . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Estimating ' . $process . '.');
    }

    public function markEligibleForTierBonus()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT c.id
            FROM {$this->options->_dbName}.{$this->table} c
            INNER JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                ON s.accountID = c.accountID
                AND c.contractDate BETWEEN IFNULL(s.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s.endDate, '9999-1-1'))
            WHERE c.monthYear = :monthYear
                AND c.bucketID IN (
                    SELECT a.bucketID
                    FROM {$this->options->_dbName}.bucket_tier_bonus_attainment a
                    WHERE a.tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                )
                AND c.accountID NOT IN (999999)
                AND c.visionCode NOT LIKE ('99999%')
            ON DUPLICATE KEY
            UPDATE isTierBonusEligible = 1
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear), array()
            );
        } catch (exception $e) {
            return "There was an error during Tier Bonus Marking. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Marked Phones Eligible for Tier Bonus.');
    }

}
