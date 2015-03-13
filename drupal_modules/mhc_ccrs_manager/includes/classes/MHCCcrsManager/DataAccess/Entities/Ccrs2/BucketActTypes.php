<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::bucket_act_types'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-25
 */
class BucketActTypes extends AbstractEntity
{
    /**
     * @field actTypeID
     * @var int(11)
     * @key primary
     */
    protected $actTypeID;

    /**
     * @field type
     * @var varchar(32)
     * @nullable
     * @default null
     */
    protected $type;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->type = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'actTypeID'.
     *
     * @return int(11)
     */
    public function getActTypeID()
    {
        return $this->actTypeID;
    }

    /**
     * Chainable setter for 'actTypeID'.
     *
     * @param int(11) $actTypeID
     */
    public function setActTypeID($actTypeID)
    {
        $this->actTypeID = $actTypeID;

        return $this;
    }

    /**
     * Getter for 'type'.
     *
     * @return varchar(32)|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Chainable setter for 'type'.
     *
     * @param varchar(32)|null $type
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }
}
