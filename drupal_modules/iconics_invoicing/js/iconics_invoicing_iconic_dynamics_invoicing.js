Drupal.behaviors.IconicsInvoicing = {
    attach: function(content, settings) {
        jQuery('#edit-startdate').datepicker();
        jQuery('.invoicedate-table-entry').datepicker({showOn: ''}).on('click', function(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to use a different invoice date?')) {
                jQuery(this).datepicker('show');
            }
        });
    }
}

jQuery.noConflict();
(function ($) {

    $(document).ready(
        function() {

            $('#btn-process-selected-orders').on('click', function() {
                processSelectedOrders();
            });

            $('.viewProblem').on('click', function(event) {
                var orderNum = $(event.target).attr('rel');
                var invoDate = $('#invoiceDate_' + orderNum).val();

                processInvoice(orderNum, invoDate);
                // this option only displays when we know it will fail validation
            });

            $('.viewOrder').on('click', function(event) {
                event.preventDefault();

                var orderNum = $(event.target).attr('rel');
                var dataPath = DRUPAL_PATH + '?jsonAction=getInvoiceByOrderNum&jsonActionData=' + orderNum;

                $.get( dataPath , function(data) {
                    $("<div style='width: 60%; minWidth: 700px' class='modal-box-view-invoice-inner' title='View Invoice'> \
                        Invoice Data<br />" + data + "</div>")
                        .dialog({width: '60%', minWidth: 700, minHeight: 300})
                        .attr('class','modal-box-view-invoice');
                } );

            });

            $('a.previewInvoice').on('click', function(event) {
                event.preventDefault();
                var orderNum = $(event.target).attr('rel');
                var dataPath = DRUPAL_PATH + '?jsonAction=displayOrderToInvoice&jsonActionData=' + orderNum;

                $.get( dataPath , function(data) {
                    $("<div style='width: 60%; minWidth: 700px' class='modal-box-view-invoice-inner' title='View Order'> \
                        Invoice Preview<br />" + data + "</div>")
                        .dialog({width: '60%', minWidth: 700, minHeight: 300})
                        .attr('class','modal-box-view-invoice');
                } );
            });

            $('a.forceInvoice').on('click', function(event) {
                event.preventDefault();
                var orderNum = $(event.target).attr('rel');
                var invoDate = $('#invoiceDate_' + orderNum).val();

                $('<div></div>').appendTo('body')
                    .html('<div><h5>Force invoice for order ' + orderNum  + ' on date ' + invoDate  + ' <br />even though there are still items to ship?</h5></div>')
                    .dialog({
                        modal: true, title: 'message', zIndex: 10000, autoOpen: true,
                        width: 'auto', resizable: false,
                        buttons: {
                            Yes: function () {
                                processInvoice(orderNum, invoDate);
                                $(this).dialog("close");
                            },
                            No: function () {
                                $(this).dialog("close");
                            }
                        },
                        close: function (event, ui) {
                            $(this).remove();
                        }
                    });

            });

            $('a.processInvoice').on('click', function(event) {
                event.preventDefault();
                var orderNum = $(event.target).attr('rel');
                var invoDate = $('#invoiceDate_' + orderNum).val();

                $('<div></div>').appendTo('body')
                    .html('<div><h5>Process invoice for order ' + orderNum  + ' <br /> with date ' + invoDate + '?</h5></div>')
                    .dialog({
                        modal: true, title: 'message', zIndex: 10000, autoOpen: true,
                        width: 'auto', resizable: false,
                        buttons: {
                            Yes: function () {
                                processInvoice(orderNum, invoDate);
                                $(this).dialog("close");
                            },
                            No: function () {
                                $(this).dialog("close");
                            }
                        },
                        close: function (event, ui) {
                            $(this).remove();
                        }
                    });

            });

        }
    );

    function processInvoice(orderNum, invoDate)
    {
        var dataPath = DRUPAL_PATH + '?jsonAction=processInvoice&jsonActionData='
            + orderNum
            + '&invoiceDate=' + invoDate;

        $.get( dataPath , function(data) {
            if (data == 'true') {
                window.location = DRUPAL_PATH + '?' + URL_QUERY;
            } else {
                $("<div style='width: 600px' class='modal-box-view-invoice-inner' \
                     title='View Invoice'>" + data + "</div>")
                    .dialog({width: 700, minHeight: 300})
                    .attr('class','modal-box-view-invoice');
            }
        } );
    }

    function processSelectedOrders()
    {
        var dataPath = DRUPAL_PATH + '?jsonAction=processSelectedOrders';
        var ordersData = [];

        $('.process-invoice-checkbox').each(function() {
            if($(this).is(':checked')) {
                var orderID = $(this).val();
                var invoiceDate = $('#invoiceDate_' + orderID).val();

                ordersData.push({orderID:orderID, invoiceDate:invoiceDate});
            }
        });

        // alert(JSON.stringify(ordersData));

        $.post( dataPath, {ordersData:ordersData} , function(data, status) {
            if (data == 'true') {
                window.location = DRUPAL_PATH + '?' + URL_QUERY;
            } else {
                $("<div style='width: 600px' class='modal-box-view-invoice-inner' \
                     title='View Invoice'>" + data + "</div>")
                    .dialog({width: 700, minHeight: 300})
                    .attr('class','modal-box-view-invoice');
            }

        });


    }

})(jQuery);


