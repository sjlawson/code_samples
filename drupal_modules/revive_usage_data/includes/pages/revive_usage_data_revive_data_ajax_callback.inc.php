<?php
/**
 * Experiment: Real Ajax data rendering
 * Page callback for page "Revive Data Ajax Callback"
 *
 * @file
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-09
 */

namespace ReviveUsageData;

use ReviveUsageData\DependencyInjection\Pages\ReviveUsageDataDependencyContainer;
use ReviveUsageData\Presenters\Pages\ReviveUsageDataPresenter;

/**
 * Page callback for "Revive Data Ajax Callback"
 * Has two major differences from a typical page callback:
 * 1. Uses a different Presenter than the one which contains the method to return its own path
 * 2. Does not render, instead echoes data, bypassing Drupal rendering
 *
 * @return array The render array
 */
function _revive_usage_data_revive_data_ajax_callback()
{
    load_resources();

    $dependencyContainer = new ReviveUsageDataDependencyContainer();
    $reviveUsageDataPresenter = $dependencyContainer['ReviveUsageDataPresenter'];

    // Set devMode parameters.
    if ($reviveUsageDataPresenter->getDevMode()) {
        ini_set('html_errors', 'On');
        ini_set('xdebug.var_display_max_depth', 5);
    }

    $urlParams = $reviveUsageDataPresenter->getUsageDataUrlParams();
    $renderedData = renderData($reviveUsageDataPresenter, $urlParams);

    /* If return data is non-json (e.g. HTML), just echo */
    if (!isJson($renderedData)) {
        echo $renderedData;
    } else {
        echo json_encode($renderedData);
    }

}

/**
 * Call method $urlParams['action']
 * @return data from method
 */
function renderData(ReviveUsageDataPresenter $presenter, array $urlParams)
{
    $action = $urlParams['action'];

    if (method_exists($presenter, $action)) {
        try {
            $data = $presenter->$action($urlParams);

            return $data;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } else {
        echo "The requested method does not exists";
        exit;
    }
}

/**
 * Utility to check if string is JSON
 * @param $string
 * @return bool
 */
function isJson($string)
{
    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
}
