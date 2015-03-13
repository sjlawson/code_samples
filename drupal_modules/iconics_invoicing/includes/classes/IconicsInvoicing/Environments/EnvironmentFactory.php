<?php

namespace IconicsInvoicing\Environments;

use IconicsInvoicing\DependencyInjection\Environments as DIEnvironments;

class EnvironmentFactory
{
    public static function createDependencyContainer($host, $environment)
    {
        // Determine domain from host url.
        $hostParts = explode('.', $host);
        while (count($hostParts) > 2) {
            array_shift($hostParts);
        }

        $domain = implode('.', $hostParts);
        switch ($domain) {
            //@ DependencyContainer method calls will be added here.

        case 'mooreheadcomm.com':
            return self::getMHCDependencyContainer($environment);
        }

        throw new \InvalidArgumentException('Invalid host: ' . $host);
    }

    //@ DependencyContainer methods will be added here.
    protected static function getMHCDependencyContainer($environment)
    {
        switch ($environment) {
            //@ MHC environments.

        case Environments::STAGING:
            return new DIEnvironments\MHC\Staging();

        case Environments::TESTING:
            return new DIEnvironments\MHC\Testing();

        case Environments::DEVELOPMENT:
            return new DIEnvironments\MHC\Development();

        case Environments::PRODUCTION:
            return new DIEnvironments\MHC\Production();
        }

        throw new \InvalidArgumentException('Invalid environment: ' . $environment);
    }
}
