<?php

/**
 * This class is the controller for the ccrs update process
 */
class ccrsUpdater extends ccrs
{
    public $ccrsPath = '/var/httpd/files/fs1/downloads/ccrs';
    public $ccrsPathProcessed = '/var/httpd/files/fs1/downloads/ccrs/processed';
    public $files;
    public $isUpdating;
    public $monthYear;

    /**
     * This method grabs the files awaiting import and sets the isUpdating flag.
     *
     * @param type $options
     */
    public function __construct($options)
    {
        //error_reporting(E_ALL);
        //ini_set("display_errors", 1);

        $this->setDB();
        $this->options = $options;
        $this->files = array_merge(
            glob($this->ccrsPath . '/MOOREHEAD_NA_*.dat'),
            glob($this->ccrsPath . '/moorehead_na_*.dat')
        );
        $this->isUpdating = $this->getSetting('isUpdating');
    }

    /**
     * This method gets called before files are processed
     * @return only if there is an error
     */
    private function preProcessFiles()
    {
        if( count(preg_grep("/.OPTIONAL_SERVICE_ACTIVATIONS\.dat|.MOBILE_ADJUSTMENTS\.dat/", $this->files)) == 2
            || count(preg_grep("/.OPTIONAL_SERVICE_ACTIVATIONS\.dat|.MOBILE_ADJUSTMENTS\.dat/", $this->files)) == 4 ) {
            $error = $this->preProcessTMP();
            if($error) {
                return $error;
            }
        }
    }

    /**
     * preProcessFiles depends on being able to alter files, checks if files are writable
     */
    public function checkWritable()
    {
        $message = '';
        foreach($this->files as $file) {
            if(!is_writable($file) ){
                $message .= "Warning: $file is not writable.<br />";
            }
        }

        return $message;
    }

    /**
     * Search mobile adjustments file for TMP lines, moves them to OPTIONAL_SERVICE_ACTIVATIONS (features)
     */
    private function preProcessTMP()
    {
        $mobileAdjustmentsKey = array_keys(preg_grep("/.MOBILE_ADJUSTMENTS\.dat/", $this->files));
        /* $mobileAdjustmentsFile = $this->files[$mobileAdjustmentsKey[0]]; */
        /* $featuresKey = array_keys(preg_grep("/.OPTIONAL_SERVICE_ACTIVATIONS\.dat/", $this->files)); */
        /* $featuresFile = $this->files[$featuresKey[0]]; */

        foreach ($mobileAdjustmentsKey as $mKey) {
            $filePrefix = substr($this->files[$mKey], 0, strpos($this->files[$mKey], 'MOBILE'));
            $mobileAdjustmentsFile = $filePrefix . 'MOBILE_ADJUSTMENTS.dat';
            $featuresFile = $filePrefix . 'OPTIONAL_SERVICE_ACTIVATIONS.dat';


            $adjustmentDefaults = array(
                'system' => '',
                'sfid' => '',
                'company' => '',
                'year' => '',
                'month' => '',
                'originalPhone' => '',
                'phone' => '',
                'deviceCategory' => '',
                'deviceID' => '',
                'market' => '',
                'planName' => '', // was adjustmentDescription /
                'spiff' => '', // was adjustmentAmount /
                'paymentType' => '',
                'phoneDescription' => '', // was model
            );

            $featureRecordDefaults = array(
                'system' => '',
                'sfid' => '',
                'company' => '',
                'year' => '',
                'month' => '',
                'originalPhone' => '',
                'phone' => '',
                'deviceCategory' => '',
                'deviceID' => '',
                'accountNumber' => '',
                'featureID' => '',
                'customerName' => '',
                'contractDate' => '',
                'pricePlan' => '',
                'commissionAmount' => '',
                'spiff' => '',
                'visionCode' => '',
                'planName' => '',
                'phoneDescription' => '',
            );

            $adjustmentRecords = array();
            $featureInserts = array();

            /* Open files... but... very... carefully */
            try {
                $adjFp = fopen($mobileAdjustmentsFile, 'r');
                if(!$adjFp) {
                    return "Error: unable to open mobile adjustments file: $mobileAdjustmentsFile";
                }

            } catch( Exception $e ) {
                return "Error: unable to open mobile adjustments file: $mobileAdjustmentsFile";
            }

            try {
                $featureFp = fopen($featuresFile, 'r');
                if(!$featureFp) {
                    return "Error: unable to open features file for reading: $featuresFile";
                }

            } catch( Exception $e ) {
                return "Error: unable to open features file for reading: $featuresFile";
            }

            try {
                $featureTempFp = fopen($featuresFile . ".tmp", 'w');
                if(!$featureTempFp) {
                    return "Error: unable to open features temp file for writing: $featuresFile";
                }

            } catch( Exception $e ) {
                return "Error: unable to open features temp file for writing: $featuresFile";
            }

            $c = 1;
            while(!feof($featureFp)) {
                $line = fgetcsv($featureFp, 2048, "\t");

                if($c === 1) {
                    /* echo "<pre>" . print_r($line, true); */
                    fputs($featureTempFp, implode($line, "\t"));
                } else {
                    fputs($featureTempFp, PHP_EOL . implode($line, "\t"));
                }
                $c++;
            }
            fclose($featureFp);

            $c = 1;
            while(!feof($adjFp)) {
                $line = fgetcsv($adjFp, 2048, "\t");
                $adjustmentRow = array_combine(array_keys($adjustmentDefaults), $line);
                if(preg_match("/TMP/" ,$adjustmentRow['planName'])) {
                    $features = array_merge($featureRecordDefaults, $adjustmentRow);
                    unset($features['market']);
                    unset($features['paymentType']);
                    $features['originalPhone'] = empty($features['originalPhone']) ? $features['phone'] : $features['originalPhone'];
                    $features['pricePlan'] = empty($features['pricePlan']) ? '0.00' : $features['pricePlan'];
                    $features['commissionAmount'] = empty($features['commissionAmount']) ? '0.00' : $features['commissionAmount'];
                    $features['phoneDescription'] = !empty($features['phoneDescription']) ? substr($features['phoneDescription'],0,31) : '';
                    $features['spiff'] = !empty($features['spiff']) ? $features['spiff'] : '0.00';
                    $features['contractDate'] = !empty($features['contractDate']) ? $features['contractDate'] : $features['month'] . '/01/' . $features['year'];
                    $featuresValues = array_values($features);

                    if($c === 1 && !empty($featuresValues)) {
                        fputs($featureTempFp, implode($featuresValues, "\t"));
                        /* echo "<pre>" . print_r($features, true); */
                    } elseif(!empty($featuresValues)) {
                        fputs($featureTempFp, PHP_EOL . implode($featuresValues, "\t"));
                    }
                    $c++;
                } else {
                    /* this line is not TMP, just copy it to temp file... ah, the irony. */
                    if(!file_exists($mobileAdjustmentsFile . '.tmp')) {

                        try {
                            $newAdjFp = fopen($mobileAdjustmentsFile . '.tmp', 'w');
                            if(!$newAdjFp) {
                                return "Error: unable to open temp mobile adjustments file: $mobileAdjustmentsFile.tmp";
                            }
                        } catch( Exception $e ) {
                            return "Error: unable to open temp mobile adjustments file: $mobileAdjustmentsFile.tmp";
                        }
                        fputs($newAdjFp, implode($line, "\t"));

                    } else {
                        fputs($newAdjFp, PHP_EOL . implode($line, "\t"));
                    }
                }
            }

            fputs($featureTempFp, PHP_EOL);
            fclose($newAdjFp);
            fclose($adjFp);
            fclose($featureTempFp);

            if(filesize($featuresFile . '.tmp') > filesize($featuresFile)) {
                rename($featuresFile, $featuresFile . '_orig' );
                rename($featuresFile . '.tmp', $featuresFile);
            } else {
                // unlink($featuresFile . '.tmp'); /
            }

            rename($mobileAdjustmentsFile, $mobileAdjustmentsFile . '_orig');
            rename($mobileAdjustmentsFile . '.tmp', $mobileAdjustmentsFile);
            
        }
    }

    /**
     * This method is called to execute the import
     *
     * Due to VZW changes, there are some time sensitive switches that can eventually be removed.
     *
     * The general flow is to import the data from the files, estimate the commissions and then estimate the payouts.
     *
     * @return boolean
     */
    public function importFiles()
    {
        //prevent multiple updates at once
        if ($this->isUpdating) {
            return "Error: Updater is process locked";
        }

        $this->addUpdateLog('', 0, 'Began Import.');
        $this->setSetting('isUpdating', 1);
        $this->setSetting('updateStartTime', date("Y-m-d H:i:s"));

        $error = $this->preProcessFiles();
        if($error) {
            return $error;
        }

        foreach ($this->files as $thisFile) {
            //skip empty files
            if (filesize($thisFile)) {
                $this->addUpdateLog($thisFile, 0, 'Started ' . basename($thisFile) . ' File.');

                $error = $this->processFile($thisFile);
                if ($error) {
                    return $error;
                }

                //instantiate receivable estimator based on month year
                switch ($this->monthYear) {

                    case strtotime($this->monthYear) <= strtotime('2012-6-1'):
                        $ce = new ccrsEstimator($this->options, $this->table, $this->monthYear);
                        $ce->addUpdateLog($thisFile, 0, 'Using Legacy Estimator.');
                        break;

                    case strtotime($this->monthYear) >= strtotime('2012-7-1'):
                        $ce = new ccrsEstimatorSimplified($this->options, $this->table, $this->monthYear);
                        $ce->addUpdateLog($thisFile, 0, 'Using Simplified Estimator.');
                        break;
                }

                $error = $ce->estimateCommissions();
                if ($error)
                    return $error;

                //instantiate payable estimator based on month year
                switch ($this->monthYear) {
                    case strtotime($this->monthYear) <= strtotime('2012-6-1'):
                        $cp = new ccrsPayout($this->options, $this->table, $this->monthYear);
                        $ce->addUpdateLog($thisFile, 0, 'Using Legacy Payout Estimator.');
                        break;

                    case strtotime($this->monthYear) >= strtotime('2012-7-1'):
                        $cp = new ccrsPayoutSimplified($this->options, $this->table, $this->monthYear);
                        $ce->addUpdateLog($thisFile, 0, 'Using Simplified Payout Estimator.');
                        break;
                }

                $error = $cp->setPayouts();
                if ($error) return $error;

                // determine if any tier files come thru
                if (in_array($this->table, array(self::ACTS, self::DEACTS, self::REACTS))) {
                    $this->addUpdateLog($thisFile, 0, 'Begin Tier payouts.');
                    $error = $cp->setTierPayouts();
                    if ($error) return $error;
                    $this->addUpdateLog($thisFile, 0, 'Finish Tier payouts.');
                }

                if (in_array($this->table, array(self::ACTS, self::DEACTS, self::REACTS, self::UPGRADES, self::UPGRADE_DEACTS))) {
                    // determine if any edge files come thru
                    $this->addUpdateLog('', 0, 'Begin Edge Fast-Start Spiff Calculations.');
                    $error = $cp->setEdgeFastStartSpiffPayouts();
                    if ($error) return $error;
                    $this->addUpdateLog($thisFile, 0, 'Finish Edge Fast-Start Spiff Calculations.');
                }

                $this->addUpdateLog($thisFile, 0, 'Finished Payouts.');
            }

            rename($thisFile, $this->ccrsPathProcessed . DIRECTORY_SEPARATOR . basename($thisFile));
            $this->addUpdateLog($thisFile, 0, 'Finished File.');
        }

        if(!empty($this->monthYear)) {
            $error = $this->normalizeTMPFeatures();
            if ($error) {
                return $error;
            }
        } else {
            drupal_set_message('Unable to normalize TMP Features, no monthYear specified.');
        }

        $this->setSetting('isUpdating', 0);
        $this->addUpdateLog('', 0, 'Finished Import.');
        $this->setSetting('updateEndTime', date("Y-m-d H:i:s"));
    }

    /**
     * This helper method breaks the filename down into usable pieces of data.
     *
     * @param  type $fileName
     * @return type
     */
    public function getFileInfo($fileName)
    {
        $fileName = explode('_', $fileName);

        //figure month and year
        $date = $fileName[2];
        $year = substr($date, 0, 4);
        $month = substr($date, strlen($year));

        return array(
            'month' => $month,
            'year' => $year,
            'verizonRegionID' => self::NATIONAL
            );
    }

    /**
     * This helper is used to 'tag' the individual rows
     * by adding denormalized data on each record for faster querying
     *
     * @return type
     */
    public function tagTable()
    {
        $this->addUpdateLog('', 0, 'Tagging');

        switch ($this->table) {

            case self::ADJUSTMENTS:
            case self::CHANGES:
                $contractDate = "monthYear";
                break;

            case self::COOP_ACTS:
            case self::COOP_DEACTS:
                $contractDate = "docDate";
                break;

            case self::UPGRADES:
            case self::UPGRADE_DEACTS:
                $contractDate = "new_esn_contractdate_start";
                break;

            case self::REACTS:
                $contractDate = "reactivationDate";
                break;

            default:
                $contractDate = "contractDate";
        }

        $SQL = "
            INSERT INTO {$this->options->_dbName}.{$this->table} (id)
            SELECT t.id
            FROM {$this->options->_dbName}.{$this->table} t
            LEFT JOIN mhcdynad.mhc_sfids s
                ON s.sfid = t.sfid
                AND IF(t.{$contractDate} = '0000-00-00', monthYear, t.{$contractDate})
                    BETWEEN IFNULL(s.fromDate, '1900-1-1') AND IFNULL(s.toDate, '9999-1-1')
            LEFT JOIN mhcdynad.mhc_locations l
                ON l.id = s.instanceID
            WHERE t.accountID IS NULL
            ON DUPLICATE KEY
            UPDATE
                accountID = l.accountID,
                instanceID = s.instanceID,
                locationID = l.locationID,
                addedOn = NOW()
        ";

        try {
            $result = $this->_db->query($SQL);
        } catch (exception $e) {
            return "There was an error during tagging. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Tagged');
    }

    /**
     * This helper sets the isPDA flag
     *
     * @return type
     */
    public function tagPDA()
    {
        $this->addUpdateLog('', 0, 'Begin Tagging PDAs.');

        $SQL = "
            UPDATE {$this->options->_dbName}.{$this->table}
            SET isPDA = 1
            WHERE monthYear = :monthYear
                AND (
                    deviceCategory LIKE 'SMT%'
                    OR deviceCategory LIKE 'IPH'
                )
             -- AND visionCode NOT LIKE ('99999%')
            ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear,), array());
        } catch (exception $e) {
            return "There was an error during PDA tagging. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Tagging PDAs.');
    }

    /**
     * This helper sets the isMBB flag
     *
     * @return type
     */
    public function tagMBB()
    {
        $this->addUpdateLog('', 0, 'Begin Tagging MBBs.');

        $SQL = "
            UPDATE {$this->options->_dbName}.{$this->table}
            SET isMBB = 1
            WHERE monthYear = :monthYear
                AND deviceCategory LIKE 'DAT%'
             -- AND visionCode NOT LIKE '99999%'
            ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear,), array());
        } catch (exception $e) {
            return "There was an error during MBB tagging. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Tagging MBBs.');
    }

    /**
     * This helper sets the isTablet flag
     *
     * @return type
     */
    public function tagTablet()
    {
        $this->addUpdateLog('', 0, 'Begin Tagging Tablets.');

        $SQL = "
            UPDATE {$this->options->_dbName}.{$this->table}
            SET isTablet = 1
            WHERE monthYear = :monthYear
                AND deviceCategory REGEXP '^TAB|IPD'
             -- AND visionCode NOT LIKE '99999%'
            ";
        try {
            $result = $this->_db->query($SQL, array(':monthYear' => $this->monthYear,), array());
        } catch (exception $e) {
            return "There was an error during Tablet tagging. " . $e->getMessage();
        }

        $this->addUpdateLog('', $result->rowCount(), 'Finish Tagging Tablets.');
    }

    /**
     * This helper method imports the data into the DB from a file
     *
     * Each file has a different format.  Fun huh?
     *
     * TODO:  Update to the new data format
     *
     * @param  type $file
     * @return type
     */
    public function processFile($file)
    {
        $this->table = null;
        $set = null;
        $columns = null;
        $fileInfo = null;
        $this->monthYear = null;

        switch ($file) {
            case stripos($file, 'agent_spiffs.dat') !== false:
                //agent spiffs
                $this->table = self::AGENT_SPIFFS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    @phoneDescription,
                    visionCode,
                    @customerName,
                    @contractDate,
                    pricePlan,
                    contractLength,
                    transactionType,
                    spiffCode,
                    spiffAmount,
                    @spiffDescription
                  )
                ";
                $set = "
                    , phoneDescription = TRIM(BOTH '\"' FROM @phoneDescription)
                    , customerName = REPLACE(TRIM(BOTH '\"' FROM @customerName), ';', ',')
                    , contractDate = STR_TO_DATE(@contractDate, '%b %e %Y 12:00:00:000AM')
                    , spiffDescription = TRIM(BOTH '\r' FROM @spiffDescription)
                ";
                break;

            case stripos($file, 'amf.dat') !== false:
                //Residuals
                $this->table = self::RESIDUALS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @paidDate,
                    @contractDate,
                    @deactDate,
                    percent,
                    amount,
                    residual,
                    model
                  )
                ";
                $set = "
                    , customerName = REPLACE(TRIM(BOTH '\"' FROM @customerName), ';', ',')
                    , paidDate = STR_TO_DATE(@paidDate, '%b %e %Y 12:00:00:000AM')
                    , contractDate = STR_TO_DATE(@contractDate, '%b %e %Y 12:00:00:000AM')
                    , deactDate = IF(@deactDate = '', NULL, STR_TO_DATE(@deactDate, '%b %e %Y 12:00:00:000AM'))
                ";
                break;

            case stripos($file, 'coop_activations.dat') !== false:
                //coop acts
                $this->table = self::COOP_ACTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    phone,
                    deviceCategory,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @docDate,
                    contractLength,
                    commissionAmount
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , docDate = STR_TO_DATE(@docDate, '%m/%d/%Y')
                ";
                break;

            case stripos($file, 'coop_deactivations.dat') !== false:
                //coop deacts
                $this->table = self::COOP_DEACTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    phone,
                    deviceCategory,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @docDate,
                    @deactDate,
                    activationChargeback,
                    contractLength,
                    commissionAmount
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , docDate = STR_TO_DATE(@docDate, '%m/%d/%Y')
                    , deactDate = STR_TO_DATE(@deactDate, '%m/%d/%Y')
                ";
                break;

            case stripos($file, 'optional_service_activations.dat') !== false:
                //features
                $this->table = self::FEATURES;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    featureID,
                    @customerName,
                    @contractDate,
                    pricePlan,
                    commissionAmount,
                    spiff,
                    visionCode,
                    @planName,
                    phoneDescription
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , planName = TRIM(BOTH '\r' FROM @planName)
                ";
                break;

            case stripos($file, 'optional_service_chargebacks.dat') !== false:
                //feature chargebacks
                $this->table = self::FEATURES_CHARGEDBACK;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    featureID,
                    @customerName,
                    @contractDate,
                    @deactDate,
                    daysOfService,
                    pricePlan,
                    commissionAmount,
                    spiff,
                    visionCode,
                    @planName,
                    model
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , deactDate = STR_TO_DATE(@deactDate, '%m/%d/%Y')
                    , planName = TRIM(BOTH '\r' FROM @planName)
                ";
                break;

            case stripos($file, 'mobile_adjustments.dat') !== false:
                //mobile adjustments
                $this->table = self::ADJUSTMENTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    market,
                    adjustmentDescription,
                    adjustmentAmount,
                    paymentType,
                    model
                  )
                ";
                $set = null;
                break;

            case stripos($file, 'phone_upgd_activations.dat') !== false:
                //upgrades
                $this->table = self::UPGRADES;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @contractDate,
                    @new_esn_contractdate_start,
                    @new_esn_contractdate_end,
                    pricePlan,
                    @contractLength,
                    commissionAmount,
                    spiff,
                    zeroCommissionReasonCode,
                    upgradeType,
                    additionalCommission,
                    @phoneDescription,
                    alternatePhone,
                    @isVZprovidedEquipment,
                    @wasPreviouslyActivated,
                    @installmentContract,
                    purchasedReceivable,
                    edgeServiceFee
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , new_esn_contractdate_start = STR_TO_DATE(@new_esn_contractdate_start, '%m/%d/%Y')
                    , new_esn_contractdate_end = STR_TO_DATE(@new_esn_contractdate_end, '%m/%d/%Y')
                    , contractLength = IF((TRIM(@contractLength) = '0' OR TRIM(@contractLength) = '12') AND TRIM(@installmentContract) = 'Y', 24 , @contractLength)
                    , phoneDescription = @phoneDescription
                    , isVZprovidedEquipment = (SELECT TRIM(BOTH '\r' FROM @isVZprovidedEquipment) = 'Y')
                    , wasPreviouslyActivated = (SELECT TRIM(BOTH '\r' FROM @wasPreviouslyActivated) = 'Y')
                    , installmentContract = IF(TRIM(BOTH '\r' FROM @installmentContract) = 'Y', 1, 0)
                ";
                break;

            case stripos($file, 'phone_upgd_deactivations.dat') !== false:
                //upgrade deacts

                $this->table = self::UPGRADE_DEACTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @contractDate,
                    @deactDate,
                    @new_esn_contractdate_start,
                    @new_esn_contractdate_end,
                    pricePlan,
                    @contractLength,
                    commissionAmount,
                    spiff,
                    additionalCommission,
                    @phoneDescription,
                    alternatePhone,
                    @isVZprovidedEquipment,
                    @wasPreviouslyActivated,
                    @deviceReturned,
                    @installmentContract,
                    purchasedReceivable,
                    edgeServiceFee
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , deactDate = STR_TO_DATE(@deactDate, '%m/%d/%Y')
                    , new_esn_contractdate_start = STR_TO_DATE(@new_esn_contractdate_start, '%m/%d/%Y')
                    , new_esn_contractdate_end = STR_TO_DATE(@new_esn_contractdate_end, '%m/%d/%Y')
                    , contractLength = IF((TRIM(@contractLength) = '0' OR TRIM(@contractLength) = '12') AND TRIM(@installmentContract) = 'Y', 24 , @contractLength)
                    , phoneDescription = @phoneDescription
                    , isVZprovidedEquipment = (SELECT TRIM(BOTH '\r' FROM @isVZprovidedEquipment) = 'Y')
                    , wasPreviouslyActivated = (SELECT TRIM(BOTH '\r' FROM @wasPreviouslyActivated) = 'Y')
                    , deviceReturned = (SELECT TRIM(BOTH '\r' FROM @deviceReturned) = 'Y')
                    , installmentContract = (SELECT TRIM(BOTH '\r' FROM @installmentContract) = 'Y')
                ";
                break;

            case (stripos($file, 'activations.dat') !== false &&
                stripos($file, 'upgd') === false &&
                stripos($file, 'price_plan') === false &&
                stripos($file, 'reactivations') === false &&
                stripos($file, 'deactivations') === false &&
                stripos($file, 'coop') === false &&
                stripos($file, 'enh_service') === false
            ):

                //new activations

                $this->table = self::ACTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @contractDate,
                    pricePlan,
                    @contractLength,
                    commissionAmount,
                    spiff,
                    coop,
                    tierBonus,
                    additionalCommission,
                    @phoneDescription,
                    @isVZprovidedEquipment,
                    @wasPreviouslyActivated,
                    @countTowardsBonus,
                    @payBonus,
                    @installmentContract,
                    purchasedReceivable,
                    edgeServiceFee
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , contractLength = IF((TRIM(@contractLength) = '12' OR TRIM(@contractLength) = '24') AND TRIM(@installmentContract) = 'Y', 0 , @contractLength)
                    , phoneDescription = @phoneDescription
                    , isVZprovidedEquipment = (SELECT TRIM(BOTH '\r' FROM @isVZprovidedEquipment) = 'Y')
                    , wasPreviouslyActivated = (SELECT TRIM(BOTH '\r' FROM @wasPreviouslyActivated) = 'Y')
                    , countTowardsBonus = (SELECT TRIM(BOTH '\r' FROM @countTowardsBonus) = 'Y')
                    , payBonus = (SELECT TRIM(BOTH '\r' FROM @payBonus) = 'Y')
                    , installmentContract = IF(TRIM(BOTH '\r' FROM @installmentContract) = 'Y', 1, 0)
                ";
                break;

            case (stripos($file, 'chargebacks.dat') !== false &&
                stripos($file, 'upgd') === false &&
                stripos($file, 'price_plan') === false &&
                stripos($file, 'optional') === false &&
                stripos($file, 'enh_service') === false
            ):
                //deactivations

                $this->table = self::DEACTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @contractDate,
                    @deactDate,
                    daysOfService,
                    pricePlan,
                    @contractLength,
                    commissionAmount,
                    spiff,
                    coop,
                    tierBonus,
                    noChargeback,
                    additionalCommission,
                    @phoneDescription,
                    @isVZprovidedEquipment,
                    @wasPreviouslyActivated,
                    @countTowardsBonus,
                    @payBonus,
                    @deviceReturned,
                    @installmentContract,
                    purchasedReceivable,
                    edgeServiceFee
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , deactDate = STR_TO_DATE(@deactDate, '%m/%d/%Y')
                    , contractLength = IF((TRIM(@contractLength) = '12' OR TRIM(@contractLength) = '24') AND TRIM(@installmentContract) = 'Y', 0 , @contractLength)
                    , phoneDescription = @phoneDescription
                    , isVZprovidedEquipment = (SELECT TRIM(BOTH '\r' FROM @isVZprovidedEquipment) = 'Y')
                    , wasPreviouslyActivated = (SELECT TRIM(BOTH '\r' FROM @wasPreviouslyActivated) = 'Y')
                    , countTowardsBonus = (SELECT TRIM(BOTH '\r' FROM @countTowardsBonus) = 'Y')
                    , payBonus = (SELECT TRIM(BOTH '\r' FROM @payBonus) = 'Y')
                    , deviceReturned = (SELECT TRIM(BOTH '\r' FROM @deviceReturned) = 'Y')
                    , installmentContract = (SELECT TRIM(BOTH '\r' FROM @installmentContract) = 'Y')
                ";
                break;

            case stripos($file, 'reactivations.dat') !== false:
                /* reactivations */
                $this->table = self::REACTS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    visionCode,
                    @customerName,
                    @contractDate,
                    @deactDate,
                    @reactivationDate,
                    pricePlan,
                    @contractLength,
                    commissionAmount,
                    spiff,
                    coop,
                    tierBonus,
                    code,
                    description,
                    additionalCommission,
                    @phoneDescription,
                    @isVZprovidedEquipment,
                    @wasPreviouslyActivated,
                    @countTowardsBonus,
                    @payBonus,
                    @installmentContract,
                    purchasedReceivable,
                    edgeServiceFee
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , deactDate = STR_TO_DATE(@deactDate, '%m/%d/%Y')
                    , reactivationDate = STR_TO_DATE(@reactivationDate, '%m/%d/%Y')
                    , contractLength = IF((TRIM(@contractLength) = '12' OR TRIM(@contractLength) = '24') AND TRIM(@installmentContract) = 'Y', 0 , @contractLength)
                    , phoneDescription = @phoneDescription
                    , isVZprovidedEquipment = (SELECT TRIM(BOTH '\r' FROM @isVZprovidedEquipment)='Y')
                    , wasPreviouslyActivated = (SELECT TRIM(BOTH '\r' FROM @wasPreviouslyActivated)='Y')
                    , countTowardsBonus = (SELECT TRIM(BOTH '\r' FROM @countTowardsBonus)='Y')
                    , payBonus = (SELECT TRIM(BOTH '\r' FROM @payBonus)='Y')
                    , installmentContract = IF(TRIM(BOTH '\r' FROM @installmentContract) = 'Y', 1, 0)
                ";
                break;

            case stripos($file, 'security_deposits.dat') !== false:
                //deposits
                $this->table = self::DEPOSITS;
                $columns = "
                  (
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    originalPhone,
                    phone,
                    deviceCategory,
                    deviceID,
                    accountNumber,
                    @customerName,
                    @contractDate,
                    @depositDate,
                    @depositTerm,
                    depositAmount
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , contractDate = STR_TO_DATE(@contractDate, '%m/%d/%Y')
                    , depositDate = IF(@depositDate = '', NULL, STR_TO_DATE(@depositDate, '%m/%d/%Y'))
                    , depositTerm = IF(@depositTerm = '', NULL, @depositTerm)
                ";
                break;

            case stripos($file, 'transaction_changes.dat') !== false:
                //transaction changes
                $this->table = self::CHANGES;
                $columns = "
                  (
                    type,
                    system,
                    sfid,
                    company,
                    year,
                    month,
                    sfid2,
                    new_phone,
                    old_phone,
                    @customerName,
                    new_rate_plan,
                    old_rate_plan,
                    new_account_number,
                    old_account_number,
                    feature,
                    @feature_desc
                  )
                ";
                $set = "
                    , customerName = TRIM(BOTH '\"' FROM @customerName)
                    , feature_desc = TRIM(BOTH '\r' FROM @feature_desc)
                ";
                break;

            case stripos($file, 'summary_adjustments.dat') !== false:
                rename($file, $this->ccrsPathProcessed . DIRECTORY_SEPARATOR . basename($file));
                break;

            default:
                //move unknowns to processed folder
                rename($file, $this->ccrsPathProcessed . DIRECTORY_SEPARATOR . basename($file));
        }

        //if a table was found
        if ($this->table) {
            $fileInfo = $this->getFileInfo($file);

            //restrict the file to current or last month
            $this->monthYear = $fileInfo['year'].'-'.$fileInfo['month'].'-01';
            if ((!(floor((time() - strtotime($this->monthYear)) / 60 / 60 / 24 / 30) <= 2))) {
                return;
            }

            //remove the old
            $SQL = "
                DELETE
                FROM {$this->options->_dbName}.{$this->table}
                WHERE monthYear = STR_TO_DATE('{$fileInfo['year']}-{$fileInfo['month']}-1', '%Y-%c-%e')
                    AND (
                        verizonRegionID = {$fileInfo['verizonRegionID']}
                        OR verizonRegionID IS NULL
                    )
                ";

            try {
                $result = $this->_db->query($SQL);
            } catch (exception $e) {
                return "There was an error during {$this->table} table deletion. " . $e->getMessage();
            }

            $this->addUpdateLog($file, $result->rowCount(), "Deleted old records.");

            $databaseImportFile = $this->databaseImportDirectory . DIRECTORY_SEPARATOR . basename($file);
            copy($file, $databaseImportFile);

            if (!file_exists($databaseImportFile)) return 'Could not locate the .dat file to import - ' . $databaseImportFile;

            //add file data
            $SQL = "
                LOAD DATA INFILE '$databaseImportFile'
                INTO TABLE {$this->options->_dbName}.{$this->table}
                $columns
                SET
                    id = null,
                    addedOn = NOW(),
                    verizonRegionID = {$fileInfo['verizonRegionID']},
                    monthYear = STR_TO_DATE('{$fileInfo['year']}-{$fileInfo['month']}-1', '%Y-%c-%e')
                    $set
                ";

            try {
                $options['throw_exception'] = TRUE;
                $result = $this->_db->query($SQL, $options);
            } catch (PDOException $e) {
                $error = "Database Error: There was an error during {$this->table} INFILE Load. " . $e->getMessage();
            } catch (exception $e) {
                $error = "General Error: There was an error during {$this->table} INFILE Load. " . $e->getMessage();
            }

            unlink($databaseImportFile);
            if (isset($error)) {
                return $error;
            }

            $this->addUpdateLog($file, $result->rowCount(), 'Loaded new records.');

            $error = $this->tagTable();
            if ($error) return $error;

            // $this->addUpgradeType();

            switch ($this->table) {
                case self::ACTS:
                case self::DEACTS:
                case self::REACTS:
                case self::UPGRADES:
                case self::UPGRADE_DEACTS:

                    $error = $this->tagPDA();
                    if ($error) return $error();

                    $error = $this->tagMBB();
                    if ($error) return $error();

                    $error = $this->tagTablet();
                    if ($error) return $error();
            }
        }
    }

    private function normalizeTMPFeatures()
    {
        $query = "
    INSERT INTO {$this->options->_dbName}.ccrs_features (id)
        SELECT f.id
        FROM {$this->options->_dbName}.ccrs_features f
        LEFT JOIN {$this->options->_dbName}.ccrs_activations c1 ON (c1.phone = f.phone AND c1.monthYear = f.monthYear AND c1.visionCode NOT LIKE ('99999%'))
        LEFT JOIN {$this->options->_dbName}.ccrs_deactivations c2 ON (c2.phone = f.phone AND c2.monthYear = f.monthYear AND c2.visionCode NOT LIKE ('99999%'))
        LEFT JOIN {$this->options->_dbName}.ccrs_upgrades c3 ON (c3.phone = f.phone AND c3.monthYear = f.monthYear AND c3.visionCode NOT LIKE ('99999%'))
        LEFT JOIN {$this->options->_dbName}.ccrs_upgrade_deactivations c4 ON (c4.phone = f.phone AND c4.monthYear = f.monthYear AND c4.visionCode NOT LIKE ('99999%'))
        WHERE f.monthYear = '{$this->monthYear}' AND f.planName LIKE ('%TMP%')
        ON DUPLICATE KEY UPDATE
    deviceCategory =
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.deviceCategory,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.deviceCategory,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.deviceCategory,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.deviceCategory, NULL)))),
    deviceID =
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.deviceID,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.deviceID,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.deviceID,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.deviceID, NULL)))),
    accountNumber = COALESCE(
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.accountNumber,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.accountNumber,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.accountNumber,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.accountNumber, NULL)))), ''),
    customerName = COALESCE(
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.customerName,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.customerName,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.customerName,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.customerName, NULL)))), ''),
    contractDate = COALESCE(
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.contractDate,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.contractDate,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.contractDate,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.contractDate, NULL)))), '{$this->monthYear}'),
    contractLength = COALESCE(
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.contractLength,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.contractLength,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.contractLength,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.contractLength, NULL)))), 0),
    pricePlan = COALESCE(
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.pricePlan,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.pricePlan,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.pricePlan,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.pricePlan, NULL)))), 0),
    visionCode =
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.visionCode,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.visionCode,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.visionCode,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.visionCode, NULL)))),
    phoneDescription =
        IF (f.spiff >= 0 AND f.planName LIKE ('%ACTIVATION%'), c1.phoneDescription,
        IF (f.spiff < 0 AND f.planName LIKE ('%ACTIVATION%'), c2.phoneDescription,
        IF (f.spiff >= 0 AND f.planName LIKE ('%UPGRADE%'), c3.phoneDescription,
        IF (f.spiff < 0 AND f.planName LIKE ('%UPGRADE%'), c4.phoneDescription, NULL))))
        ";

        try {
            $result = $this->_db->query($query);
        } catch (exception $e) {
            return "There was an error during ccrs_features TMP normalization. " . $e->getMessage() . "<pre>" . $query;
        }

    }

}
