<?php
/**
 * Page callback for page "Dealer Ledger Account Exports"
 *
 * @file
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-25
 */

namespace DealerLedger;

use DealerLedger\DependencyInjection\Pages\DealerLedgerAccountExportsDependencyContainer;
use DealerLedger\Presenters\Pages\DealerLedgerAccountExportsPresenter;

/**
 * Page callback for "Dealer Ledger Account Exports"
 *
 * @return array The render array
 */
function _dealer_ledger_dealer_ledger_account_exports()
{
    load_resources();

    // Build composition root.
    $dependencyContainer = new DealerLedgerAccountExportsDependencyContainer();
    $dealerLedgerAccountExportsPresenter = $dependencyContainer['DealerLedgerAccountExportsPresenter'];

    // Load css.
    drupal_add_css(
        get_module_path() . '/css/dealer_ledger_dealer_ledger_account_exports.css',
        array(
            'type' => 'file',
            'group' => CSS_DEFAULT,
            'every_page' => false
        )
    );

    $pageRenderArray = array(
        '#dealerLedgerAccountExportsForm' => drupal_get_form('DealerLedger\_dealer_ledger_dealer_ledger_account_exports_form', $dealerLedgerAccountExportsPresenter),
        '#theme' => 'dealer_ledger_dealer_ledger_account_exports_page',
    );

    return $pageRenderArray;
}

/**
 * Form Builder
 *
 * @param array $form
 * @param array $form_state
 * @param DealerLedgerAccountExportsPreseneter $presenter
 *
 * @return array
 */
function _dealer_ledger_dealer_ledger_account_exports_form($form, &$form_state, DealerLedgerAccountExportsPresenter $presenter)
{
    // We are removing form caching, since closures (and hence our DI containers
    // and therefore our presenters) are not serializable.  This will also stop
    // annoying "Serialization of 'Closure'" exceptions in Drupal.
    $form_state['no_cache'] = true;

    return array(
        'accountID' => array(
            '#type' => 'select',
            '#title' => t('Select account for export'),
            '#description' => t('AccountID - Name'),
            '#multiple' => false,
            '#size' => 7,
            '#options' => $presenter->getAccountsSelectOptions(),
            '#empty_option' => t('- choose one -'),
            '#required' => true,
        ),

        'submit' => array(
            '#type' => 'submit',
            '#value' => t('Export Selected Account'),
        ),
    );
}

/**
 * Form Submit Handler
 *
 * @param array $form
 * @param array $form_state
 */
function _dealer_ledger_dealer_ledger_account_exports_form_submit($form, &$form_state)
{
    // Get the presenter.
    $presenter = $form_state['build_info']['args'][0];
    $accountID = $form_state['input']['accountID'];

    // Redirect the user.
    $form_state['redirect'] = Presenters\Crons\DealerLedgerAccountExportsPresenter::getDrupalMenuRouterPath() . $accountID ;
}
