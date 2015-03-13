<?php

namespace ReviveUsageData\DependencyInjection;

use Pimple;

/**
 * Dependency injection base class.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-06
 */
abstract class DependencyBase extends Pimple
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Drupal base URL.  (Ex: "https://internal.mooreheadcomm.com")
        global $base_url;
        $this['BaseUrl'] = $base_url;

        // Development mode.
        $this['DevMode'] = \mhc_development_mode_get_status() === MHC_DEVELOPMENT_MODE_STATUS_DEVELOPMENT;
    }
}
