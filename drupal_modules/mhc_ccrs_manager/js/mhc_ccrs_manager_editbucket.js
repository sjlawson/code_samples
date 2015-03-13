(function($)
{
$(document).ready( function() {

        var ACTIVATION = '1';
        var UPGRADE = '2';
        var PREPAID = '3';

        var SELECTOR_TERM = '#edit-term';
        var SELECTOR_M2M = '#edit-ism2m';
        var SELECTOR_BUCKETCAT = '#edit-bucketcategoryid';
        var SELECTOR_ACTTYPE = '#edit-acttypeid';
        var SELECTOR_ISNE2 = '#edit-isne2';
        var SELECTOR_ISEDGE = '#edit-isedge';
        var SELECTOR_SHORTDESC = '#edit-shortdescription';
        var SELECTOR_DESC = '#edit-description';
        // var SELECTOR_CTTYPE = '#edit-contracttypeid'; // does not currently affect anything

        $(SELECTOR_TERM).change(function(){setTermOptions();buildDesc();});
        $(SELECTOR_BUCKETCAT).change(function(){setBucketCatOptions();buildDesc();});
        $(SELECTOR_ACTTYPE).change(function(){setActTypeOptions();buildDesc();});
        $(SELECTOR_ISNE2).change(function(){setNE2Options();buildDesc();});
        $(SELECTOR_ISEDGE).change(function(){setEdgeOptions();buildDesc();});
        $(SELECTOR_M2M).change(function(){setM2MOptions();buildDesc();});

        function setTermOptions()
        {
            if($(SELECTOR_TERM).val() != 24) {
                $(SELECTOR_ISNE2).prop('checked', false);
            }

            if($(SELECTOR_TERM).val() != 0) {
                $(SELECTOR_M2M).prop('checked', false);
                if($(SELECTOR_ACTTYPE).val() == PREPAID) {
                    $(SELECTOR_ACTTYPE).val(null);
                }
            }

            if($(SELECTOR_TERM).val() == 12) {
                $(SELECTOR_ISEDGE).prop('checked', false);
            }

            if ($(SELECTOR_ISEDGE).is(':checked')) {
                if($(SELECTOR_TERM).val() == 0) {
                    $(SELECTOR_ACTTYPE).val(ACTIVATION);
                } else if($(SELECTOR_TERM).val() == 24) {
                    $(SELECTOR_ACTTYPE).val(UPGRADE);
                }
            }
        }

        function setBucketCatOptions()
        {
            // No options yet, but allowing for future options related to specific categories

        }

        function setEdgeOptions()
        {
            if ($(SELECTOR_ISEDGE).is(':checked')) {
                $(SELECTOR_ISNE2).prop('checked', false);
                $(SELECTOR_M2M).prop('checked', false);
                if($(SELECTOR_ACTTYPE).val() == ACTIVATION ) {
                    $(SELECTOR_TERM).val(0);
                } else if($(SELECTOR_ACTTYPE).val() == UPGRADE) {
                    $(SELECTOR_TERM).val(24);
                }

                if( ($(SELECTOR_ACTTYPE).val() == PREPAID || $(SELECTOR_ACTTYPE).val() == '')
                        && $(SELECTOR_TERM).val() == 0) {
                    $(SELECTOR_ACTTYPE).val(ACTIVATION);
                } else if($(SELECTOR_ACTTYPE).val() == PREPAID && $(SELECTOR_TERM).val() == 24) {
                    $(SELECTOR_ACTTYPE).val(UPGRADE);
                }
            }
        }

        function setActTypeOptions()
        {
            var activationTypeID = $(SELECTOR_ACTTYPE).val();

            switch(activationTypeID) {
                case ACTIVATION :
                    if($(SELECTOR_ISEDGE).is(':checked')) {
                        $(SELECTOR_TERM).val(0);
                    }
                    $(SELECTOR_ISNE2).prop('checked', false);
                break;
                case UPGRADE :
                    if($(SELECTOR_ISEDGE).is(':checked')) {
                        $(SELECTOR_TERM).val(24);
                        $(SELECTOR_ISNE2).prop('checked', false);
                    }
                    $(SELECTOR_M2M).prop('checked', false);
                break;
                case PREPAID :
                    $(SELECTOR_TERM).val(0);
                    $(SELECTOR_ISNE2).prop('checked', false);
                    $(SELECTOR_M2M).prop('checked', false);
                    $(SELECTOR_ISEDGE).prop('checked', false);
                break;
            }
        }

        function setM2MOptions()
        {
            $(SELECTOR_TERM).val(0);
            $(SELECTOR_ACTTYPE).val(ACTIVATION);
            $(SELECTOR_ISNE2).prop('checked', false);
            $(SELECTOR_ISEDGE).prop('checked', false);
        }

        function setNE2Options()
        {
            $(SELECTOR_ACTTYPE).val(UPGRADE);
            $(SELECTOR_TERM).val(24);
            $(SELECTOR_ISEDGE).prop('checked', false);
            $(SELECTOR_M2M).prop('checked', false);
        }

        /**
         * Based on current form selections, build description and shortDecription
         */
        function buildDesc()
        {
            var description = "";
            var shortDescription = "";

            if ($(SELECTOR_M2M).is(':checked')) {
                description += "M2M ";
                shortDescription += "M2M ";
            }

            if ($(SELECTOR_ISEDGE).is(':checked')) {

                description += "Edge ";
                shortDescription += "EDG ";

                // category goes after term
                description += $(SELECTOR_BUCKETCAT + ' option:selected').text() + " ";
                shortDescription += $(SELECTOR_BUCKETCAT + ' option:selected').val() + " ";

                // activation type
                description += $('#edit-acttypeid option:selected').text();

            } else if ($(SELECTOR_TERM).val() > 0) {
                // insert term if > 0
                description += $(SELECTOR_TERM).val() + " Month ";
                shortDescription += $(SELECTOR_TERM).val() + "M ";

                // category goes after term
                description += $(SELECTOR_BUCKETCAT + ' option:selected').text() + " ";
                shortDescription += $(SELECTOR_BUCKETCAT + ' option:selected').val() + " ";

                // activation type
                description += $('#edit-acttypeid option:selected').text();

            } else if($('#edit-acttypeid option:selected').text() == "Prepaid") {
                // Term is PREPAY
                description += "Prepaid ";
                shortDescription += "PREPAY ";

                // Category
                description += $(SELECTOR_BUCKETCAT + ' option:selected').text() + " ";
                shortDescription += $(SELECTOR_BUCKETCAT + ' option:selected').val() + " ";

                // no activation (PREPAY)
            } else {
                // category goes after term
                description += $(SELECTOR_BUCKETCAT + ' option:selected').text() + " ";
                shortDescription += $(SELECTOR_BUCKETCAT + ' option:selected').val() + " ";

                // activation type
                description += $('#edit-acttypeid option:selected').text();
            }

            description += $(SELECTOR_ISNE2).is(':checked') ? ' NE2' : '';
            if ($(SELECTOR_ISNE2).is(':checked')) {
                shortDescription += "NE2U";

            } else if($('#edit-acttypeid option:selected').text() != "Prepaid") {
                shortDescription += $('#edit-acttypeid option:selected').text().substr(0,3).toUpperCase();
            }

            $(SELECTOR_DESC).val(description);
            $(SELECTOR_SHORTDESC).val(shortDescription);
    }

    // set warning confirmation dialogue if a receivable or payable begin date is older than 30 days
    $('.datewarn').on('click', function(){return confirm("This item has a begin date earlier than 30 days in the past, are you VERY sure you want to edit this?");});

});
})(jQuery);