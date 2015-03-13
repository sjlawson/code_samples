<?php

namespace MHCCcrsManager\Presenters\Pages;

use PDO;
use MHCCcrsManager\Presenters\AbstractPresenter;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;

/**
 * "CCRS Manager" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-21
 */
class CcrsManagerPresenter extends AbstractPresenter
{
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
    public static function getDrupalMenuRouterPath()
    {
        return 'apps/accounting/ccrs/manager/list_buckets';
    }
    
    public function getCcrsFormDefaults() {
         $defaultParams = array();
         return array_merge($defaultParams, $this->getParameters);
    }
    
    /**
     *Will return an array of Drupal-formatted form option items
     */
    public function getCcrsBucketsOptionsArray()
    {
        $options = array();   
        $stmt = $this->dataAccessContainer['Table.Ccrs2.Buckets']->getBucketsOptionsArray();
        
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        	$options[$result['id']] = $result['fieldText'];
        }
        
        return $options;
    }
    
    /**
     *	Return bucket with table joins 
     *	@return array assoc
     */
    public function getCcrsBucketsBucketData($bucketId) {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.Buckets']->getBucketById($bucketId);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

/**
     * Returns an array of rows from bucket_commision_buckets (a.k.a. Bucket Receivables)
     * @access private
     * @param array $filters sent as $urlParams
     * @param array $limit['rowCount'] and $limit['offset']
     * @return array
     *
     * */
    private function getBucketReceivablesRecords(array $filters, array $limit)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->getBucketReceivablesRecords($filters, $limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
	/**
     * Grab column headers 
     * @return array
     */
    public function getBucketReceivablesTableHeader() 
    {
        return array( 
            'begDate' => 'Begin Date',
            'endDate' => 'End Date',
            'amount' => 'Amount',
            'adSpiff' => 'AD Spiff'
        );
    }
    
    /**
     * 
     * Build table rows for preview
     * @param array $urlParams
     * @param array $limit
     */
    public function getBucketReceivablesTableRows(array $urlParams, array $limit) 
    {
        $rows = array();
        $results = $this->getBucketReceivablesRecords($urlParams, $limit);
        
        if($results) {
            foreach($results as $result) {
            	// $editLink = $result['endDate'] == null ? '<a class="button" href="edit_receivable?commissionBucketID=' . $result['commissionBucketID'] . '" >EDIT</a>' : ' ';
                $rows[] = array(
                    'data' => array(
                        ( $result['begDate'] ? date('m/d/Y', strtotime($result['begDate'])) : 'none' ),
                        ( $result['endDate'] ? date('m/d/Y', strtotime($result['endDate'])) : 'none' ),
                        money_format('$%i', $result['amount']),
                        money_format('$%i', $result['adSpiff'])
                    )
                );
            }
        }
        return $rows;
    }
    
	/**
     * Grab column headers for preview
     *
     */
    public function getBucketPayablesTableHeader() 
    {
        return array(
            'payoutSchedule' => 'Payout Schedule',
            'begDate' => 'Begin Date',
            'endDate' => 'End Date',
            'amount' => 'Amount',
            'adSpiff' => 'AD Spiff',
            //'empSpiff' => 'Employee Spiff'
        );
    }

    /**
     * Returns an array of rows from bucket_commision_payout_buckets (a.k.a. Bucket Receivables)
     * @access private
     * @param array $filters sent as $urlParams
     * @param array $limit['rowCount'] and $limit['offset']
     * @return array
     *
     * */
    private function getBucketPayablesRecords(array $filters, array $limit)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->getBucketPayablesRecords($filters, $limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
	/**
	 * 
	 * load payables from BucketCommissionPayoutBuckets
	 * @param array $urlParams
	 * @param array $limit
	 * @return array['data'][array]
	 */
    public function getBucketPayablesTableRows(array $urlParams, array $limit) 
    {
        $rows = array();
        $results = $this->getBucketPayablesRecords($urlParams, $limit);
        
        if($results) {
            foreach($results as $result) {
            	// $editLink = $result['endDate'] == null ? '<a class="button" href="edit_payable?payoutBucketID=' . $result['payoutBucketID'] . '" >EDIT</a>' : ' ';
            	
                $rows[] = array(
                    'data' => array(
                        $result['payoutSchedule'],
                        ( $result['begDate'] ? date('m/d/Y', strtotime($result['begDate'])) : 'none' ),
                        ( $result['endDate'] ? date('m/d/Y', strtotime($result['endDate'])) : 'none' ),
                        money_format('$%i', $result['amount']),
                        money_format('$%i', $result['adSpiff']),
                        // $editLink
                        // money_format('$%i', $result['empSpiff']), // invisible for now, may add later
                    )
                );
            }
        }
        return $rows;
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function buildMasterCSV()
    {
		$fh = fopen( 'php://output', 'w' );
		
    	$bucketPDOStatement = $this->dataAccessContainer['Table.Ccrs2.Buckets']->getCSVData();
        $headerDisplayed = false;
        while($result = $bucketPDOStatement->fetch(PDO::FETCH_ASSOC)) {

        	if(!$headerDisplayed) {
	        	fputcsv($fh, array_keys($result));
	        	
	        	$headerDisplayed = true;
        	}
        	fputcsv($fh, $result);
        	
        }
        fclose($fh);
    	
    }
    
}
