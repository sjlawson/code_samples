<?php

namespace MHCCcrsManager\DependencyInjection;

use Database as DrupalDatabaseObject;
use MHCCcrsManager\DataAccess\Connections;
use MHCCcrsManager\DataAccess\Tables;

/**
 * Database DI container.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class DataAccessDependencyContainer extends DependencyBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Connection services.
        $this['Connection.Ccrs2'] = $this->share(function () {
                return new Connections\Ccrs2Connection();
        });


        // ActiveRecord services.
        $this['Table.Ccrs2.EstimatorCommissionSchedules'] = $this->share(function ($c) {
                return new Tables\Ccrs2\EstimatorCommissionSchedules($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.BucketActTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketActTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.BucketContractTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketContractTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.BucketCategories'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketCategories($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.BucketDeviceTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketDeviceTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.BucketPayoutSchedules'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketPayoutSchedules($c['Connection.Ccrs2']);
        });

        $this['Table.Ccrs2.BucketCommissionPayoutBuckets'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketCommissionPayoutBuckets($c['Connection.Ccrs2']);
        });

        $this['Table.Ccrs2.BucketCommissionBuckets'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketCommissionBuckets($c['Connection.Ccrs2']);
        });

        $this['Table.Ccrs2.Buckets'] = $this->share(function ($c) {
                return new Tables\Ccrs2\Buckets($c['Connection.Ccrs2']);
        });

    }
}
