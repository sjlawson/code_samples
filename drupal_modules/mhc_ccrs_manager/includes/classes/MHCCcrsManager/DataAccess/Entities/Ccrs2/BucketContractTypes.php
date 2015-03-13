<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::bucket_contract_types'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-25
 */
class BucketContractTypes extends AbstractEntity
{
    /**
     * @field contractTypeID
     * @var int(11)
     * @key primary
     */
    protected $contractTypeID;

    /**
     * @field description
     * @var varchar(40)
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
     * Getter for 'contractTypeID'.
     *
     * @return int(11)
     */
    public function getContractTypeID()
    {
        return $this->contractTypeID;
    }

    /**
     * Chainable setter for 'contractTypeID'.
     *
     * @param int(11) $contractTypeID
     */
    public function setContractTypeID($contractTypeID)
    {
        $this->contractTypeID = $contractTypeID;

        return $this;
    }

    /**
     * Getter for 'description'.
     *
     * @return varchar(40)|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Chainable setter for 'description'.
     *
     * @param varchar(40)|null $description
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }
}
