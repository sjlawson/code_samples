<?php

namespace DealerLedger\DataAccess\Entities\Ccrs2;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::estimator_contract_types'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class EstimatorContractTypes extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field type
     * @var varchar(40)
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
     * Getter for 'type'.
     *
     * @return varchar(40)|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Chainable setter for 'type'.
     *
     * @param varchar(40)|null $type
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }
}
