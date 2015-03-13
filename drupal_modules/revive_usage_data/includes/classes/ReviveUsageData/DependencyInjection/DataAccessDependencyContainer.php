<?php

namespace ReviveUsageData\DependencyInjection;

use ReviveUsageData\DataAccess\Connections;
use ReviveUsageData\DataAccess\Tables;

/**
 * Data access DI container.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-06
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
        $this['Connection.ReviveApi'] = $this->share(function () {
                return new Connections\ReviveApiConnection();
        });
        $this['Connection.ReviveInternal'] = $this->share(function () {
                return new Connections\ReviveInternalConnection();
        });

        // ActiveRecord services.
        $this['Table.ReviveApi.Processes'] = $this->share(function ($c) {
                return new Tables\ReviveApi\Processes($c['Connection.ReviveApi']);
        });
        $this['Table.ReviveInternal.MachinesHistory'] = $this->share(function ($c) {
                return new Tables\ReviveInternal\MachinesHistory($c['Connection.ReviveInternal']);
        });
        $this['Table.ReviveInternal.Configurations'] = $this->share(function ($c) {
                return new Tables\ReviveInternal\Configurations($c['Connection.ReviveInternal']);
        });
        $this['Table.ReviveApi.ProcessDataValues'] = $this->share(function ($c) {
                return new Tables\ReviveApi\ProcessDataValues($c['Connection.ReviveApi']);
        });
        $this['Table.ReviveInternal.Locations'] = $this->share(function ($c) {
                return new Tables\ReviveInternal\Locations($c['Connection.ReviveInternal']);
        });
        $this['Table.ReviveInternal.Machines'] = $this->share(function ($c) {
                return new Tables\ReviveInternal\Machines($c['Connection.ReviveInternal']);
        });

    }
}
