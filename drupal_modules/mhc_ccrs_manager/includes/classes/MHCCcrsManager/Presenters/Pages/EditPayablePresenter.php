<?php

namespace MHCCcrsManager\Presenters\Pages;

use PDO;
use MHCCcrsManager\Presenters\AbstractPresenter;
use MHCCcrsManager\DependencyInjection\DataAccessDependencyContainer;

/**
 * "Edit Payable" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-03-28
 */
class EditPayablePresenter extends AbstractPresenter
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
        return 'apps/accounting/ccrs/manager/edit_payable';
    }

    /**
     *
     * @return array
     */
    public function getPayableFormDefaults()
    {
        $defaultParams = array(); // wip

         return array_merge($defaultParams, $this->getParameters);
    }

    /**
     *
     * Get single payable from model
     * @param int $payoutBucketID
     * @return assoc array
     */
    public function getPayable($payoutBucketID)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->getPayableById($payoutBucketID);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     *
     * Get count of payables for given filters (bucketID, payaoutScheduleID
     * @param $filters
     * @return int
     */
    public function getPayablesCountForSchedule(array $filters)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->getPayablesCountForSchedule($filters);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $bucketID
     */
    public function getMostRecentPayableByEndDateAndSchedule($bucketID, $payoutScheduleID)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->getBucketPayablesByEndDateAndSchedule($bucketID, $payoutScheduleID);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Payable validation currently limited to consistency with begDate and endDate
     * @access public
     * @param array $formInput
     * @return mixed boolean true if no conflict, on fail, return array('result'=>'error','message'=>'...msg')
     */
    public function validatePayable(array $formInput)
    {
        $existingPayable = $this->getMostRecentPayableByEndDateAndSchedule($formInput['bucketID'], $formInput['payoutScheduleID']);

        $formInput['begDate'] = $formInput['begDate'] == '' ? null : date('Y-m-d', strtotime($formInput['begDate']));
        $formInput['endDate'] = $formInput['endDate'] == '' ? null : date('Y-m-d', strtotime($formInput['endDate']));

        $entity = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->createEntity($formInput);
        $isNewPayable = $formInput['payoutBucketID'] ? false : true;
        if($isNewPayable) {
            // validate cannot insert record with earlier begDate than an existing begDate
            if($formInput['begDate'] != null && $formInput['begDate'] < $existingPayable['begDate'] ) {
                return array(
                    'result'=>'error',
                    'message'=>'Proposed payable date conflicts with existing payables'
                );
            } elseif($formInput['begDate'] != null && $formInput['begDate'] == $existingPayable['begDate'] ) {
                return array(
                    'result'=>'error',
                    'message'=>'Proposed payable begin date matches existing payable begin date'
                );
            }
        } else {  // check valid UPDATE
            /* Rules: begDate must be after previous payable endDate
             *  endDate must be before next payable begDate
             */
            $valid = $this->checkPayableDateValidation($formInput);
            if(!$valid) {
                return array(
                    'result'=>'error',
                    'message'=>'Proposed date changes conflict with existing payables'
                );
            }
        }

        return true;

    }

    /**
     *
     * Determines whether to INSERT or UPDATE, performs some post-form validation to ensure against date conflicts
     * @param array $formInput
     * @return boolean
     */
    public function savePayable(array $formInput)
    {
        $existingPayable = $this->getMostRecentPayableByEndDateAndSchedule($formInput['bucketID'], $formInput['payoutScheduleID']);

        $formInput['begDate'] = $formInput['begDate'] == '' ? null : date('Y-m-d', strtotime($formInput['begDate']));
        $formInput['endDate'] = $formInput['endDate'] == '' ? null : date('Y-m-d', strtotime($formInput['endDate']));

        $entity = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->createEntity($formInput);

        $isNewPayable = $formInput['payoutBucketID'] ? false : true;
        if($isNewPayable) { // INSERT
            if(!$existingPayable){
                return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->insert($entity);
            } else {
                // payable exists, so check if most recent endDate is later than the new begDate
                if($existingPayable['endDate'] == null || $existingPayable['endDate']  >= $formInput['begDate'] ){
                    $newEndDate = new \DateTime( $formInput['begDate'] );
                    // $newEndDate->sub(new \DateInterval('P1D')); // set to same day. Uncomment to set 1 day before
                    $existingPayable['endDate'] = $newEndDate->format('Y-m-d');
                    $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->update(
                        $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->createEntity($existingPayable),
                        array('endDate'), array('payoutBucketID' =>$existingPayable['payoutBucketID']));

                        return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->insert($entity);
                } else {
                    return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->insert($entity);
                }
            }
        } else { // this is an UPDATE
            return $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->update(
                $entity, array(), array('payoutBucketID' => $formInput['payoutBucketID'] ));
        }
    }

    /**
    * Check if there are records with overlapping begDate and/or endDate
    * @param array $formInput
    * @return boolean true on successful validation, false if conflicts exist
    */
    public function checkPayableDateValidation(array $formInput)
    {
        $stmt = $this->dataAccessContainer['Table.Ccrs2.BucketCommissionPayoutBuckets']->findPayableDateConflict($formInput);
        $conflicts = $stmt->fetchAll();
        return empty($conflicts);
    }

    public function getPayoutScheduleOptionsArray()
    {
        $options = array();

        $stmt = $this->dataAccessContainer['Table.Ccrs2.EstimatorCommissionSchedules']->getPayoutSchedules();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$result['id']] = $result['schedule'];
        }

        return $options;
    }

}
