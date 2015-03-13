<?php

/**
 * Will bootstrap drupal and run the "dealer ledger account exports" cron page.
 *
 * To run this cron call:
 *     php -f <path-to-this-module>/dealer_ledger_cron_dealer_ledger_account_exports.php nn -- --site=<fqdn>
 *      (nn is the accountID for export)
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-25
 */

// Bootstrap Drupal.
require_once __DIR__ . '/includes/bootstrap_drupal.php';

// Allow this cron job to run for up to 5 minutes.
set_time_limit(300);

// Run the cron.
require_once __DIR__ . '/includes/crons/dealer_ledger_cron_dealer_ledger_account_exports.inc.php';
if (isset($argv[1]) && intval($argv[1]) > 0) {
    DealerLedger\_dealer_ledger_cron_dealer_ledger_account_exports($argv[1]);
} else {
    echo "Account ID is required";
}
