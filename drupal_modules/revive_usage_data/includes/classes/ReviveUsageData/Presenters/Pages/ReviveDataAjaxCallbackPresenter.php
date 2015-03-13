<?php

namespace ReviveUsageData\Presenters\Pages;

use PDO;
use ReviveUsageData\Presenters\AbstractPresenter;
use ReviveUsageData\DependencyInjection\DataAccessDependencyContainer;

/**
 * "Revive Data Ajax Callback" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-09
 */
class ReviveDataAjaxCallbackPresenter extends AbstractPresenter
{
    const DRUPAL_MENU_ROUTER_PATH = 'data/usage/ajax_callback';

    /**
     * Constructor
     */
    public function __construct($devMode,
                                DataAccessDependencyContainer $dataAccessContainer,
                                array $getParameters)
    {
        parent::__construct($devMode, $dataAccessContainer, $getParameters);
    }

    /**
     * Will return the drupal path for this page.
     *
     * @return string
     */
    public static function getDrupalMenuRouterPath()
    {
        return self::DRUPAL_MENU_ROUTER_PATH;
    }
}
