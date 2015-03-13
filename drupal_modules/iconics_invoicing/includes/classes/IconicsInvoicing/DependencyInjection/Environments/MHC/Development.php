<?php

namespace IconicsInvoicing\DependencyInjection\Environments\MHC;

use IconicsInvoicing\DataAccess\Database;
use IconicsInvoicing\DependencyInjection\Environments\AbstractEnvironment;
use IconicsInvoicing\Environments\Environments;

class Development extends AbstractEnvironment
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            Environments::DEVELOPMENT,
            'mooreheadcomm.com',
            'iconics_invoicing',
            'default',
            'dynamics',
            'invoicing'
        );

        // Database
        $this['Database'] = function ($container) {
            // Create database environment.
            $dbEnvironment = new Database\Configuration\Environment(
                $container['Environment.Name'],
                $container['Connection']
            );

            // Configure environment.
            $dbKeys = array(
                'warehouse',
                'mhcdynad',
            );
            foreach ($dbKeys as $key) {
                $database = $container['Database.' . $key];
                $dbEnvironment[$database->getCommonDatabaseName()] = $database;
            }

            return $dbEnvironment;
        };

        // Connection
        $this['Connection'] = function ($container) {
            return new Database\Connection($container['Connection.Info']);
        };

        $this['ConnectionMSSQL'] = function ($container) {
            return new Database\Connection($container['MSSQL.Connection.Info']);
        };

        $this['Connection.Info'] = function ($container) {
            return new Database\Configuration\Connections\DrupalDatabaseConnectionInformation(
                $container['Drupal.Connection.Name'],
                $container['Drupal.Connection.Target']
            );
        };

        $this['MSSQL.Connection.Info'] = function ($container) {
            return new Database\Configuration\Connections\DrupalDatabaseConnectionInformation(
                $container['MSSQL.Connection.Name'],
                $container['MSSQL.Connection.Target']
            );
        };

        //@ Databases
        $this['Database.warehouse'] = function ($container) {
            $db = new Database\Configuration\Database('warehouse', 'warehouse');

            //@ warehouse Table Names
            $db['_temp_rq4_ccpp_invoices'] = '_temp_rq4_ccpp_invoices';
            $db['_temp_rq4_dish_customers'] = '_temp_rq4_dish_customers';
            $db['_temp_rq4_mobile_device_invoices'] = '_temp_rq4_mobile_device_invoices';
            $db['_verizon_iconic_orders_and_dynamics_invoices'] = '_verizon_iconic_orders_and_dynamics_invoices';
            $db['brightpoint_invoices'] = 'brightpoint_invoices';
            $db['cust_apprec_dish'] = 'cust_apprec_dish';
            $db['cust_apprec_email_exclusion'] = 'cust_apprec_email_exclusion';
            $db['cust_apprec_email_queue'] = 'cust_apprec_email_queue';
            $db['cust_apprec_email_tracking'] = 'cust_apprec_email_tracking';
            $db['cust_apprec_mobile'] = 'cust_apprec_mobile';
            $db['cust_apprec_stages'] = 'cust_apprec_stages';
            $db['cust_apprec_types'] = 'cust_apprec_types';
            $db['edi_import_data_formats'] = 'edi_import_data_formats';
            $db['edi_import_data_formats_csv'] = 'edi_import_data_formats_csv';
            $db['edi_import_data_formats_xml'] = 'edi_import_data_formats_xml';
            $db['edi_import_database_insert_methods'] = 'edi_import_database_insert_methods';
            $db['edi_import_groups'] = 'edi_import_groups';
            $db['edi_import_groups_and_sources'] = 'edi_import_groups_and_sources';
            $db['edi_import_sources'] = 'edi_import_sources';
            $db['edi_import_sources_dev'] = 'edi_import_sources_dev';
            $db['edi_retrieval_sources'] = 'edi_retrieval_sources';
            $db['iconic_adjustments'] = 'iconic_adjustments';
            $db['reliance_invoices'] = 'reliance_invoices';
            $db['retailnext_conversion_rate_exclusions'] = 'retailnext_conversion_rate_exclusions';
            $db['retailnext_high_qty_products'] = 'retailnext_high_qty_products';
            $db['retailnext_line_item_info'] = 'retailnext_line_item_info';
            $db['retailnext_line_item_info_dev'] = 'retailnext_line_item_info_dev';
            $db['retailnext_transaction_info'] = 'retailnext_transaction_info';
            $db['retailnext_transaction_info_dev'] = 'retailnext_transaction_info_dev';
            $db['rq4_po_vendor_edi_log'] = 'rq4_po_vendor_edi_log';
            $db['rq_edi_order_items'] = 'rq_edi_order_items';
            $db['rq_edi_orders'] = 'rq_edi_orders';
            $db['ship_log'] = 'ship_log';
            $db['ship_log_deprec_schema'] = 'ship_log_deprec_schema';
            $db['ship_log_dev'] = 'ship_log_dev';
            $db['ship_log_shipping'] = 'ship_log_shipping';
            $db['ship_log_vendor_brightpoint'] = 'ship_log_vendor_brightpoint';
            $db['ship_log_vendor_brightpoint_dev'] = 'ship_log_vendor_brightpoint_dev';
            $db['ship_log_vendor_brightstar'] = 'ship_log_vendor_brightstar';
            $db['ship_log_vendor_brightstar_dev'] = 'ship_log_vendor_brightstar_dev';
            $db['ship_log_vendor_reliance'] = 'ship_log_vendor_reliance';
            $db['ship_log_vendor_reliance_dev'] = 'ship_log_vendor_reliance_dev';
            $db['shipping_carriers'] = 'shipping_carriers';
            $db['shipping_methods'] = 'shipping_methods';
            $db['ups_service_levels'] = 'ups_service_levels';
            $db['ups_service_types'] = 'ups_service_types';
            $db['vendor_stock'] = 'vendor_stock';
            $db['vendor_stock_log'] = 'vendor_stock_log';
            $db['vendor_stock_types'] = 'vendor_stock_types';
            $db['vendors'] = 'vendors';
            $db['verizon_iconic_orders'] = 'verizon_iconic_orders';
            $db['verizon_iconic_orders_and_dynamics_invoices'] = 'verizon_iconic_orders_and_dynamics_invoices';
            $db['verizon_iconic_orders_dev'] = 'verizon_iconic_orders_dev';
            $db['verizon_iconic_orders_status_codes'] = 'verizon_iconic_orders_status_codes';
            $db['verizon_item_codes_prices'] = 'verizon_item_codes_prices';
            $db['verizon_locations'] = 'verizon_locations';
            $db['verizon_sofie_dfill'] = 'verizon_sofie_dfill';
            $db['verizon_sofie_dfill_tracking'] = 'verizon_sofie_dfill_tracking';
            $db['verizon_sofie_esn'] = 'verizon_sofie_esn';
            $db['verizon_sofie_esn_tracking'] = 'verizon_sofie_esn_tracking';
            $db['verizon_sofie_locations'] = 'verizon_sofie_locations';

            return $db;
        };

        $this['Database.mhcdynad'] = function ($container) {
            $db = new Database\Configuration\Database('mhcdynad', 'mhcdynad');

            //@ mhcdynad Table Names
            $db['MHC_ship_log'] = 'MHC_ship_log';
            $db['actplan_buckets'] = 'actplan_buckets';
            $db['actplan_lookup'] = 'actplan_lookup';
            $db['actplans'] = 'actplans';
            $db['apple_inventory'] = 'apple_inventory';
            $db['apple_inventory_dev'] = 'apple_inventory_dev';
            $db['apple_inventory_hist'] = 'apple_inventory_hist';
            $db['apple_sales'] = 'apple_sales';
            $db['apple_sales_dev'] = 'apple_sales_dev';
            $db['ccrs_importer'] = 'ccrs_importer';
            $db['ccrs_monthYear'] = 'ccrs_monthYear';
            $db['ccrs_monthly_log'] = 'ccrs_monthly_log';
            $db['commission_dealer_rpt_groups'] = 'commission_dealer_rpt_groups';
            $db['commission_schedules'] = 'commission_schedules';
            $db['commission_schedules_test'] = 'commission_schedules_test';
            $db['datascape'] = 'datascape';
            $db['dealer_balance_updt'] = 'dealer_balance_updt';
            $db['dealer_balance_updt_dev'] = 'dealer_balance_updt_dev';
            $db['dealer_coop_activity'] = 'dealer_coop_activity';
            $db['dealer_coop_ledger'] = 'dealer_coop_ledger';
            $db['dealer_coop_ledger_starting_balance'] = 'dealer_coop_ledger_starting_balance';
            $db['dealer_credit'] = 'dealer_credit';
            $db['dealer_discrepancy'] = 'dealer_discrepancy';
            $db['dealer_ldap'] = 'dealer_ldap';
            $db['dealer_settings'] = 'dealer_settings';
            $db['doall_adjustments'] = 'doall_adjustments';
            $db['doall_ccppinvoices'] = 'doall_ccppinvoices';
            $db['doall_chris_comms'] = 'doall_chris_comms';
            $db['doall_commdata'] = 'doall_commdata';
            $db['doall_coupons'] = 'doall_coupons';
            $db['doall_details'] = 'doall_details';
            $db['doall_details_dev'] = 'doall_details_dev';
            $db['doall_history_check'] = 'doall_history_check';
            $db['doall_locnarray'] = 'doall_locnarray';
            $db['doall_payroll_check'] = 'doall_payroll_check';
            $db['doall_pto'] = 'doall_pto';
            $db['doall_punchclock'] = 'doall_punchclock';
            $db['doall_punchclock_hours'] = 'doall_punchclock_hours';
            $db['doall_punchclock_hours_rq'] = 'doall_punchclock_hours_rq';
            $db['doall_punchclock_revisions'] = 'doall_punchclock_revisions';
            $db['doall_receiving_adjustments'] = 'doall_receiving_adjustments';
            $db['doall_regionlist'] = 'doall_regionlist';
            $db['doall_regions'] = 'doall_regions';
            $db['doall_rq_comms'] = 'doall_rq_comms';
            $db['doall_rq_securityroles'] = 'doall_rq_securityroles';
            $db['doall_summary'] = 'doall_summary';
            $db['doall_summary_pool'] = 'doall_summary_pool';
            $db['doall_trueups'] = 'doall_trueups';
            $db['essbase_appended_locations_list'] = 'essbase_appended_locations_list';
            $db['essbasetest'] = 'essbasetest';
            $db['ipad_sales'] = 'ipad_sales';
            $db['ivitemad'] = 'ivitemad';
            $db['mhc_IT_alerts'] = 'mhc_IT_alerts';
            $db['mhc_accounting'] = 'mhc_accounting';
            $db['mhc_accounting_bankType'] = 'mhc_accounting_bankType';
            $db['mhc_accounting_bank_security'] = 'mhc_accounting_bank_security';
            $db['mhc_accounting_log'] = 'mhc_accounting_log';
            $db['mhc_analytics_OStype'] = 'mhc_analytics_OStype';
            $db['mhc_analytics_OSversion'] = 'mhc_analytics_OSversion';
            $db['mhc_analytics_browserType'] = 'mhc_analytics_browserType';
            $db['mhc_districts'] = 'mhc_districts';
            $db['mhc_divisions'] = 'mhc_divisions';
            $db['mhc_image_captcha'] = 'mhc_image_captcha';
            $db['mhc_locationType'] = 'mhc_locationType';
            $db['mhc_locations'] = 'mhc_locations';
            $db['mhc_locations_log'] = 'mhc_locations_log';
            $db['mhc_locations_sfid_log'] = 'mhc_locations_sfid_log';
            $db['mhc_locations_view'] = 'mhc_locations_view';
            $db['mhc_locations_view_openOnly'] = 'mhc_locations_view_openOnly';
            $db['mhc_maintenance'] = 'mhc_maintenance';
            $db['mhc_regions'] = 'mhc_regions';
            $db['mhc_sfids'] = 'mhc_sfids';
            $db['mhc_states'] = 'mhc_states';
            $db['mhc_subagent_account_type_history'] = 'mhc_subagent_account_type_history';
            $db['mhc_subagent_account_type_history_log'] = 'mhc_subagent_account_type_history_log';
            $db['mhc_subagent_account_types'] = 'mhc_subagent_account_types';
            $db['mhc_subagent_ads_schedule_history'] = 'mhc_subagent_ads_schedule_history';
            $db['mhc_subagent_ads_schedule_history_log'] = 'mhc_subagent_ads_schedule_history_log';
            $db['mhc_subagent_commission_schedule_history'] = 'mhc_subagent_commission_schedule_history';
            $db['mhc_subagent_commission_schedule_history_log'] = 'mhc_subagent_commission_schedule_history_log';
            $db['mhc_subagent_feature_schedule_history'] = 'mhc_subagent_feature_schedule_history';
            $db['mhc_subagent_feature_schedule_history_log'] = 'mhc_subagent_feature_schedule_history_log';
            $db['mhc_subagent_spiff_schedule_history'] = 'mhc_subagent_spiff_schedule_history';
            $db['mhc_subagent_spiff_schedule_history_log'] = 'mhc_subagent_spiff_schedule_history_log';
            $db['mhc_subagent_tier_attainment_schedule_history'] = 'mhc_subagent_tier_attainment_schedule_history';
            $db['mhc_subagent_tier_attainment_schedule_history_log'] = 'mhc_subagent_tier_attainment_schedule_history_log';
            $db['mhc_subagent_tier_bonus_schedule_history'] = 'mhc_subagent_tier_bonus_schedule_history';
            $db['mhc_subagent_tier_bonus_schedule_history_log'] = 'mhc_subagent_tier_bonus_schedule_history_log';
            $db['mhc_subagent_type_history'] = 'mhc_subagent_type_history';
            $db['mhc_subagent_type_history_log'] = 'mhc_subagent_type_history_log';
            $db['mhc_subagent_types'] = 'mhc_subagent_types';
            $db['mhc_subagents'] = 'mhc_subagents';
            $db['mhc_subagents_log'] = 'mhc_subagents_log';
            $db['mhc_trax'] = 'mhc_trax';
            $db['mhc_trax_copy'] = 'mhc_trax_copy';
            $db['mhc_verizon_districts'] = 'mhc_verizon_districts';
            $db['mhc_verizon_districts_and_regions'] = 'mhc_verizon_districts_and_regions';
            $db['mhc_verizon_districts_codes'] = 'mhc_verizon_districts_codes';
            $db['mhc_verizon_districts_old'] = 'mhc_verizon_districts_old';
            $db['mhc_verizon_locations'] = 'mhc_verizon_locations';
            $db['mhc_verizon_regions'] = 'mhc_verizon_regions';
            $db['order_emails'] = 'order_emails';
            $db['order_hist_batch_processing'] = 'order_hist_batch_processing';
            $db['order_items'] = 'order_items';
            $db['order_items_dev'] = 'order_items_dev';
            $db['order_stock_tracking'] = 'order_stock_tracking';
            $db['order_stock_tracking_dev'] = 'order_stock_tracking_dev';
            $db['orderhist'] = 'orderhist';
            $db['orderhist_dev'] = 'orderhist_dev';
            $db['ordersheet'] = 'ordersheet';
            $db['ordersheet_dev'] = 'ordersheet_dev';
            $db['ordersheet_pacs'] = 'ordersheet_pacs';
            $db['pos_activity'] = 'pos_activity';
            $db['pos_passwords'] = 'pos_passwords';
            $db['pricer_esl'] = 'pricer_esl';
            $db['pricer_esl_dev'] = 'pricer_esl_dev';
            $db['pricer_item'] = 'pricer_item';
            $db['pricer_item_dev'] = 'pricer_item_dev';
            $db['pricer_item_feature_list'] = 'pricer_item_feature_list';
            $db['pricer_item_features'] = 'pricer_item_features';
            $db['pricer_item_revisions'] = 'pricer_item_revisions';
            $db['pricer_item_revisions_copy'] = 'pricer_item_revisions_copy';
            $db['pricer_item_revisions_dev'] = 'pricer_item_revisions_dev';
            $db['pricer_log'] = 'pricer_log';
            $db['pricer_test_markets'] = 'pricer_test_markets';
            $db['purch_po'] = 'purch_po';
            $db['purch_poline'] = 'purch_poline';
            $db['purch_vendor'] = 'purch_vendor';
            $db['pyrl_emp_split'] = 'pyrl_emp_split';
            $db['referral_config'] = 'referral_config';
            $db['referral_leads'] = 'referral_leads';
            $db['referral_redemptions'] = 'referral_redemptions';
            $db['residual_dealer_hist'] = 'residual_dealer_hist';
            $db['residual_dealer_totals'] = 'residual_dealer_totals';
            $db['residual_dealer_totals_copy'] = 'residual_dealer_totals_copy';
            $db['residual_dealer_totals_hist'] = 'residual_dealer_totals_hist';
            $db['residual_dealer_totals_test'] = 'residual_dealer_totals_test';
            $db['residual_log'] = 'residual_log';
            $db['residual_retail_totals'] = 'residual_retail_totals';
            $db['residual_totals'] = 'residual_totals';
            $db['residual_verizon'] = 'residual_verizon';
            $db['resolver_activations'] = 'resolver_activations';
            $db['resolver_deactivations'] = 'resolver_deactivations';
            $db['resolver_enhactivations'] = 'resolver_enhactivations';
            $db['resolver_enhdeactivations'] = 'resolver_enhdeactivations';
            $db['resolver_upgdeactivations'] = 'resolver_upgdeactivations';
            $db['resolver_upgrades'] = 'resolver_upgrades';
            $db['rqgl'] = 'rqgl';
            $db['scorecard_adjustment_queue'] = 'scorecard_adjustment_queue';
            $db['scorecard_adjustment_rates'] = 'scorecard_adjustment_rates';
            $db['scorecard_caps'] = 'scorecard_caps';
            $db['scorecard_config'] = 'scorecard_config';
            $db['scorecard_daily_history'] = 'scorecard_daily_history';
            $db['scorecard_employees'] = 'scorecard_employees';
            $db['scorecard_goal_role_weights'] = 'scorecard_goal_role_weights';
            $db['scorecard_goals'] = 'scorecard_goals';
            $db['scorecard_headcount_queue'] = 'scorecard_headcount_queue';
            $db['scorecard_log'] = 'scorecard_log';
            $db['scorecard_weights'] = 'scorecard_weights';
            $db['sfid_activations'] = 'sfid_activations';
            $db['sfid_activity'] = 'sfid_activity';
            $db['sfid_deactivations'] = 'sfid_deactivations';
            $db['sfid_enhactivations'] = 'sfid_enhactivations';
            $db['sfid_enhdeactivations'] = 'sfid_enhdeactivations';
            $db['sfid_lookup'] = 'sfid_lookup';
            $db['sfid_upgradedeacts'] = 'sfid_upgradedeacts';
            $db['sfid_upgrades'] = 'sfid_upgrades';
            $db['ship_log'] = 'ship_log';
            $db['ship_log_dev'] = 'ship_log_dev';
            $db['sop_contract'] = 'sop_contract';
            $db['sopactad'] = 'sopactad';
            $db['sopactad_hist'] = 'sopactad_hist';
            $db['sopccpp'] = 'sopccpp';
            $db['sopsatad'] = 'sopsatad';
            $db['spiff'] = 'spiff';
            $db['subagent_type_history_view'] = 'subagent_type_history_view';
            $db['temp'] = 'temp';
            $db['test'] = 'test';
            $db['tierbonus'] = 'tierbonus';
            $db['vendor_stock'] = 'vendor_stock';
            $db['verizonxml'] = 'verizonxml';
            $db['verizonxml_copy'] = 'verizonxml_copy';
            $db['verizonxml_customertypes'] = 'verizonxml_customertypes';
            $db['verizonxml_incfeatures'] = 'verizonxml_incfeatures';
            $db['verizonxml_mandfeatures'] = 'verizonxml_mandfeatures';
            $db['verizonxml_orderrequest_types'] = 'verizonxml_orderrequest_types';
            $db['verizonxml_phonespecifics'] = 'verizonxml_phonespecifics';
            $db['verizonxml_selfeatures'] = 'verizonxml_selfeatures';
            $db['verizonxml_test'] = 'verizonxml_test';
            $db['vzexport'] = 'vzexport';
            $db['vzexport2'] = 'vzexport2';
            $db['warehouse_log'] = 'warehouse_log';
            $db['warehouse_reporting'] = 'warehouse_reporting';
            $db['warehouse_vendor'] = 'warehouse_vendor';
            $db['ws_sessions'] = 'ws_sessions';
            $db['ww_rptcategories'] = 'ww_rptcategories';
            $db['ww_serviceplangrouping'] = 'ww_serviceplangrouping';
            $db['xls_import'] = 'xls_import';
            $db['zipcodes'] = 'zipcodes';

            return $db;
        };
    }
}
