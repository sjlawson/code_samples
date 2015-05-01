<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveApi;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_api::process_data_values'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-20
 */
class ProcessDataValues extends AbstractEntity
{
    /**
     * @field processDataValuesID
     * @var int(11)
     * @key primary
     */
    protected $processDataValuesID;

    /**
     * @field machineID
     * @var varchar(64)
     */
    protected $machineID;

    /**
     * @field processID
     * @var varchar(16)
     */
    protected $processID;

    /**
     * @field processBusinessKeysID
     * @var int(11)
     */
    protected $processBusinessKeysID;

    /**
     * @field processValue
     * @var varchar(64)
     */
    protected $processValue;

    /**
     * @field datetimeAdded
     * @var varchar(45)
     */
    protected $datetimeAdded;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'processDataValuesID'.
     *
     * @return int(11)
     */
    public function getProcessDataValuesID()
    {
        return $this->processDataValuesID;
    }

    /**
     * Chainable setter for 'processDataValuesID'.
     *
     * @param int(11) $processDataValuesID
     */
    public function setProcessDataValuesID($processDataValuesID)
    {
        $this->processDataValuesID = $processDataValuesID;

        return $this;
    }

    /**
     * Getter for 'machineID'.
     *
     * @return varchar(64)
     */
    public function getMachineID()
    {
        return $this->machineID;
    }

    /**
     * Chainable setter for 'machineID'.
     *
     * @param varchar(64) $machineID
     */
    public function setMachineID($machineID)
    {
        $this->machineID = $machineID;

        return $this;
    }

    /**
     * Getter for 'processID'.
     *
     * @return varchar(16)
     */
    public function getProcessID()
    {
        return $this->processID;
    }

    /**
     * Chainable setter for 'processID'.
     *
     * @param varchar(16) $processID
     */
    public function setProcessID($processID)
    {
        $this->processID = $processID;

        return $this;
    }

    /**
     * Getter for 'processBusinessKeysID'.
     *
     * @return int(11)
     */
    public function getProcessBusinessKeysID()
    {
        return $this->processBusinessKeysID;
    }

    /**
     * Chainable setter for 'processBusinessKeysID'.
     *
     * @param varchar(64) $processBusinessKeysID
     */
    public function setProcessBusinessKeysID($processBusinessKeysID)
    {
        $this->processBusinessKeysID = $processBusinessKeysID;

        return $this;
    }

    /**
     * Getter for 'processValue'.
     *
     * @return varchar(64)
     */
    public function getProcessValue()
    {
        return $this->processValue;
    }

    /**
     * Chainable setter for 'processValue'.
     *
     * @param varchar(64) $processValue
     */
    public function setProcessValue($processValue)
    {
        $this->processValue = $processValue;

        return $this;
    }

    /**
     * Getter for 'datetimeAdded'.
     *
     * @return varchar(45)
     */
    public function getDatetimeAdded()
    {
        return $this->datetimeAdded;
    }

    /**
     * Chainable setter for 'datetimeAdded'.
     *
     * @param varchar(45) $datetimeAdded
     */
    public function setDatetimeAdded($datetimeAdded)
    {
        $this->datetimeAdded = $datetimeAdded;

        return $this;
    }
}
