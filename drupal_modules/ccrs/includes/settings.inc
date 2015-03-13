<?php

function _ccrs_settings() {
    $page = array();
    // $page[] = drupal_get_form('_ccrs_bucket_settings_form');
    // $page[]=drupal_get_form('_ccrs_hpc_settings_form');
    // $page[]=drupal_get_form('_ccrs_hf_settings_form');
    // $page[]=drupal_get_form('_ccrs_mbb_settings_form');
    // $page[]=drupal_get_form('_ccrs_act_family_share_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_ups_family_share_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_act_hpc_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_ups_hpc_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_prepaid_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_acts_alp_limited_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_acts_ups_limited_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_acts_alp_unlimited_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_acts_ups_unlimited_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_acts_noncomm_code_settings_form');
    // $page[]=drupal_get_form('_ccrs_ups_noncomm_code_settings_form');

    return $page;
}

function _ccrs_bucket_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getBucketSettingsForm();
}

function _ccrs_hpc_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getHPCsettingsForm();
}

function _ccrs_hpc_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateHPC($form_state);

    if ($success)
        drupal_set_message('HPC List Saved.');
}

function _ccrs_hf_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getHFsettingsForm();
}

function _ccrs_hf_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateHF($form_state);

    if ($success)
        drupal_set_message('HF List Saved.');
}

function _ccrs_mbb_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getMBBsettingsForm();
}

function _ccrs_mbb_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateMBB($form_state);

    if ($success)
        drupal_set_message('MBB List Saved.');
}

function _ccrs_act_family_share_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_activation_family_share_vision_codes', 'New Act Family Share Vision Code');
}

function _ccrs_act_family_share_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_activation_family_share_vision_codes');

    if ($success)
        drupal_set_message('Act Family Share Codes Saved.');
}

function _ccrs_ups_family_share_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_upgrade_family_share_vision_codes', 'Upgrade Family Share Vision Code');
}

function _ccrs_ups_family_share_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_upgrade_family_share_vision_codes');

    if ($success)
        drupal_set_message('Upgrade Family Share Codes Saved.');
}

function _ccrs_act_hpc_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_activation_home_phone_connect_vision_codes', 'New Act HPC Vision Code');
}

function _ccrs_act_hpc_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_activation_home_phone_connect_vision_codes');

    if ($success)
        drupal_set_message('New Act HPC Codes Saved.');
}

function _ccrs_ups_hpc_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_upgrade_home_phone_connect_vision_codes', 'Upgrade HPC Vision Code');
}

function _ccrs_ups_hpc_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_upgrade_home_phone_connect_vision_codes');

    if ($success)
        drupal_set_message('Upgrade HPC Codes Saved.');
}

function _ccrs_prepaid_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_activation_prepaid_vision_codes', 'Prepaid Vision Code');
}

function _ccrs_prepaid_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_activation_prepaid_vision_codes');

    if ($success)
        drupal_set_message('Prepaid Codes Saved.');
}

function _ccrs_acts_alp_limited_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_activation_alp_limited_vision_codes', 'New Act ALP Limited Vision Code');
}

function _ccrs_acts_alp_limited_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_activation_alp_limited_vision_codes');

    if ($success)
        drupal_set_message('New Act ALP Limited Codes Saved.');
}

function _ccrs_acts_ups_limited_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_upgrade_alp_limited_vision_codes', 'Upgrade ALP Limited Vision Code');
}

function _ccrs_acts_ups_limited_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_upgrade_alp_limited_vision_codes');

    if ($success)
        drupal_set_message('Upgrade ALP Limited Codes Saved.');
}

function _ccrs_acts_alp_unlimited_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_activation_alp_unlimited_vision_codes', 'New Act ALP Unlimited Vision Code');
}

function _ccrs_acts_alp_unlimited_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_activation_alp_unlimited_vision_codes');

    if ($success)
        drupal_set_message('New Act ALP Unlimited Codes Saved.');
}

function _ccrs_acts_ups_unlimited_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_upgrade_alp_unlimited_vision_codes', 'Upgrade ALP Unlimited Vision Code');
}

function _ccrs_acts_ups_unlimited_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_upgrade_alp_unlimited_vision_codes');

    if ($success)
        drupal_set_message('Upgrade ALP Unlimited Codes Saved.');
}

function _ccrs_acts_noncomm_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_activation_non_commissonable_vision_codes', 'New Act Non-Comm Vision Code');
}

function _ccrs_acts_noncomm_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_activation_non_commissonable_vision_codes');

    if ($success)
        drupal_set_message('New Act Non-Comm Codes Saved.');
}

function _ccrs_ups_noncomm_code_settings_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getCodeSettingsForm('estimator_upgrade_non_commissonable_vision_codes', 'Upgrade Non-Comm Vision Code');
}

function _ccrs_ups_noncomm_code_settings_form_submit($form, &$form_state) {

    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);
    $success = $cd->updateCodes($form_state, 'estimator_upgrade_non_commissonable_vision_codes');

    if ($success)
        drupal_set_message('Upgrade Non-Comm Codes Saved.');
}

function _ccrs_settings_pda_tiers() {
    $page = array();
    $page[] = drupal_get_form('_ccrs_settings_pda_tiers_form');
    return $page;
}

function _ccrs_settings_pda_tiers_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getPdaTierForm($form, &$form_state);
}

function _ccrs_settings_pda_tiers_form_validate($form, &$form_state) {
    if (empty($form_state['values']['pda_select']) && empty($form_state['values']['pda_bucket_description_add'])) {
        form_set_error('pda_select', 'Please select a phone.');
    }

    if (!empty($form_state['values']['pda_bucket_description_add'])) {
        if (empty($form_state['values']['pda_bucket_select_add'])) {
            form_set_error('pda_bucket_description_add', 'Please select a bucket.');
        }
    }
}

function _ccrs_settings_pda_tiers_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    //if a new bucket is selected, save new bucket
    if ($form_state['values']['pda_bucket_description_add']) {
        $cd->savePDAbucket($form_state);
    }

    $cd->updatePDAbuckets($form_state);
    drupal_set_message('Saved.');
}

function pda_form_js($form, &$form_state) {

    $commands = array();
    $commands[] = ajax_command_html(NULL, render($form['pda_form']['phone']));

    return array(
        '#type' => 'ajax',
        '#commands' => $commands
    );
}

function _schedule_validate($form, &$form_state) {
    $bucketID = _get_bucket_from_tab($form_state);

    if ($form_state['values']['beg_price_' . $bucketID] >= $form_state['values']['end_price_' . $bucketID]) {
        form_set_error('beg_price_' . $bucketID, 'The beg price must be less than the end price.');
    }

    $begDate = "{$form_state['values']['beg_date_' . $bucketID]['year']}-{$form_state['values']['beg_date_' . $bucketID]['month']}-{$form_state['values']['beg_date_' . $bucketID]['day']}";
    $endDate = ($form_state['values']['enable_end_date_' . $bucketID]) ? "{$form_state['values']['end_date_' . $bucketID]['year']}-{$form_state['values']['end_date_' . $bucketID]['month']}-{$form_state['values']['end_date_' . $bucketID]['day']}" : null;

    if ($begDate >= $endDate && !is_null($endDate)) {
        form_set_error('beg_date_' . $bucketID, 'The beg date must be less than the end date.');
    }
}

function _get_bucket_from_tab($form_state) {
    $activeTab = $form_state['input']['verticalTabs__active_tab'];
    $bucketID = explode('-', $activeTab);
    $bucketID = $bucketID[3];
    return $bucketID;
}

//NA LLP editor
function _ccrs_settings_na_postpaid_llp_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isPostpaid', '!isFamilyShare'), 'NA');
}

function _ccrs_settings_na_postpaid_llp_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_postpaid_llp_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA LLP family share editor
function _ccrs_settings_na_postpaid_llp_fs_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isPostpaid', 'isFamilyShare'), 'NA');
}

function _ccrs_settings_na_postpaid_llp_fs_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_postpaid_llp_fs_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA HPC editor
function _ccrs_settings_na_hpc_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isHomePhoneConnect'), 'NA');
}

function _ccrs_settings_na_hpc_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_hpc_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA HF editor
function _ccrs_settings_na_hf_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isHomeFusion'), 'NA');
}

function _ccrs_settings_na_hf_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_hf_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA ALP Base editor
function _ccrs_settings_na_alp_base_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isALP', 'isALPbase'), 'NA');
}

function _ccrs_settings_na_alp_base_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_alp_base_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA ALP Limited editor
function _ccrs_settings_na_alp_limited_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isALP', 'isALPlimited'), 'NA');
}

function _ccrs_settings_na_alp_limited_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_alp_limited_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA ALP UnLimited editor
function _ccrs_settings_na_alp_unlimited_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isALP', '!isALPlimited'), 'NA');
}

function _ccrs_settings_na_alp_unlimited_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_alp_unlimited_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA iPhone editor
function _ccrs_settings_na_iphone_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isIphone', '!isFamilyShare'), 'NA');
}

function _ccrs_settings_na_iphone_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_iphone_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA iPhone fs editor
function _ccrs_settings_na_iphone_fs_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isIphone', 'isFamilyShare'), 'NA');
}

function _ccrs_settings_na_iphone_fs_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_iphone_fs_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA noncomm editor
function _ccrs_settings_na_noncomm_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isNonCommissionable'), 'NA');
}

function _ccrs_settings_na_noncomm_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_noncomm_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//NA prepaid editor
function _ccrs_settings_na_prepaid_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_activation_plans', $filter = array('isPrepaid'), 'NA');
}

function _ccrs_settings_na_prepaid_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_prepaid_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_activation_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS LLP editor
function _ccrs_settings_ups_postpaid_llp_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isPostpaid', '!isFamilyShare'), 'UP');
}

function _ccrs_settings_ups_postpaid_llp_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_postpaid_llp_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS LLP family share editor
function _ccrs_settings_ups_postpaid_llp_fs_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isPostpaid', 'isFamilyShare'), 'UP');
}

function _ccrs_settings_ups_postpaid_llp_fs_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_postpaid_llp_fs_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS HPC editor
function _ccrs_settings_ups_hpc_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isHomePhoneConnect'), 'UP');
}

function _ccrs_settings_ups_hpc_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_hpc_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS ALP Base editor
function _ccrs_settings_ups_alp_base_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isALP', 'isALPbase'), 'UP');
}

function _ccrs_settings_ups_alp_base_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_alp_base_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS ALP Limited editor
function _ccrs_settings_ups_alp_limited_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isALP', 'isALPlimited'), 'UP');
}

function _ccrs_settings_ups_alp_limited_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_alp_limited_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS ALP UnLimited editor
function _ccrs_settings_ups_alp_unlimited_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isALP', '!isALPlimited'), 'UP');
}

function _ccrs_settings_ups_alp_unlimited_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_alp_unlimited_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS iPhone editor
function _ccrs_settings_ups_iphone_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isIphone', '!isFamilyShare'), 'UP');
}

function _ccrs_settings_ups_iphone_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_iphone_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS iPhone fs editor
function _ccrs_settings_ups_iphone_fs_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isIphone', 'isFamilyShare'), 'UP');
}

function _ccrs_settings_ups_iphone_fs_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_iphone_fs_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

//UPS noncomm editor
function _ccrs_settings_ups_noncomm_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getScheduleEditForm($form, &$form_state, 'estimator_upgrade_plans', $filter = array('isNonCommissionable'), 'UP');
}

function _ccrs_settings_ups_noncomm_schedule_edit_form_validate($form, &$form_state) {
    _schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_noncomm_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $cd->saveBucket($form_state, 'estimator_upgrade_plans', _get_bucket_from_tab($form_state));
    drupal_set_message('Saved');
}

function _get_payout_ID($form_state) {
    $payoutID = $form_state['clicked_button']['#id'];
    $payoutID = explode('-', $payoutID);
    $payoutID = $payoutID[2];
    return $payoutID;
}

function _payout_schedule_validate($form, &$form_state) {
    $payoutID = _get_payout_ID($form_state);

    $begDate = "{$form_state['values']['beg_date_' . $payoutID]['year']}-{$form_state['values']['beg_date_' . $payoutID]['month']}-{$form_state['values']['beg_date_' . $payoutID]['day']}";
    $endDate = ($form_state['values']['enable_end_date_' . $payoutID]) ? "{$form_state['values']['end_date_' . $payoutID]['year']}-{$form_state['values']['end_date_' . $payoutID]['month']}-{$form_state['values']['end_date_' . $payoutID]['day']}" : null;

    if ($begDate >= $endDate && !is_null($endDate)) {
        form_set_error('beg_date_' . $payoutID, 'The beg date must be less than the end date.');
    }

    if (!is_numeric($form_state['values']['commission_' . $payoutID])) {
        form_set_error('commission_' . $payoutID, 'Please enter a commission payout.');
    }
}

function commission_payout_form_js($form, &$form_state) {

    $commands = array();
    $commands[] = ajax_command_html(NULL, render($form['commission_payout_form']['schedule']));

    return array(
        '#type' => 'ajax',
        '#commands' => $commands
    );
}

function _ccrs_settings_na_payout_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getPayoutScheduleEditForm($form_state, 'estimator_activation_plans', 'estimator_activation_commissions', 'NA');
}

function _ccrs_settings_na_payout_schedule_edit_form_validate($form, &$form_state) {
    _payout_schedule_validate($form, &$form_state);
}

function _ccrs_settings_na_payout_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $payoutID = _get_payout_ID($form_state);

    if ($payoutID == 'add') {
        $cd->addCommissionPayoutBucket($form_state, 'estimator_activation_commissions');
        drupal_set_message('Added');
    } else {
        $cd->saveCommissionPayoutBucket($form_state, 'estimator_activation_commissions', $payoutID);
        drupal_set_message('Saved');
    }
}

function _ccrs_settings_ups_payout_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getPayoutScheduleEditForm($form_state, 'estimator_upgrade_plans', 'estimator_upgrade_commissions', 'UP');
}

function _ccrs_settings_ups_payout_schedule_edit_form_validate($form, &$form_state) {
    _payout_schedule_validate($form, &$form_state);
}

function _ccrs_settings_ups_payout_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $payoutID = _get_payout_ID($form_state);

    if ($payoutID == 'add') {
        $cd->addCommissionPayoutBucket($form_state, 'estimator_upgrade_commissions');
        drupal_set_message('Added');
    } else {
        $cd->saveCommissionPayoutBucket($form_state, 'estimator_upgrade_commissions', $payoutID);
        drupal_set_message('Saved');
    }
}

function _ccrs_settings_ad_payout_schedule_edit_form($form, &$form_state) {
    $options = new ccrsOptions(false);
    $c = new ccrsForms($options);
    return $c->getAdPayoutScheduleEditForm($form_state);
}

function _ccrs_settings_ad_payout_schedule_edit_form_validate($form, &$form_state) {
    _payout_schedule_validate($form, &$form_state);
}

function _ccrs_settings_ad_payout_schedule_edit_form_submit($form, &$form_state) {
    $options = new ccrsOptions(false);
    $cd = new ccrsData($options);

    $payoutID = _get_payout_ID($form_state);

    if ($payoutID == 'add') {
        $cd->addAdCommissionPayoutBucket($form_state);
        drupal_set_message('Added');
    } else {
        $cd->saveAdCommissionPayoutBucket($form_state, $payoutID);
        drupal_set_message('Saved');
    }
}
