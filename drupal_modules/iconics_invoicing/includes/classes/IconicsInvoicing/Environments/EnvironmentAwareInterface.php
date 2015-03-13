<?php

namespace IconicsInvoicing\Environments;

use IconicsInvoicing\DependencyInjection\Environments\AbstractEnvironment;

interface EnvironmentAwareInterface
{
    /**
     * Getter for 'environment'.
     *
     * @return string
     */
    public function getEnvironment();

    /**
     * Setter for 'environment'.
     *
     * @param string $environment
     */
    public function setEnvironment(AbstractEnvironment $environment);

    /**
     * @return boolean True if in development environment.
     */
    public function isDevelopment();

    /**
     * @return boolean True if in testing environment.
     */
    public function isTesting();

    /**
     * @return boolean True if in staging environment.
     */
    public function isStaging();

    /**
     * @return boolean True if in production environment.
     */
    public function isProduction();
}
