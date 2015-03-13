<?php

class ccrsPayout extends ccrs
{

    public $monthYear;
    public $accountIDs;

    public function __construct($options, $table, $monthYear)
    {
        $this->setDB();
        $this->options = $options;
        $this->table = $table;
        $this->monthYear = date('Y-m-1', strtotime($monthYear));
    }

    public function setPayouts()
    {
        $this->addUpdateLog('', 0, 'Begin Setting Payouts.');

        switch ($this->table) {
            case self::ACTS;
            case self::DEACTS;
            case self::REACTS;
                $error = $this->setActivationPayouts();
                if ($error)
                    return $error;

                $error = $this->setNonComm();
                if ($error)
                    return $error;
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $error = $this->setUpgradePayouts();
                if ($error)
                    return $error;

                $error = $this->setNonComm();
                if ($error)
                    return $error;

                break;

            case self::FEATURES:
            case self::FEATURES_CHARGEDBACK:
                $error = $this->setFeaturePayouts();
                if ($error)
                    return $error;
                break;
        }

        $this->addUpdateLog('', 0, 'Finish Setting Payouts.');
    }

    public function setTierPayouts()
    {
        $error = $this->setTierBonusAccountAttainment();
        if ($error)
            return $error;

        $error = $this->setTierBonusLocationAttainment();
        if ($error)
            return $error;

        $error = $this->setTierBonusPayout();
        if ($error)
            return $error;

        $error = $this->determineLocationPlatinumStatus();
        if ($error)
            return $error;

        $error = $this->reassessPlatinumLocationTierBonus();
        if ($error)
            return $error;
    }

    public function setTierBonusLocationAttainment()
    {
        //remove previous attainment counts for the provided month
        $SQL = "
            DELETE
            FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            WHERE monthyear=:monthYear
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier location attainments removal. " . $e->getMessage();
        }

        //insert attainment counts
        //acts
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
                    (IF(c.contractTypeID IN (SELECT contractTypeID FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment WHERE attainmentScheduleID=s.tierAttainmentScheduleID), 1,0)) AS isTBE
                FROM {$this->options->_dbName}.ccrs_activations c
                LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                ON
                    s.accountID=c.accountID
                AND (
                    monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                )

             WHERE
                monthyear=:monthYear
                AND c.accountID NOT IN (999999)
                AND locationID>0
                AND IF(tierAttainmentScheduleID=2,isTierBonusEligible,1)
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

        //deacts
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
               (IF(c.contractTypeID IN (SELECT contractTypeID FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment WHERE attainmentScheduleID=s.tierAttainmentScheduleID), 1,0)) AS isTBE
              FROM {$this->options->_dbName}.ccrs_deactivations c
              LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                ON
                s.accountID=c.accountID
                AND (
                 monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                )

              WHERE
                monthyear=:monthYear
                AND c.accountID NOT IN (999999)
                AND locationID>0
                AND IF(tierAttainmentScheduleID=2,isTierBonusEligible,1)
              GROUP BY phone
             ) AS t1
             GROUP BY locationID
             ORDER BY locationID
            ) AS t1
            ON DUPLICATE KEY UPDATE deacts=t1.deacts, addedOn=NOW()
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attainment location deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier deact location attainment counts.");

        //reacts
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
               (IF(c.contractTypeID IN (SELECT contractTypeID FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment WHERE attainmentScheduleID=s.tierAttainmentScheduleID), 1,0)) AS isTBE
              FROM {$this->options->_dbName}.ccrs_reactivations c
              LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                ON
                s.accountID=c.accountID
                AND (
                 monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                )

              WHERE
                monthyear=:monthYear
                AND c.accountID NOT IN (999999)
                AND locationID>0
                AND IF(tierAttainmentScheduleID=2,isTierBonusEligible,1)
              GROUP BY phone
             ) AS t1
             GROUP BY locationID
             ORDER BY locationID
            ) AS t1
            ON DUPLICATE KEY UPDATE reacts=t1.reacts, addedOn=NOW()
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attaiment location deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier react location attainment counts.");
        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier location attainment counts.");
    }

    public function setTierBonusAccountAttainment()
    {
        //remove previous attainment counts for the provided month
        $SQL = "
            DELETE
            FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts
            WHERE monthyear=:monthYear
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier account attainments removal. " . $e->getMessage();
        }

        //insert attainment counts
        //acts
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts
            (monthYear, accountID, attainmentScheduleID, acts, addedOn)
            SELECT :monthYear, accountID, tierAttainmentScheduleID, SUM(isTBE), NOW()
            FROM(
             SELECT
              c.accountID,
              s.tierAttainmentScheduleID,
              (IF(c.contractTypeID IN (SELECT contractTypeID FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment WHERE attainmentScheduleID=s.tierAttainmentScheduleID), 1,0)) AS isTBE
             FROM {$this->options->_dbName}.ccrs_activations c
             LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
               ON
               s.accountID=c.accountID
               AND (
                monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
               )

             WHERE
                monthyear=:monthYear
                AND c.accountID NOT IN (999999)
                AND IF(tierAttainmentScheduleID=2,isTierBonusEligible,1)
             GROUP BY phone
            ) AS t1
            GROUP BY accountID
            ORDER BY accountID
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attaiment account act inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier act account attainment counts.");

        //deacts
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
               (IF(c.contractTypeID IN (SELECT contractTypeID FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment WHERE attainmentScheduleID=s.tierAttainmentScheduleID), 1,0)) AS isTBE
              FROM {$this->options->_dbName}.ccrs_deactivations c
              LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                ON
                s.accountID=c.accountID
                AND (
                 monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                )

              WHERE
                monthyear = :monthYear
                AND c.accountID NOT IN (999999)
                AND IF(tierAttainmentScheduleID=2,isTierBonusEligible,1)
              GROUP BY phone
             ) AS t1
             GROUP BY accountID
             ORDER BY accountID
            ) AS t1
            ON DUPLICATE KEY UPDATE deacts=t1.deacts, addedOn=NOW()
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attaiment account deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier deact account attainment counts.");

        //reacts
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
               (IF(c.contractTypeID IN (SELECT contractTypeID FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment WHERE attainmentScheduleID=s.tierAttainmentScheduleID), 1,0)) AS isTBE
              FROM {$this->options->_dbName}.ccrs_reactivations c
              LEFT JOIN mhcdynad.mhc_subagent_tier_attainment_schedule_history s
                ON
                s.accountID=c.accountID
                AND (
                 monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                )

              WHERE
                monthyear = :monthYear
                AND c.accountID NOT IN (999999)
                AND IF(tierAttainmentScheduleID=2,isTierBonusEligible,1)
              GROUP BY phone
             ) AS t1
             GROUP BY accountID
             ORDER BY accountID
            ) AS t1
            ON DUPLICATE KEY UPDATE reacts=t1.reacts, addedOn=NOW()
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier attaiment account deact inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier react account attainment counts.");
        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier account attainment counts.");
    }

    public function setTierBonusPayout()
    {
        //cycle thru all three tier tables
        foreach (array(self::ACTS, self::DEACTS, self::REACTS) as $thisTable) {
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$thisTable}
                (id, estimatedTierBonusPayout)
                SELECT id, amount
                FROM(

                    SELECT
                        c.id,
                        c.accountID,
                        c.isTierBonusEligible,
                        ABS(tbac.acts-tbac.deacts+tbac.reacts) AS attainment,
                        s.tierBonusScheduleID,
                        IFNULL(tb.amount,0) AS amount
                    FROM {$this->options->_dbName}.{$thisTable} c
                    LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_account_counts tbac
                    ON
                        tbac.monthYear=c.monthYear AND
                        tbac.accountID=c.accountID
                    LEFT JOIN mhcdynad.mhc_subagent_tier_bonus_schedule_history s
                    ON
                        s.accountID=c.accountID
                    AND (
                        c.monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                    )
                    LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus tb
                    ON
                        tb.scheduleID=s.tierBonusScheduleID
                    AND (
                        ABS(tbac.acts-tbac.deacts+tbac.reacts) BETWEEN tb.min AND tb.max AND
                        c.monthYear BETWEEN IFNULL(tb.begDate, '1900-1-1') AND IFNULL(tb.endDate, '9999-1-1')
                    )
                    WHERE c.monthyear=:monthYear AND c.accountID NOT IN (999999) AND isTierBonusEligible
                ) AS t1
                ON DUPLICATE KEY
                UPDATE
                    estimatedTierBonusPayout=t1.amount
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
        // return;
        //cycle thru all three tier tables
        foreach (array(self::ACTS, self::DEACTS, self::REACTS) as $thisTable) {
            $SQL = "
                INSERT
                INTO {$this->options->_dbName}.{$thisTable}
                (id, estimatedTierBonusPayout)
                SELECT id, amount
                FROM(
                SELECT
                    c.id,
                    c.accountID,
                    c.locationID,
                    c.isTierBonusEligible,
                    attainmentScheduleID,
                    s.tierBonusScheduleID,
                    tb.amount
                FROM {$this->options->_dbName}.{$thisTable} c

                -- join the location counts
                LEFT JOIN (
                    SELECT locationID, attainmentScheduleID, acts, deacts, reacts
                    FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
                    WHERE monthYear=:monthYear AND isPlatinum AND !madePlatinumMinimum
                ) AS lc ON lc.locationID=c.locationID


                -- join the right schedule by history
                LEFT JOIN mhcdynad.mhc_subagent_tier_bonus_schedule_history s
                ON
                    s.accountID=c.accountID
                AND (
                    c.monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
                )

                -- join the right schedule
                LEFT JOIN {$this->options->_dbName}.estimator_activation_tier_bonus tb
                ON
                    tb.scheduleID=s.tierBonusScheduleID
                AND (
                    ABS(lc.acts-lc.deacts+lc.reacts) BETWEEN tb.min AND tb.max AND
                    c.monthYear BETWEEN IFNULL(tb.begDate, '1900-1-1') AND IFNULL(tb.endDate, '9999-1-1')
                )
                WHERE c.monthyear=:monthYear AND c.accountID NOT IN (999999) AND isTierBonusEligible AND attainmentScheduleID IS NOT NULL
                ) AS t1
                ON DUPLICATE KEY
                UPDATE
                    estimatedTierBonusPayout=t1.amount
            ";
            try {
                $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear)
                );
            } catch (exception $e) {
                return "There was an error during tier platinum tier payout {$thisTable} reassessment inserts. " . $e->getMessage();
            }

            $this->addUpdateLog('', $result->rowCount(), "Finish setting platinum tier payout {$thisTable} reassessments.");
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier platinum payout reassessments.");
    }

    public function determineLocationPlatinumStatus()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts
            (monthYear, accountID, locationID, isPlatinum, madePlatinumMinimum)
            SELECT *
            FROM(
            SELECT
                lc.monthYear,
                lc.accountID,
                lc.locationID,
                IF(s.accountTypeID=" . self::PLATINUM . ", 1,0) AS isPlatinum,
                IF(lm.min<=(acts-deacts+reacts),1,0) AS madePlatinumMinimum
            FROM {$this->options->_dbName}.estimator_activation_tier_bonus_attainment_location_counts lc
            LEFT JOIN mhcdynad.mhc_subagent_account_type_history s
            ON
                s.accountID=lc.accountID
            AND (
                monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
            )
            LEFT JOIN  {$this->options->_dbName}.estimator_activation_tier_bonus_platinum_location_minimum lm
            ON
                monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND IFNULL(s.endDate, '9999-1-1')
            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                isPlatinum=t1.isPlatinum,
                madePlatinumMinimum=t1.madePlatinumMinimum
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during tier location platinum status check. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting tier location platinum check.");
    }

    public function setActivationPayouts()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatedCommissionPayout, estimatedCoopPayout, estimatedSpiffPayout, estimatedAdditionalCommissionPayout)
            SELECT id, commission, coop, spiff, adsCommission
            FROM(

                SELECT
                    c.id,
                    c.accountID,
                    IFNULL(c2.commission,0) AS commission,
                    s.commissionScheduleID,
                    IFNULL(coop, 0)*IFNULL(c3.percentage,0) AS coop,
                    IFNULL(spiff, 0)*IFNULL(c3.percentage,0) AS spiff,
                    s2.spiffScheduleID,
                    c3.percentage,
                    s3.adScheduleID,
                    c.additionalCommissionEstimatorID,
                    IFNULL(c4.commission,0) AS adsCommission
                FROM {$this->options->_dbName}.{$this->table} c

                -- commission
                LEFT JOIN mhcdynad.mhc_subagent_commission_schedule_history s
                    ON
                        s.accountID=c.accountID
                    AND (
                        c.contractDate BETWEEN IFNULL(s.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s.endDate, '9999-1-1'))
                    )
                LEFT JOIN {$this->options->_dbName}.estimator_activation_commissions c2
                    ON
                        c2.scheduleID=s.commissionScheduleID
                    AND (
                        c.estimatorID=c2.estimatorID AND
                        c.contractDate BETWEEN IFNULL(c2.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c2.endDate, '9999-1-1'))
                    )

                -- spiff
                LEFT JOIN mhcdynad.mhc_subagent_spiff_schedule_history s2
                    ON
                        s2.accountID=c.accountID
                    AND (
                        c.contractDate BETWEEN IFNULL(s2.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s2.endDate, '9999-1-1'))
                    )
                LEFT JOIN {$this->options->_dbName}.estimator_spiffs c3
                    ON
                        c3.scheduleID=s2.spiffScheduleID
                    AND (
                        c.contractDate BETWEEN IFNULL(c3.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c3.endDate, '9999-1-1'))
                    )

                -- ads
                LEFT JOIN mhcdynad.mhc_subagent_ads_schedule_history s3
                    ON
                        s3.accountID=c.accountID
                    AND (
                        c.contractDate BETWEEN IFNULL(s3.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s3.endDate, '9999-1-1'))
                    )
                LEFT JOIN {$this->options->_dbName}.estimator_advanced_device_commissions c4
                    ON
                        c4.scheduleID=s3.adScheduleID
                    AND (
                        c4.estimatorID=c.additionalCommissionEstimatorID AND
                        c.contractDate BETWEEN IFNULL(c4.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c4.endDate, '9999-1-1'))
                    )

                WHERE c.monthyear=:monthYear AND c.accountID NOT IN (999999)

            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                estimatedCommissionPayout=t1.commission,
                estimatedCoopPayout=t1.coop,
                estimatedSpiffPayout=t1.spiff,
                estimatedAdditionalCommissionPayout=t1.adsCommission
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during {$this->table} payout inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting {$this->table} payouts.");
    }

    public function setUpgradePayouts()
    {
        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatedCommissionPayout, estimatedSpiffPayout, estimatedAdditionalCommissionPayout)
            SELECT id, commission, spiff, adsCommission
            FROM(

                SELECT
                    c.id,
                    c.accountID,
                    IFNULL(c2.commission,0) AS commission,
                    s.commissionScheduleID,
                    IFNULL(spiff, 0)*IFNULL(c3.percentage,0) AS spiff,
                    s2.spiffScheduleID,
                    c3.percentage,
                    s3.adScheduleID,
                    c.additionalCommissionEstimatorID,
                    IFNULL(c4.commission,0) AS adsCommission
                FROM {$this->options->_dbName}.{$this->table} c

                -- commission
                LEFT JOIN mhcdynad.mhc_subagent_commission_schedule_history s
                    ON
                        s.accountID=c.accountID
                    AND (
                        c.new_esn_contractdate_start BETWEEN IFNULL(s.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s.endDate, '9999-1-1'))
                    )
                LEFT JOIN {$this->options->_dbName}.estimator_upgrade_commissions c2
                    ON
                        c2.scheduleID=s.commissionScheduleID
                    AND (
                        c.estimatorID=c2.estimatorID AND
                        c.new_esn_contractdate_start BETWEEN IFNULL(c2.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c2.endDate, '9999-1-1'))
                    )

                -- spiff
                LEFT JOIN mhcdynad.mhc_subagent_spiff_schedule_history s2
                    ON
                        s2.accountID=c.accountID
                    AND (
                        c.new_esn_contractdate_start BETWEEN IFNULL(s2.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s2.endDate, '9999-1-1'))
                    )
                LEFT JOIN {$this->options->_dbName}.estimator_spiffs c3
                    ON
                        c3.scheduleID=s2.spiffScheduleID
                    AND (
                        c.new_esn_contractdate_start BETWEEN IFNULL(c3.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c3.endDate, '9999-1-1'))
                    )

                -- ads
                LEFT JOIN mhcdynad.mhc_subagent_ads_schedule_history s3
                    ON
                        s3.accountID=c.accountID
                    AND (
                        c.new_esn_contractdate_start BETWEEN IFNULL(s3.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s3.endDate, '9999-1-1'))
                    )
                LEFT JOIN {$this->options->_dbName}.estimator_advanced_device_commissions c4
                    ON
                        c4.scheduleID=s3.adScheduleID
                    AND (
                        c4.estimatorID=c.additionalCommissionEstimatorID AND
                        c.new_esn_contractdate_start BETWEEN IFNULL(c4.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c4.endDate, '9999-1-1'))
                    )

                WHERE c.monthyear=:monthYear AND c.accountID NOT IN (999999)

            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                estimatedCommissionPayout=t1.commission,
                estimatedSpiffPayout=t1.spiff,
                estimatedAdditionalCommissionPayout=t1.adsCommission
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during {$this->table} payout inserts. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish setting {$this->table} payouts.");
    }

    public function setFeaturePayouts()
    {
        $this->addUpdateLog('', 0, "Begin Setting Feature Payouts.");

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id, estimatedCommissionPayout, estimatedSpiffPayout)
            SELECT id, commissionAmount, spiff
            FROM(
                SELECT
                    c.id,
                    c.accountID,
                    c2.scheduleID,
                    c2.percentage,
                    IFNULL(c.commissionAmount,0)*IFNULL(percentage,0) AS commissionAmount,
                    IFNULL(c.spiff,0)*IFNULL(percentage,0) AS spiff
                FROM {$this->options->_dbName}.{$this->table} c

                -- percent
                LEFT JOIN mhcdynad.mhc_subagent_feature_schedule_history s
                ON
                    s.accountID=c.accountID
                AND (
                    c.monthYear BETWEEN IFNULL(s.begDate, '1900-1-1') AND LAST_DAY(IFNULL(s.endDate, '9999-1-1'))
                )
                LEFT JOIN {$this->options->_dbName}.estimator_features c2
                ON
                    c2.scheduleID=s.featureScheduleID
                AND (
                    c.monthYear BETWEEN IFNULL(c2.begDate, '1900-1-1') AND LAST_DAY(IFNULL(c2.endDate, '9999-1-1'))
                )

                WHERE c.monthyear=:monthYear AND c.accountID NOT IN (999999)
            ) AS t1
            ON DUPLICATE KEY
            UPDATE
                estimatedCommissionPayout=t1.commissionAmount,
                estimatedSpiffPayout=t1.spiff
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during feature payout. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish Setting {$this->table} Payouts.");
    }

    public function setNonComm()
    {
        $this->addUpdateLog('', 0, "Begin Setting NonComm Payouts.");

        switch ($this->table) {
            case self::ACTS;
            case self::DEACTS;
            case self::REACTS;
                $planName = "estimator_activation_non_commissonable_vision_codes";
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $planName = "estimator_upgrade_non_commissonable_vision_codes";
        }

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$this->table}
            (id)
            SELECT id
            FROM {$this->options->_dbName}.{$this->table}
            WHERE monthYear=:monthYear AND visionCode IN (
                SELECT visionCode
                FROM {$this->options->_dbName}.{$planName}
            )
            ON DUPLICATE KEY
            UPDATE
                estimatedCommissionPayout=0,
                estimatedSpiffPayout=0,
                estimatedAdditionalCommissionPayout=0
        ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear));
        } catch (exception $e) {
            return "There was an error during noncomm payout. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), "Finish Setting {$this->table} NonComm Payouts.");
    }

}
