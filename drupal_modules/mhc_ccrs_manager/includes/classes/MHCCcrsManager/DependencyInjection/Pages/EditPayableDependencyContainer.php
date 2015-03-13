<?php

namespace MHCCcrsManager\DependencyInjection\Pages;

use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;
use MHCCcrsManager\DependencyInjection\DependencyBase;
use MHCCcrsManager\Presenters\Pages\EditPayablePresenter;

/**
 * "Edit Payable" dependency injection container.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-28
 */
class EditPayableDependencyContainer extends DependencyBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this['EditPayablePresenter'] = $this->share(function ($c) {
                return new EditPayablePresenter($c['DevMode'],
                                             $c['DataAccess'],
                                             $c['QueryParameters']);
            });

        $this['DataAccess'] = $this->share(function () {
                return new DataAccessDependencyContainer();
            });

        $this['QueryParameters'] = \drupal_get_query_parameters();
    }
}
