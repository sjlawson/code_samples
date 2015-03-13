<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveInternal;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_internal::configurations'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-30
 */
class Configurations extends AbstractEntity
{
    /**
     * @field configurationsID
     * @var int(10) unsigned
     * @key primary
     */
    protected $configurationsID;

    /**
     * @field name
     * @var varchar(36)
     */
    protected $name;

    /**
     * @field dateCreated
     * @var date
     */
    protected $dateCreated;

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
     * Getter for 'configurationsID'.
     *
     * @return int(10) unsigned
     */
    public function getConfigurationsID()
    {
        return $this->configurationsID;
    }

    /**
     * Chainable setter for 'configurationsID'.
     *
     * @param int(10) unsigned $configurationsID
     */
    public function setConfigurationsID($configurationsID)
    {
        $this->configurationsID = $configurationsID;

        return $this;
    }

    /**
     * Getter for 'name'.
     *
     * @return varchar(36)
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Chainable setter for 'name'.
     *
     * @param varchar(36) $name
     */
    public function setName($name)
    {
        $this->name = $name;

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
}
