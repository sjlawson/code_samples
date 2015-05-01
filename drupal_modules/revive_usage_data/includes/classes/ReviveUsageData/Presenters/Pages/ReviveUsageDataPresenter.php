<?php

namespace ReviveUsageData\Presenters\Pages;

use PDO;
use ReviveUsageData\Presenters\AbstractPresenter;
use ReviveUsageData\DependencyInjection\DataAccessDependencyContainer;

define('KEY_OUTCOME','REDS_OUT_');

/**
 * "Revive Usage Data" page presenter.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-07
 */
class ReviveUsageDataPresenter extends AbstractPresenter
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
        return 'data/usage';
    }

    /**
     *
     * @return array of url parameters
     */
    public function getUsageDataUrlParams()
    {
        $defaultParams = array(
            'configurationsID' => null,
            'machineID' => null,
            'processValue' => null,
            'processName' => null,
            'key_filters' => array(),
            'locationID' => null,
            'start_date' => null,
            'end_date' => null,
            'load_defaults' => null,
        );

        $urlParams = array_merge($defaultParams, $this->getParameters);

        if(strpos($_SERVER['REQUEST_URI'], 'load_defaults'))
        {
            $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getProcessBusinessKeyFromKeyName('REDS_IN_Mode');
            $urlParams['key_filters'] = array($dataSet[0]['processBusinessKeysID'].'<>Live');

            $urlParams['start_date'] = date('m/d/Y', time() - 60 * 60 * 24);
            $urlParams['end_date'] = date('m/d/Y');
        }

        return $urlParams;
    }

    /**
     * Get list of Business Keys for selection options
     * @return array { revive_data_key_id , name }
     */
    public function getBusinessKeysOptionsList()
    {
        $options = array();
        $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getBusinessKeys();

        foreach ($dataSet as $result) {
            $options[$result['processBusinessKeysID']] = str_replace('REDS_','',$result['processName']);
        }

        return $options;
    }

    /**
     * get PBK id from name
     *
     * @param $processName
     * @return id
     */
    public function getBusinessKeyIDFromName($processName)
    {
        return $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']
            ->getBusinessKeyIDFromName($processName);
    }

    /**
     * Get distinct values from keyname
     * @return array - Drupal select options array
     */
    public function getDistinctBusinessKeyValuesOptionsList($urlParams)
    {
        $options = array();

        if (!empty($urlParams['processKeyID'])) {
            $keyID = $urlParams['processKeyID'];
            $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']
                ->getDistinctValuesFromKeyID($keyID);

            foreach ($dataSet as $result) {
                $options[$result['processValue']] = $result['processValue'];
            }

        }

        return $options;
    }

    public function getConfigurationsOptionsList()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.ReviveInternal.Configurations']->getMachineConfigurations();

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$result['configurationsID']] = $result['name'];
        }

        return $options;
    }

    /**
     * Get list of Outcome types for selection options
     * @return array { revive_data_key_id , name }
     */
    public function getOutcomeTypesOptionsList()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getKeyTypes(KEY_OUTCOME);

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nameStr = substr($result['name'],strrpos($result['name'], '_' )+1);
            $nameStr = implode(' ',preg_split('/(?=[A-Z])/',$nameStr));

            $options[$result['revive_data_key_id']] = $nameStr;
        }

        return $options;
    }

    /**
     * Retrieve a list of machines from the data model and assemble an options list for Drupal select element
     * @return array { revive_machine_id : name }
     */
    public function getMachinesOptionList()
    {
        $options = array();
        $dataSet = $this->dataAccessContainer['Table.ReviveInternal.Machines']->getMachines();

        foreach ($dataSet as $result) {
            $options[$result['machineID']] = $result['machineID'];
        }

        return $options;
    }

    /**
     * Get list of locations for select box options
     * @return array { locationsID : name }
     */
    public function getLocationsOptionList()
    {
        $options = array();
        $stmt = $this->dataAccessContainer['Table.ReviveInternal.Locations']->getLocations();

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[$result['locationsID']] = $result['name'];
        }

        return $options;
    }

    /**
     * Build header for drupal table
     * @return array
     */
    public function getUsageDataTableHeaders()
    {
        return array(
            'locationName' => 'Location',
            /* 'machineID' => 'Machine', */
            'processID' => 'Process ID',
            'Process Business Data' => 'Process Data Preview <i>(Click \'expand\' for details)</i>',
            'processDate' => 'Process Date',
            'datetimeAdded' => 'Date Received',
        );
    }

    /**
     * Find a history line with a given machineID, and a date grater than the processID, check locationsID against param
     * @param $machineID
     * @param $processID
     * @param $currentLocationsID
     * @return mixed: array decoded json | boolean false if no match or history location = given
     */
    private function checkRuntimeLocation($machineID, $processID, $currentLocationsID)
    {
        $dataRow = $this->dataAccessContainer['Table.ReviveInternal.MachinesHistory']->findHistoryForDate($machineID, $processID);
        if (empty($dataRow) || $dataRow['locationsID'] == $currentLocationsID) {
            return false;
        } else {
            return $dataRow;
        }

    }

    /**
     * Rows for drupal table
     * @return array(array())
     */
    public function getUsageDataTableRows(array $urlParams, array $limit)
    {
        $rows = array();

        $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getUsageData($urlParams, $limit);

        while ($result = $dataSet->fetch(PDO::FETCH_ASSOC)) {

            // check if the machine was at a different location at the time the process ran
            /* $historyJson = $this->checkRuntimeLocation($result['machineID'], $result['processID'], $result['locationsID']); */
            /* if (is_array($historyJson)) { */
            /*     $result['locationName'] = $historyJson['locationName']; */
            /* } */
            $locationName = $this->dataAccessContainer['Table.ReviveInternal.Locations']
                ->getLocationNameByID($result['locationsID']);

            $rows[] = array(
                'data' => array(
                    //$result['locationName'],
                    /* $result['machineID'], */
                    $locationName,
                    $result['processID'] .
                        '<br /><a rel="' . $result['processID'] .
                        '" class="button revive-process-export-button"
                         >Export</a>' ,
                    $this->buildProcessMiniTable($result['processID']),
                    date('Y-m-d, g:i A', $result['processID']),
                    date('Y-m-d, g:i A', strtotime($result['processDatetime']))
                )
            );
        }

        return $rows;
    }

    /**
     * Build mini table that fits within the main data usage table for "Process Data Preview"
     * @param $processID
     * @param $limit (default 4)
     * @return string, html
     */
    private function buildProcessMiniTable($processID, $limit = 9)
    {
        $processNameFilter = array(
            'REDS_IN_Consumer_NameFirst',
            'REDS_IN_Consumer_NameLast',
            'REDS_OUT_Device_ReviveSuccessful',
            'REDS_PROCESS_TimeSincePeril',
            'REDS_IN_Peril_DryAttempted',
            'REDS_OUT_Device_ReviveSuccessfulSecondary',
            'REDS_OUT_Device_ReviveSuccessfulPartial',
            'REDS_IN_Device_Model',
            'REDS_IN_Peril_ChargedPost',
        );

        $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getProcessKeyValuePairs($processID, $limit, $processNameFilter );
        $miniTableHtml = "<div class='process-mini-table' rel='".$processID."' ><table>";
        $consumerNameFirst = '';
        $consumerNameLast = '';
        $rowsHtml = '';
        foreach ($dataSet as $row) {
            if($row['processName'] == 'REDS_IN_Consumer_NameFirst' || $row['processName'] == 'REDS_IN_Consumer_NameLast') {
                if($row['processName'] == 'REDS_IN_Consumer_NameFirst') {
                    $consumerNameFirst = $row['processValue'];
                }

                if($row['processName'] == 'REDS_IN_Consumer_NameLast') {
                    $consumerNameLast = $row['processValue'];
                }

            } else {
                $rowsHtml .= "<tr><td rel='".$processID."'>" . $row['processName']
                    . "</td><td rel='".$processID."'>" . $row['processValue'] . "</td></tr>";
            }
        }
        $miniTableHtml .= "<tr><td rel='$processID'>Consumer Name</td><td>$consumerNameFirst $consumerNameLast</td></tr>"
            . $rowsHtml;

        $miniTableHtml .= "</table><a class='mini-table-process-expand button' rel='".$processID."'>Expand</a> </div>";

        return $miniTableHtml;
    }

    /**
     * Table with full data for display in lightbox when the user shift+clicks the Process Data Preview column
     * @param $urlParams - must contain 'processID', or else return empty table
     * @return html table
     *              @access public through Ajax request
     */
    public function buildProcessLightboxTable($urlParams)
    {
        if (!empty($urlParams['processID'])) {
            $processID = $urlParams['processID'];

            $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getProcessKeyValuePairs($processID);
            $miniTableHtml = "
                <div class='process-lightbox-table' >
                        <div id='revive-lightbox-header'>
                                <div id='closeAnchor'>X</div>
                                <div id='lightbox-header-text'>
                                        Usage Data for process {$urlParams['processID']} : "
                                        . date('Y-m-d',$urlParams['processID']) .
                                "</div>
                        </div>
                        <table>";

            foreach ($dataSet as $row) {
                if($row['processName'] == 'REDS_IN_CheckinTime') {
                    $row['processValue'] = date('Y-m-d g:ia', $row['processValue']);
                }

                if($row['processTimestamp'] == '1969-12-31 19:00:00' ) {
                    $row['processTimestamp'] = '';
                }

                $miniTableHtml .= "<tr><td>" . $row['processName']
                    . "</td><td>" . $row['processValue'] . "</td>"
                    . "</td><td>" . $row['processTimestamp'] . "</td>"
                    . "</tr>";
            }

            $miniTableHtml .= "</table></div>";

            return $miniTableHtml;
        }
    }

    /**
     * Get number of rows in the Usage table
     * @param  array   $filters
     * @return integer
     */
    public function getUsageDataTableCount(array $filters)
    {
        $rowCount = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getUsageDataCount($filters);

        return $rowCount;
    }

    /**
     * Kickout csv of data for one process
     * php://output
     */
    public function reviveExportProcess($urlParams)
    {
        // Get meta data from single row (same for all rows)
        $metaDataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getUsageDataForExport($urlParams, 1);

        //Get Business key-value pairs
        $dataSet = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getProcessKeyValuePairs($urlParams['processID']);

        if (!empty($urlParams['processID'])) {
            header('Content-Type: text/csv; utf-8');
            header("Content-Disposition: attachment; filename=export_"
                . $urlParams['processID'] . '_' . date('Ymd',$urlParams['processID']) . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

            $fh = fopen( 'php://output', 'w' );

            $metaData = array(
                array('Location',$metaDataSet[0]['locationName']),
                array('MachineID', $metaDataSet[0]['machineID']),
                array('ProcessID', $metaDataSet[0]['processID']),
                array('Date', date('r e', $metaDataSet[0]['processID']))
            );

            foreach ($metaData as $metaRow) {
                fputcsv($fh, $metaRow, ",");
            }

            foreach ($dataSet as $row) {
                fputcsv($fh, array($row['processName'],$row['processValue'] ) , ",");
            }

            fclose($fh);
            exit;
        }
    }

    private function getDLogHeaders()
    {
        return array(
            'date',
            'TemperaturePlaten',
            'TemperatureInjection',
            'Pressure',
            'RHChamber',
            'RHAmbient',
            'ModeType',
            'Current',
        );
    }

    public function reviveExportToDLog($urlParams)
    {
        $stmt = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getLimitedSet($urlParams['processID']);

        header('Content-Type: text/csv; utf-8');
        header("Content-Disposition: attachment; filename=export_"
            . $urlParams['processID'] . '_' . date('Ymd',$urlParams['processID']) . ".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        $fp = fopen( 'php://output', 'w' );

        fputcsv($fp, $this->getDLogHeaders(), ",");
        $row = $this->cleanRow();

        while($processDataValue = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(empty($row['date'])) {
                $row['date'] = $processDataValue['processTimestamp'];
            }

            if(!$this->checkEmptyRow($row)) {
                if($processDataValue['processTimestamp'] != $row['date']  )  {
                    // write row
                    fputcsv($fp, $row, ",");
                    $row = $this->cleanRow();
                }
            }

            switch ($processDataValue['processName']) {
                case 'REDS_PROCESS_TableCycles_TableTime_TemperaturePlaten' :
                case 'REDS_PROCESS_TemperatureRORPlaten':
                    if(!empty($row['TemperaturePlaten'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['TemperaturePlaten'] = $processDataValue['processValue'];
                    break;
                /* case 'REDS_PROCESS_TableCycles_TableTime_TemperatureDessicant' : */
                /*     if(!empty($row['TemperatureDessicant'])) { */
                /*         fputcsv($fp, $row, ","); */
                /*         $row = $this->cleanRow(); */
                /*         $row['date'] = $processDataValue['processTimestamp']; */
                /*     } */

                /*     $row['TemperatureDessicant'] = $processDataValue['processValue']; */
                /*     break; */
                case 'REDS_PROCESS_TableCycles_TableTime_TemperatureInjection' :
                case 'REDS_PROCESS_TemperatureRORInjection':
                    if(!empty($row['TemperatureInjection'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['TemperatureInjection'] = $processDataValue['processValue'];
                    break;
                case 'REDS_PROCESS_TableCycles_TableTime_Pressure' :
                case 'REDS_PROCESS_VacuumMax':
                    if(!empty($row['Pressure'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['Pressure'] = $processDataValue['processValue'];
                    break;
                case 'REDS_PROCESS_TableCycles_TableTime_RHChamber' :
                case 'REDS_PROCESS_RHChamberMax':
                case 'REDS_PROCESS_RHChamberMin':
                    if(!empty($row['RHChamber'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['RHChamber'] = $processDataValue['processValue'];
                    break;
                case 'REDS_PROCESS_TableCycles_TableTime_RHAmbient' :
                case 'REDS_PROCESS_TemperatureAmbientMax':
                    if(!empty($row['RHAmbient'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['RHAmbient'] = $processDataValue['processValue'];
                    break;
                case 'REDS_PROCESS_TableCycles_TableTime_ModeType' :
                    if(!empty($row['ModeType'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['ModeType'] = $processDataValue['processValue'];
                    break;
                case 'REDS_PROCESS_TableCycles_TableTime_Current' :
                    if(!empty($row['Current'])) {
                        fputcsv($fp, $row, ",");
                        $row = $this->cleanRow();
                        $row['date'] = $processDataValue['processTimestamp'];
                    }

                    $row['Current'] = $processDataValue['processValue'];
                    break;
            }
        }

        // any extra data needs to be written to last row
        if(!$this->checkEmptyRow($row)) {
            if(empty($row['date'])) {
                $row['date'] = $processDataValue['processTimestamp'];
            }

            fputcsv($fp, array_values($row), ",");
            $row = null;
        }

        fclose($fp);
        exit();
    }

    private function checkFullRow($row)
    {
        foreach($row as $col => $value) {
            if(empty($value)) {
                return false;
            }
        }
        return true;
    }

    private function checkEmptyRow($row)
    {
        foreach($row as $col => $value) {
            if(!empty($value)) {
                return false;
            }
        }
        return true;
    }

    private function cleanRow()
    {
        return array(
            'date' => '',
            'TemperaturePlaten' => '',
            'TemperatureInjection' => '',
            'Pressure' => '',
            'RHChamber' => '',
            'RHAmbient' => '',
            'ModeType' => '',
            'Current' => ''
        );
    }

    /**
     * Create data export file for selected filters and with keys like REDS_IN/REDS_OUT
     * @param array $filters
     *
     */
    public function exportIOData(array $urlParams)
    {
        $stmt = $this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getIOforExport($urlParams);

        header('Content-Type: text/csv; utf-8');
        header("Content-Disposition: attachment; filename=export_ioData_"
            . '_' . date('Ymd') . ".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        $fp = fopen( 'php://output', 'w' );
        $headerArray = array('processID','datetimeAdded','processName','processValue');
        fputcsv($fp, $headerArray, ",");

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($fp, $row, ",");
        }

        fclose($fp);
        exit();
    }

    public function getProcessBusinessKeyNameFromBusinessID($processBusinessKeyID)
    {
        $dataRow=$this->dataAccessContainer['Table.ReviveApi.ProcessDataValues']->getProcessBusinessKeyNameFromBusinessID($processBusinessKeyID);
        return $dataRow[0]['processName'];
    }
}
