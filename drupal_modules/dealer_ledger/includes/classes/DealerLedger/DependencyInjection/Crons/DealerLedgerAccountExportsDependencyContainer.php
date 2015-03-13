<?php

namespace DealerLedger\DependencyInjection\Crons;

use DealerLedger\DependencyInjection\DependencyBase;
use DealerLedger\DependencyInjection\DataAccessDependencyContainer;
use DealerLedger\Presenters\Crons\DealerLedgerAccountExportsPresenter;
use DealerLedger\Utilities\Reporter;

/**
 * Cron "dealer ledger account exports" dependency injection container.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-25
 */
class DealerLedgerAccountExportsDependencyContainer extends DependencyBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this['DataAccess'] = $this->share(function () {
                return new DataAccessDependencyContainer();
            });

        $this['DealerLedgerAccountExportsPresenter'] = $this->share(function ($c) {
                return new DealerLedgerAccountExportsPresenter($c['DevMode'],
                    $c['DataAccess'],
                    $c['Reporter']
                );
            });

        $this['Reporter'] = $this->share(function ($c) {
                return new Reporter($c['DevMode']);
            });
    }
}
