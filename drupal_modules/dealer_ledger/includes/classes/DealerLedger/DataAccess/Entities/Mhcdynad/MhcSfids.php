<?php

namespace DealerLedger\DataAccess\Entities\Mhcdynad;

use DealerLedger\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'mhcdyna::mhc_sfids'.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
class MhcSfids extends AbstractEntity
{
    /**
     * @field id
     * @var int(11)
     * @key primary
     */
    protected $id;

    /**
     * @field sfid
     * @var varchar(15)
     * @key mul
     * @nullable
     * @default null
     */
    protected $sfid;

    /**
     * @field instanceID
     * @var int(11)
     * @key mul
     * @nullable
     * @default null
     */
    protected $instanceID;

    /**
     * @field fromDate
     * @var date
     * @key mul
     * @nullable
     * @default null
     */
    protected $fromDate;

    /**
     * @field toDate
     * @var date
     * @key mul
     * @nullable
     * @default null
     */
    protected $toDate;

    /**
     * @field residualStartDate
     * @var date
     * @nullable
     * @default null
     */
    protected $residualStartDate;

    /**
     * @field residualBackPayment
     * @var tinyint(1)
     * @nullable
     * @default null
     */
    protected $residualBackPayment;

    /**
     * @field residualPaymentTerm
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $residualPaymentTerm;

    /**
     * @field residualMinActs
     * @var int(11)
     * @nullable
     * @default null
     */
    protected $residualMinActs;

    /**
     * @field residualPlanID
     * @var int(11)
     * @nullable
     * @default 1
     */
    protected $residualPlanID;

    /**
     * @field verizonPOSID
     * @var varchar(10)
     * @nullable
     * @default null
     */
    protected $verizonPOSID;

    /**
     * @field addedOn
     * @var datetime
     * @nullable
     * @default null
     */
    protected $addedOn;

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
        $this->sfid = null;
        $this->instanceID = null;
        $this->fromDate = null;
        $this->toDate = null;
        $this->residualStartDate = null;
        $this->residualBackPayment = null;
        $this->residualPaymentTerm = null;
        $this->residualMinActs = null;
        $this->residualPlanID = 1;
        $this->verizonPOSID = null;
        $this->addedOn = null;
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
     * Getter for 'sfid'.
     *
     * @return varchar(15)|null
     */
    public function getSfid()
    {
        return $this->sfid;
    }

    /**
     * Chainable setter for 'sfid'.
     *
     * @param varchar(15)|null $sfid
     */
    public function setSfid($sfid = null)
    {
        $this->sfid = $sfid;

        return $this;
    }

    /**
     * Getter for 'instanceID'.
     *
     * @return int(11)|null
     */
    public function getInstanceID()
    {
        return $this->instanceID;
    }

    /**
     * Chainable setter for 'instanceID'.
     *
     * @param int(11)|null $instanceID
     */
    public function setInstanceID($instanceID = null)
    {
        $this->instanceID = $instanceID;

        return $this;
    }

    /**
     * Getter for 'fromDate'.
     *
     * @return date|null
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * Chainable setter for 'fromDate'.
     *
     * @param date|null $fromDate
     */
    public function setFromDate($fromDate = null)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Getter for 'toDate'.
     *
     * @return date|null
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * Chainable setter for 'toDate'.
     *
     * @param date|null $toDate
     */
    public function setToDate($toDate = null)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Getter for 'residualStartDate'.
     *
     * @return date|null
     */
    public function getResidualStartDate()
    {
        return $this->residualStartDate;
    }

    /**
     * Chainable setter for 'residualStartDate'.
     *
     * @param date|null $residualStartDate
     */
    public function setResidualStartDate($residualStartDate = null)
    {
        $this->residualStartDate = $residualStartDate;

        return $this;
    }

    /**
     * Getter for 'residualBackPayment'.
     *
     * @return tinyint(1)|null
     */
    public function getResidualBackPayment()
    {
        return $this->residualBackPayment;
    }

    /**
     * Chainable setter for 'residualBackPayment'.
     *
     * @param tinyint(1)|null $residualBackPayment
     */
    public function setResidualBackPayment($residualBackPayment = null)
    {
        $this->residualBackPayment = $residualBackPayment;

        return $this;
    }

    /**
     * Getter for 'residualPaymentTerm'.
     *
     * @return int(11)|null
     */
    public function getResidualPaymentTerm()
    {
        return $this->residualPaymentTerm;
    }

    /**
     * Chainable setter for 'residualPaymentTerm'.
     *
     * @param int(11)|null $residualPaymentTerm
     */
    public function setResidualPaymentTerm($residualPaymentTerm = null)
    {
        $this->residualPaymentTerm = $residualPaymentTerm;

        return $this;
    }

    /**
     * Getter for 'residualMinActs'.
     *
     * @return int(11)|null
     */
    public function getResidualMinActs()
    {
        return $this->residualMinActs;
    }

    /**
     * Chainable setter for 'residualMinActs'.
     *
     * @param int(11)|null $residualMinActs
     */
    public function setResidualMinActs($residualMinActs = null)
    {
        $this->residualMinActs = $residualMinActs;

        return $this;
    }

    /**
     * Getter for 'residualPlanID'.
     *
     * @return int(11)|null
     */
    public function getResidualPlanID()
    {
        return $this->residualPlanID;
    }

    /**
     * Chainable setter for 'residualPlanID'.
     *
     * @param int(11)|null $residualPlanID
     */
    public function setResidualPlanID($residualPlanID = 1)
    {
        $this->residualPlanID = $residualPlanID;

        return $this;
    }

    /**
     * Getter for 'verizonPOSID'.
     *
     * @return varchar(10)|null
     */
    public function getVerizonPOSID()
    {
        return $this->verizonPOSID;
    }

    /**
     * Chainable setter for 'verizonPOSID'.
     *
     * @param varchar(10)|null $verizonPOSID
     */
    public function setVerizonPOSID($verizonPOSID = null)
    {
        $this->verizonPOSID = $verizonPOSID;

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
