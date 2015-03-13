<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveApi;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_api::processes'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-07-18
 */
class Processes extends AbstractEntity
{
    /**
     * @field processesID
     * @var int(11)
     * @key primary
     */
    protected $processesID;

    /**
     * @field processID
     * @var char(10)
     * @key mul
     * @nullable
     * @default null
     */
    protected $processID;

    /**
     * @field machineID
     * @var char(36)
     * @key mul
     * @nullable
     * @default null
     */
    protected $machineID;

    /**
     * @field locationsID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $locationsID;

    /**
     * @field configurationsID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $configurationsID;

    /**
     * @field reviveSuccessful
     * @var tinyint(1)
     * @nullable
     * @default null
     */
    protected $reviveSuccessful;

    /**
     * @field processDatetime
     * @var datetime
     * @nullable
     * @default null
     */
    protected $processDatetime;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->processID = null;
        $this->machineID = null;
        $this->locationsID = null;
        $this->configurationsID = null;
        $this->reviveSuccessful = null;
        $this->processDatetime = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'processesID'.
     *
     * @return int(11)
     */
    public function getProcessesID()
    {
        return $this->processesID;
    }

    /**
     * Chainable setter for 'processesID'.
     *
     * @param int(11) $processesID
     */
    public function setProcessesID($processesID)
    {
        $this->processesID = $processesID;

        return $this;
    }

    /**
     * Getter for 'processID'.
     *
     * @return char(10)|null
     */
    public function getProcessID()
    {
        return $this->processID;
    }

    /**
     * Chainable setter for 'processID'.
     *
     * @param char(10)|null $processID
     */
    public function setProcessID($processID = null)
    {
        $this->processID = $processID;

        return $this;
    }

    /**
     * Getter for 'machineID'.
     *
     * @return char(36)|null
     */
    public function getMachineID()
    {
        return $this->machineID;
    }

    /**
     * Chainable setter for 'machineID'.
     *
     * @param char(36)|null $machineID
     */
    public function setMachineID($machineID = null)
    {
        $this->machineID = $machineID;

        return $this;
    }

    /**
     * Getter for 'locationsID'.
     *
     * @return int(11)|null
     */
    public function getLocationsID()
    {
        return $this->locationsID;
    }

    /**
     * Chainable setter for 'locationsID'.
     *
     * @param int(11)|null $locationsID
     */
    public function setLocationsID($locationsID = null)
    {
        $this->locationsID = $locationsID;

        return $this;
    }

    /**
     * Getter for 'configurationsID'.
     *
     * @return int(11)|null
     */
    public function getConfigurationsID()
    {
        return $this->configurationsID;
    }

    /**
     * Chainable setter for 'configurationsID'.
     *
     * @param int(11)|null $configurationsID
     */
    public function setConfigurationsID($configurationsID = null)
    {
        $this->configurationsID = $configurationsID;

        return $this;
    }

    /**
     * Getter for 'reviveSuccessful'.
     *
     * @return tinyint(1)|null
     */
    public function getReviveSuccessful()
    {
        return $this->reviveSuccessful;
    }

    /**
     * Chainable setter for 'reviveSuccessful'.
     *
     * @param tinyint(1)|null $reviveSuccessful
     */
    public function setReviveSuccessful($reviveSuccessful = null)
    {
        $this->reviveSuccessful = $reviveSuccessful;

        return $this;
    }

    /**
     * Getter for 'processDatetime'.
     *
     * @return datetime|null
     */
    public function getProcessDatetime()
    {
        return $this->processDatetime;
    }

    /**
     * Chainable setter for 'processDatetime'.
     *
     * @param datetime|null $processDatetime
     */
    public function setProcessDatetime($processDatetime = null)
    {
        $this->processDatetime = $processDatetime;

        return $this;
    }
}
