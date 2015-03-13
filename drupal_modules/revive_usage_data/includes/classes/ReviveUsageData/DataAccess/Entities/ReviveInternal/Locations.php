<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveInternal;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_internal::locations'.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-14
 */
class Locations extends AbstractEntity
{
    /**
     * @field locationsID
     * @var int(10) unsigned
     * @key primary
     */
    protected $locationsID;

    /**
     * @field accountsID
     * @var int(10) unsigned
     * @key mul
     */
    protected $accountsID;

    /**
     * @field name
     * @var varchar(64)
     */
    protected $name;

    /**
     * @field address
     * @var varchar(128)
     */
    protected $address;

    /**
     * @field city
     * @var varchar(64)
     */
    protected $city;

    /**
     * @field state
     * @var char(2)
     */
    protected $state;

    /**
     * @field postal
     * @var varchar(16)
     */
    protected $postal;

    /**
     * @field email
     * @var varchar(128)
     */
    protected $email;

    /**
     * @field phone
     * @var varchar(16)
     */
    protected $phone;

    /**
     * @field fax
     * @var varchar(16)
     * @nullable
     * @default null
     */
    protected $fax;

    /**
     * @field website
     * @var varchar(256)
     * @nullable
     * @default null
     */
    protected $website;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->fax = null;
        $this->website = null;

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'locationsID'.
     *
     * @return int(10) unsigned
     */
    public function getLocationsID()
    {
        return $this->locationsID;
    }

    /**
     * Chainable setter for 'locationsID'.
     *
     * @param int(10) unsigned $locationsID
     */
    public function setLocationsID($locationsID)
    {
        $this->locationsID = $locationsID;

        return $this;
    }

    /**
     * Getter for 'accountsID'.
     *
     * @return int(10) unsigned
     */
    public function getAccountsID()
    {
        return $this->accountsID;
    }

    /**
     * Chainable setter for 'accountsID'.
     *
     * @param int(10) unsigned $accountsID
     */
    public function setAccountsID($accountsID)
    {
        $this->accountsID = $accountsID;

        return $this;
    }

    /**
     * Getter for 'name'.
     *
     * @return varchar(64)
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Chainable setter for 'name'.
     *
     * @param varchar(64) $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for 'address'.
     *
     * @return varchar(128)
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Chainable setter for 'address'.
     *
     * @param varchar(128) $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Getter for 'city'.
     *
     * @return varchar(64)
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Chainable setter for 'city'.
     *
     * @param varchar(64) $city
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Getter for 'state'.
     *
     * @return char(2)
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Chainable setter for 'state'.
     *
     * @param char(2) $state
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Getter for 'postal'.
     *
     * @return varchar(16)
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * Chainable setter for 'postal'.
     *
     * @param varchar(16) $postal
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;

        return $this;
    }

    /**
     * Getter for 'email'.
     *
     * @return varchar(128)
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Chainable setter for 'email'.
     *
     * @param varchar(128) $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Getter for 'phone'.
     *
     * @return varchar(16)
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Chainable setter for 'phone'.
     *
     * @param varchar(16) $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Getter for 'fax'.
     *
     * @return varchar(16)|null
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Chainable setter for 'fax'.
     *
     * @param varchar(16)|null $fax
     */
    public function setFax($fax = null)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Getter for 'website'.
     *
     * @return varchar(256)|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Chainable setter for 'website'.
     *
     * @param varchar(256)|null $website
     */
    public function setWebsite($website = null)
    {
        $this->website = $website;

        return $this;
    }
}
