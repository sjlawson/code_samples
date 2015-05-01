<?php

namespace ReviveUsageData\DataAccess\Entities\ReviveApi;

use ReviveUsageData\DataAccess\Entities\AbstractEntity;

/**
 * Entity class for 'revive_api::process_business_keys'.
 *
 * @author Ritesh Kumar Sahu <rsahu@mooreheadcomm.com>
 * @date 2015-02-04
 */
class ProcessBusinessKeys extends AbstractEntity
{
    /**
     * @field processBusinessKeysID
     * @var int(11)
     * @key primary
     */
    protected $processBusinessKeysID;

    /**
     * @field processName
     * @var varchar(64)
     */
    protected $processName;

    /**
     * Constructor
     */
    public function __construct(array $data = array())
    {
        // Set default values.

        // Load in data.
        $this->fromArray($data);
    }

    /**
     * Getter for 'processBusinessKeysID'.
     *
     * @return int(11)
     */
    public function getProcessBusinessKeysID()
    {
        return $this->processBusinessKeysID;
    }

    /**
     * Chainable setter for 'processBusinessKeysID'.
     *
     * @param int(11) #processBusinessKeysID
     */
    public function setProcessBusinessKeysID($processBusinessKeysID)
    {
        $this->processBusinessKeysID = $processBusinessKeysID;

        return $this;
    }

    /**
     * Getter for 'processName'.
     *
     * @return varchar(64)
     */
    public function getProcessName()
    {
        return $this->processName;
    }

    /**
     * Chainable setter for 'processName'.
     *
     * @param varchar(64) $processName
     */
    public function setProcessName($processName)
    {
        $this->processName = $processName;

        return $this;
    }
}
