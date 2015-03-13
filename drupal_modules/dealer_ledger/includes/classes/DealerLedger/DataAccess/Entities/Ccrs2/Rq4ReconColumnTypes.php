<?php

namespace DealerLedger\DataAccess\Entities\Ccrs2;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::rq4_recon_column_types'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class Rq4ReconColumnTypes extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field type
     * @var varchar(50)
     * @nullable
     * @default null
     */
    protected $type;

    /**
     * @field tn
     * @var varchar(50)
     * @nullable
     * @default null
     */
    protected $tn;

    /**
     * @field sortOrder
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $sortOrder;

    /**
     * @field dynamicsGroupID
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $dynamicsGroupID;

    /**
     * @field commissionType
     * @var varchar(50)
     * @nullable
     * @default null
     */
    protected $commissionType;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->type = null;
        $this->tn = null;
        $this->sortOrder = null;
        $this->dynamicsGroupID = null;
        $this->commissionType = null;

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
     * @return varchar(50)|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Chainable setter for 'type'.
     *
     * @param varchar(50)|null $type
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Getter for 'tn'.
     *
     * @return varchar(50)|null
     */
    public function getTn()
    {
        return $this->tn;
    }

    /**
     * Chainable setter for 'tn'.
     *
     * @param varchar(50)|null $tn
     */
    public function setTn($tn = null)
    {
        $this->tn = $tn;

        return $this;
    }

    /**
     * Getter for 'sortOrder'.
     *
     * @return int(11)|null
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Chainable setter for 'sortOrder'.
     *
     * @param int(11)|null $sortOrder
     */
    public function setSortOrder($sortOrder = null)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Getter for 'dynamicsGroupID'.
     *
     * @return int(11)|null
     */
    public function getDynamicsGroupID()
    {
        return $this->dynamicsGroupID;
    }

    /**
     * Chainable setter for 'dynamicsGroupID'.
     *
     * @param int(11)|null $dynamicsGroupID
     */
    public function setDynamicsGroupID($dynamicsGroupID = null)
    {
        $this->dynamicsGroupID = $dynamicsGroupID;

        return $this;
    }

    /**
     * Getter for 'commissionType'.
     *
     * @return varchar(50)|null
     */
    public function getCommissionType()
    {
        return $this->commissionType;
    }

    /**
     * Chainable setter for 'commissionType'.
     *
     * @param varchar(50)|null $commissionType
     */
    public function setCommissionType($commissionType = null)
    {
        $this->commissionType = $commissionType;

        return $this;
    }
}
