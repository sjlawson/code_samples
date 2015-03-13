<?php

/**
 * Abstract class that all others inherit off of in order to have common properties and constants.
 */
abstract class ccrs
{

    const MAX_FILE_AGE = 300; //in seconds
    //
    const ACTS = 'ccrs_activations';
    const DEACTS = 'ccrs_deactivations';
    const ACTS_CHARGEDBACK = 'ccrs_activation_chargebacks';
    const FEATURES = 'ccrs_features';
    const FEATURES_CHARGEDBACK = 'ccrs_feature_chargebacks';
    const REACTS = 'ccrs_reactivations';
    const UPGRADES = 'ccrs_upgrades';
    const UPGRADE_DEACTS = 'ccrs_upgrade_deactivations';
    const ADJUSTMENTS = 'ccrs_adjustments';
    const DEPOSITS = 'ccrs_security_deposits';
    const RESIDUALS = 'ccrs_residuals';
    const COOP_ACTS = 'ccrs_coop_activations';
    const COOP_DEACTS = 'ccrs_coop_deactivations';
    const CHANGES = 'ccrs_transaction_changes';
    const AGENT_SPIFFS = 'ccrs_agent_spiffs';
    //
    //not really needed anymore, but kept for historical reasons.
    const NATIONAL = 5;
    //
    //column type ids - from rq4_recon_column_types table
    const NEWACTSPIFF = 1;
    const NEWACTCOOP = 2;
    const NEWACTADDITIONALCOMMISSION = 3;
    const NEWACTCOMMISSION = 4;
    const TIERBONUS = 5;
    const UPGRADECOMMISSION = 6;
    const FEATURECOMMISSION = 7;
    const FEATURESPIFF = 8;
    const UPGRADESPIFF = 9;
    const UPGRADEADDITIONALCOMMISSION = 10;
    const DEACTSPIFF = 11;
    const DEACTCOOP = 12;
    const DEACTADDITIONALCOMMISSION = 13;
    const DEACTCOMMISSION = 14;
    const DEACTTIERBONUS = 15;
    const UPGRADEDEACTCOMMISSION = 16;
    const UPGRADEDEACTSPIFF = 17;
    const UPGRADEDEACTADDITIONALCOMMISSION = 18;
    const FEATUREDEACTCOMMISSION = 19;
    const FEATUREDEACTSPIFF = 20;
    const DEPOSITAMOUNT = 21;
    const ADJUSTMENTCOMMISSION = 22;
    const REACTCOMMISSION = 23;
    const REACTSPIFF = 24;
    const REACTCOOP = 25;
    const REACTADDITIONALCOMMISSION = 26;
    const REACTTIERBONUS = 27;
    const COOPCOMMISSION = 28;
    const COOPDEACTCOMMISSION = 29;
    const FIOSADJUSTMENT = 30;
    const OEADJUSTMENT = 31;
    const REFERRAL = 32;
    const NEWACTEMPLOYEECOMMISSION = 33;
    const UPGRADEEMPLOYEECOMMISSION = 34;
    const DEACTEMPLOYEECOMMISSION = 35;
    const UPGRADEDEACTEMPLOYEECOMMISSION = 36;
    const REACTEMPLOYEECOMMISSION = 37;
    //
    const NORMAL = 1;
    const EXCHANGES = 2;
    const RETURNS = 3;
    //
    const IPHONEACCRUAL = 1;
    const TIERBONUSACCRUAL = 2;
    const CHARGEBACKPLUS60 = 3;
    //
    //contract type ids
    const IPHONE = 1;
    const ALPLIMITED = 2;
    const LLP = 3;
    const LLPFAMILYSHARE = 4;
    const PREPAID = 5;
    const HPC = 6;
    const NONCOMM = 7;
    const LLPNE2 = 8;
    const ALPNE2UNLIMITED = 9;
    const ALPUNLIMITED = 10;
    const IPHONEFAMILYSHARE = 11;
    const ALPNE2LIMITED = 12;
    const LLPNE2FAMILYSHARE = 13;
    const ALPBASE = 14;
    const FEATURE = 15;
    const HF = 16;
    const BASIC = 17;
    const SMARTPHONE = 18;
    const DEDICATEDMHS = 19;
    const TABLET = 20;
    const IPAD = 21;
    const CUSTOMERPROVIDED = 22;
    //
    //subagent account types
    const PLATINUM = 2;
    const PLATINUM_AMOUNT = 15;
    //
    //device types
    const AIRCARDDEVICE = 1;
    const MIFIDEVICE = 2;
    const NETBOOKDEVICE = 3;
    const SMARTPHONEDEVICE = 4;
    const TABLETDEVICE = 5;
    const IPADDEVICE = 6;
    const IPHONEDEVICE = 7;
    const BASICDEVICE = 8;
    const HPCDEVICE = 9;
    const HFDEVICE = 10;
    //
    //act types
    const NEWACTTYPE = 1;
    const UPGRADETYPE = 2;
    const PREPAIDTYPE = 3;

    public $_db;
    public $databaseExportDirectory = '/var/httpd/files/sqlexports';
    public $databaseImportDirectory = '/var/httpd/files/sqlexports';
    public $destDirectory = 'private://ccrs';
    public $options;
    public $table;

    /**
     * Sets the db
     */
    public function setDB()
    {
        $this->_db = Database::getConnection('default', 'mhcdynad');
    }

    /**
     * Add info to the log
     *
     * @global type $user
     * @param type $filename
     * @param type $recordsAffected
     * @param type $comment
     */
    public function addUpdateLog($filename, $recordsAffected, $comment = null)
    {
        global $user;
        $username = ($user->name) ? $user->name : 'System';
        $filename = basename($filename);

        $SQL = "
            INSERT
            INTO {$this->options->_dbName}.ccrs_update_log
            (id, tn, filename, recordsAffected, comment, username, addedOn)
            VALUES
            (NULL, '{$this->table}', '$filename', '$recordsAffected', '$comment', '$username', NOW())
        ";
        $this->_db->query($SQL);
    }

    /**
     * gets a particular setting
     *
     * @param  type $setting
     * @return type
     */
    public function getSetting($setting)
    {
        $SQL = "
            SELECT value
            FROM {$this->options->_dbName}.ccrs_settings
            WHERE setting = :setting
        ";
        $result = $this->_db->query($SQL, array(
            ':setting' => $setting
            ), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );
        $result = $result->fetchAll();

        return $result[0]['value'];
    }

    /**
     * sets a particular setting
     *
     * @param type $setting
     * @param type $value
     */
    public function setSetting($setting, $value)
    {
        $SQL = "
            UPDATE {$this->options->_dbName}.ccrs_settings
            SET value = :value
            WHERE setting = :setting
        ";
        $result = $this->_db->query($SQL, array(
            ':setting' => $setting,
            ':value' => $value
            ), array(
            'fetch' => PDO::FETCH_ASSOC
            )
        );
    }

}
