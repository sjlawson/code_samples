<?php

namespace IconicsInvoicing\Environments;

use IconicsInvoicing\DependencyInjection\Environments\AbstractEnvironment;

class EnvironmentAware implements EnvironmentAwareInterface
{
    /** @var AbstractEnvironment */
    protected $environment = null;

    /**
     * Getter for 'environment'.
     *
     * @return string
     */
    public function getEnvironment()
    {
        if ($this->environment === null) {
            throw new \RuntimeException('Environment has not been set.');
        }

        return $this->environment;
    }

    /**
     * Chainable setter for 'environment'.
     *
     * @param string
     */
    public function setEnvironment(AbstractEnvironment $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    public function isDevelopment()
    {
        if ($this->environment === null) {
            throw new \RuntimeException('Environment has not been set.');
        }

        return $this->environment['Environment.Name'] == Environments::DEVELOPMENT;
    }

    public function isTesting()
    {
        if ($this->environment === null) {
            throw new \RuntimeException('Environment has not been set.');
        }

        return $this->environment['Environment.Name'] == Environments::TESTING;
    }

    public function isStaging()
    {
        if ($this->environment === null) {
            throw new \RuntimeException('Environment has not been set.');
        }

        return $this->environment['Environment.Name'] == Environments::STAGING;
    }

    public function isProduction()
    {
        if ($this->environment === null) {
            throw new \RuntimeException('Environment has not been set.');
        }

        return $this->environment['Environment.Name'] == Environments::PRODUCTION;
    }
}
