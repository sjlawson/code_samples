<?php

namespace ReviveUsageData\DependencyInjection\Pages;

use ReviveUsageData\DependencyInjection\DataAccessDependencyContainer;
use ReviveUsageData\DependencyInjection\DependencyBase;
use ReviveUsageData\Presenters\Pages\ReviveDataAjaxCallbackPresenter;

/**
 * "Revive Data Ajax Callback" dependency injection container.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-09
 */
class ReviveDataAjaxCallbackDependencyContainer extends DependencyBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this['ReviveDataAjaxCallbackPresenter'] = $this->share(function ($c) {
                return new ReviveDataAjaxCallbackPresenter($c['DevMode'],
                                             $c['DataAccess'],
                                             $c['QueryParameters']);
            });

        $this['DataAccess'] = $this->share(function () {
                return new DataAccessDependencyContainer();
            });

        $this['QueryParameters'] = \drupal_get_query_parameters();
    }
}
