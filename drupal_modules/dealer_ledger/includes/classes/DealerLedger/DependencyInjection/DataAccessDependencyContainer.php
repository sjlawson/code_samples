<?php

namespace DealerLedger\DependencyInjection;

use DealerLedger\DataAccess\Connections;
use DealerLedger\DataAccess\Tables;

/**
 * Data access DI container.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
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
        $this['Connection.Mhcdynad'] = $this->share(function () {
                return new Connections\MhcdynadConnection();
        });
        $this['Connection.Ccrs2'] = $this->share(function () {
                return new Connections\Ccrs2Connection();
        });

        // ActiveRecord services.
        $this['Table.Mhcdynad.MhcSubagents'] = $this->share(function ($c) {
                return new Tables\Mhcdynad\MhcSubagents($c['Connection.Mhcdynad']);
        });
        $this['Table.Ccrs2.BucketActTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketActTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.BucketCategories'] = $this->share(function ($c) {
                return new Tables\Ccrs2\BucketCategories($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.EstimatorContractTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\EstimatorContractTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.Buckets'] = $this->share(function ($c) {
                return new Tables\Ccrs2\Buckets($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.Rq4ReconColumnTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\Rq4ReconColumnTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Ccrs2.EstimatorContractTypes'] = $this->share(function ($c) {
                return new Tables\Ccrs2\EstimatorContractTypes($c['Connection.Ccrs2']);
        });
        $this['Table.Mhcdynad.MhcSfids'] = $this->share(function ($c) {
                return new Tables\Mhcdynad\MhcSfids($c['Connection.Mhcdynad']);
        });
        $this['Table.Mhcdynad.MhcLocations'] = $this->share(function ($c) {
                return new Tables\Mhcdynad\MhcLocations($c['Connection.Mhcdynad']);
        });
        $this['Table.Ccrs2.DealerLedger'] = $this->share(function ($c) {
                return new Tables\Ccrs2\DealerLedger($c['Connection.Ccrs2']);
        });
    }
}
