<?php

namespace DealerLedger\Presenters;

use DealerLedger\DependencyInjection\DataAccessDependencyContainer;
use DealerLedger\Utilities\DLTime;
use PDO;

/**
 * Abstract base presenter class for common variables and helper functions.
 *
 * @author Samuel J. Lawson <slawson@mooreheadcomm.com>
 * @date 2014-06-19
 */
abstract class AbstractPresenter
{
    const EXPORT_TMP_DIR = '/tmp';
    const SHARED_DIR_DOWNLOADS = '/files/fs1/downloads'; // Not active, but might be used later

    /** @var DataAccessDependencyContainer */
    protected $dataAccessContainer;

    /** @var boolean */
    protected $devMode;

    protected $baseExportDir;

    protected $settingsOutPath;
    protected $commissionsOutPath;
    protected $monthYear;

    protected $dailyFileOutPath;
    protected $monthlyFileOutPath;
    protected $metaFileOutPath;

    protected $accountID;
    protected $ftpDirectory;
    protected $sharedDirectory;
    protected $accountName;

    protected $reporter;

    /**
     * Constructor.
     */
    public function __construct($devMode,
        DataAccessDependencyContainer $dataAccessContainer)
    {
        $this->dataAccessContainer = $dataAccessContainer;
        $this->devMode = (boolean) $devMode;
        $this->monthYear = date('Y-m-1');
        if ($this->devMode) {
            $this->baseExportDir = '/var/httpd/files/sftp/dealers';
        } else {
            $this->baseExportDir = '/var/httpd/files/sftp/dealers';
        }
    }

    /**
     * Getter for devMode.
     *
     * @return boolean True if in devMode.
     */
    public function getDevMode()
    {
        return $this->devMode;
    }

    /**
     * Set class properties relevant to output paths
     *
     * @param boolean $sendToFTP
     */
    protected function setPathProperties($sendToFTP)
    {
        $this->commissionsOutPath = ($sendToFTP)? $this->ftpDirectory : $this->sharedDirectory;
        $this->settingsOutPath = ($sendToFTP)? $this->ftpDirectory . "/settings" : $this->sharedDirectory . "/settings";
        $this->dailyFileOutPath = ($sendToFTP) ? $this->ftpDirectory . "/daily" : $this->sharedDirectory . "/daily";
        $this->monthlyFileOutPath = ($sendToFTP) ? $this->ftpDirectory . "/monthly" : $this->sharedDirectory . "/monthly";
        $this->metaFileOutPath = ($sendToFTP) ? $this->ftpDirectory . "/meta" : $this->sharedDirectory . "/meta";
    }

    /**
     * Check that output paths exists. If they don't, create them.
     */
    protected function checkOutputPaths()
    {
        /* Uncomment iff shared dir is brought back  */
        /* if (!is_dir($this->baseExportDir . self::SHARED_DIR_DOWNLOADS . '/' . $this->accountID)) { */
        /*     mkdir($this->baseExportDir . self::SHARED_DIR_DOWNLOADS . '/' . $this->accountID); */
        /*     chmod($this->baseExportDir . self::SHARED_DIR_DOWNLOADS . '/' . $this->accountID, 0777); */
        /* } */

        if (!is_dir($this->baseExportDir . '/'. $this->accountID)) {
            mkdir($this->baseExportDir . '/'. $this->accountID);
            chmod($this->baseExportDir . '/'. $this->accountID, 0777);
        }

        if (!is_dir($this->commissionsOutPath)) {
            mkdir($this->commissionsOutPath);
            chmod($this->commissionsOutPath, 0777);
        }

        if (!is_dir($this->settingsOutPath)) {
            mkdir($this->settingsOutPath);
            chmod($this->settingsOutPath, 0777);
        }

        if (!is_dir($this->dailyFileOutPath)) {
            mkdir($this->dailyFileOutPath);
            chmod($this->dailyFileOutPath, 0777);
        }

        if (!is_dir($this->monthlyFileOutPath)) {
            mkdir($this->monthlyFileOutPath);
            chmod($this->monthlyFileOutPath, 0777);
        }

        if (!is_dir($this->metaFileOutPath)) {
            mkdir($this->metaFileOutPath);
            chmod($this->metaFileOutPath, 0777);
        }
    }

    /**
     * Delete working temp files
     */
    public function cleanupFiles()
    {

        echo 'Cleaning ' . $this->accountID . ' - ' . $this->accountName . ' File Directories' . PHP_EOL;

        foreach (glob("{$this->commissionsOutPath}/*.txt") as $filename) {
            if (filemtime($filename) < strtotime('1 months ago')) {
                unlink($filename);
            }
        }

        foreach (glob("{$this->dailyFileOutPath}/*.txt") as $filename) {
            if (filemtime($filename) < strtotime('1 months ago')) {
                unlink($filename);
            }
        }

        foreach (glob("{$this->monthlyFileOutPath}/*.txt") as $filename) {
            if (filemtime($filename) < strtotime('2 months ago')) {
                unlink($filename);
            }
        }

        foreach (glob("{$this->metaFileOutPath}/*.txt") as $filename) {
            if (filemtime($filename) < strtotime('1 months ago')) {
                unlink($filename);
            }
        }

        foreach (glob(self::EXPORT_TMP_DIR . "/*.txt") as $filename) {
                unlink($filename);
        }

        echo 'Finished cleaning ' . $this->accountName . ' File Directories' . PHP_EOL;
    }

    /**
     * Export location data for account
     *
     */
    protected function exportLocations()
    {
        echo 'Starting ' . $this->accountName  . ' locations file generation.' . PHP_EOL;
        $time = time();
        $fileName = $this->accountID . "_locations.txt";
        $fullFilePath = self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName;

        $dataSet = $this->dataAccessContainer['Table.Mhcdynad.MhcLocations']->exportLocations($this->accountID);
        if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
            echo $this->accountID . ' locations tmp file could not be created.' . PHP_EOL;

            return false;
        }

        // file move operations
        $month = date("m", strtotime($this->monthYear));
        $year = date("Y", strtotime($this->monthYear));
        if (filesize(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName)) {

            if (rename(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName, $this->settingsOutPath . '/' . $fileName)) {
                chmod($this->settingsOutPath . '/' . $fileName, 0666);
                copy(
                    $this->settingsOutPath . '/' . $fileName,
                    $this->metaFileOutPath . '/' . $year . '_' . $month . '_' . $fileName
                );
                chmod($this->metaFileOutPath . '/' . $year . '_' . $month . '_' . $fileName, 0666);
                echo $this->accountID . ' locations file created successfully.' . PHP_EOL;
            } else {
                echo $this->accountID . ' locations tmp file could not be moved!' . PHP_EOL;
            }

        } else {
            echo $this->accountID . ' locations file could not be created.' . PHP_EOL;
        }

        echo 'Finishing ' . $this->accountName . ' locations file generation.' . PHP_EOL;
    }

    /**
     * Get customers for account, export to tab-separated-file
     */
    protected function exportCustomerAssociations()
    {
        echo 'Starting ' . $this->accountName . ' customers file generation.' . PHP_EOL;

        $time     = time();
        $fileName = $this->accountID . "_customers.txt";
        $fullFilePath = self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName;

        $dataSet = $this->dataAccessContainer['Table.Ccrs2.DealerLedger']->getCustomerAssociations(
            $this->accountID,
            $this->monthYear
        );
        if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
            echo $this->accountName . ' customers tmp file could not be created.' . PHP_EOL;

            return false;
        }

        // file move operations
        $month = date("m", strtotime($this->monthYear));
        $year = date("Y", strtotime($this->monthYear));
        if (filesize(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName)) {
            rename(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName,
                $this->metaFileOutPath . '/' . $year . '_' . $month . '_' . $fileName
            );
            chmod($this->metaFileOutPath . '/' . $year . '_' . $month . '_' . $fileName, 0666);
            echo $this->accountName . ' customers file created successfully.' . PHP_EOL;
        } else {
            echo $this->accountName . ' customers file could not be created.' . PHP_EOL;
        }

        echo 'Finishing ' . $this->accountName . ' customers file generation.' . PHP_EOL;
    }

    /**
     * Export current full list of buckets
     */
    protected function exportBuckets()
    {
        echo 'Starting ' . $this->accountName . ' buckets file generation.' . PHP_EOL;

        $time     = time();
        $fileName = $this->accountID . "_buckets.txt";
        $fullFilePath = self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName;

        $dataSet = $this->dataAccessContainer['Table.Ccrs2.Buckets']->getBuckets();
        if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
            echo $this->accountName . ' buckets tmp file could not be created.' . PHP_EOL;

            return false;
        }

        // file move operations
        $month = date("m", strtotime($this->monthYear));
        $year = date("Y", strtotime($this->monthYear));

        if (filesize(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName)) {
            rename(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName,
                $this->metaFileOutPath . '/' . $year . '_' . $month . '_' . $fileName
            );
            chmod($this->metaFileOutPath . '/' . $year . '_' . $month . '_' . $fileName, 0666);
            echo $this->accountName . ' buckets file created successfully.' . PHP_EOL;
        } else {
            echo $this->accountName . ' buckets file could not be created or was empty.' . PHP_EOL;
        }

        echo 'Finishing ' . $this->accountName . ' buckets file generation.' . PHP_EOL;
    }

    /**
     * Get daily estimates for accountID, export
     */
    protected function exportDailyEstimateFiles()
    {
        echo 'Starting ' . $this->accountName . ' daily estimate file(s) generation.' . PHP_EOL;

        $fileName = date('Y_m_d_Hm') . '_' . $this->accountID . "_estimate.txt";
        $fullFilePath = self::EXPORT_TMP_DIR . '/' . $fileName;

        $estimateMonthYear = $this->monthYear;
        if (date('d') < 3) {
            $estimateMonthYear = DLTime::getDate(DLTime::getDate('last month'), DLTime::FIRST);
        }

        $dataSet = $this->dataAccessContainer['Table.Ccrs2.DealerLedger']->getEstimates(
            $this->accountID,
            $estimateMonthYear,
            true
        );
        if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
            echo $this->accountName . ' current month estimate tmp file could not be created or was empty.' . PHP_EOL;

            return false;
        }

        // File move operations
        if (filesize($fullFilePath)) {
            rename($fullFilePath,
                $this->dailyFileOutPath . '/' . $fileName
            );
            chmod($this->dailyFileOutPath . '/' . $fileName, 0666);
            echo $this->accountName . ' current month estimate file created successfully.' . PHP_EOL;
        } else {
            echo $this->accountName . ' current month estimate file could not be created or was empty.' . PHP_EOL;
        }

        /*** create the estimated payment files from the 2nd to the 30th/31st for the previous month ***/

        if (date('d') >=2) {
            $prevEstimateMonthYear =  DLTime::getDate(DLTime::getDate('31 days ago'), DLTime::FIRST);
            $fileName = date('Y_m_d_Hm') . '_' . $this->accountID . "_payment_not_final.txt";
            $fullFilePath = self::EXPORT_TMP_DIR . '/' . $fileName;

            $dataSet = $this->dataAccessContainer['Table.Ccrs2.DealerLedger']->getEstimates(
                $this->accountID,
                $prevEstimateMonthYear,
                false
            );
            if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
                echo $this->accountName . ' estimated payment tmp file could not be created.' . PHP_EOL;

                return false;
            }

            // File move operations
            if (filesize($fullFilePath)) {
                rename($fullFilePath,
                    $this->dailyFileOutPath . '/' . $fileName
                );
                chmod($this->dailyFileOutPath . '/' . $fileName, 0666);
                echo $this->accountName . ' estimated payment file created successfully.' . PHP_EOL;
            } else {
                echo $this->accountName . ' estimated payment file could not be created or was empty.' . PHP_EOL;
            }
        }

        echo 'Finishing ' . $this->accountName . ' buckets file generation.' . PHP_EOL;
    }

    /**
     * Export records from DealerLedger with final paidOn dates
     */
    protected function exportFinalPaymentFile()
    {
        echo 'Starting ' . $this->accountName . ' final payment file generation.' . PHP_EOL;
        $month = date('m', strtotime($this->monthYear));
        $year = date('Y', strtotime($this->monthYear));
        $time = time();
        $fileName = $year . '_' . $month . '_' . $this->accountID . '_payment_final.txt';
        $fullFilePath = self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName;

        $dataSet = $this->dataAccessContainer['Table.Ccrs2.DealerLedger']->getFinalPayments($this->accountID, $this->monthYear);
        if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
            echo $this->accountName . ' final payments tmp file could not be created.' . PHP_EOL;

            return false;
        }

        // File move operations
        if (filesize(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName)) {
            rename(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName,
                $this->monthlyFileOutPath . '/' . $fileName
            );
            chmod($this->monthlyFileOutPath . '/' . $fileName, 0666);
            echo $this->accountName . ' final payment file created successfully.' . PHP_EOL;
        } else {
            echo $this->accountName . ' final payment file could not be created or was empty.' . PHP_EOL;
        }
    }

    /**
     * Export DealerLedger records with CoOp* column types
     */
    protected function exportCoopPaymentFile()
    {
        echo 'Starting ' . $this->accountName . ' coop payment file generation.' . PHP_EOL;

        $month = date("m", strtotime($this->monthYear));
        $year = date("Y", strtotime($this->monthYear));
        $time = time();
        $fileName = "{$year}_{$month}_{$this->accountID}_coop_payment.txt";
        $fullFilePath = self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName;

        $dataSet = $this->dataAccessContainer['Table.Ccrs2.DealerLedger']->getCoopPayments($this->accountID, $this->monthYear);
        if (!$this->writeTabSeparatedFileStmt($fullFilePath, $dataSet)) {
            echo $this->accountName . ' coop payments tmp file could not be created.' . PHP_EOL;

            return false;
        }

        // File move operations
        if (filesize(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName)) {

            if(!rename(self::EXPORT_TMP_DIR . '/' . $time . "_" . $fileName,
                    $this->monthlyFileOutPath . '/' . $fileName) ) {
                echo $this->accountName . 'final payment file could not be moved.' . PHP_EOL;
            } else {
                echo $this->accountName . ' final payment file created successfully.' . PHP_EOL;
                chmod($this->monthlyFileOutPath . '/' . $fileName, 0666);
            }

        } else {
            echo $this->accountName . ' final payment file could not be created or was empty.' . PHP_EOL;
        }
    }

    /**
     * Writes data from $outputData to $fullPath with "\t" separators
     * @param string       $fullPath
     * @param PDOStatement $stmt
     */
    private function writeTabSeparatedFileStmt($fullPath, $stmt)
    {
        try {
            $file = fopen($fullPath, "w");

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($file, $row, "\t");
            }

            fclose($file);
            chmod($fullPath, 0666);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
