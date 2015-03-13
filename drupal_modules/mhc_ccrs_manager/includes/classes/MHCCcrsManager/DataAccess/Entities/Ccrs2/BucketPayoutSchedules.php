<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::bucket_payout_schedules'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class BucketPayoutSchedules extends AbstractEntity
{
    /**
     * @field payoutScheduleID
     * @var int(11)
     * @key primary
     */
    protected $payoutScheduleID;

    /**
     * @field description
     * @var varchar(25)
     * @nullable
     * @default null
     */
    protected $description;


    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->description = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'payoutScheduleID'.
     *
     * @return int(11)
     */
    public function getPayoutScheduleID()
    {
        return $this->payoutScheduleID;
    }

    /**
     * Chainable setter for 'payoutScheduleID'.
     *
     * @param int(11) $payoutScheduleID
     */
    public function setPayoutScheduleID($payoutScheduleID)
    {
        $this->payoutScheduleID = $payoutScheduleID;
        return $this;
    }

    /**
     * Getter for 'description'.
     *
     * @return varchar(25)|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Chainable setter for 'description'.
     *
     * @param varchar(25)|null $description
     */
    public function setDescription($description = null)
    {
        $this->description = $description;
        return $this;
    }
}
