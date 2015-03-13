<?php

namespace DealerLedger\DataAccess\Entities\Mhcdynad;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'mhcdyna::mhc_subagents'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-24
 */
class MhcSubagents extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field name
     * @var varchar(100)
     * @nullable
     * @default null
     */
    protected $name;

    /**
     * @field address
     * @var varchar(100)
     * @nullable
     * @default null
     */
    protected $address;

    /**
     * @field city
     * @var varchar(30)
     * @nullable
     * @default null
     */
    protected $city;

    /**
     * @field stateID
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $stateID;

    /**
     * @field zipCode
     * @var varchar(10)
     * @nullable
     * @default null
     */
    protected $zipCode;

    /**
     * @field POC
     * @var varchar(40)
     * @nullable
     * @default null
     */
    protected $POC;

    /**
     * @field phoneNumber
     * @var varchar(12)
     * @nullable
     * @default null
     */
    protected $phoneNumber;

    /**
     * @field faxNumber
     * @var varchar(12)
     * @nullable
     * @default null
     */
    protected $faxNumber;

    /**
     * @field email
     * @var varchar(256)
     * @nullable
     * @default null
     */
    protected $email;

    /**
     * @field addedOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $addedOn;

    /**
     * @field manager
     * @var varchar(32)
     * @nullable
     * @default null
     */
    protected $manager;

    /**
     * @field username
     * @var varchar(32)
     * @nullable
     * @default null
     */
    protected $username;

    /**
     * @field updatedOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $updatedOn;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->name = null;
        $this->address = null;
        $this->city = null;
        $this->stateID = null;
        $this->zipCode = null;
        $this->POC = null;
        $this->phoneNumber = null;
        $this->faxNumber = null;
        $this->email = null;
        $this->addedOn = null;
        $this->manager = null;
        $this->username = null;
        $this->updatedOn = null;

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
     * Getter for 'name'.
     *
     * @return varchar(100)|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Chainable setter for 'name'.
     *
     * @param varchar(100)|null $name
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for 'address'.
     *
     * @return varchar(100)|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Chainable setter for 'address'.
     *
     * @param varchar(100)|null $address
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Getter for 'city'.
     *
     * @return varchar(30)|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Chainable setter for 'city'.
     *
     * @param varchar(30)|null $city
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Getter for 'stateID'.
     *
     * @return int(11)|null
     */
    public function getStateID()
    {
        return $this->stateID;
    }

    /**
     * Chainable setter for 'stateID'.
     *
     * @param int(11)|null $stateID
     */
    public function setStateID($stateID = null)
    {
        $this->stateID = $stateID;

        return $this;
    }

    /**
     * Getter for 'zipCode'.
     *
     * @return varchar(10)|null
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Chainable setter for 'zipCode'.
     *
     * @param varchar(10)|null $zipCode
     */
    public function setZipCode($zipCode = null)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Getter for 'POC'.
     *
     * @return varchar(40)|null
     */
    public function getPOC()
    {
        return $this->POC;
    }

    /**
     * Chainable setter for 'POC'.
     *
     * @param varchar(40)|null $POC
     */
    public function setPOC($POC = null)
    {
        $this->POC = $POC;

        return $this;
    }

    /**
     * Getter for 'phoneNumber'.
     *
     * @return varchar(12)|null
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Chainable setter for 'phoneNumber'.
     *
     * @param varchar(12)|null $phoneNumber
     */
    public function setPhoneNumber($phoneNumber = null)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Getter for 'faxNumber'.
     *
     * @return varchar(12)|null
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * Chainable setter for 'faxNumber'.
     *
     * @param varchar(12)|null $faxNumber
     */
    public function setFaxNumber($faxNumber = null)
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * Getter for 'email'.
     *
     * @return varchar(256)|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Chainable setter for 'email'.
     *
     * @param varchar(256)|null $email
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

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

    /**
     * Getter for 'manager'.
     *
     * @return varchar(32)|null
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Chainable setter for 'manager'.
     *
     * @param varchar(32)|null $manager
     */
    public function setManager($manager = null)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Getter for 'username'.
     *
     * @return varchar(32)|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Chainable setter for 'username'.
     *
     * @param varchar(32)|null $username
     */
    public function setUsername($username = null)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Getter for 'updatedOn'.
     *
     * @return datetime|null
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * Chainable setter for 'updatedOn'.
     *
     * @param datetime|null $updatedOn
     */
    public function setUpdatedOn($updatedOn = null)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }
}
