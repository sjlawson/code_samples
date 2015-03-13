<?php

namespace DealerLedger\DataAccess\Entities\Ccrs2;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::bucket_categories'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class BucketCategories extends AbstractEntity
{
    /**
     * @field bucketCategoryID
     * @var varchar(25)
     * @key primary
     */
    protected $bucketCategoryID;

    /**
     * @field description
     * @var varchar(255)
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
     * Getter for 'bucketCategoryID'.
     *
     * @return varchar(25)
     */
    public function getBucketCategoryID()
    {
        return $this->bucketCategoryID;
    }

    /**
     * Chainable setter for 'bucketCategoryID'.
     *
     * @param varchar(25) $bucketCategoryID
     */
    public function setBucketCategoryID($bucketCategoryID)
    {
        $this->bucketCategoryID = $bucketCategoryID;

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
}
