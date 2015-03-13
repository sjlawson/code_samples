Drupal.behaviors.ReviveUsageData = {
    attach: function() {
        jQuery('.revive_usage_form_container #edit-revive-usage-data-date-fieldset .form-text').datepicker();
    }
}

jQuery.noConflict();
(function($) {

    $(document).ready(
        /**
         * Setup event listeners
         */
        function()
        {
            $('.revive_usage_form_container #btn-add-selected-to-queue').on('click', function() {
                addSelectedKeyPair();
            });

            $('.revive_usage_form_container #btn-remove-selected-from-queue').on('click', function() {
                removeSelectedKeyPair();
            });

            $('#revive_usage_device_mfc_options select').on('change', function(){
                changeReviveDeviceList('mfc','make');
            });

            $('#revive_usage_device_make_options select').on('change', function(){
                changeReviveDeviceList('make','model');
            });

            $('.revive_usage_form_container #edit-revive-business-key').on('change', updateBusinessKeyValues);

            $('.revive_usage_form_container #revive-clear-business-key-button').on(
                'click',
                function() {
                    $('.revive_usage_form_container #edit-revive-business-key').val([]);
                    $('.revive_usage_form_container #edit-revive-business-key-value').val([]);
                    $('.revive_usage_form_container #edit-revive-business-key-value').html('');
                });

            $('.revive_usage_form_container #revive-clear-configurationsID-button').on(
                'click',
                function() {
                    $('.revive_usage_form_container #edit-configurationsid').val([]);
                });

            $('.revive_usage_form_container #revive-clear-machine-button').on(
                'click',
                function() {
                    $('.revive_usage_form_container #edit-revive-machine-id').val([]);
                });

            $('.revive_usage_form_container #revive-clear-location-button').on(
                'click',
                function() {
                    $('.revive_usage_form_container #edit-revive-location-id').val([]);
                });

            $('.revive_usage_form_container #revive-clear-dates-button').on(
                'click',
                function() {
                    $('.revive_usage_form_container #edit-revive-start-date').val('');
                    $('.revive_usage_form_container #edit-revive-end-date').val('');
                });

            $('.revive_usage_form_container #edit-submit').on(
                'click',
                function() {
                    selectAllQueueItems();
                });

            $('.revive_usage_form_container #edit-ioexport').on(
                'click',
                function() {
                    selectAllQueueItems();
                });

            // init listener, on shift+click over a Process Data cell
            // $('.process-mini-table').on('click', function(event) {
            //     if(event.shiftKey) {
            //         var processID = $(event.target).attr('rel');
            //         showExpandedProcessBusinessData(processID);
            //     }
            // });
            $('.mini-table-process-expand').on('click', function(event) {
                var processID = $(event.target).attr('rel');
                showExpandedProcessBusinessData(processID);
            });

            $('.revive-process-export-button').on('click', function(event) {
                var processID = $(event.target).attr('rel');
                reviveExportProcess(processID);
            });

        }
    );

    /**
     * Options have to be selected to get passed as values
     */
    function selectAllQueueItems()
    {
        $('#edit-business-key-queue option').each(
            function() {
                $(this).attr('selected','selected');
            }
        );
    }

    /**
     * Handle lightbox launching, lightbox code in reviveLightBox.js
     */
    function showExpandedProcessBusinessData(processID)
    {
        var dataPath = DRUPAL_PATH +
            '?action=buildProcessLightboxTable&processID=' + processID;

        launchReviveLightbox('Process Data', dataPath);
    }

    /**
     * Load Outcome values based on Outcome key selection
     *
     */
    function updateBusinessKeyValues()
    {
        displayLoadingIcon('#edit-revive-business-key-value-loading');
        var selectedValue = $('#edit-revive-business-key').val();
        var optionsHtml = "";
        var dataPath = DRUPAL_PATH +
            '?action=getDistinctBusinessKeyValuesOptionsList&processName=' + selectedValue;

        $.get( dataPath  , function( data ) {
            var dataObject = $.parseJSON(data);

            $.each(dataObject, function(key, value) {
                optionsHtml += "<option value='" + key + "'>" + value + "</option>";
            });

            $('#edit-revive-business-key-value').html(
                optionsHtml
            );
        });
        removeLoadingIcon('#edit-revive-business-key-value-loading');
    }

    function displayLoadingIcon(containerElementID)
    {
        var loaderHtml = '<img src="' + MODULE_PATH + '/images/loading-small.gif" alt="Loading" />';
        $(containerElementID).html(loaderHtml);
        $(containerElementID).css('display', 'block');
    }

    function removeLoadingIcon(containerElementID)
    {
        $(containerElementID).html('');
        $(containerElementID).css('display', 'none');
    }

})(jQuery);

/**
* Load the kickout generator in an invisible iframe
* uses same method for getting data as ajax
* @param eventTarget - clicked button has rel element containing processID
*/
function reviveExportProcess(processID) {
    jQuery.noConflict();
    (function($) {
        var dataPath = DRUPAL_PATH +
            '?action=reviveExportToDLog&processID=' + processID;

        $('iframe').remove();
        $('body').append('<iframe src="' + dataPath +
                         '" name="revive-process-export-iframe" id="revive-process-export-iframe" ' +
                         'style="visibility:hidden; display: none; width: 0px; height: 0px"></iframe>');
    })(jQuery);
}

/**
* If an option is selected in Key Name and Key Value, add it to Business Key Filter Queue as colon-separated pair, key:value
*/
function addSelectedKeyPair()
{
    (function($) {
        var keyName = $('#edit-revive-business-key').val();
        var keyValue = $('#edit-revive-business-key-value').val();
        var currentQueue = "<option value='" + keyName + "<>" + keyValue + "'>" + keyName + "<>" + keyValue + "</option>";
        var entryExists = false;
        $('#edit-business-key-queue option').each(function() {
            if ($(this).val() == keyName + "<>" + keyValue) {
                entryExists = true;
            }
        });

        if(keyName != null && keyValue != null && !entryExists ) {
            $('#edit-business-key-queue').append(currentQueue);
            $('.revive_usage_form_container #edit-revive-business-key').val([]);
            $('.revive_usage_form_container #edit-revive-business-key-value').val([]);
        }

    })(jQuery);
}

/**
* Takes an option element out of the Business Key Filter Queue
*/
function removeSelectedKeyPair()
{
    jQuery('#edit-business-key-queue option:selected').remove();
}
