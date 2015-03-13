<?php

namespace MHCCcrsManager\DependencyInjection;

use Pimple;

/**
 * Dependency injection base class.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
abstract class DependencyBase extends Pimple
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this['DevMode'] = \mhc_development_mode_get_status() === MHC_DEVELOPMENT_MODE_STATUS_DEVELOPMENT;
    }
}
