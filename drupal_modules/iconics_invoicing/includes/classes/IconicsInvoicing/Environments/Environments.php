<?php

namespace IconicsInvoicing\Environments;

final class Environments
{
    const DEVELOPMENT = 'dev';
    const TESTING     = 'testing';
    const STAGING     = 'staging';
    const PRODUCTION  = 'prod';

    /**
     * Will determine whether a environment string is valid.
     *
     * @param string $environmentName
     *
     * @return boolean True if name is valid.
     */
    public static function isEnvironmentNameValid($environmentName)
    {
        if ($environmentName == self::DEVELOPMENT
            || $environmentName == self::TESTING
            || $environmentName == self::STAGING
            || $environmentName == self::PRODUCTION) {
            return true;
        }

        return false;
    }
}
