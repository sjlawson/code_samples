<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::estimator_commission_schedules'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-27
 */
class EstimatorCommissionSchedules extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field schedule
     * @var varchar(30)
     * @nullable
     * @default null
     */
    protected $schedule;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->schedule = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'id'.
     *
     * @return int(11)
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Chainable setter for 'id'.
     *
     * @param int(11) $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Getter for 'schedule'.
     *
     * @return varchar(30)|null
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Chainable setter for 'schedule'.
     *
     * @param varchar(30)|null $schedule
     */
    public function setSchedule($schedule = null)
    {
        $this->schedule = $schedule;

        return $this;
    }
}
