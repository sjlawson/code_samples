Drupal.behaviors.MHCCcrsManager = {
		attach: function(contect, settings) {
					(function($) {
						$('.datepicker_field div input').datepicker();
					})(jQuery);
		}
}