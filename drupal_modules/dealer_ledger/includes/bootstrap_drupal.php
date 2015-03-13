<?php

/**
 * This was taken from index.php. The only difference is that we only
 * boot Drupal to DRUPAL_BOOTSTRAP_VARIABLES instead of DRUPAL_BOOTSTRAP_FULL
 * and that we know exactly where we are in a modules directory to find
 * DRUPAL_ROOT to make the code portable.
 *
 * Current directory this file should be in:
 * DRUPAL_ROOT/sites/<SITE>/modules/<MODULE>/includes/bootstrap_drupal.php
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
if (defined('DRUPAL_ROOT') === false) {
    if (php_sapi_name() === 'cli') {
        $site = '';

        if ((isset($_SERVER['argv']) === true) && (count($_SERVER['argv']) > 1)) {
            foreach ($_SERVER['argv'] as $arg) {
                if (strstr($arg, '--site=') !== false) {
                    $siteOptionAndSite = explode('=', $arg);

                    // Save everything after the =
                    $site = trim($siteOptionAndSite[1]);
                }
            }
        }

        if (strlen($site) == 0) {
            exit('You need to specify --site=<SITE> for Drupal to boot properly!');
        }

        // This is needed to make Drupal boot
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['HTTP_HOST'] = $site;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    // Go back 5 directories and we should be in the root directory for Drupal.
    define('DRUPAL_ROOT', realpath(__DIR__ . '/../../../../../'));

    require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
    drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

    // Load required modules
    drupal_load('module', 'mhc_development_mode');
    drupal_load('module', 'mhc_error_and_exception_handler');
    drupal_load('module', 'dealer_ledger');
}
