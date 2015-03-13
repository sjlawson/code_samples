<?php

namespace MHCCcrsManager\Presenters\Pages;

use PDO;
use MHCCcrsManager\Presenters\AbstractPresenter;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;
use MHCCcrsManager\DependencyInjection\Pages\CcrsManagerDependencyContainer;

/**
 * "Edit Receivable" page presenter.
 * (Receivable is the real-world name for BucketCommissionBuckets)
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-28
 */
class EditReceivablePresenter extends AbstractPresenter
{
    /**
     * Constructor
     */
    public function __construct($devMode,
                                DataAccessDependencyContainer $dataAccessContainer,
                                array $getParameters
                                )
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
        return 'apps/accounting/ccrs/manager/edit_receivable';
    }

    /**
     *
     * Get receivable data to populate form
     * @param integer $commissionBucketID
     */
    public function getCcrsBuceketCommissionBucketsRecord($commissionBucketID)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->getBucketCommissionBucketsReceivableById($commissionBucketID);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     *
     * @return array
     */
    public function getReceivableFormDefaults()
    {
        $defaultParams = array();
         return array_merge($defaultParams, $this->getParameters);
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
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getMostRecentReceivableByEndDate($bucketID)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->getBucketReceivablesByBucketAndEndDate($bucketID);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Separate validation from INSERT logic.
     * @access public
     * @param array $formInput
     * @return mixed boolean true if no conflict, on fail, return array('result'=>'error','message'=>'...msg')
     */
    public function validateReceivable(array $formInput)
    {
        $existingReceivable = $this->getMostRecentReceivableByEndDate($formInput['bucketID']);

        $formInput['begDate'] = $formInput['begDate'] == '' ? null : date('Y-m-d', strtotime($formInput['begDate']));
        $formInput['endDate'] = $formInput['endDate'] == '' ? null : date('Y-m-d', strtotime($formInput['endDate']));

        $isNewReceivable = $formInput['commissionBucketID'] ? false : true;
        if($isNewReceivable) { // check for valid INSERT
            if($formInput['begDate'] != null && $formInput['begDate'] < $existingReceivable['begDate'] ) {
                // validate cannot insert record with earlier begDate than an existing begDate (or same begDate)
                return array(
                    'result'=>'error',
                    'message'=>'Proposed receivable date conflicts with existing receivable'
                );
            } elseif($formInput['begDate'] != null && $formInput['begDate'] == $existingReceivable['begDate'] ) {
                return array(
                    'result'=>'error',
                    'message'=>'Proposed receivable begin date matches existing receivable begin date'
                );
            }
        } else { // check valid UPDATE
            /* Rules: begDate must be after previous receivable endDate
             *  endDate must be before next receivable begDate
             */
            $valid = $this->checkReceivableDateValidation($formInput);
            if(!$valid) {
                return array(
                    'result'=>'error',
                    'message'=>'Proposed date changes conflict with existing receivables'
                );
            }
        }

        return true;
    }

    /**
     * Do whatever data formatting needs to happen, determine whether to INSERT or UPDATE, serve to model.
     * @access public
     * @param array $formInput
     * @return boolean
     */
    public function saveReceivable(array $formInput)
    {
        $existingReceivable = $this->getMostRecentReceivableByEndDate($formInput['bucketID']);

        $formInput['begDate'] = $formInput['begDate'] == '' ? null : date('Y-m-d', strtotime($formInput['begDate']));
        $formInput['endDate'] = $formInput['endDate'] == '' ? null : date('Y-m-d', strtotime($formInput['endDate']));

        $entity = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->createEntity($formInput);

        $isNewReceivable = $formInput['commissionBucketID'] ? false : true;
        if($isNewReceivable) { // INSERT

            if(!$existingReceivable){
                return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->insert($entity);
            } else {
                // receivable exists, so check if most recent endDate is later than the new begDate
                if($existingReceivable['endDate'] == null
                        || $existingReceivable['endDate']  >= $formInput['begDate'] ){
                    $newEndDate = new \DateTime( $formInput['begDate'] );
                    $existingReceivable['endDate'] = $newEndDate->format('Y-m-d');

                    $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->update(
                        $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->createEntity($existingReceivable),
                        array('endDate'),
                        array('commissionBucketID' =>$existingReceivable['commissionBucketID']));

                    return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->insert($entity);
                } else {
                    return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->insert($entity);
                }
            }

        } else { // this is an UPDATE
            return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->update(
                    $entity,
                    array('begDate','endDate','amount','adSpiff'),
                    array('commissionBucketID' => $formInput['commissionBucketID'] )
            );
        }
    }

    /**
    * Check if there are records with overlapping begDate and/or endDate
    * @param array $formInput
    * @return boolean true on successful validation, false if conflicts exist
    */
    public function checkReceivableDateValidation(array $formInput)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionBuckets']->findReceivableDateConflict($formInput);
        $conflicts = $stmt->fetchAll();
        return empty($conflicts);
    }

}
