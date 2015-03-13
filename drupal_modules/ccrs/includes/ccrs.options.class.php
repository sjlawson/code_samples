<?php

/**
 * helper object to determine process flows
 */
class ccrsOptions extends ccrs
{

    public $type;
    public $locationValue;
    public $begMonth;
    public $begYear;
    public $endMonth;
    public $endYear;
    public $tableValue;
    public $_dbName;

    public function __construct($setOptions = true)
    {
        $args = arg();
        if ($setOptions) {
            $this->setOptions();
        }

        $this->setDB();
    }

    public function mungeValueToArray(&$value)
    {
        $value = explode('|', $value);
    }

    public function setDB()
    {
        global $base_url;

        $this->_dbName = 'ccrs2'; //(strpos($base_url, 'dev-internal') !== false) ? 'ccrs2_dev' : 'ccrs2';
    }

    public function setOptions()
    {
        //ini defaults
        $args = arg();

        //location
        if (empty($args[5])) {
            //default first time
            drupal_goto('apps/accounting/ccrs///account//' . date('n') . '/' . date('Y') . '/' . date('n') . '/' . date('Y') . '/ccrs_activations|ccrs_deactivations|ccrs_features|ccrs_feature_chargebacks|ccrs_reactivations|ccrs_upgrades|ccrs_upgrade_deactivations|ccrs_adjustments|ccrs_security_deposits|ccrs_residuals|ccrs_coop_activations|ccrs_coop_deactivations|ccrs_transaction_changes');
        } else {
            $this->type = $args[5];
            $this->locationValue = $args[6];
        }

        //dates
        $this->begMonth = $args[7];
        $this->begYear = $args[8];

        $this->endMonth = $args[9];
        $this->endYear = $args[10];

        //table value
        $this->tableValue = $args[11];

        $this->mungeValueToArray($this->locationValue);
        $this->mungeValueToArray($this->tableValue);
    }

}
