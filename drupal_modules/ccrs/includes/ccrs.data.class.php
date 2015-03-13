<?php

/*
 * Provides a data model for ccrs reporting
 *
 *
 *
 *
 */

class ccrsData extends ccrs
{

    public $options;
    public $_db;
    public $searchByTable;
    public $searchSummary;
    public $detail;
    public $hpcList;
    public $hfList;
    public $mbbList;
    public $mbbTypeList;
    public $pdaTierList;
    public $pdaBucketList;
    public $bucketList;
    public $bucket;
    public $commissionPayoutScheduleList;
    public $commissionPayoutSchedule;
    public $adCommissionPayoutScheduleList;
    public $adCommissionPayoutSchedule;

    /**
     * Set the DB on instantiation
     *
     * @param type $options
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->_db = Database::getConnection('default', 'mhcdynad');
    }

    /**
     * used from a recursive array walk
     *
     * @param type $value
     * @param type $key
     */
    public function only_ints(&$value, &$key)
    {
        if ($value)
            $value = (int) $value;
    }

    /**
     * used from a recursive array walk
     *
     * @param type $value
     * @param type $key
     */
    public function singleQuote(&$value, &$key)
    {
        if ($value)
            $value = "'{$value}'";
    }

    /**
     * log the difference between two arrays
     *
     * @param type $old_array
     * @param type $new_array
     * @param type $callback
     */
    public function logDifference($old_array, $new_array, $callback)
    {
        // print_r($old_array);exit;

        $difference = array_diff($old_array, $new_array);

        if ($difference) {
            $log_array = array();
            foreach ($difference as $key => $value) {
                $log_array[] = "{$key}_old=>'{$old_array[$key]}', {$key}_new=>'{$new_array[$key]}'";
            }

            $message = implode('; ', $log_array);
            $callback[1][] = $message;

            call_user_func_array(array($this, $callback[0]), $callback[1]);
        }
    }

    /**
     * runs a query
     */
    public function search()
    {
        $SQL = array();

        //setup the wher clause
        $where = "
            WHERE monthYear BETWEEN :begDate AND :endDate
                AND
        ";

        //sanitize the locationValue

        if (!empty($this->options->locationValue[0])) {
            switch ($this->options->type) {
                case 'account':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " c.accountID IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'division':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " lt.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'district':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " d.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'region':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " r.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'location':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'singleQuote'));
                    $where .= " l.locationID IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'phonenumber':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " c.phone IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;
            }
        } else {
            $where .= 1;
        }

        foreach ($this->options->tableValue as $key => $thisTable) {

            //setup the from clause\
            $from = "
                FROM {$this->options->_dbName}.{$thisTable} c
                LEFT JOIN mhcdynad.mhc_locations l ON l.id = c.instanceID
                LEFT JOIN mhcdynad.mhc_regions r ON r.id = l.regionID
                LEFT JOIN mhcdynad.mhc_districts d ON d.id = r.districtID
                LEFT JOIN mhcdynad.mhc_locationType lt ON lt.id = d.locationTypeID
            ";

            switch ($thisTable) {

                case self::ACTS:
                case self::REACTS:
                    $SQL[] = "
                        SELECT
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), 1, 0)), 0) AS count,
                            SUM(isUnknownBucket) AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            IFNULL(SUM(isTierBonusEligible), 0) AS tbe,
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), installmentContract, 0)), 0) AS edges,
                            SUM(commissionAmount + additionalCommission) AS commission,
                            SUM(spiff + coop) AS spiff,
                            SUM(tierBonus) AS tier,
                            SUM(purchasedReceivable) AS purchasedReceivable,
                            SUM(edgeServiceFee) * -1 AS edgeServiceFee,
                            SUM(
                                  commissionAmount
                                + additionalCommission
                                + spiff
                                + coop
                                + tierBonus
                                + purchasedReceivable
                                - edgeServiceFee
                            ) AS receivableTotal,
                            SUM(
                                  estimatedCommissionPayout
                                + estimatedAdditionalCommissionPayout
                                + estimatedSpiffPayout
                                + estimatedCoopPayout
                                + estimatedTierBonusPayout
                                + estimatedPlatinumBonusPayout
                                + estimatedEmployeePayout
                                + estimatedPurchasedReceivablePayout
                                - estimatedEdgeServiceFeePayout
                            ) * -1 AS payableTotal,
                            SUM(
                                  (commissionAmount - estimatedCommissionPayout)
                                + (additionalCommission - estimatedAdditionalCommissionPayout)
                                + (spiff - estimatedSpiffPayout)
                                + (coop - estimatedCoopPayout)
                                + (tierBonus - estimatedTierBonusPayout - estimatedPlatinumBonusPayout)
                                + (purchasedReceivable - estimatedPurchasedReceivablePayout)
                                - (edgeServiceFee - estimatedEdgeServiceFeePayout)
                                - estimatedEmployeePayout
                            ) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::DEACTS:
                    $SQL[] = "
                        SELECT
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), 1, 0)), 0) * -1 AS count,
                            SUM(isUnknownBucket) AS unknownBucketCount,
                            SUM(isOrphanDeact) AS orphanDeactCount,
                            IFNULL(SUM(isTierBonusEligible), 0) AS tbe,
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), installmentContract, 0)), 0) * -1 AS edges,
                            SUM(commissionAmount + additionalCommission) * -1 AS commission,
                            SUM(spiff + coop) * -1 AS spiff,
                            SUM(tierBonus) * -1 AS tier,
                            SUM(purchasedReceivable) * -1 AS purchasedReceivable,
                            SUM(edgeServiceFee) AS edgeServiceFee,
                            SUM(
                                  commissionAmount
                                + additionalCommission
                                + spiff
                                + coop
                                + tierBonus
                                + purchasedReceivable
                                - edgeServiceFee
                            ) * -1 AS receivableTotal,
                            SUM(
                                  estimatedCommissionPayout
                                + estimatedAdditionalCommissionPayout
                                + estimatedSpiffPayout
                                + estimatedCoopPayout
                                + estimatedTierBonusPayout
                                + estimatedPlatinumBonusPayout
                                + estimatedEmployeePayout
                                + estimatedPurchasedReceivablePayout
                                - estimatedEdgeServiceFeePayout
                            ) AS payableTotal,
                            SUM(
                                  (commissionAmount - estimatedCommissionPayout)
                                + (additionalCommission - estimatedAdditionalCommissionPayout)
                                + (spiff - estimatedSpiffPayout)
                                + (coop - estimatedCoopPayout)
                                + (tierBonus - estimatedTierBonusPayout - estimatedPlatinumBonusPayout)
                                + (purchasedReceivable - estimatedPurchasedReceivablePayout)
                                - (edgeServiceFee - estimatedEdgeServiceFeePayout)
                                - estimatedEmployeePayout
                            ) * -1 AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::FEATURES:
                    $SQL[] = "
                        SELECT
                            IFNULL(SUM(IF(commissionAmount, 1, 0)), 0) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(commissionAmount) AS commission,
                            SUM(spiff) AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(commissionAmount + spiff) AS receivableTotal,
                            SUM(estimatedCommissionPayout + estimatedSpiffPayout) * -1 AS payableTotal,
                            SUM(
                                  (commissionAmount - estimatedCommissionPayout)
                                + (spiff - estimatedSpiffPayout)
                            ) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::FEATURES_CHARGEDBACK:
                    $SQL[] = "
                        SELECT
                            IFNULL(SUM(IF(commissionAmount, 1, 0)), 0) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(commissionAmount) * -1 AS commission,
                            SUM(spiff) * -1 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(commissionAmount + spiff) * -1 AS receivableTotal,
                            SUM(estimatedCommissionPayout + estimatedSpiffPayout) AS payableTotal,
                            SUM(
                                  (commissionAmount - estimatedCommissionPayout)
                                + (spiff - estimatedSpiffPayout)
                            ) * -1 AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::UPGRADES:
                    $SQL[] = "
                        SELECT
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), 1, 0)), 0) AS count,
                            SUM(isUnknownBucket) AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), installmentContract, 0)), 0) AS edges,
                            SUM(commissionAmount + additionalCommission) AS commission,
                            SUM(spiff) AS spiff,
                            0 AS tier,
                            SUM(purchasedReceivable) AS purchasedReceivable,
                            SUM(edgeServiceFee) * -1 AS edgeServiceFee,
                            SUM(
                                  commissionAmount
                                + additionalCommission
                                + spiff
                                + purchasedReceivable
                                - edgeServiceFee
                            ) AS receivableTotal,
                            SUM(
                                  estimatedCommissionPayout
                                + estimatedAdditionalCommissionPayout
                                + estimatedSpiffPayout
                                + estimatedEmployeePayout
                                + estimatedPurchasedReceivablePayout
                                - estimatedEdgeServiceFeePayout
                            ) * -1 AS payableTotal,
                            SUM(
                                  (commissionAmount - estimatedCommissionPayout)
                                + (spiff - estimatedSpiffPayout)
                                + (additionalCommission - estimatedAdditionalCommissionPayout)
                                + (purchasedReceivable - estimatedPurchasedReceivablePayout)
                                - (edgeServiceFee - estimatedEdgeServiceFeePayout)
                                - estimatedEmployeePayout
                            ) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::UPGRADE_DEACTS:
                    $SQL[] = "
                        SELECT
                            IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), 1, 0)), 0) * -1 AS count,
                            SUM(isUnknownBucket) AS unknownBucketCount,
                            SUM(isOrphanDeact) AS orphanDeactCount,
                            0 AS tbe,
                            SUM(installmentContract) * -1 as edges,
                            -- IFNULL(SUM(IF(visionCode NOT LIKE ('99999%'), installmentContract,0)), 0) * -1 AS edges,
                            SUM(commissionAmount + additionalCommission) * -1 AS commission,
                            SUM(spiff) * -1 AS spiff,
                            0 AS tier,
                            SUM(purchasedReceivable) * -1 AS purchasedReceivable,
                            SUM(edgeServiceFee) AS edgeServiceFee,
                            SUM(
                                  commissionAmount
                                + additionalCommission
                                + spiff
                                + purchasedReceivable
                                - edgeServiceFee
                            ) * -1 AS receivableTotal,
                            SUM(
                                  estimatedCommissionPayout
                                + estimatedAdditionalCommissionPayout
                                + estimatedSpiffPayout
                                + estimatedEmployeePayout
                                + estimatedPurchasedReceivablePayout
                                - estimatedEdgeServiceFeePayout
                            ) AS payableTotal,
                            SUM(
                                  (commissionAmount - estimatedCommissionPayout)
                                + (spiff - estimatedSpiffPayout)
                                + (additionalCommission - estimatedAdditionalCommissionPayout)
                                + (purchasedReceivable - estimatedPurchasedReceivablePayout)
                                - (edgeServiceFee - estimatedEdgeServiceFeePayout)
                                - estimatedEmployeePayout
                            ) * -1 AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::ADJUSTMENTS:
                    $SQL[] = "
                        SELECT
                            COUNT(c.id) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(adjustmentAmount) AS commission,
                            0 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(adjustmentAmount) AS receivableTotal,
                            0 AS payableTotal,
                            SUM(adjustmentAmount) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::DEPOSITS:
                    $SQL[] = "
                        SELECT
                            COUNT(c.id) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(depositAmount) AS commission,
                            0 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(depositAmount) AS receivableTotal,
                            SUM(depositAmount) *-1 AS payableTotal,
                            SUM(depositAmount - depositAmount) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::RESIDUALS:
                    $SQL[] = "
                        SELECT
                            COUNT(c.id) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(residual) AS commission,
                            0 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(residual) AS receivableTotal,
                            0 AS payableTotal,
                            SUM(residual) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::CHANGES:

                    //if phone number, alter behavior
                    switch ($this->options->type) {
                        case 'phonenumber':
                            $where = "
                                WHERE monthYear BETWEEN :begDate AND :endDate
                                    AND c.old_phone IN (" . implode(', ', $this->options->locationValue) . ")
                                    OR c.new_phone IN (" . implode(', ', $this->options->locationValue) . ")
                            ";
                            break;
                    }

                    $SQL[] = "
                        SELECT
                            COUNT(c.id) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            0 AS commission,
                            0 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            0 AS receivableTotal,
                            0 AS payableTotal,
                            0 AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::COOP_ACTS:
                    $SQL[] = "
                        SELECT
                            COUNT(c.id) AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(commissionAmount) AS commission,
                            0 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(commissionAmount) AS receivableTotal,
                            0 AS payableTotal,
                            SUM(commissionAmount) AS netTotal
                        $from
                        $where
                    ";
                    break;

                case self::COOP_DEACTS:
                    $SQL[] = "
                        SELECT
                            COUNT(c.id) *-1 AS count,
                            '' AS unknownBucketCount,
                            '' AS orphanDeactCount,
                            0 AS tbe,
                            0 AS edges,
                            SUM(commissionAmount)*-1 AS commission,
                            0 AS spiff,
                            0 AS tier,
                            0 AS purchasedReceivable,
                            0 AS edgeServiceFee,
                            SUM(commissionAmount)*-1 AS receivableTotal,
                            0 AS payableTotal,
                            SUM(commissionAmount)*-1 AS netTotal
                        $from
                        $where
                    ";
                    break;
                default:
            }
        }

        //paste each query together
        $SQL = implode('UNION ALL', $SQL);

        $this->searchByTable = $this->_db->query($SQL, array(
            ':begDate' => "{$this->options->begYear}-{$this->options->begMonth}-1",
            ':endDate' => "{$this->options->endYear}-{$this->options->endMonth}-1"
            ), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );

        $SQL = "
            SELECT
                SUM(count) AS count,
                SUM(tbe) AS tbe,
                SUM(edges) AS edges,
                SUM(commission) AS commission,
                SUM(spiff) AS spiff,
                SUM(tier) AS tier,
                SUM(purchasedReceivable) AS purchasedReceivable,
                SUM(edgeServiceFee) AS edgeServiceFee,
                SUM(receivableTotal) AS receivableTotal,
                SUM(payableTotal) AS payableTotal,
                SUM(netTotal) AS netTotal
                FROM(
                    $SQL
                ) AS t1
        ";

        $this->searchSummary = $this->_db->query($SQL, array(
            ':begDate' => "{$this->options->begYear}-{$this->options->begMonth}-01",
            ':endDate' => "{$this->options->endYear}-{$this->options->endMonth}-01",
            ), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );
    }

    /**
     * runs a query with details
     */
    public function searchDetails()
    {
        $SQL = array();

        $thisTable = $this->options->tableValue[0];

        //setup the from clause\
        $from = "
            FROM {$this->options->_dbName}.{$thisTable} c
            LEFT JOIN mhcdynad.mhc_locations l ON l.id = c.instanceID
            LEFT JOIN mhcdynad.mhc_regions r ON r.id = l.regionID
            LEFT JOIN mhcdynad.mhc_districts d ON d.id = r.districtID
            LEFT JOIN mhcdynad.mhc_locationType lt ON lt.id = d.locationTypeID
        ";

        //setup the wher clause
        $where = "
            WHERE monthYear BETWEEN :begDate AND :endDate
                AND
        ";

        //sanitize the locationValue
        if (!empty($this->options->locationValue[0])) {
            switch ($this->options->type) {
                case 'account':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " c.accountID IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'division':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " lt.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'district':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " d.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'region':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " r.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'location':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'singleQuote'));
                    $where .= " l.locationID IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'phonenumber':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " c.phone IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;
            }
        } else {
            $where .= 1;
        }

        switch ($thisTable) {
            case self::ACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Customer Name',
                        'Phone',
                        'Vision Code',
                        'Price Plan',
                        'Contract Length',
                        'Contract Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Coop',
                        'Tier Bonus',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Coop Payout',
                        'Estimated Tier Bonus Payout',
                        'Estimated Platinum Bonus Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee Payout',
                        'Unknown Bucket'
                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.customerName,
                        c.phone,
                        c.visionCode,
                        c.pricePlan,
                        c.contractLength,
                        DATE_FORMAT(contractDate, '%m/%d/%Y'),
                        c.phoneDescription,
                        c.commissionAmount,
                        c.additionalCommission,
                        c.spiff,
                        c.coop,
                        c.tierBonus,
                        c.purchasedReceivable,
                        c.edgeServiceFee * -1,
                        c.estimatedCommissionPayout,
                        c.estimatedAdditionalCommissionPayout,
                        c.estimatedSpiffPayout,
                        c.estimatedCoopPayout,
                        c.estimatedTierBonusPayout,
                        c.estimatedPlatinumBonusPayout,
                        c.estimatedEmployeePayout,
                        c.estimatedPurchasedReceivablePayout,
                        c.estimatedEdgeServiceFeePayout * -1,
                        c.isUnknownBucket
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::DEACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Price Plan',
                        'Days of Service',
                        'CustomerName',
                        'Phone',
                        'Vision Code',
                        'Phone Description',
                        'Contract Length',
                        'Contract Date',
                        'Deact Date',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Coop',
                        'Tier Bonus',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Coop Payout',
                        'Estimated Tier Bonus Payout',
                        'Estimated Platinum Bonus Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee Payout',
                        'Unknown Bucket',
                        'Orphaned Deactivation'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.pricePlan,
                        c.daysOfService,
                        c.customerName,
                        c.phone,
                        c.visionCode,
                        c.phoneDescription,
                        c.contractLength,
                        DATE_FORMAT(c.contractDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.deactDate, '%m/%d/%Y'),
                        c.commissionAmount * -1,
                        c.additionalCommission * -1,
                        c.spiff * -1,
                        c.coop * -1,
                        c.tierBonus * -1,
                        c.purchasedReceivable * -1,
                        c.edgeServiceFee,
                        c.estimatedCommissionPayout * -1,
                        c.estimatedAdditionalCommissionPayout * -1,
                        c.estimatedSpiffPayout * -1,
                        c.estimatedCoopPayout * -1,
                        c.estimatedTierBonusPayout * -1,
                        c.estimatedPlatinumBonusPayout * -1,
                        c.estimatedEmployeePayout * -1,
                        c.estimatedPurchasedReceivablePayout * -1,
                        c.estimatedEdgeServiceFeePayout,
                        c.isUnknownBucket,
                        c.isOrphanDeact
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::FEATURES:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Customer Name',
                        'Phone',
                        'Device Category',
                        'Device ID',
                        'Price Plan',
                        'Vision Code',
                        'Feature ID',
                        'Plan Name',
                        'Contract Date',
                        'Commission Amount',
                        'Spiff'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.customerName,
                        c.phone,
                        c.deviceCategory,
                        c.deviceID,
                        c.pricePlan,
                        c.visionCode,
                        c.featureID,
                        c.planName,
                        DATE_FORMAT(c.contractDate, '%m/%d/%Y'),
                        c.commissionAmount,
                        c.spiff
                    $from
                    $where
                ";
                break;

            case self::FEATURES_CHARGEDBACK:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Vision Code',
                        'Feature ID',
                        'Plan Name',
                        'Price Plan',
                        'Customer Name',
                        'Phone',
                        'Device Category',
                        'Device ID',
                        'Contract Date',
                        'Deact Date',
                        'Commission Amount',
                        'Spiff'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.visionCode,
                        c.featureID,
                        c.planName,
                        c.pricePlan,
                        c.customerName,
                        c.phone,
                        c.deviceCategory,
                        c.deviceID,
                        DATE_FORMAT(c.contractDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.deactDate, '%m/%d/%Y'),
                        c.commissionAmount*-1,
                        c.spiff * -1
                    $from
                    $where
                ";
                break;

            case self::REACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Customer Name',
                        'Phone',
                        'Vision Code',
                        'Code',
                        'Price Plan',
                        'Contract Length',
                        'Contract Date',
                        'Reactivation Date',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Coop',
                        'Tier Bonus',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Coop Payout',
                        'Estimated Tier Bonus Payout',
                        'Estimated Platinum Bonus Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable',
                        'Estimated Edge Service Fee',
                        'Unknown Bucket'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.customerName,
                        c.phone,
                        c.visionCode,
                        c.code,
                        c.pricePlan,
                        c.contractLength,
                        DATE_FORMAT(c.contractDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.reactivationDate, '%m/%d/%Y'),
                        c.commissionAmount,
                        c.additionalCommission,
                        c.spiff,
                        c.coop,
                        c.tierBonus,
                        c.purchasedReceivable,
                        c.edgeServiceFee * -1,
                        c.estimatedCommissionPayout,
                        c.estimatedAdditionalCommissionPayout,
                        c.estimatedSpiffPayout,
                        c.estimatedCoopPayout,
                        c.estimatedTierBonusPayout,
                        c.estimatedPlatinumBonusPayout,
                        c.estimatedEmployeePayout,
                        c.estimatedPurchasedReceivablePayout,
                        c.estimatedEdgeServiceFeePayout *-1,
                        c.isUnknownBucket
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::UPGRADES:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Vision Code',
                        'Price Plan',
                        'Customer Name',
                        'Phone',
                        'Contract Length',
                        'Upgrade Type',
                        'New ESN Contract Start Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee Payout',
                        'Unknown Bucket'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.visionCode,
                        c.pricePlan,
                        c.customerName,
                        c.phone,
                        c.contractLength,
                        c.upgradeType,
                        DATE_FORMAT(c.new_esn_contractdate_start, '%m/%d/%Y'),
                        c.phoneDescription,
                        c.commissionAmount,
                        c.additionalCommission,
                        c.spiff,
                        c.purchasedReceivable,
                        c.edgeServiceFee * -1,
                        c.estimatedCommissionPayout,
                        c.estimatedAdditionalCommissionPayout,
                        c.estimatedSpiffPayout,
                        c.estimatedEmployeePayout,
                        c.estimatedPurchasedReceivablePayout,
                        c.estimatedEdgeServiceFeePayout * -1,
                        c.isUnknownBucket
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::UPGRADE_DEACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'LocationID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Vision Code',
                        'Price Plan',
                        'Customer Name',
                        'Phone',
                        'Contract Length',
                        'New ESN Contract Start Date',
                        'Deact Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee Payout',
                        'Unknown Bucket',
                        'Orphaned Deactivation'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.visionCode,
                        c.pricePlan,
                        c.customerName,
                        c.phone,
                        c.contractLength,
                        DATE_FORMAT(c.new_esn_contractdate_start, '%m/%d/%Y'),
                        DATE_FORMAT(c.deactDate, '%m/%d/%Y'),
                        c.phoneDescription,
                        c.commissionAmount * -1,
                        c.additionalCommission * -1,
                        c.spiff * -1,
                        c.purchasedReceivable * -1,
                        c.edgeServiceFee,
                        c.estimatedCommissionPayout * -1,
                        c.estimatedAdditionalCommissionPayout * -1,
                        c.estimatedSpiffPayout * -1,
                        c.estimatedEmployeePayout * -1,
                        c.estimatedPurchasedReceivablePayout * -1,
                        c.estimatedEdgeServiceFeePayout,
                        c.isUnknownBucket,
                        c.isOrphanDeact
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::ADJUSTMENTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'Month',
                        'Year',
                        'SFID',
                        'Phone',
                        'Device Category',
                        'Device ID',
                        'Adjustment Description',
                        'Adjustment Amount'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.month,
                        c.year,
                        c.sfid,
                        c.phone,
                        c.deviceCategory,
                        c.deviceID,
                        c.adjustmentDescription,
                        c.adjustmentAmount
                    $from
                    $where
                ";
                break;

            case self::DEPOSITS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Phone',
                        'Customer Name',
                        'Deposit Date',
                        'Contract Date',
                        'Deposit Amount'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.phone,
                        c.customerName,
                        DATE_FORMAT(c.depositDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.contractDate, '%m/%d/%Y'),
                        c.depositAmount
                    $from
                    $where
                ";
                break;

            case self::RESIDUALS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Company',
                        'Customer Name',
                        'Phone',
                        'Device Category',
                        'Device ID',
                        'Vision Code',
                        'Paid Date',
                        'Contract Date',
                        'Deact Date',
                        'Percent',
                        'Amount',
                        'Residual'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.company,
                        c.customerName,
                        c.phone,
                        c.deviceCategory,
                        c.deviceID,
                        c.visionCode,
                        DATE_FORMAT(c.paidDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.contractDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.deactDate, '%m/%d/%Y'),
                        c.percent,
                        c.amount,
                        c.residual
                    $from
                    $where
                ";
                break;

            case self::CHANGES:

                //if phone number, alter behavior
                switch ($this->options->type) {
                    case 'phonenumber':
                        $where = "
                            WHERE monthYear BETWEEN :begDate AND :endDate
                                AND c.old_phone IN (" . implode(', ', $this->options->locationValue) . ")
                                OR c.new_phone IN (" . implode(', ', $this->options->locationValue) . ")
                        ";
                        break;
                }

                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'SFID 2',
                        'Company',
                        'Customer Name',
                        'Old Phone',
                        'New Phone',
                        'Old Rate Plan',
                        'New Rate Plan',
                        'Old Account Number',
                        'New Account Number',
                        'Feature',
                        'Feature Desc'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.sfid2,
                        c.company,
                        c.customerName,
                        c.old_phone,
                        c.new_phone,
                        c.old_rate_plan,
                        c.new_rate_plan,
                        c.old_account_number,
                        c.new_account_number,
                        c.feature,
                        c.feature_desc
                    $from
                    $where
                ";
                break;

            case self::COOP_ACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Device Category',
                        'Customer Name',
                        'Phone',
                        'Vision Code',
                        'Doc Date',
                        'Commission Amount'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.deviceCategory,
                        c.customerName,
                        c.phone,
                        c.visionCode,
                        DATE_FORMAT(c.docDate, '%m/%d/%Y'),
                        c.commissionAmount
                    $from
                    $where
                ";
                break;

            case self::COOP_DEACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Device Category',
                        'Customer Name',
                        'Phone',
                        'Vision Code',
                        'Doc Date',
                        'Deact Date',
                        'Commission Amount'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.deviceCategory,
                        c.customerName,
                        c.phone,
                        c.visionCode,
                        DATE_FORMAT(c.docDate, '%m/%d/%Y'),
                        DATE_FORMAT(c.deactDate, '%m/%d/%Y'),
                        c.commissionAmount * -1
                    $from
                    $where
                ";
                break;

            default:
        }

        $this->file = $this->databaseExportDirectory . '/ccrs_' . time() . '.xls';
        $SQL.="
            INTO OUTFILE '{$this->file}'
            FIELDS TERMINATED BY '\t'
            OPTIONALLY ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
        ";

        $this->detail = $this->_db->query($SQL, array(
            ':begDate' => "{$this->options->begYear}-{$this->options->begMonth}-1",
            ':endDate' => "{$this->options->endYear}-{$this->options->endMonth}-1"
            ), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );
    }

    public function searchPayout()
    {
        $SQL = array();

        $thisTable = $this->options->tableValue[0];

        //setup the from clause\
        $from = "
            FROM {$this->options->_dbName}.{$thisTable} c
            LEFT JOIN mhcdynad.mhc_locations l ON l.id = c.instanceID
            LEFT JOIN mhcdynad.mhc_regions r ON r.id = l.regionID
            LEFT JOIN mhcdynad.mhc_districts d ON d.id = r.districtID
            LEFT JOIN mhcdynad.mhc_locationType lt ON lt.id = d.locationTypeID
        ";

        //setup the wher clause
        $where = "
            WHERE monthYear BETWEEN :begDate AND :endDate
                AND
        ";

        //sanitize the locationValue
        if (!empty($this->options->locationValue[0])) {
            switch ($this->options->type) {
                case 'account':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " c.accountID IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'division':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " lt.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'district':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " d.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'region':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " r.id IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'location':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'singleQuote'));
                    $where .= " l.locationID IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;

                case 'phonenumber':
                    array_walk_recursive($this->options->locationValue, array(&$this, 'only_ints'));
                    $where .= " c.phone IN (" . implode(', ', $this->options->locationValue) . ") ";
                    break;
            }
        } else {
            $where .= 1;
        }

        $thisTable = $this->options->tableValue[0];
        switch ($thisTable) {
            case self::ACTS:
            case self::REACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Price Plan',
                        'Vision Code',
                        'Contract Length',
                        'Contract Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Coop',
                        'Tier Bonus',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Coop Payout',
                        'Estimated Tier Bonus Payout',
                        'Estimated Platinum Bonus Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee',
                        'Receivable Total',
                        'Payable Total',
                        'Net Total'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.pricePlan,
                        c.visionCode,
                        c.contractLength,
                        c.contractDate,
                        c.phoneDescription,
                        c.commissionAmount,
                        c.additionalCommission,
                        c.spiff,
                        c.coop,
                        c.tierBonus,
                        c.purchasedReceivable,
                        c.edgeServiceFee * -1,
                        c.estimatedCommissionPayout,
                        c.estimatedAdditionalCommissionPayout,
                        c.estimatedSpiffPayout,
                        c.estimatedCoopPayout,
                        c.estimatedTierBonusPayout,
                        c.estimatedPlatinumBonusPayout,
                        c.estimatedEmployeePayout,
                        c.estimatedPurchasedReceivablePayout,
                        c.estimatedEdgeServiceFeePayout * -1,
                        (  c.commissionAmount
                         + c.additionalCommission
                         + c.spiff
                         + c.coop
                         + c.tierBonus
                         + c.purchasedReceivable
                         - c.edgeServiceFee
                        ),
                        (  c.estimatedCommissionPayout
                         + c.estimatedAdditionalCommissionPayout
                         + c.estimatedSpiffPayout
                         + c.estimatedCoopPayout
                         + c.estimatedTierBonusPayout
                         + c.estimatedPlatinumBonusPayout
                         + c.estimatedEmployeePayout
                         + c.estimatedPurchasedReceivablePayout
                         - c.estimatedEdgeServiceFeePayout
                        ) * -1,
                        (     (c.commissionAmount -  c.estimatedCommissionPayout)
                            + (c.additionalCommission - c.estimatedAdditionalCommissionPayout)
                            + (c.spiff - c.estimatedSpiffPayout)
                            + (c.coop - c.estimatedCoopPayout)
                            + (c.tierBonus - c.estimatedTierBonusPayout - c.estimatedPlatinumBonusPayout)
                            + (c.purchasedReceivable - c.estimatedPurchasedReceivablePayout)
                            - (c.edgeServiceFee -  c.estimatedEdgeServiceFeePayout)
                            - c.estimatedEmployeePayout
                        )
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::DEACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Price Plan',
                        'Vision Code',
                        'Contract Length',
                        'Contract Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Coop',
                        'Tier Bonus',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Coop Payout',
                        'Estimated Tier Bonus Payout',
                        'Estimated Platinum Bonus Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee',
                        'Receivable Total',
                        'Payable Total',
                        'Net Total'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.pricePlan,
                        c.visionCode,
                        c.contractLength,
                        c.contractDate,
                        c.phoneDescription,
                        c.commissionAmount * -1,
                        c.additionalCommission * -1,
                        c.spiff * -1,
                        c.coop * -1,
                        c.tierBonus * -1,
                        c.purchasedReceivable * -1,
                        c.edgeServiceFee,
                        c.estimatedCommissionPayout * -1,
                        c.estimatedAdditionalCommissionPayout * -1,
                        c.estimatedSpiffPayout * -1,
                        c.estimatedCoopPayout * -1,
                        c.estimatedTierBonusPayout * -1,
                        c.estimatedPlatinumBonusPayout * -1,
                        c.estimatedEmployeePayout * -1,
                        c.estimatedPurchasedReceivablePayout * -1,
                        c.estimatedEdgeServiceFeePayout,
                        (  c.commissionAmount
                          + c.additionalCommission
                          + c.spiff
                          + c.coop
                          + c.tierBonus
                          + c.purchasedReceivable
                          - c.edgeServiceFee
                        ) * -1,
                        (  c.estimatedCommissionPayout
                         + c.estimatedAdditionalCommissionPayout
                         + c.estimatedSpiffPayout
                         + c.estimatedCoopPayout
                         + c.estimatedTierBonusPayout
                         + c.estimatedPlatinumBonusPayout
                         + c.estimatedEmployeePayout
                         + c.estimatedPurchasedReceivablePayout
                         - c.estimatedEdgeServiceFeePayout
                        ),
                        (     (c.commissionAmount -  c.estimatedCommissionPayout)
                            + (c.additionalCommission - c.estimatedAdditionalCommissionPayout)
                            + (c.spiff - c.estimatedSpiffPayout)
                            + (c.coop - c.estimatedCoopPayout)
                            + (c.tierBonus - c.estimatedTierBonusPayout - c.estimatedPlatinumBonusPayout)
                            + (c.purchasedReceivable - c.estimatedPurchasedReceivablePayout)
                            - (c.edgeServiceFee -  c.estimatedEdgeServiceFeePayout)
                            - c.estimatedEmployeePayout
                        ) * -1
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::FEATURES:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Device Category',
                        'Device ID',
                        'Price Plan',
                        'Vision Code',
                        'Contract Length',
                        'Contract Date',
                        'Plan Name',
                        'Commission Amount',
                        'Spiff',
                        'Estimated Commission Payout',
                        'Estimated Spiff Payout',
                        'Receivable Total',
                        'Payable Total',
                        'Net Total'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.deviceCategory,
                        c.deviceID,
                        c.pricePlan,
                        c.visionCode,
                        c.contractLength,
                        c.contractDate,
                        c.planName,
                        c.commissionAmount,
                        spiff,
                        estimatedCommissionPayout,
                        estimatedSpiffPayout,
                        (commissionAmount + spiff),
                        (estimatedCommissionPayout + estimatedSpiffPayout) * -1,
                        (  (commissionAmount - estimatedCommissionPayout)
                         + (spiff - estimatedSpiffPayout)
                        )
                    $from
                    $where
                ";
                break;

            case self::FEATURES_CHARGEDBACK:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Device Category',
                        'Device ID',
                        'Price Plan',
                        'Vision Code',
                        'Contract Length',
                        'Contract Date',
                        'Plan Name',
                        'Commission Amount',
                        'Spiff',
                        'Estimated Commission Payout',
                        'Estimated Spiff Payout',
                        'Receivable Total',
                        'Payable Total',
                        'Net Total'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.deviceCategory,
                        c.deviceID,
                        c.pricePlan,
                        c.visionCode,
                        c.contractLength,
                        c.contractDate,
                        c.planName,
                        c.commissionAmount * -1,
                        spiff * -1,
                        estimatedCommissionPayout * -1,
                        estimatedSpiffPayout * -1,
                        (commissionAmount + spiff) * -1,
                        (estimatedCommissionPayout + estimatedSpiffPayout),
                        (  (commissionAmount - estimatedCommissionPayout)
                         + (spiff - estimatedSpiffPayout)
                        ) * -1
                    $from
                    $where
                ";
                break;

            case self::UPGRADES:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Price Plan',
                        'Vision Code',
                        'Upgrade Type',
                        'Contract Length',
                        'Contract Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee',
                        'Receivable Total',
                        'Payable Total',
                        'Net Total'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.pricePlan,
                        c.visionCode,
                        c.upgradeType,
                        c.contractLength,
                        c.contractDate,
                        c.phoneDescription,
                        c.commissionAmount,
                        c.additionalCommission,
                        c.spiff,
                        c.purchasedReceivable,
                        c.edgeServiceFee * -1,
                        c.estimatedCommissionPayout,
                        c.estimatedAdditionalCommissionPayout,
                        c.estimatedSpiffPayout,
                        c.estimatedEmployeePayout,
                        c.estimatedPurchasedReceivablePayout,
                        c.estimatedEdgeServiceFeePayout * -1,
                        (  c.commissionAmount
                         + c.additionalCommission
                         + c.spiff
                         + c.purchasedReceivable
                         - c.edgeServiceFee
                        ),
                        (  c.estimatedCommissionPayout
                         + c.estimatedAdditionalCommissionPayout
                         + c.estimatedSpiffPayout
                         + c.estimatedEmployeePayout
                         + c.estimatedPurchasedReceivablePayout
                         - c.estimatedEdgeServiceFeePayout
                        ) * -1,
                        (     (c.commissionAmount -  c.estimatedCommissionPayout)
                            + (c.additionalCommission - c.estimatedAdditionalCommissionPayout)
                            + (c.spiff - c.estimatedSpiffPayout)
                            + (c.purchasedReceivable - c.estimatedPurchasedReceivablePayout)
                            - (c.edgeServiceFee -  c.estimatedEdgeServiceFeePayout)
                            - c.estimatedEmployeePayout
                        )
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::UPGRADE_DEACTS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Device Category',
                        'Device ID',
                        'Bucket ID',
                        'Bucket Description',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Price Plan',
                        'Vision Code',
                        'Upgrade Type',
                        'Contract Length',
                        'Contract Date',
                        'Phone Description',
                        'Commission Amount',
                        'Additional Commission',
                        'Spiff',
                        'Purchased Receivable',
                        'Edge Service Fee',
                        'Estimated Commission Payout',
                        'Estimated Additional Commission Payout',
                        'Estimated Spiff Payout',
                        'Estimated Employee Payout',
                        'Estimated Purchased Receivable Payout',
                        'Estimated Edge Service Fee',
                        'Receivable Total',
                        'Payable Total',
                        'Net Total'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.deviceCategory,
                        c.deviceID,
                        c.bucketID,
                        b.description,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.pricePlan,
                        c.visionCode,
                        c.upgradeType,
                        c.contractLength,
                        c.contractDate,
                        c.phoneDescription,
                        c.commissionAmount * -1,
                        c.additionalCommission * -1,
                        c.spiff * -1,
                        c.purchasedReceivable * -1,
                        c.edgeServiceFee,
                        c.estimatedCommissionPayout * -1,
                        c.estimatedAdditionalCommissionPayout * -1,
                        c.estimatedSpiffPayout * -1,
                        c.estimatedEmployeePayout * -1,
                        c.estimatedPurchasedReceivablePayout * -1,
                        c.estimatedEdgeServiceFeePayout,
                        (  c.commissionAmount
                         + c.additionalCommission
                         + c.spiff
                         + c.purchasedReceivable
                         - c.edgeServiceFee
                        ) * -1,
                        (  c.estimatedCommissionPayout
                         + c.estimatedAdditionalCommissionPayout
                         + c.estimatedSpiffPayout
                         + c.estimatedEmployeePayout
                         + c.estimatedPurchasedReceivablePayout
                         - c.estimatedEdgeServiceFeePayout
                        ),
                        (     (c.commissionAmount -  c.estimatedCommissionPayout)
                            + (c.additionalCommission - c.estimatedAdditionalCommissionPayout)
                            + (c.spiff - c.estimatedSpiffPayout)
                            + (c.purchasedReceivable - c.estimatedPurchasedReceivablePayout)
                            - (c.edgeServiceFee -  c.estimatedEdgeServiceFeePayout)
                            - c.estimatedEmployeePayout
                        ) * -1
                    $from
                    LEFT JOIN {$this->options->_dbName}.buckets b USING (bucketID)
                    $where
                ";
                break;

            case self::DEPOSITS:
                $SQL = "
                    SELECT
                        'Account ID',
                        'Instance ID',
                        'Location ID',
                        'District',
                        'Region',
                        'SFID',
                        'Month Year',
                        'Account Number',
                        'Customer Name',
                        'Phone',
                        'Deposit Term',
                        'VZW Total',
                        'Payout'

                    UNION ALL

                    SELECT
                        c.accountID,
                        c.instanceID,
                        c.locationID,
                        d.district,
                        r.region,
                        c.sfid,
                        c.monthYear,
                        c.accountNumber,
                        c.customerName,
                        c.phone,
                        c.depositTerm,
                        depositAmount,
                        depositAmount
                    $from
                    $where
                ";
                break;

            default:
        }

        $this->file = $this->databaseExportDirectory . '/ccrs_' . time() . '.xls';
        $SQL .= "
            INTO OUTFILE '{$this->file}'
            FIELDS TERMINATED BY '\t'
            OPTIONALLY ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
        ";

        $this->detail = $this->_db->query($SQL, array(
            ':begDate' => "{$this->options->begYear}-{$this->options->begMonth}-1",
            ':endDate' => "{$this->options->endYear}-{$this->options->endMonth}-1"
            ), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );
    }

    /**
     * removes old reports on the server
     */
    public function purgeOldReports()
    {
        $files = file_scan_directory($this->destDirectory, '/.*\.xls$/', $options = array(), $depth = 0);
        if ($files) {
            foreach ($files as $thisFile => $fileObject) {
                if (time() - filemtime($thisFile) > self::MAX_FILE_AGE) {
                    file_unmanaged_delete($thisFile);
                }
            }
        }
    }

    /**
     * exports a file to the browser
     *
     * @param type $file_suffix
     */
    public function export($file_suffix = null)
    {
        $this->purgeOldReports();

        // creates directory if not exists
        file_prepare_directory($this->destDirectory, FILE_CREATE_DIRECTORY);
        $newFilePath = file_unmanaged_copy($this->file, $this->destDirectory . '/' . basename($this->file));

        file_transfer($newFilePath, array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $this->options->tableValue[0] . $file_suffix . '.xls"',
            'Content-Length' => filesize($newFilePath),
            'Pragma' => 'no-cache',
            'Expires' => '-1',
            'Cache-Control' => 'must-revalidate, no-cache, post-check=0, pre-check=0',
            )
        );
    }

    public function getUpdateLog()
    {
        $SQL = "
            SELECT
                tn, comment, recordsaffected, username, addedon
            FROM {$this->options->_dbName}.ccrs_update_log
            WHERE addedOn >= (
                SELECT STR_TO_DATE(value, '%Y-%m-%d %H:%i:%s')
                FROM {$this->options->_dbName}.ccrs_settings
                WHERE setting = 'updateStartTime'
            )
            ORDER BY id DESC
        ";

        $this->updateLog = $this->_db->query($SQL, array(
            ':begDate' => "{$this->options->begYear}-{$this->options->begMonth}-01",
            ':endDate' => "{$this->options->endYear}-{$this->options->endMonth}-01",
            ), array()
        );
    }

    /**
     * pulls the orphaned sfids from the system
     *
     * @return type
     */
    public function getOrphanSFIDs()
    {
        $SQL = array();

        $tables = array(
            self::ACTS => 'Activations',
            self::DEACTS => 'Deactivations',
            self::FEATURES => 'Features',
            self::FEATURES_CHARGEDBACK => 'Feature Chargebacks',
            self::REACTS => 'Reactivations',
            self::UPGRADES => 'Upgrades',
            self::UPGRADE_DEACTS => 'Upgrade Deacts',
            self::ADJUSTMENTS => 'Adjustments',
            self::DEPOSITS => 'Deposits',
            self::RESIDUALS => 'Residuals',
        );

        foreach ($tables as $table => $name) {
            $SQL[] = "
                SELECT DISTINCT(sfid) AS sfid
                FROM {$this->options->_dbName}.{$table}
                WHERE accountID IS NULL
            ";
        }

        $SQL = implode("UNION ALL \n", $SQL);

        $SQL = "
            SELECT DISTINCT(sfid) AS sfid
            FROM($SQL)
            AS t1
        ";

        $result = $this->_db->query($SQL, array(), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );
        $result = $result->fetchAll();

        $orphans = array();

        if (!empty($result)) {
            foreach ($result as $thisResult) {
                $orphans[] = $thisResult['sfid'];
            }
        }

        return $orphans;
    }

    public function setBucketList()
    {
        $SQL = "
            SELECT
                cs.`schedule`,
                b.bucketID,
                b.bucketCategoryID,
                ct.description,
                act.type,
                b.term,
                b.description,
                cr.amount,
                cr.adSpiff,
                cp.amount,
                cp.adSpiff,
                cp.empSpiff
            FROM {$this->options->_dbName}.buckets b
            LEFT JOIN {$this->options->_dbName}.bucket_act_types act USING (actTypeID)
            LEFT JOIN {$this->options->_dbName}.bucket_contract_types ct USING (contractTypeID)
            LEFT JOIN {$this->options->_dbName}.bucket_commission_buckets cr USING (bucketID)
            LEFT JOIN {$this->options->_dbName}.bucket_commission_payout_buckets cp USING (bucketID)
            RIGHT JOIN {$this->options->_dbName}.estimator_commission_schedules cs ON cs.id = cp.payoutScheduleID
            WHERE cs.id IN (1,10,11)
            ORDER BY b.bucketCategoryID, b.term, b.bucketID, cs.id
        ";

        $this->bucketList = $this->_db->query($SQL, array(), array()
        );
    }

    public function log($referenceID, $table, $message)
    {
        global $user;

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.{$table}
            (message, username, loggedOn, referenceID)
            VALUES
            (:message, :username, NOW(), :referenceID)
        ";
        $result = $this->_db->query($SQL, array(
            ':referenceID' => $referenceID,
            ':message' => $message,
            ':username' => $user->name
            )
        );
    }

    /**
     * Given a table name, return most recent addedOn and contractDate columns
     * @param array $tableInfo (tableName:'name',field:'field')
     * @return array PDO::FETCH_ASSOC
     */
    public function getDatesForTable(array $tableInfo)
    {
        $tableName = $tableInfo['tableName'];
        $fieldName = $tableInfo['field'];

        $query = "
                SELECT
                    (SELECT $fieldName FROM {$this->options->_dbName}.`$tableName`
                        WHERE $fieldName <= CURDATE()
                        ORDER BY $fieldName DESC LIMIT 1) AS dateAlias,
                    (SELECT addedOn FROM {$this->options->_dbName}.`$tableName`
                        WHERE addedOn < NOW()
                        ORDER BY addedOn DESC LIMIT 1) AS addedOn
        ";

        $result = $this->_db->query($query);

        return $result->fetch(PDO::FETCH_ASSOC);
    }

}
