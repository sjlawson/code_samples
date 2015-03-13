<?php

namespace MHCCcrsManager\Presenters;

use PDO;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;

/**
 * Abstract base presenter class for common variables and helper functions.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
abstract class AbstractPresenter
{
    /** @var DataAccessDependencyContainer */
    protected $dataAccessContainer;

    /** @var boolean */
    protected $devMode;

    /** @var array */
    protected $getParameters;

    /**
     * Constructor.
     */
    public function __construct($devMode,
                                DataAccessDependencyContainer $dataAccessContainer,
                                array $getParameters)
    {
        $this->dataAccessContainer = $dataAccessContainer;
        $this->devMode = (boolean) $devMode;
        $this->getParameters = $getParameters;
    }

    /**
     * Getter for devMode.
     *
     * @return boolean True if in devMode.
     */
    public function getDevMode()
    {
        return $this->devMode;
    }

    /**
     * Getter for get parameters.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function getGetParameter($id)
    {
        if (!array_key_exists($id, $this->getParameters)) {
            return null;
        }

        return $this->getParameters[$id];
    }
}
