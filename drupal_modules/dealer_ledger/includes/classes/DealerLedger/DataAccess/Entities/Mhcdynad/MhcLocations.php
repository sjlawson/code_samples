<?php

namespace DealerLedger\DataAccess\Entities\Mhcdynad;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'mhcdyna::mhc_locations'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class MhcLocations extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field accountID
     * @var int(11)
     * @key mul
     * @nullable
     * @default 999999
     */
    protected $accountID;

    /**
     * @field locationID
     * @var varchar(8)
     * @key mul
     * @nullable
     * @default null
     */
    protected $locationID;

    /**
     * @field regionID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $regionID;

    /**
     * @field name
     * @var varchar(50)
     * @nullable
     * @default null
     */
    protected $name;

    /**
     * @field address
     * @var mediumtext
     * @nullable
     * @default null
     */
    protected $address;

    /**
     * @field city
     * @var varchar(35)
     * @nullable
     * @default null
     */
    protected $city;

    /**
     * @field stateID
     * @var int(11)
     * @key mul
     * @nullable
     * @default 52
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
     * @field locator
     * @var varchar(255)
     * @nullable
     * @default ''
     */
    protected $locator;

    /**
     * @field county
     * @var varchar(30)
     * @nullable
     * @default null
     */
    protected $county;

    /**
     * @field township
     * @var varchar(30)
     * @nullable
     * @default null
     */
    protected $township;

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
     * @field lat
     * @var float
     * @nullable
     * @default 40.5598
     */
    protected $lat;

    /**
     * @field lng
     * @var float
     * @nullable
     * @default -85.6881
     */
    protected $lng;

    /**
     * @field locationTypeID
     * @var tinyint(4)
     * @nullable
     * @default null
     */
    protected $locationTypeID;

    /**
     * @field businessHours
     * @var varchar(50)
     * @nullable
     * @default ''
     */
    protected $businessHours;

    /**
     * @field openDate
     * @var date
     * @key mul
     * @nullable
     * @default null
     */
    protected $openDate;

    /**
     * @field closedDate
     * @var date
     * @key mul
     * @nullable
     * @default null
     */
    protected $closedDate;

    /**
     * @field subAgentTypeID
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $subAgentTypeID;

    /**
     * @field priceMarkup
     * @var decimal(5,2)
     * @nullable
     * @default null
     */
    protected $priceMarkup;

    /**
     * @field manPower
     * @var decimal(4,2) unsigned
     * @nullable
     * @default 3.50
     */
    protected $manPower;

    /**
     * @field colocationID
     * @var smallint(4) unsigned zerofill
     * @nullable
     * @default null
     */
    protected $colocationID;

    /**
     * @field verizonOrderCode
     * @var varchar(3)
     * @nullable
     * @default null
     */
    protected $verizonOrderCode;

    /**
     * @field verizonIconicLocationCode
     * @var varchar(7)
     * @key mul
     * @nullable
     * @default null
     */
    protected $verizonIconicLocationCode;

    /**
     * @field verizonRegionID
     * @var int(10) unsigned
     * @nullable
     * @default null
     */
    protected $verizonRegionID;

    /**
     * @field updatedOn
     * @var datetime
     * @nullable
     * @default 2012-01-01 00:00:00
     */
    protected $updatedOn;

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
        $this->accountID = 999999;
        $this->locationID = null;
        $this->regionID = null;
        $this->name = null;
        $this->address = null;
        $this->city = null;
        $this->stateID = 52;
        $this->zipCode = null;
        $this->locator = '';
        $this->county = null;
        $this->township = null;
        $this->phoneNumber = null;
        $this->faxNumber = null;
        $this->lat = 40.5598;
        $this->lng = -85.6881;
        $this->locationTypeID = null;
        $this->businessHours = '';
        $this->openDate = null;
        $this->closedDate = null;
        $this->subAgentTypeID = null;
        $this->priceMarkup = null;
        $this->manPower = 3.50;
        $this->colocationID = null;
        $this->verizonOrderCode = null;
        $this->verizonIconicLocationCode = null;
        $this->verizonRegionID = null;
        $this->updatedOn = 2012-01-01 00:00:00;
        $this->addedOn = null;

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
     * Getter for 'accountID'.
     *
     * @return int(11)|null
     */
    public function getAccountID()
    {
        return $this->accountID;
    }

    /**
     * Chainable setter for 'accountID'.
     *
     * @param int(11)|null $accountID
     */
    public function setAccountID($accountID = 999999)
    {
        $this->accountID = $accountID;

        return $this;
    }

    /**
     * Getter for 'locationID'.
     *
     * @return varchar(8)|null
     */
    public function getLocationID()
    {
        return $this->locationID;
    }

    /**
     * Chainable setter for 'locationID'.
     *
     * @param varchar(8)|null $locationID
     */
    public function setLocationID($locationID = null)
    {
        $this->locationID = $locationID;

        return $this;
    }

    /**
     * Getter for 'regionID'.
     *
     * @return int(11)|null
     */
    public function getRegionID()
    {
        return $this->regionID;
    }

    /**
     * Chainable setter for 'regionID'.
     *
     * @param int(11)|null $regionID
     */
    public function setRegionID($regionID = null)
    {
        $this->regionID = $regionID;

        return $this;
    }

    /**
     * Getter for 'name'.
     *
     * @return varchar(50)|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Chainable setter for 'name'.
     *
     * @param varchar(50)|null $name
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for 'address'.
     *
     * @return mediumtext|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Chainable setter for 'address'.
     *
     * @param mediumtext|null $address
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Getter for 'city'.
     *
     * @return varchar(35)|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Chainable setter for 'city'.
     *
     * @param varchar(35)|null $city
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
    public function setStateID($stateID = 52)
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
     * Getter for 'locator'.
     *
     * @return varchar(255)|null
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Chainable setter for 'locator'.
     *
     * @param varchar(255)|null $locator
     */
    public function setLocator($locator = '')
    {
        $this->locator = $locator;

        return $this;
    }

    /**
     * Getter for 'county'.
     *
     * @return varchar(30)|null
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Chainable setter for 'county'.
     *
     * @param varchar(30)|null $county
     */
    public function setCounty($county = null)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Getter for 'township'.
     *
     * @return varchar(30)|null
     */
    public function getTownship()
    {
        return $this->township;
    }

    /**
     * Chainable setter for 'township'.
     *
     * @param varchar(30)|null $township
     */
    public function setTownship($township = null)
    {
        $this->township = $township;

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
     * Getter for 'lat'.
     *
     * @return float|null
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Chainable setter for 'lat'.
     *
     * @param float|null $lat
     */
    public function setLat($lat = 40.5598)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Getter for 'lng'.
     *
     * @return float|null
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Chainable setter for 'lng'.
     *
     * @param float|null $lng
     */
    public function setLng($lng = -85.6881)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Getter for 'locationTypeID'.
     *
     * @return tinyint(4)|null
     */
    public function getLocationTypeID()
    {
        return $this->locationTypeID;
    }

    /**
     * Chainable setter for 'locationTypeID'.
     *
     * @param tinyint(4)|null $locationTypeID
     */
    public function setLocationTypeID($locationTypeID = null)
    {
        $this->locationTypeID = $locationTypeID;

        return $this;
    }

    /**
     * Getter for 'businessHours'.
     *
     * @return varchar(50)|null
     */
    public function getBusinessHours()
    {
        return $this->businessHours;
    }

    /**
     * Chainable setter for 'businessHours'.
     *
     * @param varchar(50)|null $businessHours
     */
    public function setBusinessHours($businessHours = '')
    {
        $this->businessHours = $businessHours;

        return $this;
    }

    /**
     * Getter for 'openDate'.
     *
     * @return date|null
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * Chainable setter for 'openDate'.
     *
     * @param date|null $openDate
     */
    public function setOpenDate($openDate = null)
    {
        $this->openDate = $openDate;

        return $this;
    }

    /**
     * Getter for 'closedDate'.
     *
     * @return date|null
     */
    public function getClosedDate()
    {
        return $this->closedDate;
    }

    /**
     * Chainable setter for 'closedDate'.
     *
     * @param date|null $closedDate
     */
    public function setClosedDate($closedDate = null)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Getter for 'subAgentTypeID'.
     *
     * @return int(11)|null
     */
    public function getSubAgentTypeID()
    {
        return $this->subAgentTypeID;
    }

    /**
     * Chainable setter for 'subAgentTypeID'.
     *
     * @param int(11)|null $subAgentTypeID
     */
    public function setSubAgentTypeID($subAgentTypeID = null)
    {
        $this->subAgentTypeID = $subAgentTypeID;

        return $this;
    }

    /**
     * Getter for 'priceMarkup'.
     *
     * @return decimal(5,2)|null
     */
    public function getPriceMarkup()
    {
        return $this->priceMarkup;
    }

    /**
     * Chainable setter for 'priceMarkup'.
     *
     * @param decimal(5,2)|null $priceMarkup
     */
    public function setPriceMarkup($priceMarkup = null)
    {
        $this->priceMarkup = $priceMarkup;

        return $this;
    }

    /**
     * Getter for 'manPower'.
     *
     * @return decimal(4,2) unsigned|null
     */
    public function getManPower()
    {
        return $this->manPower;
    }

    /**
     * Chainable setter for 'manPower'.
     *
     * @param decimal(4,2) unsigned|null $manPower
     */
    public function setManPower($manPower = 3.50)
    {
        $this->manPower = $manPower;

        return $this;
    }

    /**
     * Getter for 'colocationID'.
     *
     * @return smallint(4) unsigned zerofill|null
     */
    public function getColocationID()
    {
        return $this->colocationID;
    }

    /**
     * Chainable setter for 'colocationID'.
     *
     * @param smallint(4) unsigned zerofill|null $colocationID
     */
    public function setColocationID($colocationID = null)
    {
        $this->colocationID = $colocationID;

        return $this;
    }

    /**
     * Getter for 'verizonOrderCode'.
     *
     * @return varchar(3)|null
     */
    public function getVerizonOrderCode()
    {
        return $this->verizonOrderCode;
    }

    /**
     * Chainable setter for 'verizonOrderCode'.
     *
     * @param varchar(3)|null $verizonOrderCode
     */
    public function setVerizonOrderCode($verizonOrderCode = null)
    {
        $this->verizonOrderCode = $verizonOrderCode;

        return $this;
    }

    /**
     * Getter for 'verizonIconicLocationCode'.
     *
     * @return varchar(7)|null
     */
    public function getVerizonIconicLocationCode()
    {
        return $this->verizonIconicLocationCode;
    }

    /**
     * Chainable setter for 'verizonIconicLocationCode'.
     *
     * @param varchar(7)|null $verizonIconicLocationCode
     */
    public function setVerizonIconicLocationCode($verizonIconicLocationCode = null)
    {
        $this->verizonIconicLocationCode = $verizonIconicLocationCode;

        return $this;
    }

    /**
     * Getter for 'verizonRegionID'.
     *
     * @return int(10) unsigned|null
     */
    public function getVerizonRegionID()
    {
        return $this->verizonRegionID;
    }

    /**
     * Chainable setter for 'verizonRegionID'.
     *
     * @param int(10) unsigned|null $verizonRegionID
     */
    public function setVerizonRegionID($verizonRegionID = null)
    {
        $this->verizonRegionID = $verizonRegionID;

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
    public function setUpdatedOn($updatedOn = 2012-01-01 00:00:00)
    {
        $this->updatedOn = $updatedOn;

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
