<?php

/**
 * class used to determine payouts to subagents
 */
class ccrsPayoutSimplified extends ccrs
{

    public $monthYear;
    public $accountIDs;

    public function __construct($options, $table, $monthYear)
    {
        $this->setDB();
        $this->options = $options;
        $this->table = $table;
        $this->monthYear = date('Y-m-01', strtotime($monthYear));
    }

    public function setPayouts()
    {
        $this->addUpdateLog('', 0, 'Begin Setting Payouts.');

        switch ($this->table) {
            case self::ACTS:
            case self::REACTS:
            case self::UPGRADES:
            case self::DEACTS:
            case self::UPGRADE_DEACTS:
                $this->setCommissionPayoutAmounts();
                $this->setSpiffPayoutAmounts();
                break;

            case self::FEATURES:
            case self::FEATURES_CHARGEDBACK:
                $this->setFeaturePayouts();
                break;
        }

        $this->addUpdateLog('', 0, 'Finish Setting Payouts.');
    }

    public function setCommissionPayoutAmounts()
    {
        $this->addUpdateLog('', 0, "Begin setting commission payout amounts.");

        $contractDate  = $this->table === self::UPGRADE_DEACTS ? 'c.new_esn_contractdate_start' : 'c.contractDate';
        $isOrphanDeact = $this->table === self::UPGRADE_DEACTS || $this->table === self::DEACTS ? ' AND c.`isOrphanDeact` = 1 ' : '';

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT c.id
            FROM {$this->options->_dbName}.{$this->table} c
            LEFT JOIN mhcdynad.mhc_subagent_commission_schedule_history s
                ON s.accountID = c.accountID
                AND {$contractDate} BETWEEN IFNULL(s.begDate, '1900-01-01') AND LAST_DAY(IFNULL(s.endDate, '9999-01-01'))
            LEFT JOIN {$this->options->_dbName}.bucket_commission_payout_buckets cb
                ON cb.bucketID = c.bucketID
                AND cb.payoutScheduleID = s.commissionScheduleID
                AND {$contractDate} BETWEEN IFNULL(cb.begDate, '1900-01-01') AND LAST_DAY(IFNULL(cb.endDate, '9999-01-01'))
            WHERE c.monthYear = :monthYear
                AND c.bucketID IS NOT NULL
                {$isOrphanDeact}
            ON DUPLICATE KEY
            UPDATE
               -- estimatedCommissionPayout = IF(c.visionCode LIKE ('99999%'), 0, IFNULL(cb.amount, 0)),
               -- estimatedAdditionalCommissionPayout = IF(c.visionCode LIKE ('99999%'), 0, IFNULL(cb.adSpiff, 0)),
               -- estimatedEmployeePayout = IF(c.visionCode LIKE ('99999%'), 0, IFNULL(cb.empSpiff, 0)),
               -- estimatedPurchasedReceivablePayout = c.purchasedReceivable,
               -- estimatedEdgeServiceFeePayout = c.edgeServiceFee
                estimatedCommissionPayout = IF(c.commissionAmount > 0, IFNULL(cb.amount, 0), 0),
                estimatedAdditionalCommissionPayout = IF(c.additionalCommission > 0, IFNULL(cb.adSpiff, 0), 0),
                estimatedEmployeePayout = IF(c.commissionAmount > 0, IFNULL(cb.empSpiff, 0), 0),
                estimatedPurchasedReceivablePayout = c.purchasedReceivable,
                estimatedEdgeServiceFeePayout = c.edgeServiceFee
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during setting commission payout amounts." . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting commission payout amounts.");
    }

    public function setSpiffPayoutAmounts()
    {
        $this->addUpdateLog('', 0, "Begin setting spiff payout amounts.");

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT c.id
            FROM {$this->options->_dbName}.{$this->table} c
            LEFT JOIN mhcdynad.mhc_subagent_spiff_schedule_history s
                ON s.accountID = c.accountID
                AND c.contractDate BETWEEN IFNULL(s.begDate, '1900-01-01') AND LAST_DAY(IFNULL(s.endDate, '9999-01-01'))
            LEFT JOIN {$this->options->_dbName}.estimator_spiffs ss
                ON ss.scheduleID = s.spiffScheduleID
                AND c.contractDate BETWEEN IFNULL(ss.begDate, '1900-01-01') AND LAST_DAY(IFNULL(ss.endDate, '9999-01-01'))
            WHERE c.monthYear = :monthYear
                AND c.bucketID IS NOT NULL
            ON DUPLICATE KEY
            UPDATE
                estimatedSpiffPayout = IFNULL(c.spiff, 0) * IFNULL(ss.percentage, 0)
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during setting spiff payout amounts." . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting spiff payout amounts.");
    }

    public function setFeaturePayouts()
    {
        $this->addUpdateLog('', 0, "Begin Setting Feature Payouts.");

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT c.id
            FROM {$this->options->_dbName}.{$this->table} c
            LEFT JOIN mhcdynad.mhc_subagent_feature_schedule_history s
                ON s.accountID = c.accountID
                AND c.monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND LAST_DAY(IFNULL(s.endDate, '9999-01-01'))
            LEFT JOIN {$this->options->_dbName}.estimator_features c2
                ON c2.scheduleID = s.featureScheduleID
                AND c.monthYear BETWEEN IFNULL(c2.begDate, '1900-01-01') AND LAST_DAY(IFNULL(c2.endDate, '9999-01-01'))
            WHERE c.monthYear = :monthYear
            ON DUPLICATE KEY
            UPDATE
                estimatedCommissionPayout = IFNULL(c.commissionAmount, 0) * IFNULL(percentage, 0),
                estimatedSpiffPayout =
                    CASE
                        WHEN c.planName LIKE ('%Edge%') AND c.planName NOT LIKE ('%NON-EDGE%') AND c.spiff = 60 THEN 50
                        WHEN c.planName LIKE ('%Edge%') AND c.planName NOT LIKE ('%NON-EDGE%') AND c.spiff = 40 THEN 30
                        WHEN c.planName LIKE ('%Edge%') AND c.planName NOT LIKE ('%NON-EDGE%') AND c.spiff = -60 THEN -50
                        WHEN c.planName LIKE ('%Edge%') AND c.planName NOT LIKE ('%NON-EDGE%') AND c.spiff = -40 THEN -30
                        ELSE IFNULL(c.spiff, 0) * IFNULL(percentage, 0)
                    END
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during feature payout. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish Setting {$this->table} Payouts.");
    }

    public function setTierPayouts()
    {
        $error = $this->setTierBonusAccountAttainment();
        if ($error) return $error;

        $error = $this->setTierBonusLocationAttainment();
        if ($error) return $error;

        $error = $this->setTierBonusPayout();
        if ($error) return $error;

        //$error=$this->determineLocationPlatinumStatus();
        //if($error) return $error;

        $error = $this->reassessPlatinumLocationTierBonus();
        if ($error) return $error;
    }

    public function setEdgeFastStartSpiffPayouts()
    {
        $error = $this->setEdgeAttainmentAccountCounts();
        if ($error) return $error;

        $error = $this->setFastStartPayouts();
        if ($error) return $error;
    }

    public function setEdgeAttainmentAccountCounts()
    {
        //remove previous attainment counts for the provided month
        $SQL = "
            DELETE
            FROM {$this->options->_dbName}.edge_attainment_account_counts
            WHERE monthYear = :monthYear
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier account attainments removal. " . $e->getMessage();
        }

        $SQL = "
            INSERT INTO {$this->options->_dbName}.edge_attainment_account_counts (
                monthYear,
                accountID,
                netActivations,
                netEdgeActivations,
                netReactivations,
                netEdgeReactivations,
                netUpgrades,
                netEdgeUpgrades,
                netEdgeContracts,
                netContracts,
                edgePercentage,
                edgeSmartphoneFastStartSpiffPayoutAmount,
                edgeBasicphoneFastStartSpiffPayoutAmount,
                addedOn
            )
            SELECT
                t2.*,
                CASE
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage < .1 THEN 0
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage >= .1 AND t2.edgePercentage < .2 THEN 10
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage >= .2 AND t2.edgePercentage  < .3 THEN 15
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage >= .3 THEN 25
                    ELSE 35 END
                AS edgeSmartSpiffPayoutAmount,
                CASE
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage < .1 THEN 0
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage >= .1 AND t2.edgePercentage  < .2 THEN 5
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage >= .2 AND t2.edgePercentage  < .3 THEN 10
                    WHEN t2.accountID <> 999999 AND t2.edgePercentage >= .3 THEN 15
                    ELSE 25 END
                AS edgeBaicSpiffPayoutAmount,
                NOW() AS addedOn
            FROM (
                SELECT
                    monthYear,
                    accountID,
                    SUM(netAct) AS netActivations,
                    SUM(netEdgeAct) AS netEdgeActivations,
                    SUM(netReact) AS netReactivations,
                    SUM(netEdgeReact) AS netEdgeReactivations,
                    SUM(netUpg) AS netUpgrades,
                    SUM(netEdgeUpg) AS netEdgeUpgrades,
                    SUM(netEdgeAct) + SUM(netEdgeReact) + SUM(netEdgeUpg) AS netEdgeContracts,
                    SUM(netAct) + SUM(netReact) + SUM(netUpg) AS netContracts,
                    (SUM(netEdgeAct) + SUM(netEdgeReact) + SUM(netEdgeUpg)) /
                        (SUM(netAct) + SUM(netReact) + SUM(netUpg)) AS edgePercentage
                FROM (
                    SELECT
                        monthYear,
                        accountID,
                        1 AS netAct,
                        installmentContract AS netEdgeAct,
                        0 AS netReact,
                        0 AS netEdgeReact,
                        0 AS netUpg,
                        0 AS netEdgeUpg
                    FROM {$this->options->_dbName}.ccrs_activations
                    WHERE monthYear = :monthYear
                        AND deviceCategory NOT IN ('HFN', 'HPC', 'IPD', 'TAB', 'DAT4')
                        AND isPrepaid = 0
                        AND visionCode NOT LIKE ('99999%')
                        AND accountID IS NOT NULL

                    UNION ALL

                    SELECT
                        monthYear,
                        accountID,
                        0 AS netAct,
                        0 AS netEdgeAct,
                        1 AS netReact,
                        installmentContract AS netEdgeReact,
                        0 AS netUpg,
                        0 AS netEdgeUpg
                    FROM {$this->options->_dbName}.ccrs_reactivations
                    WHERE monthYear = :monthYear
                        AND deviceCategory NOT IN ('HFN', 'HPC', 'IPD', 'TAB', 'DAT4')
                        AND isPrepaid = 0
                        AND visionCode NOT LIKE ('99999%')
                        AND accountID IS NOT NULL

                    UNION ALL

                    SELECT
                        monthYear,
                        accountID,
                        0 AS netAct,
                        0 AS netEdgeAct,
                        0 AS netReact,
                        0 AS netEdgeReact,
                        1 AS netUpg,
                        installmentContract AS netEdgeUpg
                    FROM {$this->options->_dbName}.ccrs_upgrades
                    WHERE monthYear = :monthYear
                        AND deviceCategory NOT IN ('HFN', 'HPC', 'IPD', 'TAB', 'DAT4')
                        AND visionCode NOT LIKE ('99999%')
                        AND accountID IS NOT NULL
                ) AS t1
                GROUP BY t1.accountID
            ) AS t2
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during edge account attainment counts inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting edge account attainment counts.");
    }

    public function setFastStartPayouts()
    {
        //cycle thru all three tier tables
        foreach (array(self::ACTS, self::DEACTS, self::REACTS, self::UPGRADES, self::UPGRADE_DEACTS) as $thisTable) {
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table} (id)
                SELECT c.id
                FROM {$this->options->_dbName}.{$this->table} c
                LEFT JOIN {$this->options->_dbName}.edge_attainment_account_counts count
                    ON count.monthYear = c.monthYear
                    AND count.accountID = c.accountID
                 WHERE c.monthYear = :monthYear
                    AND c.installmentContract
                    AND c.spiff > 0
                ON DUPLICATE KEY
                UPDATE estimatedSpiffPayout = CASE
                    WHEN c.deviceCategory = 'BPN' THEN count.edgeBasicphoneFastStartSpiffPayoutAmount
                    ELSE count.edgeSmartphoneFastStartSpiffPayoutAmount END
            ";

            try {
                $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
            } catch (exception $e) {
                return "There was an error during edge {$thisTable} fast-start payout inserts. " . $e->getMessage();
            }

            $this->addUpdateLog('', $result->rowCount(), "Finish setting edge {$thisTable} fast-start payouts.");
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting edge fast-start payouts.");
    }

    public function setTierBonusAccountAttainment()
    {
        //remove previous attainment counts for the provided month
        $SQL = "
            DELETE
            FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts
            WHERE monthYear = :monthYear
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier account attainments removal. " . $e->getMessage();
        }

        //insert attainment counts
        //activations
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts
            (monthYear, accountID, attainmentScheduleID, acts, addedOn)
            SELECT :monthYear, accountID, tierAttainmentScheduleID, SUM(isTBE), NOW()
            FROM(
                SELECT
                    c.accountID,
                    s.tierAttainmentScheduleID,
                    IF(c.bucketID IN (
                        SELECT bucketID
                        FROM {$this->options->_dbName}.bucket_tier_bonus_attainment
                        WHERE tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                     ), 1, 0) AS isTBE
                FROM {$this->options->_dbName}.ccrs_activations c
                LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                    ON s.accountID = c.accountID
                    AND monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND IFNULL(s.endDate, '9999-01-01')
                 WHERE monthYear = :monthYear
                    AND isTierBonusEligible
                 GROUP BY phone
            ) AS t1
            GROUP BY accountID
            ORDER BY accountID
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attainment account act inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier act account attainment counts.");

        //deactivations
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts
            (monthYear, accountID, attainmentScheduleID, deacts, addedOn)
            SELECT *
            FROM(
                SELECT :monthYear, accountID, tierAttainmentScheduleID, SUM(isTBE) AS deacts, NOW()
                FROM(
                    SELECT
                        c.accountID,
                        s.tierAttainmentScheduleID,
                        IF(c.bucketID IN (
                            SELECT bucketID
                            FROM {$this->options->_dbName}.bucket_tier_bonus_attainment
                            WHERE tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                        ), 1, 0) AS isTBE
                    FROM {$this->options->_dbName}.ccrs_deactivations c
                    LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                        ON s.accountID = c.accountID
                        AND monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND IFNULL(s.endDate, '9999-01-01')
                    WHERE monthYear = :monthYear
                        AND isTierBonusEligible
                    GROUP BY phone
                ) AS t1
                GROUP BY accountID
                ORDER BY accountID
            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                deacts = t1.deacts,
                addedOn = NOW()
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attainment account deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier deact account attainment counts.");

        //reactivations
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts
            (monthYear, accountID, attainmentScheduleID, reacts, addedOn)
            SELECT *
            FROM(
                SELECT :monthYear, accountID, tierAttainmentScheduleID, SUM(isTBE) AS reacts, NOW()
                FROM(
                    SELECT
                        c.accountID,
                        s.tierAttainmentScheduleID,
                        IF(c.bucketID IN (
                            SELECT bucketID
                            FROM {$this->options->_dbName}.bucket_tier_bonus_attainment
                            WHERE tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                        ), 1, 0) AS isTBE
                    FROM {$this->options->_dbName}.ccrs_reactivations c
                    LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                    ON s.accountID = c.accountID
                        AND monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND IFNULL(s.endDate, '9999-01-01')
                    WHERE monthYear = :monthYear
                       AND isTierBonusEligible
                    GROUP BY phone
                ) AS t1
                GROUP BY accountID
                ORDER BY accountID
            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                reacts = t1.reacts,
                addedOn = NOW()
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attainment account deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier react account attainment counts.");
        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier account attainment counts.");
    }

    public function setTierBonusLocationAttainment()
    {
        //remove previous attainment counts for the provided month
        $SQL = "
            DELETE
            FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            WHERE monthYear = :monthYear
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier location attainments removal. " . $e->getMessage();
        }

        //insert attainment counts
        //activations
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            (monthYear, accountID, locationID, attainmentScheduleID, acts, addedOn)
            SELECT :monthYear, accountID, locationID, tierAttainmentScheduleID, SUM(isTBE), NOW()
            FROM(
                SELECT
                    c.accountID,
                    c.locationID,
                    s.tierAttainmentScheduleID,
                    IF(c.bucketID IN (
                        SELECT bucketID
                        FROM {$this->options->_dbName}.bucket_tier_bonus_attainment
                        WHERE tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                     ), 1, 0) AS isTBE
                FROM {$this->options->_dbName}.ccrs_activations c
                LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                    ON s.accountID = c.accountID
                    AND monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND IFNULL(s.endDate, '9999-01-01')
                WHERE monthYear = :monthYear
                   AND locationID > 0
                   AND isTierBonusEligible
                GROUP BY phone
                ) AS t1
            GROUP BY locationID
            ORDER BY locationID
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attainment location act inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier act location attainment counts.");

        //deactivations
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            (monthYear, accountID, locationID, attainmentScheduleID, deacts, addedOn)
            SELECT *
            FROM(
                SELECT :monthYear, accountID, locationID, tierAttainmentScheduleID, SUM(isTBE) AS deacts, NOW()
                FROM(
                    SELECT
                        c.accountID,
                        c.locationID,
                        s.tierAttainmentScheduleID,
                        IF(c.bucketID IN (
                            SELECT bucketID
                            FROM {$this->options->_dbName}.bucket_tier_bonus_attainment
                            WHERE tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                        ), 1, 0) AS isTBE
                    FROM {$this->options->_dbName}.ccrs_deactivations c
                    LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                        ON s.accountID = c.accountID
                        AND monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND IFNULL(s.endDate, '9999-01-01')
                    WHERE monthYear = :monthYear
                        AND locationID > 0
                           AND isTierBonusEligible
                      GROUP BY phone
                   ) AS t1
                GROUP BY locationID
                ORDER BY locationID
            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                deacts = t1.deacts,
                addedOn = NOW()
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attainment location deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier deact location attainment counts.");

        //reactivations
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            (monthYear, accountID, locationID, attainmentScheduleID, reacts, addedOn)
            SELECT *
            FROM(
                SELECT :monthYear, accountID, locationID, tierAttainmentScheduleID, SUM(isTBE) AS reacts, NOW()
                FROM(
                    SELECT
                        c.accountID,
                        c.locationID,
                        s.tierAttainmentScheduleID,
                        IF(c.bucketID IN (
                            SELECT bucketID
                            FROM {$this->options->_dbName}.bucket_tier_bonus_attainment
                            WHERE tierBonusAttainmentScheduleID = s.tierAttainmentScheduleID
                        ), 1, 0) AS isTBE
                    FROM {$this->options->_dbName}.ccrs_reactivations c
                    LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                        ON s.accountID = c.accountID
                        AND monthYear BETWEEN IFNULL(s.begDate, '1900-01-01') AND IFNULL(s.endDate, '9999-01-01')
                    WHERE monthYear = :monthYear
                        AND locationID > 0
                           AND isTierBonusEligible
                       GROUP BY phone
                   ) AS t1
                GROUP BY locationID
                ORDER BY locationID
            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                reacts = t1.reacts,
                addedOn = NOW()
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear)
            );
        } catch (exception $e) {
            return "There was an error during tier attaiment location deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier react location attainment counts.");
        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier location attainment counts.");
    }

    public function setTierBonusPayout()
    {
        //cycle thru all three tier tables
        foreach (array(self::ACTS, self::DEACTS, self::REACTS) as $thisTable) {
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$this->table} (id)
                SELECT c.id
                FROM {$this->options->_dbName}.{$this->table} c
                LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts tbac
                    ON tbac.monthYear = c.monthYear
                    AND tbac.accountID = c.accountID
                LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts tblc
                    ON tblc.monthYear = c.monthYear
                    AND tblc.accountID = c.accountID
                    AND tblc.locationID = c.locationID
                LEFT JOIN mhcdynad.mhc_subagent_tier_bonus_schedule_history s
                    ON s.accountID = c.accountID
                    AND c.monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                LEFT JOIN {$this->options->_dbName}.bucket_tier_bonus_tiers tb
                    ON tb.tierBonusScheduleID = s.tierBonusScheduleID
                    AND ABS(tbac.acts - tbac.deacts + tbac.reacts) BETWEEN tb.low AND IFNULL(tb.high, '99999999')
                    AND c.monthYear BETWEEN IFNULL(tb.begDate, '1900-01-01') AND IFNULL(tb.endDate, '9999-01-01')
                 WHERE c.monthYear = :monthYear
                    AND c.isTierBonusEligible
                    AND NOT c.installmentContract
                    AND deviceCategory NOT IN ('IPH', 'IPD', 'HPC', 'TAB', 'DAT0')
                ON DUPLICATE KEY
                UPDATE estimatedTierBonusPayout = CASE
                    WHEN s.tierBonusScheduleID IN (1) AND ABS(tblc.acts - tblc.deacts + tblc.reacts) < 25 THEN 0
                    ELSE IFNULL(tb.amount, 0)
                    END
            ";

            try {
                $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
            } catch (exception $e) {
                return "There was an error during tier payout {$thisTable} inserts. " . $e->getMessage();
            }

            $this->addUpdateLog('', $result->rowCount(), "Finish setting tier {$thisTable} payouts.");
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier payouts.");
    }

    public function reassessPlatinumLocationTierBonus()
    {
        //foreach (array(self::ACTS, self::DEACTS, self::REACTS) as $thisTable) {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT
                c.id
            FROM {$this->options->_dbName}.{$this->table} c
            LEFT JOIN mhcdynad.mhc_subagent_account_type_history sath
                ON c.accountID = sath.accountID
                AND c.contractDate BETWEEN IFNULL(sath.begDate, '1900-01-01') AND IFNULL(sath.endDate, '9999-01-01')
            WHERE c.monthYear = :monthYear
                AND c.isTierBonusEligible
                AND NOT c.installmentContract
                AND c.deviceCategory NOT IN ('IPH', 'IPD', 'HPC', 'TAB')
                AND sath.accountTypeID = " . self::PLATINUM . "
            ON DUPLICATE KEY
            UPDATE
                estimatedPlatinumBonusPayout = " . self::PLATINUM_AMOUNT;

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during platinum payout {$thisTable} inserts. " . $e->getMessage();
        }

        //$this->addUpdateLog('', $result->rowCount(), "Finish setting platinum {$thisTable} payouts.");
        //}

        $this->addUpdateLog('', $result->rowCount(), "Finish setting platinum payouts.");
    }

    public function determineLocationPlatinumStatus()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            (monthYear, accountID, locationID)
            SELECT
                lc.monthYear,
                lc.accountID,
                lc.locationID
            FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts lc
            LEFT JOIN mhcdynad.mhc_subagent_account_type_history s
                ON s.accountID=lc.accountID
                AND monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
            LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus_platinum_location_minimum lm
                ON monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
            ON DUPLICATE KEY
            UPDATE
                isPlatinum = IF(s.accountTypeID = " . self::PLATINUM . ", 1, 0),
                madePlatinumMinimum = IF(lm.min <= ( lc.acts - lc.deacts + lc.reacts), 1, 0)
        ";

        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier location platinum status check. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier location platinum check.");
    }

}
