<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveInternal;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_internal::machines'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-14
 */
class Machines extends AbstractEntity
{
    /**
     * @field machinesID
     * @var int(10) unsigned
     * @key primary
     */
    protected $machinesID;

    /**
     * @field machineID
     * @var varchar(64)
     * @key mul
     */
    protected $machineID;

    /**
     * @field locationsID
     * @var int(10) unsigned
     * @key mul
     */
    protected $locationsID;

    /**
     * @field dateCreated
     * @var date
     */
    protected $dateCreated;

    /**
     * @field dateRetired
     * @var date
     * @nullable
     * @default null
     */
    protected $dateRetired;

    /**
     * @field faultTypesID
     * @var int(10) unsigned
     * @key mul
     * @nullable
     * @default null
     */
    protected $faultTypesID;

    /**
     * @field notes
     * @var text
     * @nullable
     * @default null
     */
    protected $notes;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->dateRetired = null;
        $this->faultTypesID = null;
        $this->notes = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'machinesID'.
     *
     * @return int(10) unsigned
     */
    public function getMachinesID()
    {
        return $this->machinesID;
    }

    /**
     * Chainable setter for 'machinesID'.
     *
     * @param int(10) unsigned $machinesID
     */
    public function setMachinesID($machinesID)
    {
        $this->machinesID = $machinesID;

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
     * Getter for 'dateCreated'.
     *
     * @return date
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Chainable setter for 'dateCreated'.
     *
     * @param date $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Getter for 'dateRetired'.
     *
     * @return date|null
     */
    public function getDateRetired()
    {
        return $this->dateRetired;
    }

    /**
     * Chainable setter for 'dateRetired'.
     *
     * @param date|null $dateRetired
     */
    public function setDateRetired($dateRetired = null)
    {
        $this->dateRetired = $dateRetired;

        return $this;
    }

    /**
     * Getter for 'faultTypesID'.
     *
     * @return int(10) unsigned|null
     */
    public function getFaultTypesID()
    {
        return $this->faultTypesID;
    }

    /**
     * Chainable setter for 'faultTypesID'.
     *
     * @param int(10) unsigned|null $faultTypesID
     */
    public function setFaultTypesID($faultTypesID = null)
    {
        $this->faultTypesID = $faultTypesID;

        return $this;
    }

    /**
     * Getter for 'notes'.
     *
     * @return text|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Chainable setter for 'notes'.
     *
     * @param text|null $notes
     */
    public function setNotes($notes = null)
    {
        $this->notes = $notes;

        return $this;
    }
}
