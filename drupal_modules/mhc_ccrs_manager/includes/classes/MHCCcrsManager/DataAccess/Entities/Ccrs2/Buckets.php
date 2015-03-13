<?php

namespace MHCCcrsManager\DataAccess\Entities\Ccrs2;

use MHCCcrsManager\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::buckets'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class Buckets extends AbstractEntity
{
    /**
     * @field bucketID
     * @var int(11)
     * @key primary
     */
    protected $bucketID;

    /**
     * @field bucketCategoryID
     * @var varchar(25)
     * @key mul
     * @nullable
     * @default null
     */
    protected $bucketCategoryID;

    /**
     * @field contractTypeID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $contractTypeID;

    /**
     * @field actTypeID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $actTypeID;

    /**
     * @field term
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $term;

    /**
     * @field description
     * @var varchar(255)
     * @nullable
     * @default null
     */
    protected $description;

    /**
     * @field shortDescription
     * @var varchar(128)
     * @nullable
     * @default null
     */
    protected $shortDescription;

    /**
     * @field isNE2
     * @var tinyint(1)
     * @nullable
     * @default 0
     */
    protected $isNE2;

    /**
     * @field isEdge
     * @var tinyint(1)
     * @nullable
     * @default 0
     */
    protected $isEdge;

    /**
     * @field isM2M
     * @var tinyint(1)
     * @nullable
     * @default 0
     */
    protected $isM2M;


    /**
     * @field addedOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $addedOn;


    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->bucketCategoryID = null;
        $this->contractTypeID = null;
        $this->actTypeID = null;
        $this->term = null;
        $this->description = null;
        $this->shortDescription = null;
        $this->isNE2 = 0;
        $this->isEdge = 0;
        $this->isM2M = 0;
        $this->addedOn = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'bucketID'.
     *
     * @return int(11)
     */
    public function getBucketID()
    {
        return $this->bucketID;
    }

    /**
     * Chainable setter for 'bucketID'.
     *
     * @param int(11) $bucketID
     */
    public function setBucketID($bucketID)
    {
        $this->bucketID = $bucketID;
        return $this;
    }

    /**
     * Getter for 'bucketCategoryID'.
     *
     * @return varchar(25)|null
     */
    public function getBucketCategoryID()
    {
        return $this->bucketCategoryID;
    }

    /**
     * Chainable setter for 'bucketCategoryID'.
     *
     * @param varchar(25)|null $bucketCategoryID
     */
    public function setBucketCategoryID($bucketCategoryID = null)
    {
        $this->bucketCategoryID = $bucketCategoryID;
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

    /**
     * Getter for 'actTypeID'.
     *
     * @return int(11)|null
     */
    public function getActTypeID()
    {
        return $this->actTypeID;
    }

    /**
     * Chainable setter for 'actTypeID'.
     *
     * @param int(11)|null $actTypeID
     */
    public function setActTypeID($actTypeID = null)
    {
        $this->actTypeID = $actTypeID;
        return $this;
    }

    /**
     * Getter for 'term'.
     *
     * @return int(11)|null
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Chainable setter for 'term'.
     *
     * @param int(11)|null $term
     */
    public function setTerm($term = null)
    {
        $this->term = $term;
        return $this;
    }

    /**
     * Getter for 'description'.
     *
     * @return varchar(255)|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Chainable setter for 'description'.
     *
     * @param varchar(255)|null $description
     */
    public function setDescription($description = null)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Getter for 'shortDescription'.
     *
     * @return varchar(128)|null
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Chainable setter for 'shortDescription'.
     *
     * @param varchar(128)|null $shortDescription
     */
    public function setShortDescription($shortDescription = null)
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * Getter for 'isNE2'.
     *
     * @return tinyint(1)|null
     */
    public function getIsNE2()
    {
        return $this->isNE2;
    }

    /**
     * Chainable setter for 'isNE2'.
     *
     * @param tinyint(1)|null $isNE2
     */
    public function setIsNE2($isNE2 = 0)
    {
        $this->isNE2 = $isNE2;
        return $this;
    }

    /**
     * Getter for 'isEdge'.
     *
     * @return tinyint(1)|null
     */
    public function getIsEdge()
    {
        return $this->isEdge;
    }

    /**
     * Chainable setter for 'isEdge'.
     *
     * @param tinyint(1)|null $isEdge
     */
    public function setIsEdge($isEdge = 0)
    {
        $this->isEdge = $isEdge;
        return $this;
    }

    /**
     * Getter for 'isM2M'.
     *
     * @return tinyint(1)|null
     */
    public function getIsM2M()
    {
        return $this->isM2M;
    }

    /**
     * Chainable setter for 'isM2M'.
     *
     * @param tinyint(1)|null $isM2M
     */
    public function setIsM2M($isM2M = 0)
    {
        $this->isM2M = $isM2M;
        return $this;
    }

    /**
     * Getter for 'addedOn'.
     *
     * @return datetime|null
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }

    /**
     * Chainable setter for 'addedOn'.
     *
     * @param datetime|null $addedOn
     */
    public function setAddedOn($addedOn = null)
    {
        $this->addedOn = $addedOn;
        return $this;
    }
}
