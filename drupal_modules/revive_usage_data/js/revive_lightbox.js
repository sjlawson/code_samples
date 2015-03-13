function launchReviveLightbox(insertContent, ajaxContentUrl){
    (function($) { // Drupid jquery init

        // add lightbox/shadow <div/>'s if not previously added
        if($('#lightbox-revivedata').size() == 0){
            var theLightbox = $('<div id="lightbox-revivedata"/>');
            var theShadow = $('<div id="lightbox-shadow-revivedata"/>');
            $(theShadow).click(function(e){
                closeReviveLightbox();
            });
            $('body').append(theShadow);
            $('body').append(theLightbox);
        }

        // remove any previously added content
        $('#lightbox-revivedata').empty();

        // insert HTML content
        if(insertContent != null){
            $('#lightbox-revivedata').append(insertContent);
        }

        // insert AJAX content
        if(ajaxContentUrl != null){
            // temporarily add a "Loading..." message in the lightbox
            $('#lightbox-revivedata').append('<p class="loading">Loading...</p>');

            // request AJAX content
            $.ajax({
                type: 'GET',
                url: ajaxContentUrl,
                success:function(data){
                    // remove "Loading..." message and append AJAX content
                    $('#lightbox-revivedata').empty();
                    $('#lightbox-revivedata').append(data);
                    $('#lightbox-revivedata div.process-lightbox-table #revive-lightbox-header').on('click', function() {
                        closeReviveLightbox();
                    });

                },
                error:function(){
                    alert('Unable to load content');
                }
            });
        }

        // move the lightbox to the current window top + 100px
        $('#lightbox-revivedata').css('top', $(window).scrollTop() + 100 + 'px');

        // display the lightbox
        $('#lightbox-revivedata').show();
        $('#lightbox-shadow-revivedata').show();

    })(jQuery);
}

// close the lightbox
function closeReviveLightbox(){
    (function($) {
        // hide lightbox and shadow <div/>'s
        $('#lightbox-revivedata').hide();
        $('#lightbox-shadow-revivedata').hide();

        // remove contents of lightbox in case a video or other content is actively playing
        $('#lightbox-revivedata').empty();
    })(jQuery);
}
