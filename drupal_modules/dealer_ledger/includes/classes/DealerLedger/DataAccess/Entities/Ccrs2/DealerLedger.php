<?php

namespace DealerLedger\DataAccess\Entities\Ccrs2;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'ccrs2::dealer_ledger'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class DealerLedger extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field associationID
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $associationID;

    /**
     * @field accountID
     * @var mediumint(9)
     * @key mul
     * @nullable
     * @default null
     */
    protected $accountID;

    /**
     * @field instanceID
     * @var mediumint(9)
     * @key mul
     * @nullable
     * @default null
     */
    protected $instanceID;

    /**
     * @field locationID
     * @var varchar(4)
     * @key mul
     * @nullable
     * @default null
     */
    protected $locationID;

    /**
     * @field sfid
     * @var varchar(25)
     * @key mul
     * @nullable
     * @default null
     */
    protected $sfid;

    /**
     * @field monthYear
     * @var date
     * @key mul
     * @nullable
     * @default null
     */
    protected $monthYear;

    /**
     * @field accountNumber
     * @var varchar(25)
     * @nullable
     * @default null
     */
    protected $accountNumber;

    /**
     * @field customerName
     * @var varchar(35)
     * @nullable
     * @default null
     */
    protected $customerName;

    /**
     * @field originalPhone
     * @var varchar(10)
     * @nullable
     * @default null
     */
    protected $originalPhone;

    /**
     * @field phone
     * @var varchar(10)
     * @key mul
     * @nullable
     * @default null
     */
    protected $phone;

    /**
     * @field deviceCategory
     * @var varchar(25)
     * @nullable
     * @default null
     */
    protected $deviceCategory;

    /**
     * @field deviceID
     * @var varchar(6)
     * @nullable
     * @default null
     */
    protected $deviceID;

    /**
     * @field bucketID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $bucketID;

    /**
     * @field columnTypeID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $columnTypeID;

    /**
     * @field contractDate
     * @var date
     * @nullable
     * @default null
     */
    protected $contractDate;

    /**
     * @field deactDate
     * @var date
     * @nullable
     * @default null
     */
    protected $deactDate;

    /**
     * @field daysOfService
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $daysOfService;

    /**
     * @field visionCode
     * @var varchar(15)
     * @nullable
     * @default null
     */
    protected $visionCode;

    /**
     * @field contractLength
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $contractLength;

    /**
     * @field pricePlan
     * @var decimal(10,2)
     * @nullable
     * @default null
     */
    protected $pricePlan;

    /**
     * @field upgradeType
     * @var varchar(5)
     * @nullable
     * @default null
     */
    protected $upgradeType;

    /**
     * @field description
     * @var varchar(32)
     * @nullable
     * @default null
     */
    protected $description;

    /**
     * @field estimatorID
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $estimatorID;

    /**
     * @field contractTypeID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $contractTypeID;

    /**
     * @field isPDA
     * @var tinyint(1)
     * @default 0
     */
    protected $isPDA;

    /**
     * @field isMBB
     * @var tinyint(1)
     * @default 0
     */
    protected $isMBB;

    /**
     * @field receivable
     * @var decimal(10,2)
     * @default 0.00
     */
    protected $receivable;

    /**
     * @field payable
     * @var decimal(10,2)
     * @default 0.00
     */
    protected $payable;

    /**
     * @field discrepancyID
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $discrepancyID;

    /**
     * @field discrepancyStatusID
     * @var int(11)
     * @nullable
     * @default 1
     */
    protected $discrepancyStatusID;

    /**
     * @field addedOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $addedOn;

    /**
     * @field isLocked
     * @var tinyint(1)
     * @default 0
     */
    protected $isLocked;

    /**
     * @field paidOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $paidOn;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.
        $this->associationID = null;
        $this->accountID = null;
        $this->instanceID = null;
        $this->locationID = null;
        $this->sfid = null;
        $this->monthYear = null;
        $this->accountNumber = null;
        $this->customerName = null;
        $this->originalPhone = null;
        $this->phone = null;
        $this->deviceCategory = null;
        $this->deviceID = null;
        $this->bucketID = null;
        $this->columnTypeID = null;
        $this->contractDate = null;
        $this->deactDate = null;
        $this->daysOfService = null;
        $this->visionCode = null;
        $this->contractLength = null;
        $this->pricePlan = null;
        $this->upgradeType = null;
        $this->description = null;
        $this->estimatorID = null;
        $this->contractTypeID = null;
        $this->isPDA = 0;
        $this->isMBB = 0;
        $this->receivable = 0.00;
        $this->payable = 0.00;
        $this->discrepancyID = null;
        $this->discrepancyStatusID = 1;
        $this->addedOn = null;
        $this->isLocked = 0;
        $this->paidOn = null;

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
     * Getter for 'associationID'.
     *
     * @return int(11)|null
     */
    public function getAssociationID()
    {
        return $this->associationID;
    }

    /**
     * Chainable setter for 'associationID'.
     *
     * @param int(11)|null $associationID
     */
    public function setAssociationID($associationID = null)
    {
        $this->associationID = $associationID;

        return $this;
    }

    /**
     * Getter for 'accountID'.
     *
     * @return mediumint(9)|null
     */
    public function getAccountID()
    {
        return $this->accountID;
    }

    /**
     * Chainable setter for 'accountID'.
     *
     * @param mediumint(9)|null $accountID
     */
    public function setAccountID($accountID = null)
    {
        $this->accountID = $accountID;

        return $this;
    }

    /**
     * Getter for 'instanceID'.
     *
     * @return mediumint(9)|null
     */
    public function getInstanceID()
    {
        return $this->instanceID;
    }

    /**
     * Chainable setter for 'instanceID'.
     *
     * @param mediumint(9)|null $instanceID
     */
    public function setInstanceID($instanceID = null)
    {
        $this->instanceID = $instanceID;

        return $this;
    }

    /**
     * Getter for 'locationID'.
     *
     * @return varchar(4)|null
     */
    public function getLocationID()
    {
        return $this->locationID;
    }

    /**
     * Chainable setter for 'locationID'.
     *
     * @param varchar(4)|null $locationID
     */
    public function setLocationID($locationID = null)
    {
        $this->locationID = $locationID;

        return $this;
    }

    /**
     * Getter for 'sfid'.
     *
     * @return varchar(25)|null
     */
    public function getSfid()
    {
        return $this->sfid;
    }

    /**
     * Chainable setter for 'sfid'.
     *
     * @param varchar(25)|null $sfid
     */
    public function setSfid($sfid = null)
    {
        $this->sfid = $sfid;

        return $this;
    }

    /**
     * Getter for 'monthYear'.
     *
     * @return date|null
     */
    public function getMonthYear()
    {
        return $this->monthYear;
    }

    /**
     * Chainable setter for 'monthYear'.
     *
     * @param date|null $monthYear
     */
    public function setMonthYear($monthYear = null)
    {
        $this->monthYear = $monthYear;

        return $this;
    }

    /**
     * Getter for 'accountNumber'.
     *
     * @return varchar(25)|null
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Chainable setter for 'accountNumber'.
     *
     * @param varchar(25)|null $accountNumber
     */
    public function setAccountNumber($accountNumber = null)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Getter for 'customerName'.
     *
     * @return varchar(35)|null
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * Chainable setter for 'customerName'.
     *
     * @param varchar(35)|null $customerName
     */
    public function setCustomerName($customerName = null)
    {
        $this->customerName = $customerName;

        return $this;
    }

    /**
     * Getter for 'originalPhone'.
     *
     * @return varchar(10)|null
     */
    public function getOriginalPhone()
    {
        return $this->originalPhone;
    }

    /**
     * Chainable setter for 'originalPhone'.
     *
     * @param varchar(10)|null $originalPhone
     */
    public function setOriginalPhone($originalPhone = null)
    {
        $this->originalPhone = $originalPhone;

        return $this;
    }

    /**
     * Getter for 'phone'.
     *
     * @return varchar(10)|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Chainable setter for 'phone'.
     *
     * @param varchar(10)|null $phone
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Getter for 'deviceCategory'.
     *
     * @return varchar(25)|null
     */
    public function getDeviceCategory()
    {
        return $this->deviceCategory;
    }

    /**
     * Chainable setter for 'deviceCategory'.
     *
     * @param varchar(25)|null $deviceCategory
     */
    public function setDeviceCategory($deviceCategory = null)
    {
        $this->deviceCategory = $deviceCategory;

        return $this;
    }

    /**
     * Getter for 'deviceID'.
     *
     * @return varchar(6)|null
     */
    public function getDeviceID()
    {
        return $this->deviceID;
    }

    /**
     * Chainable setter for 'deviceID'.
     *
     * @param varchar(6)|null $deviceID
     */
    public function setDeviceID($deviceID = null)
    {
        $this->deviceID = $deviceID;

        return $this;
    }

    /**
     * Getter for 'bucketID'.
     *
     * @return int(11)|null
     */
    public function getBucketID()
    {
        return $this->bucketID;
    }

    /**
     * Chainable setter for 'bucketID'.
     *
     * @param int(11)|null $bucketID
     */
    public function setBucketID($bucketID = null)
    {
        $this->bucketID = $bucketID;

        return $this;
    }

    /**
     * Getter for 'columnTypeID'.
     *
     * @return int(11)|null
     */
    public function getColumnTypeID()
    {
        return $this->columnTypeID;
    }

    /**
     * Chainable setter for 'columnTypeID'.
     *
     * @param int(11)|null $columnTypeID
     */
    public function setColumnTypeID($columnTypeID = null)
    {
        $this->columnTypeID = $columnTypeID;

        return $this;
    }

    /**
     * Getter for 'contractDate'.
     *
     * @return date|null
     */
    public function getContractDate()
    {
        return $this->contractDate;
    }

    /**
     * Chainable setter for 'contractDate'.
     *
     * @param date|null $contractDate
     */
    public function setContractDate($contractDate = null)
    {
        $this->contractDate = $contractDate;

        return $this;
    }

    /**
     * Getter for 'deactDate'.
     *
     * @return date|null
     */
    public function getDeactDate()
    {
        return $this->deactDate;
    }

    /**
     * Chainable setter for 'deactDate'.
     *
     * @param date|null $deactDate
     */
    public function setDeactDate($deactDate = null)
    {
        $this->deactDate = $deactDate;

        return $this;
    }

    /**
     * Getter for 'daysOfService'.
     *
     * @return int(11)|null
     */
    public function getDaysOfService()
    {
        return $this->daysOfService;
    }

    /**
     * Chainable setter for 'daysOfService'.
     *
     * @param int(11)|null $daysOfService
     */
    public function setDaysOfService($daysOfService = null)
    {
        $this->daysOfService = $daysOfService;

        return $this;
    }

    /**
     * Getter for 'visionCode'.
     *
     * @return varchar(15)|null
     */
    public function getVisionCode()
    {
        return $this->visionCode;
    }

    /**
     * Chainable setter for 'visionCode'.
     *
     * @param varchar(15)|null $visionCode
     */
    public function setVisionCode($visionCode = null)
    {
        $this->visionCode = $visionCode;

        return $this;
    }

    /**
     * Getter for 'contractLength'.
     *
     * @return int(11)|null
     */
    public function getContractLength()
    {
        return $this->contractLength;
    }

    /**
     * Chainable setter for 'contractLength'.
     *
     * @param int(11)|null $contractLength
     */
    public function setContractLength($contractLength = null)
    {
        $this->contractLength = $contractLength;

        return $this;
    }

    /**
     * Getter for 'pricePlan'.
     *
     * @return decimal(10,2)|null
     */
    public function getPricePlan()
    {
        return $this->pricePlan;
    }

    /**
     * Chainable setter for 'pricePlan'.
     *
     * @param decimal(10,2)|null $pricePlan
     */
    public function setPricePlan($pricePlan = null)
    {
        $this->pricePlan = $pricePlan;

        return $this;
    }

    /**
     * Getter for 'upgradeType'.
     *
     * @return varchar(5)|null
     */
    public function getUpgradeType()
    {
        return $this->upgradeType;
    }

    /**
     * Chainable setter for 'upgradeType'.
     *
     * @param varchar(5)|null $upgradeType
     */
    public function setUpgradeType($upgradeType = null)
    {
        $this->upgradeType = $upgradeType;

        return $this;
    }

    /**
     * Getter for 'description'.
     *
     * @return varchar(32)|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Chainable setter for 'description'.
     *
     * @param varchar(32)|null $description
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Getter for 'estimatorID'.
     *
     * @return int(11)|null
     */
    public function getEstimatorID()
    {
        return $this->estimatorID;
    }

    /**
     * Chainable setter for 'estimatorID'.
     *
     * @param int(11)|null $estimatorID
     */
    public function setEstimatorID($estimatorID = null)
    {
        $this->estimatorID = $estimatorID;

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
     * Getter for 'isPDA'.
     *
     * @return tinyint(1)
     */
    public function getIsPDA()
    {
        return $this->isPDA;
    }

    /**
     * Chainable setter for 'isPDA'.
     *
     * @param tinyint(1) $isPDA
     */
    public function setIsPDA($isPDA = 0)
    {
        $this->isPDA = $isPDA;

        return $this;
    }

    /**
     * Getter for 'isMBB'.
     *
     * @return tinyint(1)
     */
    public function getIsMBB()
    {
        return $this->isMBB;
    }

    /**
     * Chainable setter for 'isMBB'.
     *
     * @param tinyint(1) $isMBB
     */
    public function setIsMBB($isMBB = 0)
    {
        $this->isMBB = $isMBB;

        return $this;
    }

    /**
     * Getter for 'receivable'.
     *
     * @return decimal(10,2)
     */
    public function getReceivable()
    {
        return $this->receivable;
    }

    /**
     * Chainable setter for 'receivable'.
     *
     * @param decimal(10,2) $receivable
     */
    public function setReceivable($receivable = 0.00)
    {
        $this->receivable = $receivable;

        return $this;
    }

    /**
     * Getter for 'payable'.
     *
     * @return decimal(10,2)
     */
    public function getPayable()
    {
        return $this->payable;
    }

    /**
     * Chainable setter for 'payable'.
     *
     * @param decimal(10,2) $payable
     */
    public function setPayable($payable = 0.00)
    {
        $this->payable = $payable;

        return $this;
    }

    /**
     * Getter for 'discrepancyID'.
     *
     * @return int(11)|null
     */
    public function getDiscrepancyID()
    {
        return $this->discrepancyID;
    }

    /**
     * Chainable setter for 'discrepancyID'.
     *
     * @param int(11)|null $discrepancyID
     */
    public function setDiscrepancyID($discrepancyID = null)
    {
        $this->discrepancyID = $discrepancyID;

        return $this;
    }

    /**
     * Getter for 'discrepancyStatusID'.
     *
     * @return int(11)|null
     */
    public function getDiscrepancyStatusID()
    {
        return $this->discrepancyStatusID;
    }

    /**
     * Chainable setter for 'discrepancyStatusID'.
     *
     * @param int(11)|null $discrepancyStatusID
     */
    public function setDiscrepancyStatusID($discrepancyStatusID = 1)
    {
        $this->discrepancyStatusID = $discrepancyStatusID;

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
     * Getter for 'isLocked'.
     *
     * @return tinyint(1)
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Chainable setter for 'isLocked'.
     *
     * @param tinyint(1) $isLocked
     */
    public function setIsLocked($isLocked = 0)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Getter for 'paidOn'.
     *
     * @return datetime|null
     */
    public function getPaidOn()
    {
        return $this->paidOn;
    }

    /**
     * Chainable setter for 'paidOn'.
     *
     * @param datetime|null $paidOn
     */
    public function setPaidOn($paidOn = null)
    {
        $this->paidOn = $paidOn;

        return $this;
    }
}
