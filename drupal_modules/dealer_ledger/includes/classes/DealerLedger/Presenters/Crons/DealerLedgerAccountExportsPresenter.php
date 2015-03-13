<?php

namespace DealerLedger\Presenters\Crons;

use DealerLedger\DependencyInjection\DataAccessDependencyContainer;
use DealerLedger\Presenters\AbstractPresenter;
use DealerLedger\Utilities\Reporter;

/**
 * "dealer ledger account exports" presenter.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-25
 */
class DealerLedgerAccountExportsPresenter extends AbstractPresenter
{
    /** @var Reporter */
    protected $reporter;

    /**
     * Constructor.
     */
    public function __construct($devMode,
        DataAccessDependencyContainer $dataAccessContainer,
        Reporter $reporter)
    {
        parent::__construct($devMode, $dataAccessContainer);

        $this->reporter = $reporter;
    }

    /**
     * Will return the drupal path for this page.
     *
     * @return string
     */
    public static function getDrupalMenuRouterPath()
    {
        return 'app/dealer_ledger/crons/account/';
    }

    /**
     * Primary cron presenter callback. Execute dealer ledger export for $accountID
     *
     * @param $accountID
     */
    public function runCron($accountID)
    {
        $this->accountID = $accountID;
        $this->accountName = $this->getAccountName($this->accountID);
        $this->ftpDirectory = $this->baseExportDir . '/'. $this->accountID    . '/commissions';
        $this->sharedDirectory = $this->baseExportDir . self::SHARED_DIR_DOWNLOADS . '/'
            . $this->accountID . '/commissions';

        echo "<pre>\nStarting " . $this->accountID . ' - ' . $this->accountName . ' Cron Job.' . PHP_EOL . PHP_EOL;

        //to the ftp
        echo 'Starting ' . $this->accountName . ' FTP Directory Scheduled Tasks.' . PHP_EOL;
        $this->setPathProperties(true);
        $this->checkOutputPaths(); // check that paths exist, mkdir if not

        // export jobs
        $this->exportLocations();
        $this->exportCustomerAssociations();
        $this->exportBuckets();
        $this->exportDailyEstimateFiles();
        $this->exportCoopPaymentFile();
        $this->exportFinalPaymentFile();

        $this->cleanupFiles();

        echo 'Finishing ' . $this->accountName . ' Cron Job.' . PHP_EOL;
    }

    /**
     * Get mch_subagent.name from dealer_ledger.accountID
     *
     * @param $accountID
     * @return string name, lowercase without spaces
     */
    private function getAccountName($accountID)
    {
        $subAgentData = $this->dataAccessContainer['Table.Mhcdynad.MhcSubagents']->getSubAgentById($accountID);
        if (!$subAgentData) {
            echo "\nInvalid accountID\n";
            exit();
        }

        $nameForCron = strtolower(str_replace(' ','',$subAgentData['name']));

        return $nameForCron;
    }
}
