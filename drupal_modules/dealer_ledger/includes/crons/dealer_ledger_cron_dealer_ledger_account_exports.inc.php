<?php
/**
 * Cron page callback for "dealer ledger account exports"
 *
 * @file
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-25
 */

namespace DealerLedger;

use DealerLedger\DependencyInjection\Crons\DealerLedgerAccountExportsDependencyContainer;

function _dealer_ledger_cron_dealer_ledger_account_exports($accountID)
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new DealerLedgerAccountExportsDependencyContainer();
    $dealerLedgerAccountExportsPresenter = $dependencyContainer['DealerLedgerAccountExportsPresenter'];
    $reporter = $dependencyContainer['Reporter'];

    // Set devMode parameters.
    if ($dealerLedgerAccountExportsPresenter->getDevMode()) {
        ini_set('display_errors', 'On');
    }

    $reporter->writeLine("*** dealer_ledger - dealer ledger account exports ***");

    $dealerLedgerAccountExportsPresenter->runCron($accountID);

    $reporter->writeLine("*** Done ***");
}
