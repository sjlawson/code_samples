<?php

namespace MHCCcrsManager\Presenters\Pages;

use PDO;
use MHCCcrsManager\Presenters\AbstractPresenter;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;

/**
 * "editbucket" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-26
 */
class EditbucketPresenter extends AbstractPresenter
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
        return 'apps/accounting/ccrs/manager/edit_bucket';
    }

    /**
     *
     * @return array
     */
    public function getCcrsBucketsBucketData($bucketID)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.Buckets']->getBucketById($bucketID);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     *
     * @return array
     */
    public function getBucketFormDefaults()
    {
        $defaultParams = array(); // wip

         return array_merge($defaultParams, $this->getParameters);
    }

    /**
     * Will return an array of Drupal-formatted form option items
     * @return array
     */
    public function getBucketCategoriesOptionsArray()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCategories']->getBucketCategories();

        while($bucketCategoryData = $stmt->fetch(PDO::FETCH_ASSOC)) {
             $options[$bucketCategoryData['bucketCategoryID']] = $bucketCategoryData['description'];
        }

        return $options;
    }

    /**
     * Will return an array of Drupal-formatted form option items
     * @return array
     */
    public function getActivationTypesOptionsArray()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketActTypes']->getBucketActTypes();

        while($activationTypesData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$activationTypesData['actTypeID']] = $activationTypesData['type'];
        }

        return $options;
    }

    /**
     * Retrieve PDOStatement to get all ContractTypes, return array formatted for drupal selection form control
     * @return array
     */
    public function getBucketContractTypesOptionsArray()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketContractTypes']->getBucketContractTypes();

        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
             $options[$result['contractTypeID']] = $result['description'];
        }

        return $options;
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
            'adSpiff' => 'Adv Dev Spiff',
            ' ' => ' '
        );
    }

    public function getBucketReceivablesTableRows(array $urlParams, array $limit)
    {
        $rows = array();
        $results = $this->getBucketReceivablesRecords($urlParams, $limit);

        if($results) {
            foreach($results as $result) {
                $jsWarn = ( strtotime('now') - strtotime($result['begDate']) > 2592000 ) ? ' datewarn' : '' ;

                $editLink = $result['endDate'] == null ? '<a class="button'.$jsWarn.'" href="edit_receivable?commissionBucketID='
                                                        . $result['commissionBucketID'] . '" >EDIT</a>'
                                                        : ' ';

                $rows[] = array(
                    'data' => array(
                        ( $result['begDate'] ? date('m/d/Y', strtotime($result['begDate'])) : 'none' ),
                        ( $result['endDate'] ? date('m/d/Y', strtotime($result['endDate'])) : 'none' ),
                        money_format('$%i', $result['amount']),
                        money_format('$%i', $result['adSpiff']),
                        $editLink
                    )
                );
            }
        }

        return $rows;
    }

    /**
     * Get number of receivables for bucket
     * @access public
     * @param array $filters - contains bucketID
     * @return integer
     */
    public function getBucketReceivablesCount(array $filters)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->getBucketReceivablesCount($filters);
        return $stmt->fetchColumn();
    }

    /**
     * Grab column headers
     *
     */
    public function getBucketPayablesTableHeader()
    {
        return array(
            'payoutSchedule' => 'Payout Schedule',
            'begDate' => 'Begin Date',
            'endDate' => 'End Date',
            'amount' => 'Amount',
            'adSpiff' => 'Adv Dev Spiff',
            //'empSpiff' => 'Employee Spiff',
            ' ' => ' '
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
                $jsWarn = ( strtotime('now') - strtotime($result['begDate']) > 2592000 ) ? ' datewarn' : '' ;
                $editLink = $result['endDate'] == null ? '<a class="button' . $jsWarn . '" href="edit_payable?payoutBucketID='
                                                        . $result['payoutBucketID'] . '" >EDIT</a>'
                                                        : ' ';

                $rows[] = array(
                    'data' => array(
                        $result['payoutSchedule'],
                        ( $result['begDate'] ? date('m/d/Y', strtotime($result['begDate'])) : 'none' ),
                        ( $result['endDate'] ? date('m/d/Y', strtotime($result['endDate'])) : 'none' ),
                        money_format('$%i', $result['amount']),
                        money_format('$%i', $result['adSpiff']),
                        $editLink
                        // money_format('$%i', $result['empSpiff']), // invisible for now, may add later
                    )
                );
            }
        }

        return $rows;
    }

    /**
     * Get number of payables for bucket
     * @access public
     * @param array $filters - contains bucketID
     * @return integer
     */
    public function getBucketPayablesCount(array $filters)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->getBucketPayablesCount($filters);
        return $stmt->fetchColumn();
    }

    /**
     * Save changes to current or new bucket
     *
     * @param array $input - form data
     * @return PDOStatement
     */
    public function pushToBucket(array $input)
    {
        $savedata = array();

        $savedata['term'] = $input['term'];
        $savedata['bucketCategoryID'] = $input['bucketCategoryID'];
        $savedata['contractTypeID'] = $input['contractTypeID'];
        $savedata['actTypeID'] = $input['actTypeID'];
        $savedata['shortDescription'] = trim($input['shortDescription']);
        $savedata['description'] = trim($input['description']);
        $savedata['isNE2'] = $input['isNE2'] ? 1 : 0;
        $savedata['isEdge'] = $input['isEdge'] ? 1 : 0;
        $savedata['isM2M'] = $input['isM2M'] ? 1 : 0;

        $entity = $this->dataAccessContainer['Table.Ccrs2.Buckets']->createEntity($savedata);

        if(@$input['bucketID']) {
            $criteria = array('bucketID' => $input['bucketID']);
            $stmt = $this->dataAccessContainer['Table.Ccrs2.Buckets']->update($entity, $criteria);
            return $stmt;
        } else {
            $stmt = $this->dataAccessContainer['Table.Ccrs2.Buckets']->insert($entity);
            return $stmt;
        }

    }

/**
  * Check if bucket exists with same description
  *
  * @param array $formInput
  * @return mixed boolean false if no bucket exists, int bucketID on find
  */
    public function checkBucketExists( array $formInput ) {
        $entity = $this->dataAccessContainer['Table.Ccrs2.Buckets']->createEntity($formInput);
        $entityArray = $entity->toArray();
        $isNot = !empty($entityArray['bucketID'] ) ? array( 'bucketID' => $entityArray['bucketID'] ) : array();

        unset($entityArray['bucketID'],
              $entityArray['contractTypeID'],
              $entityArray['addedOn'],
              $entityArray['description'],
              $entityArray['shortDescription']
              );

        $stmt = $this->dataAccessContainer['Table.Ccrs2.Buckets']->getBucketFromData($entityArray, $isNot);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$result || empty($result) ) {
            return false;
        } else {
            return $result['bucketID'];
        }
    }
}
