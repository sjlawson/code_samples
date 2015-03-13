<?php

namespace DealerLedger\DependencyInjection\Pages;

use DealerLedger\DependencyInjection\DataAccessDependencyContainer;
use DealerLedger\DependencyInjection\DependencyBase;
use DealerLedger\Presenters\Pages\DealerLedgerAccountExportsPresenter;

/**
 * "Dealer Ledger Account Exports" dependency injection container.
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

        $this['DealerLedgerAccountExportsPresenter'] = $this->share(function ($c) {
                return new DealerLedgerAccountExportsPresenter($c['DevMode'],
                                             $c['DataAccess'],
                                             $c['QueryParameters']);
            });

        $this['DataAccess'] = $this->share(function () {
                return new DataAccessDependencyContainer();
            });

        $this['QueryParameters'] = \drupal_get_query_parameters();
    }
}
