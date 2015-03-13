<?php

namespace ReviveUsageData\DependencyInjection\Pages;

use ReviveUsageData\DependencyInjection\DataAccessDependencyContainer;
use ReviveUsageData\DependencyInjection\DependencyBase;
use ReviveUsageData\Presenters\Pages\ReviveUsageDataPresenter;

/**
 * "Revive Usage Data" dependency injection container.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-07
 */
class ReviveUsageDataDependencyContainer extends DependencyBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this['ReviveUsageDataPresenter'] = $this->share(function ($c) {
                return new ReviveUsageDataPresenter($c['DevMode'],
                                             $c['DataAccess'],
                                             $c['QueryParameters']);
            });

        $this['DataAccess'] = $this->share(function () {
                return new DataAccessDependencyContainer();
            });

        $this['QueryParameters'] = \mhc_get_query_parameters();
    }
}
