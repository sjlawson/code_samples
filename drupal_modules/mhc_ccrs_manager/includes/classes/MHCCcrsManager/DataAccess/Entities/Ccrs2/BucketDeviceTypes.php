<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::bucket_device_types'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-25
 */
class BucketDeviceTypes extends AbstractEntity
{
    /**
     * @field deviceTypeID
     * @var int(11)
     * @key primary
     */
    protected $deviceTypeID;

    /**
     * @field type
     * @var varchar(20)
     * @nullable
     * @default null
     */
    protected $type;

    /**
     * @field contractTypeID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $contractTypeID;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->type = null;
        $this->contractTypeID = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'deviceTypeID'.
     *
     * @return int(11)
     */
    public function getDeviceTypeID()
    {
        return $this->deviceTypeID;
    }

    /**
     * Chainable setter for 'deviceTypeID'.
     *
     * @param int(11) $deviceTypeID
     */
    public function setDeviceTypeID($deviceTypeID)
    {
        $this->deviceTypeID = $deviceTypeID;

        return $this;
    }

    /**
     * Getter for 'type'.
     *
     * @return varchar(20)|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Chainable setter for 'type'.
     *
     * @param varchar(20)|null $type
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Getter for 'contractTypeID'.
     *
     * @return int(11)|null
     */
    public function getContractTypeID()
    {
        return $this->contractTypeID;
    }

    /**
     * Chainable setter for 'contractTypeID'.
     *
     * @param int(11)|null $contractTypeID
     */
    public function setContractTypeID($contractTypeID = null)
    {
        $this->contractTypeID = $contractTypeID;

        return $this;
    }
}
