<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::bucket_commission_payout_buckets'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class BucketCommissionPayoutBuckets extends AbstractEntity
{
    /**
     * @field payoutBucketID
     * @var int(11)
     * @key primary
     */
    protected $payoutBucketID;

    /**
     * @field bucketID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $bucketID;

    /**
     * @field payoutScheduleID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $payoutScheduleID;

    /**
     * @field begDate
     * @var date
     * @nullable
     * @default null
     */
    protected $begDate;

    /**
     * @field endDate
     * @var date
     * @nullable
     * @default null
     */
    protected $endDate;

    /**
     * @field amount
     * @var decimal(10,2)
     * @default 0.00
     */
    protected $amount;

    /**
     * @field adSpiff
     * @var decimal(10,2)
     * @default 0.00
     */
    protected $adSpiff;

    /**
     * @field empSpiff
     * @var decimal(10,2)
     */
    protected $empSpiff;

    /**
     * @field addedOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $addedOn;


    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->bucketID = null;
        $this->payoutScheduleID = null;
        $this->begDate = null;
        $this->endDate = null;
        $this->amount = 0.00;
        $this->adSpiff = 0.00;
        $this->addedOn = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'payoutBucketID'.
     *
     * @return int(11)
     */
    public function getPayoutBucketID()
    {
        return $this->payoutBucketID;
    }

    /**
     * Chainable setter for 'payoutBucketID'.
     *
     * @param int(11) $payoutBucketID
     */
    public function setPayoutBucketID($payoutBucketID)
    {
        $this->payoutBucketID = $payoutBucketID;
        return $this;
    }

    /**
     * Getter for 'bucketID'.
     *
     * @return int(11)|null
     */
    public function getBucketID()
    {
        return $this->bucketID;
    }

    /**
     * Chainable setter for 'bucketID'.
     *
     * @param int(11)|null $bucketID
     */
    public function setBucketID($bucketID = null)
    {
        $this->bucketID = $bucketID;
        return $this;
    }

    /**
     * Getter for 'payoutScheduleID'.
     *
     * @return int(11)|null
     */
    public function getPayoutScheduleID()
    {
        return $this->payoutScheduleID;
    }

    /**
     * Chainable setter for 'payoutScheduleID'.
     *
     * @param int(11)|null $payoutScheduleID
     */
    public function setPayoutScheduleID($payoutScheduleID = null)
    {
        $this->payoutScheduleID = $payoutScheduleID;
        return $this;
    }

    /**
     * Getter for 'begDate'.
     *
     * @return date|null
     */
    public function getBegDate()
    {
        return $this->begDate;
    }

    /**
     * Chainable setter for 'begDate'.
     *
     * @param date|null $begDate
     */
    public function setBegDate($begDate = null)
    {
        $this->begDate = $begDate;
        return $this;
    }

    /**
     * Getter for 'endDate'.
     *
     * @return date|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Chainable setter for 'endDate'.
     *
     * @param date|null $endDate
     */
    public function setEndDate($endDate = null)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Getter for 'amount'.
     *
     * @return decimal(10,2)
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Chainable setter for 'amount'.
     *
     * @param decimal(10,2) $amount
     */
    public function setAmount($amount = 0.00)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Getter for 'adSpiff'.
     *
     * @return decimal(10,2)
     */
    public function getAdSpiff()
    {
        return $this->adSpiff;
    }

    /**
     * Chainable setter for 'adSpiff'.
     *
     * @param decimal(10,2) $adSpiff
     */
    public function setAdSpiff($adSpiff = 0.00)
    {
        $this->adSpiff = $adSpiff;
        return $this;
    }

    /**
     * Getter for 'empSpiff'.
     *
     * @return decimal(10,2)
     */
    public function getEmpSpiff()
    {
        return $this->empSpiff;
    }

    /**
     * Chainable setter for 'empSpiff'.
     *
     * @param decimal(10,2) $empSpiff
     */
    public function setEmpSpiff($empSpiff)
    {
        $this->empSpiff = $empSpiff;
        return $this;
    }

    /**
     * Getter for 'addedOn'.
     *
     * @return datetime|null
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }

    /**
     * Chainable setter for 'addedOn'.
     *
     * @param datetime|null $addedOn
     */
    public function setAddedOn($addedOn = null)
    {
        $this->addedOn = $addedOn;
        return $this;
    }
}
