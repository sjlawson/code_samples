<?php

namespace MHCCcrsManager\DependencyInjection\Pages;

use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;
use MHCCcrsManager\DependencyInjection\DependencyBase;
use MHCCcrsManager\Presenters\Pages\EditBucketCategoryPresenter;

/**
 * "Edit Bucket Category" dependency injection container.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-04-01
 */
class EditBucketCategoryDependencyContainer extends DependencyBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this['EditBucketCategoryPresenter'] = $this->share(function ($c) {
                return new EditBucketCategoryPresenter($c['DevMode'],
                                             $c['DataAccess'],
                                             $c['QueryParameters']);
            });

        $this['DataAccess'] = $this->share(function () {
                return new DataAccessDependencyContainer();
            });

        $this['QueryParameters'] = \drupal_get_query_parameters();
    }
}
