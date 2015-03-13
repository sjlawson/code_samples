<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveInternal;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_internal::machines_history'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-07-17
 */
class MachinesHistory extends AbstractEntity
{
    /**
     * @field machinesHistoryID
     * @var int(10) unsigned
     * @key primary
     */
    protected $machinesHistoryID;

    /**
     * @field machinesID
     * @var int(10) unsigned
     * @key mul
     */
    protected $machinesID;

    /**
     * @field json
     * @var varchar(512)
     */
    protected $json;

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
     * @field dateTimeModified
     * @var datetime
     */
    protected $dateTimeModified;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->faultTypesID = null;
        $this->notes = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'machinesHistoryID'.
     *
     * @return int(10) unsigned
     */
    public function getMachinesHistoryID()
    {
        return $this->machinesHistoryID;
    }

    /**
     * Chainable setter for 'machinesHistoryID'.
     *
     * @param int(10) unsigned $machinesHistoryID
     */
    public function setMachinesHistoryID($machinesHistoryID)
    {
        $this->machinesHistoryID = $machinesHistoryID;

        return $this;
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
     * Getter for 'json'.
     *
     * @return varchar(512)
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Chainable setter for 'json'.
     *
     * @param varchar(512) $json
     */
    public function setJson($json)
    {
        $this->json = $json;

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

    /**
     * Getter for 'dateTimeModified'.
     *
     * @return datetime
     */
    public function getDateTimeModified()
    {
        return $this->dateTimeModified;
    }

    /**
     * Chainable setter for 'dateTimeModified'.
     *
     * @param datetime $dateTimeModified
     */
    public function setDateTimeModified($dateTimeModified)
    {
        $this->dateTimeModified = $dateTimeModified;

        return $this;
    }
}
