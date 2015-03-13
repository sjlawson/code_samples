<?php

namespace DealerLedger\Presenters\Pages;

use PDO;
use DealerLedger\Presenters\AbstractPresenter;
use DealerLedger\DependencyInjection\DataAccessDependencyContainer;

/**
 * "Dealer Ledger Account Exports" page presenter.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-25
 */
class DealerLedgerAccountExportsPresenter extends AbstractPresenter
{
    const DRUPAL_MENU_ROUTER_PATH = 'app/dealer_ledger/export';

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
    public function getDrupalMenuRouterPath()
    {
        return self::DRUPAL_MENU_ROUTER_PATH;
    }

    /**
     * Get account id->name pairs for select options array
     * @return array
     */
    public function getAccountsSelectOptions()
    {
        $stmt = $this->dataAccessContainer['Table.Mhcdynad.MhcSubagents']->getSubagents();
        $options = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$row['id']] = $row['id'] . ' - ' . $row['name'];
        }

        return $options;
    }

}
