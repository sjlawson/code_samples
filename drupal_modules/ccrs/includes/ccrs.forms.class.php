<?php

/**
 * class that holds the drupal forms
 */
class ccrsForms extends ccrs
{

    public $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function getSelectArray($data, $key, $value, $glue)
    {
        $array = array();

        if ($data) {
            foreach ($data as $thisData) {
                $thisValueList = array();

                foreach ($value as $thisValue) {
                    $thisValueList[] = $thisData[$thisValue];
                }

                $array[$thisData[$key]] = implode($glue, $thisValueList);
            }
        }

        return $array;
    }

    /**
     * Get rows for status fieldset and set warnings in case of update-in-progress or files-available
     * @return array
     */
    private function getCurrentStatus()
    {
        if (!$this->_db) {
            $this->setDB();
        }

        $warnings = array();
        $rows = array();
        if ($this->getSetting('isUpdating')) {
            $rows[] = array('data' => array(array('data' => 'CCRS is currently updating','colspan' => 2) ));
            $warnings[] = 'Update in progress';
        }

        $filesAvailable = $this->getAvailableFileCount();
        $rows[] = array('data' => array('Files ready for import', $filesAvailable) );
        if ($filesAvailable) {
            $warnings[] = $filesAvailable. ' files ready to import';
        }

        $startDateTime = $this->getSetting('updateStartTime');
        // $startDateTime = '2014-06-13 21:05:22';
        $endDateTime = $this->getSetting('updateEndTime');

        $rows[] = array('data' => array('Update started', $startDateTime ));

        if ($endDateTime < $startDateTime) {
            $rows[] = array('data' => array('Update finished', 'In progress ...'));
        } else {
            $rows[] = array('data' => array('Update finished', $endDateTime ));
        }

        return array('rows' => $rows, 'warnings' => $warnings);
    }

    /**
     * Get rows for most recent contract and added-on dates
     * @return array - formatted for Drupal table
     */
    private function getCurrentContractData()
    {
        $contractTables = array(
            array('tableName' => 'ccrs_activations', 'field' => 'contractDate'),
            array('tableName' => 'ccrs_deactivations', 'field' => 'deactDate'),
            array('tableName' => 'ccrs_upgrades', 'field' => 'contractDate'),
            array('tableName' => 'ccrs_upgrade_deactivations', 'field' => 'deactDate')
        );

        if (!$this->_db) {
            $this->setDB();
        }

        $dataObj = new ccrsData($this->options);
        $contractRows = array();
        $mostRecentDate = 0;

        foreach ($contractTables as $tableInfo) {
            $rowName = ucwords(str_replace('_',' ', str_replace('ccrs_','',$tableInfo['tableName'])));
            $rowData = $dataObj->getDatesForTable($tableInfo);
            $contractRows[] = array('data' => array($rowName, $rowData['dateAlias'], $rowData['addedOn']));
            $mostRecentDate = $mostRecentDate > $rowData['dateAlias'] ? $mostRecentDate : $rowData['dateAlias'];
        }

        $dateNow = new DateTime(date('Y-m-d'));
        $dateA = new DateTime($mostRecentDate);
        $warnings = array();
        if($dateNow->sub(new DateInterval('P3D')) > $dateA ) {
            $warnings[] = 'Latest contract is more than 3 days old';
        }

        $contractTable = array(
            'header' => array(
                'Table',
                'Contract/Deact Date',
                'Added On'
            ),
            'rows' => $contractRows,
            'warnings' => $warnings
        );

        return $contractTable;
    }

    /**
     * @return int - num of files present in ccrsPath
     */
    private function getAvailableFileCount()
    {
        $updaterObj = new ccrsUpdater($this->options);
        $files = array_merge(
            glob($updaterObj->ccrsPath . '/MOOREHEAD_NA_*.dat'),
            glob($updaterObj->ccrsPath . '/moorehead_na_*.dat')
        );

        return count($files);
    }

    public function getSearchForm()
    {
        // print_r($this->options);

        $f = new mhc_locations_forms();
        $l = new mhc_locations_data();
        $l->setAccountList();
        $l->setDivisionList();
        $l->setDistrictList();
        $l->setRegionList();
        $l->setLocationList();

        $contractTable = $this->getCurrentContractData();
        $currentStatus = $this->getCurrentStatus();
        $warnings = array_merge($contractTable['warnings'], $currentStatus['warnings']);
        $warningMarkup = '<span class="ccrs_data_warnings">' .
            implode(', ', $warnings)
            . '</span>';

        $statusFieldset = array(
            '#type' => 'fieldset',
            '#title' => t('Current Status') . (count($warnings) ? ': ' . $warningMarkup : ''),
            '#collapsible' => true,
            '#collapsed' => true,

            'current_ccrs_status_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => t('Process Status'),
                '#attributes' => array(
                    'class' => array('ccrs_process_status')
                ),

                'ccrs_settings' => array(
                    '#theme' => 'table',
                    '#rows' => $currentStatus['rows']
                ),
            ),

            'recent_update_data_fieldset' => array(
                '#type' => 'fieldset',
                '#title' => 'Latest Contract Dates',
                '#attributes' => array('class' => array('ccrs_recent_contract_dates')),

                'recent_update_data' => array(
                    '#theme' => 'table',
                    '#empty' => t('No results'),
                    '#header' => $contractTable['header'],
                    '#rows' => $contractTable['rows'],
                )
            )

        );

        $form['filter_form']['verticalTabs'] = array(
            '#type' => 'vertical_tabs',
            '#default_tab' => $this->options->type . '_fieldset'
        );

        $form['filter_form']['verticalTabs']['account_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'By Account',
            '#attributes' => array(
                'id' => 'account_fieldset'
            )
        );

        $form['filter_form']['verticalTabs']['account_fieldset']['account_select'] = array(
            '#title' => t('Account'),
            '#type' => 'select',
            '#options' => $f->getSelectArray($l->accountList, 'accountid', array('name', 'accountid'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => $this->options->locationValue,
            '#multiple' => true,
            '#size' => 7,
        );

        $form['filter_form']['verticalTabs']['division_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'By Division',
            '#attributes' => array(
                'id' => 'division_fieldset'
            )
        );

        $form['filter_form']['verticalTabs']['division_fieldset']['division_select'] = array(
            '#title' => t('Division'),
            '#type' => 'select',
            '#options' => $f->getSelectArray($l->divisionList, 'divisionid', array('type'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => $this->options->locationValue,
            '#multiple' => true,
            '#size' => 7,
        );

        $form['filter_form']['verticalTabs']['district_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'By District',
            '#attributes' => array(
                'id' => 'district_fieldset'
            )
        );

        $form['filter_form']['verticalTabs']['district_fieldset']['district_select'] = array(
            '#title' => t('District'),
            '#type' => 'select',
            '#options' => $f->getSelectArray($l->districtList, 'districtid', array('district', 'manager'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => $this->options->locationValue,
            '#multiple' => true,
            '#size' => 7,
        );

        $form['filter_form']['verticalTabs']['region_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'By Region',
            '#attributes' => array(
                'id' => 'region_fieldset'
            )
        );

        $form['filter_form']['verticalTabs']['region_fieldset']['region_select'] = array(
            '#title' => t('Region'),
            '#type' => 'select',
            '#options' => $f->getSelectArray($l->regionList, 'regionid', array('region', 'manager'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => $this->options->locationValue,
            '#multiple' => true,
            '#size' => 7,
        );

        $form['filter_form']['verticalTabs']['location_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'By Location',
            '#attributes' => array(
                'id' => 'location_fieldset'
            )
        );

        $form['filter_form']['verticalTabs']['location_fieldset']['location_select'] = array(
            '#title' => t('Location'),
            '#type' => 'select',
            '#options' => $f->getSelectArray($l->locationList, 'locationid', array('locationid', 'name', 'instanceid'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => $this->options->locationValue,
            '#multiple' => true,
            '#size' => 7,
        );

        $form['filter_form']['verticalTabs']['phonenumber_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'By Phone',
            '#attributes' => array(
                'id' => 'phonenumber_fieldset'
            )
        );

        $form['filter_form']['verticalTabs']['phonenumber_fieldset']['phonenumber_select'] = array(
            '#type' => 'textfield',
            '#title' => 'Phone Number',
            '#default_value' => $this->options->locationValue,
        );

        $form['filter_form']['dates_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'Dates',
            '#prefix' => "<div id='optionWrapperDiv'>",
        );

        $form['filter_form']['dates_fieldset']['beg_date'] = array(
            '#type' => 'date',
            '#title' => 'Beg Date',
            '#default_value' => array('month' => $this->options->begMonth, 'day' => 1, 'year' => $this->options->begYear),
        );

        $form['filter_form']['dates_fieldset']['end_date'] = array(
            '#type' => 'date',
            '#title' => 'End Date',
            '#default_value' => array('month' => $this->options->endMonth, 'day' => 1, 'year' => $this->options->endYear),
        );

        $form['filter_form']['table_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'Tables',
            '#suffix' => "</div>",
        );

        $form['filter_form']['table_fieldset']['table_select'] = array(
            '#type' => 'select',
            '#options' => array(
                self::ACTS => 'Activations',
                self::DEACTS => 'Deactivations',
                self::FEATURES => 'Features',
                self::FEATURES_CHARGEDBACK => 'Feature Chargebacks',
                self::REACTS => 'Reactivations',
                self::UPGRADES => 'Upgrades',
                self::UPGRADE_DEACTS => 'Upgrade Deacts',
                self::ADJUSTMENTS => 'Adjustments',
                self::DEPOSITS => 'Deposits',
                self::RESIDUALS => 'Residuals',
                self::COOP_ACTS => 'CO-OP',
                self::COOP_DEACTS => 'CO-OP Deactivations',
                self::CHANGES => 'Transaction Changes',
            ),
            '#empty_option' => '- Select -',
            '#default_value' => $this->options->tableValue,
            '#multiple' => true,
            '#size' => 13,
        );

        $form['filter_form']['submit'] = array(
            '#value' => t('Go'),
            '#type' => 'submit',
        );

        $filterFormFieldset = array(
            '#type' => 'fieldset',
            '#title' => t('Filters'),
            '#collapsible' => true,
            '#collapsed' => true,
            $form

        );

        return array($statusFieldset, $filterFormFieldset);
    }

    public function getSearchResultTable($searchByTable, $searchSummary)
    {
        global $base_url;

        $s = new scrubber();

        //create the table headers
        $header_array = array(
            array(
                'data' => 'Table',
                'colspan' => 2,
            ),
            array(
                'data' => 'Count',
            ),
            array(
                'data' => 'TBE',
            ),
            array(
                'data' => 'Edge',
            ),
            array(
                'data' => 'Commission',
            ),
            array(
                'data' => 'Spiff',
            ),
            array(
                'data' => 'Tier',
            ),
            array(
                'data' => 'Edge Rec.',
            ),
            array(
                'data' => 'Edge Fee',
            ),
            array(
                'data' => 'Receivable',
            ),
            array(
                'data' => 'Payable',
                'colspan' => 2,
            ),
            array(
                'data' => 'Net',
            ),
            array(
                'data' => 'Unknown Buckets'
            ),
            array(
                'data' => 'Orphan Deacts'
            ),
        );

        //setup the by table table
        $table['searchByTable'] = array(
            '#theme' => 'table',
            '#header' => $header_array,
            '#rows' => array(),
            '#attributes' => array(
                'id' => 'ccrs_search_table'
            ),
        );

        $tableCount = 0;

        //for proper linking
        $args = arg();
        array_shift($args);
        array_shift($args);
        array_shift($args);
        array_shift($args);
        array_shift($args);

        while ($row = $searchByTable->fetch(PDO::FETCH_OBJ)) {
            $args[6] = $this->options->tableValue[$tableCount];

            $new_row = array(
                array(
                    'data' => $this->options->tableValue[$tableCount],
                ),
                array(
                    'data' => "<a href='" . $base_url . "/apps/accounting/ccrs/export/detail/" . implode('/', $args) . "'>
                                   <img class='action_button' title='Export {$this->options->tableValue[$tableCount]} commissions' src='{$base_url}/" . drupal_get_path('module', 'ccrs') . "/images/save_icon.png'>
                               </a>",
                ),
                array(
                    'data' => $row->count,
                    'class' => (($row->count >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $row->tbe,
                    'class' => (($row->tbe >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $row->edges,
                    'class' => (($row->edges >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->commission, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->commission >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->spiff, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->spiff >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->tier, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->tier >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->purchasedReceivable, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->purchasedReceivable >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->edgeServiceFee, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->edgeServiceFee >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->receivableTotal, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->receivableTotal >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub((double) $row->payableTotal, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->payableTotal >= 0) ? 'positive' : 'negative')
                ),
                in_array($this->options->tableValue[$tableCount], array(self::COOP_ACTS, self::COOP_DEACTS, self::CHANGES, self::RESIDUALS, self::ADJUSTMENTS)) ? null :
                    array(
                        'data' => "<a href='" . $base_url . "/apps/accounting/ccrs/export/payout/" . implode('/', $args) . "'>
                                       <img class='action_button' title='Export {$this->options->tableValue[$tableCount]} payouts' src='{$base_url}/" . drupal_get_path('module', 'ccrs') . "/images/save_icon.png'>
                                   </a>",
                    ),
                array(
                    'data' => $s->scrub((double) $row->netTotal, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->netTotal >= 0) ? 'positive' : 'negative')
                ),
                array(
                    //Unknown Buckets
                    'data' => property_exists($row, 'unknownBucketCount') ? $row->unknownBucketCount : '',
                    'class' => (($row->unknownBucketCount > 0) ? 'negative' : 'positive')
                ),
                array(
                    //Orphan Deacts
                    'data' => property_exists($row, 'orphanDeactCount') ? $row->orphanDeactCount : '',
                    'class' => (($row->orphanDeactCount > 0) ? 'negative' : 'positive')
                ),
            );
            $table['searchByTable']['#rows'][] = $new_row;
            $tableCount++;
        }

        //Summary row at table base
        while ($row = $searchSummary->fetch(PDO::FETCH_OBJ)) {
            $new_row = array(
                array(
                    'data' => 'Summary',
                ),
                array(),
                array(
                    'data' => $row->count,
                    'class' => (($row->count >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $row->tbe,
                    'class' => (($row->tbe >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $row->edges,
                    'class' => (($row->edges >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->commission, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->commission >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->spiff, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->spiff >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->tier, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->tier >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->purchasedReceivable, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->purchasedReceivable >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->edgeServiceFee, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->edgeServiceFee >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->receivableTotal, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->receivableTotal >= 0) ? 'positive' : 'negative')
                ),
                array(
                    'data' => $s->scrub($row->payableTotal, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->payableTotal >= 0) ? 'positive' : 'negative')
                ),
                array(),
                array(
                    'data' => $s->scrub($row->netTotal, array(scrubber::MONEY_DISPLAY)),
                    'class' => (($row->netTotal >= 0) ? 'positive' : 'negative')
                ),
                array(
                    //Unknown Buckets
                ),
                array(
                    //Orphan Deacts
                ),

            );
            $table['searchByTable']['#rows'][] = $new_row;
        }

        return $table['searchByTable'];
    }

    public function getUpdaterForm($form_state)
    {
        $u = new ccrsUpdater($this->options);

        $checkWriteable = $u->checkWritable();
        if(strlen($checkWriteable)) {
            drupal_set_message($checkWriteable);
        }

        $form['updater_form']['update_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'CCRS Update',
            '#description' => count($u->files) == 0 ? 'VZW .DAT Files need to be dropped into O:\\accounting_downloads\\ccrs' : 'There are ' . count($u->files) . ' file(s) ' . ($u->isUpdating ? 'pending' : 'available for')  . ' update.',
        );

        $files = array();
        foreach ($u->files as $thisFile) {
            $files[] = basename($thisFile);
        }

        $form['updater_form']['update_fieldset']['files'] = array(
            '#markup' => implode('<br/>', $files),
            '#prefix' => '<div>',
            '#suffix' => '</div>',
        );

        $form['updater_form']['update_fieldset']['update_button'] = array(
            '#type' => 'submit',
            '#value' => 'Update',
            '#disabled' => ($u->isUpdating || count($u->files) == 0),
            '#suffix' => $u->isUpdating ? '<span class=submit-error>CCRS is currently updating...</span>' : '',
        );

        $cd = new ccrsData($this->options);
        $orphans = $cd->getOrphanSFIDs();

        if (!empty($orphans)) {
            $form['filter_form']['orphans_fieldset'] = array(
                '#type' => 'fieldset',
                '#title' => 'Orphan SFID(s)',
                '#collapsible' => true,
                '#collapsed' => true,
            );

            $form['filter_form']['orphans_fieldset']['orphans_div'] = array(
                '#markup' => implode(', ', $orphans),
            );
        }

        // $page['log_table']=$c->getLogUpdateTable($cd->updateLog);
        $form['filter_form']['log_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'Update Log',
            '#collapsible' => true,
        );

        $cd->getUpdateLog();
        $log = $this->getLogUpdateTable($cd->updateLog);
        $form['filter_form']['log_fieldset']['log_table'] = array(
            '#markup' => render($log),
        );

        return $form;
    }

    public function getLogUpdateTable($log)
    {
        $headers = array_keys($log->fetch(PDO::FETCH_ASSOC));
        $header_array = array(
            array('data' => 'File'),
            array('data' => 'Process'),
            array('data' => 'Records Affected'),
            array('data' => 'User Name'),
            array('data' => 'Date Added'),
        );

        //setup the table
        $table['log'] = array(
            '#theme' => 'table',
            '#header' => $header_array,
            '#rows' => array(),
            '#attributes' => array(
                'id' => 'ccrs_detail_table'
            ),
        );

        //add the other rows
        while ($row = $log->fetch(PDO::FETCH_ASSOC)) {
            $new_row = array();

            foreach ($headers as $key => $thisHeader) {
                $new_row[] = array(
                    'data' => $row[$thisHeader],
                );
            }

            $table['log']['#rows'][] = $new_row;
        }

        return $table['log'];
    }

    public function getHPCsettingsForm()
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setHPClist();

        $form['hpc_form']['hpc_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'Home Phone Connect Descriptions',
            '#collapsible' => true,
            '#collapsed' => true,
        );

        $form['hpc_form']['hpc_fieldset']['hpc_description_add'] = array(
            '#type' => 'textfield',
            '#title' => 'Add HPC CCRS VZW Description',
        );

        $counter = 0;
        if ($cd->hpcList) {
            while ($row = $cd->hpcList->fetch(PDO::FETCH_OBJ)) {
                $counter++;
                $form['hpc_form']['hpc_fieldset']['hpc_description_edit']['description_' . $counter] = array(
                    '#type' => 'textfield',
                    '#default_value' => $row->phonedescription,
                );
            }
        }

        $form['hpc_form']['hpc_fieldset']['hpc_description_count'] = array(
            '#type' => 'hidden',
            '#value' => $counter,
        );

        $form['hpc_form']['hpc_fieldset']['hpc_submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
        );

        return $form;
    }

    public function getHFsettingsForm()
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setHFlist();

        $form['hf_form']['hf_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'Home Fusion Descriptions',
            '#collapsible' => true,
            '#collapsed' => true,
        );

        $form['hf_form']['hf_fieldset']['hf_description_add'] = array(
            '#type' => 'textfield',
            '#title' => 'Add HF CCRS VZW Description',
        );

        $counter = 0;
        if ($cd->hfList) {
            while ($row = $cd->hfList->fetch(PDO::FETCH_OBJ)) {
                $counter++;
                $form['hf_form']['hf_fieldset']['hf_description_edit']['description_' . $counter] = array(
                    '#type' => 'textfield',
                    '#default_value' => $row->phonedescription,
                );
            }
        }

        $form['hf_form']['hf_fieldset']['hf_description_count'] = array(
            '#type' => 'hidden',
            '#value' => $counter,
        );

        $form['hf_form']['hf_fieldset']['hf_submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
        );

        return $form;
    }

    public function getMBBsettingsForm()
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setMBBlist();
        $cd->setMBBtypeList();
        $options = $this->getSelectArray($cd->mbbTypeList, 'mbbtypeid', array('type'), ' - ');

        $form['mbb_form']['mbb_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => 'MBB Descriptions',
            '#collapsible' => true,
            '#collapsed' => true,
        );

        $form['mbb_form']['mbb_fieldset']['mbb_description_add_description'] = array(
            '#type' => 'textfield',
            '#title' => 'Add MBB CCRS VZW Description',
            '#prefix' => '<div>',
        );

        $form['mbb_form']['mbb_fieldset']['mbb_description_add_select'] = array(
            '#type' => 'select',
            '#options' => $options,
            '#empty_option' => '- Select -',
            '#suffix' => '</div>',
        );

        $counter = 0;
        if ($cd->mbbList) {

            while ($row = $cd->mbbList->fetch(PDO::FETCH_OBJ)) {
                $counter++;
                $form['mbb_form']['mbb_fieldset']['mbb_description_edit']['description_' . $counter] = array(
                    '#type' => 'textfield',
                    '#default_value' => $row->phonedescription,
                    '#prefix' => '<div>',
                );

                $form['mbb_form']['mbb_fieldset']['mbb_description_edit']['select_' . $counter] = array(
                    '#type' => 'select',
                    '#options' => $options,
                    '#empty_option' => '- Select -',
                    '#default_value' => $row->mbbtypeid,
                    '#suffix' => '</div>',
                );
            }
        }

        $form['mbb_form']['mbb_fieldset']['mbb_description_count'] = array(
            '#type' => 'hidden',
            '#value' => $counter,
        );

        $form['mbb_form']['mbb_fieldset']['mbb_submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
        );

        return $form;
    }

    public function getCodeSettingsForm($table, $label)
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setCodelist($table);

        $form['code_form'][$table . '_code_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => $label,
            '#collapsible' => true,
            '#collapsed' => true,
            '#class' => array('vision_code')
        );

        $form['code_form'][$table . '_code_fieldset'][$table . '_add_code'] = array(
            '#type' => 'textfield',
            '#title' => "Add $label ",
            '#attributes' => array(
                'class' => array('vision_code')
            ),
        );

        $counter = 0;
        if ($cd->codeList) {

            while ($row = $cd->codeList->fetch(PDO::FETCH_OBJ)) {
                $counter++;
                $form['code_form'][$table . '_code_fieldset'][$table . '_edit_code'][$table . '_code' . $counter] = array(
                    '#type' => 'textfield',
                    '#default_value' => $row->visionCode,
                    '#attributes' => array(
                        'class' => array('vision_code')
                    ),
                );
            }
        }

        $form['code_form'][$table . '_code_fieldset'][$table . '_count'] = array(
            '#type' => 'hidden',
            '#value' => $counter,
        );

        $form['code_form'][$table . '_code_fieldset']['code_submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
        );

        return $form;
    }

    public function getPdaTierForm($form, &$form_state)
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setPdaList();

        $form['pda_form']['pda_select'] = array(
            '#title' => t('Select Phone'),
            '#type' => 'select',
            '#options' => $this->getSelectArray($cd->pdaList, 'phoneDescription', array('phoneDescription'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => (empty($form_state['values']['pda_select']) ? '' : $form_state['values']['pda_select']),
            '#ajax' => array(
                'callback' => 'pda_form_js',
                'wrapper' => 'edit_pda_wrapper_div',
                'effect' => 'fade',
                'method' => 'replace'
            ),
        );
        $cd->setPdaBucketList();

        //add
        $begDate = strtotime('next month');
        $endDate = null;

        $form['pda_form']['phone_fieldset_add'] = array(
            '#type' => 'fieldset',
            '#title' => 'Add New ',
        );

        $form['pda_form']['phone_fieldset_add']['pda_bucket_description_add'] = array(
            '#title' => 'CCRS Description',
            '#type' => 'textfield',
            '#default_value' => ((!empty($form_state['values']['pda_select'])) ? $form_state['values']['pda_select'] : null)
        );

        $form['pda_form']['phone_fieldset_add']['pda_bucket_select_add'] = array(
            '#title' => t('Select Bucket'),
            '#type' => 'select',
            '#options' => $this->getSelectArray($cd->pdaBucketList, 'id', array('description'), ' - '),
            '#empty_option' => '- Select -',
        );

        $form['pda_form']['phone_fieldset_add']['beg_date_add'] = array(
            '#title' => 'Beg Date',
            '#type' => 'date',
            '#default_value' => array('month' => date('n', $begDate), 'day' => 1, 'year' => date('Y', $begDate),),
        );

        $form['pda_form']['phone_fieldset_add']['end_date_add'] = array(
            '#title' => 'End Date',
            '#type' => 'date',
            '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
        );

        $form['pda_form']['phone_fieldset_add']['enable_end_date_add'] = array(
            '#title' => 'Enable End Date',
            '#type' => 'checkbox',
            '#default_value' => (($endDate) ? 1 : 0)
        );

        $form['pda_form']['phone_fieldset_add']['note_add'] = array(
            '#title' => 'Note',
            '#type' => 'textfield',
        );

        $ids = array();

        if (!empty($form_state['values']['pda_select'])) {
            $cd->setPdaTierList($form_state['values']['pda_select']);

            //edits
            foreach ($cd->pdaTierList as $key => $thisPhone) {
                $begDate = strtotime($thisPhone['begdate']);
                $endDate = strtotime($thisPhone['enddate']);

                $form['pda_form']['phone']['phone_fieldset_' . $thisPhone['id']] = array(
                    '#type' => 'fieldset',
                    '#title' => "Edit " . $form_state['values']['pda_select'] . " ({$thisPhone['id']})",
                );

                $form['pda_form']['phone']['phone_fieldset_' . $thisPhone['id']]['pda_bucket_select_' . $thisPhone['id']] = array(
                    '#title' => t('Select Bucket'),
                    '#type' => 'select',
                    '#options' => $this->getSelectArray($cd->pdaBucketList, 'id', array('description'), ' - '),
                    '#default_value' => $thisPhone['bucketid'],
                );

                $form['pda_form']['phone']['phone_fieldset_' . $thisPhone['id']]['beg_date_' . $thisPhone['id']] = array(
                    '#title' => 'Beg Date',
                    '#type' => 'date',
                    '#default_value' => array('month' => date('n', $begDate), 'day' => date('j', $begDate), 'year' => date('Y', $begDate),),
                );

                $form['pda_form']['phone']['phone_fieldset_' . $thisPhone['id']]['end_date_' . $thisPhone['id']] = array(
                    '#title' => 'End Date',
                    '#type' => 'date',
                    '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
                );

                $form['pda_form']['phone']['phone_fieldset_' . $thisPhone['id']]['enable_end_date_' . $thisPhone['id']] = array(
                    '#title' => 'Enable End Date',
                    '#type' => 'checkbox',
                    '#default_value' => (($thisPhone['enddate']) ? 1 : 0)
                );

                $form['pda_form']['phone']['phone_fieldset_' . $thisPhone['id']]['note_' . $thisPhone['id']] = array(
                    '#title' => 'Note',
                    '#type' => 'textfield',
                    '#default_value' => $thisPhone['note'],
                );
                $ids[] = $thisPhone['id'];
            }
        }

        $form['pda_form']['pda_ids'] = array(
            '#type' => 'hidden',
            '#value' => $ids,
        );

        $form['pda_form']['wrapper'] = array(
            '#markup' => "<div id='edit_pda_wrapper_div'></div>",
        );

        $form['pda_form']['submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
        );

        return $form;
    }

    public function getScheduleEditForm($form, &$form_state, $table, $filter, $prefix)
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setBucketList($table, $filter, $orderBy = 'description');

        $form['schedule_edit_form']['verticalTabs'] = array(
            '#type' => 'vertical_tabs',
        );

        foreach ($cd->bucketList as $thisBucket) {
            $begDate = strtotime($thisBucket['begDate']);
            $endDate = strtotime($thisBucket['endDate']);

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']] = array(
                '#type' => 'fieldset',
                '#title' => "{$thisBucket['description']} ({$prefix}{$thisBucket['id']})",
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['description_' . $thisBucket['id']] = array(
                '#title' => 'Description',
                '#type' => 'textfield',
                '#default_value' => $thisBucket['description'],
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['commission_' . $thisBucket['id']] = array(
                '#title' => 'Commission',
                '#type' => 'textfield',
                '#default_value' => $thisBucket['commission'],
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['beg_date_' . $thisBucket['id']] = array(
                '#title' => 'Beg Date',
                '#type' => 'date',
                '#default_value' => array('month' => date('n', $begDate), 'day' => date('j', $begDate), 'year' => date('Y', $begDate),),
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['end_date_' . $thisBucket['id']] = array(
                '#title' => 'End Date',
                '#type' => 'date',
                '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['enable_end_date_' . $thisBucket['id']] = array(
                '#title' => 'Enable End Date',
                '#type' => 'checkbox',
                '#default_value' => (($thisBucket['endDate']) ? 1 : 0)
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['contract_term_' . $thisBucket['id']] = array(
                '#title' => t('Select Bucket'),
                '#type' => 'select',
                '#options' => array(
                    '0' => 'M2M',
                    '12' => '12',
                    '24' => '24',
                ),
                '#default_value' => $thisBucket['contractLength']
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['beg_price_' . $thisBucket['id']] = array(
                '#title' => 'Price Plan Beg Price',
                '#type' => 'textfield',
                '#default_value' => $thisBucket['begPrice'],
            );

            $form['schedule_edit_form']['verticalTabs']['bucket_fieldset_' . $thisBucket['id']]['end_price_' . $thisBucket['id']] = array(
                '#title' => 'Price Plan End Price',
                '#type' => 'textfield',
                '#default_value' => $thisBucket['endPrice'],
            );

            $form['schedule_edit_form']['submit'] = array(
                '#type' => 'submit',
                '#value' => 'Save',
            );
        }

        return $form;
    }

    public function getPayoutScheduleEditForm($form_state, $vzwTable, $payoutTable, $prefix)
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setCommissionPayoutScheduleList();
        $cd->setBucketList($vzwTable, array(), 'description');

        $form['commission_payout_form']['schedule_select'] = array(
            '#title' => t('Select Schedule'),
            '#type' => 'select',
            '#options' => $this->getSelectArray($cd->commissionPayoutScheduleList, 'id', array('schedule'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => (empty($form_state['values']['schedule_select']) ? '' : $form_state['values']['schedule_select']),
            '#ajax' => array(
                'callback' => 'commission_payout_form_js',
                'wrapper' => 'edit_commission_payout_wrapper_div',
                'effect' => 'fade',
                'method' => 'replace'
            ),
        );

        $form['commission_payout_form']['bucket_select'] = array(
            '#title' => t('Select Bucket'),
            '#type' => 'select',
            '#options' => $this->getSelectArray($cd->bucketList, 'id', array('description'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => (empty($form_state['values']['bucket_select']) ? '' : $form_state['values']['bucket_select']),
            '#ajax' => array(
                'callback' => 'commission_payout_form_js',
                'wrapper' => 'edit_commission_payout_wrapper_div',
                'effect' => 'fade',
                'method' => 'replace'
            ),
        );

        $form['pda_form']['wrapper'] = array(
            '#markup' => "<div id='edit_commission_payout_wrapper_div'></div>",
        );

        if (!empty($form_state['values']['schedule_select']) && !empty($form_state['values']['bucket_select'])) {
            $cd->setCommissionPayoutSchedule($form_state['values']['schedule_select'], $form_state['values']['bucket_select'], $vzwTable, $payoutTable);

            $begDate = strtotime('next month');

            $form['commission_payout_form']['schedule']['bucket_fieldset_add'] = array(
                '#type' => 'fieldset',
                '#title' => "Add a New {$thisBucket['description']} Payout for this Bucket",
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['vzwcommission_add'] = array(
                '#type' => 'textfield',
                '#title' => "VZW Commission",
                '#default_value' => $cd->commissionPayoutSchedule[0]['vzwcommission'],
                '#disabled' => true
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['commission_add'] = array(
                '#type' => 'textfield',
                '#title' => "Commission",
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['beg_date_add'] = array(
                '#type' => 'date',
                '#title' => "Beg Date",
                '#default_value' => array('month' => date('n', $begDate), 'day' => date('j', $begDate), 'year' => date('Y', $begDate),),
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['end_date_add'] = array(
                '#type' => 'date',
                '#title' => "End Date",
                '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['enable_end_date_add'] = array(
                '#title' => 'Enable End Date',
                '#type' => 'checkbox',
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['submit_add'] = array(
                '#type' => 'submit',
                '#default_value' => 'Add'
            );

            $form['commission_payout_form']['schedule']['bucketID'] = array(
                '#type' => 'hidden',
                '#value' => $cd->commissionPayoutSchedule[0]['id']
            );

            $form['commission_payout_form']['schedule']['scheduleID'] = array(
                '#type' => 'hidden',
                '#value' => $form_state['values']['schedule_select']
            );

            foreach ($cd->commissionPayoutSchedule as $thisBucket) {

                $begDate = strtotime($thisBucket['begdate']);
                $endDate = strtotime($thisBucket['enddate']);

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']] = array(
                    '#type' => 'fieldset',
                    '#title' => "{$thisBucket['description']} ({$prefix}{$thisBucket['bucketid']})",
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['vzwcommission_' . $thisBucket['id']] = array(
                    '#type' => 'textfield',
                    '#title' => "VZW Commission",
                    '#default_value' => $thisBucket['vzwcommission'],
                    '#disabled' => true
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['commission_' . $thisBucket['id']] = array(
                    '#type' => 'textfield',
                    '#title' => "Commission",
                    '#default_value' => $thisBucket['commission'],
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['beg_date_' . $thisBucket['id']] = array(
                    '#type' => 'date',
                    '#title' => "Beg Date",
                    '#default_value' => array('month' => date('n', $begDate), 'day' => date('j', $begDate), 'year' => date('Y', $begDate),),
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['end_date_' . $thisBucket['id']] = array(
                    '#type' => 'date',
                    '#title' => "End Date",
                    '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['enable_end_date_' . $thisBucket['id']] = array(
                    '#title' => 'Enable End Date',
                    '#type' => 'checkbox',
                    '#default_value' => (($thisBucket['enddate']) ? 1 : 0)
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['submit_' . $thisBucket['id']] = array(
                    '#type' => 'submit',
                    '#default_value' => 'Save'
                );
            }
        }

        return $form;
    }

    public function getAdPayoutScheduleEditForm($form_state)
    {
        $cd = new ccrsData(new ccrsOptions(false));
        $cd->setAdCommissionPayoutScheduleList();
        $cd->setBucketList('estimator_advanced_device_buckets', $filter = array(), $orderBy = 'description');

        $form['commission_payout_form']['schedule_select'] = array(
            '#title' => t('Select Schedule'),
            '#type' => 'select',
            '#options' => $this->getSelectArray($cd->adCommissionPayoutScheduleList, 'id', array('schedule'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => (empty($form_state['values']['schedule_select']) ? '' : $form_state['values']['schedule_select']),
            '#ajax' => array(
                'callback' => 'commission_payout_form_js',
                'wrapper' => 'edit_commission_payout_wrapper_div',
                'effect' => 'fade',
                'method' => 'replace'
            ),
        );

        $form['commission_payout_form']['bucket_select'] = array(
            '#title' => t('Select Bucket'),
            '#type' => 'select',
            '#options' => $this->getSelectArray($cd->bucketList, 'id', array('description'), ' - '),
            '#empty_option' => '- Select -',
            '#default_value' => (empty($form_state['values']['bucket_select']) ? '' : $form_state['values']['bucket_select']),
            '#ajax' => array(
                'callback' => 'commission_payout_form_js',
                'wrapper' => 'edit_commission_payout_wrapper_div',
                'effect' => 'fade',
                'method' => 'replace'
            ),
        );

        $form['commission_payout_form']['wrapper'] = array(
            '#markup' => "<div id='edit_commission_payout_wrapper_div'></div>",
        );

        $begDate = strtotime('next month');

        if (!empty($form_state['values']['schedule_select']) && !empty($form_state['values']['bucket_select'])) {
            $cd->setAdCommissionPayoutSchedule($form_state['values']['schedule_select'], $form_state['values']['bucket_select']);

            $form['commission_payout_form']['schedule']['bucket_fieldset_add'] = array(
                '#type' => 'fieldset',
                '#title' => "Add a New Payout for this Bucket",
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['vzwcommission_add'] = array(
                '#type' => 'textfield',
                '#title' => "VZW Commission",
                '#default_value' => $cd->adCommissionPayoutSchedule[0]['vzwcommission'],
                '#disabled' => true
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['commission_add'] = array(
                '#type' => 'textfield',
                '#title' => "Commission",
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['beg_date_add'] = array(
                '#type' => 'date',
                '#title' => "Beg Date",
                '#default_value' => array('month' => date('n', $begDate), 'day' => date('j', $begDate), 'year' => date('Y', $begDate),),
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['end_date_add'] = array(
                '#type' => 'date',
                '#title' => "End Date",
                '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['enable_end_date_add'] = array(
                '#title' => 'Enable End Date',
                '#type' => 'checkbox',
            );

            $form['commission_payout_form']['schedule']['bucket_fieldset_add']['submit_add'] = array(
                '#type' => 'submit',
                '#default_value' => 'Add'
            );

            $form['commission_payout_form']['schedule']['bucketID'] = array(
                '#type' => 'hidden',
                '#value' => $cd->adCommissionPayoutSchedule[0]['id']
            );

            $form['commission_payout_form']['schedule']['scheduleID'] = array(
                '#type' => 'hidden',
                '#value' => $form_state['values']['schedule_select']
            );

            foreach ($cd->adCommissionPayoutSchedule as $thisBucket) {

                $begDate = strtotime($thisBucket['begdate']);
                $endDate = strtotime($thisBucket['enddate']);

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']] = array(
                    '#type' => 'fieldset',
                    '#title' => "{$thisBucket['description']} ({$prefix}{$thisBucket['bucketid']})",
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['vzwcommission_' . $thisBucket['id']] = array(
                    '#type' => 'textfield',
                    '#title' => "VZW Commission",
                    '#default_value' => $thisBucket['vzwcommission'],
                    '#disabled' => true
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['commission_' . $thisBucket['id']] = array(
                    '#type' => 'textfield',
                    '#title' => "Commission",
                    '#default_value' => $thisBucket['commission'],
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['beg_date_' . $thisBucket['id']] = array(
                    '#type' => 'date',
                    '#title' => "Beg Date",
                    '#default_value' => array('month' => date('n', $begDate), 'day' => date('j', $begDate), 'year' => date('Y', $begDate),),
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['end_date_' . $thisBucket['id']] = array(
                    '#type' => 'date',
                    '#title' => "End Date",
                    '#default_value' => array('month' => date('n', $endDate), 'day' => date('j', $endDate), 'year' => date('Y', $endDate),),
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['enable_end_date_' . $thisBucket['id']] = array(
                    '#title' => 'Enable End Date',
                    '#type' => 'checkbox',
                    '#default_value' => (($thisBucket['enddate']) ? 1 : 0)
                );

                $form['commission_payout_form']['schedule']['bucket_fieldset_' . $thisBucket['id']]['submit_' . $thisBucket['id']] = array(
                    '#type' => 'submit',
                    '#default_value' => 'Save'
                );
            }
        }

        return $form;
    }

}
