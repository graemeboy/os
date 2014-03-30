jQuery(document).ready(function($) {
	
	// Fade
	
	var oisCount = 0,
		fadeSec = 500,
		fadeSelf; // default
	
	var oisFadeInterval = setInterval( function() 
	{
		$('.ois-fade').each(function ()
		{
			oisCount++; // there is a fader
			fadeSelf = this;
			if ($(fadeSelf).is(":onScreen"))
			{
				
				// The design ought to appear.
				// Get the number of seconds before it should do.
				if ($(fadeSelf).attr('data-ois-fade-sec'))
				{
					fadeSec = parseInt($(this).attr('data-ois-fade-sec')) * 1000;
				} // if
				
				// Fade in after the given number of seconds.
				setTimeout( function ()
				{
					$(fadeSelf).find('.ois-outer').fadeIn(1000, function () {
						$(fadeSelf).removeClass('ois-fade');
					});
				} , fadeSec );
				oisCount--; // remove this fader from our count
			} // if
			if (oisCount == 0)
			{
				// There are none left.
				clearInterval(oisFadeInterval);
			} // if	
		}); // each fader
		oisCount = 0; // reset for next check!
	}, // anon () 
	3000
	); // set Interval; every 3 seconds.
	
	
	var disable_submissions = ois.disable_submissions_stats;
	if (disable_submissions != 'yes') 
	{
		var submitted = new Array();

		$('.ois-design form').submit(function(e) 
		{
			var ois_service = $(this).attr('service');
				if (ois_service != 'feedburner') {
					e.preventDefault();
				}
				var selfId = $(this).attr('id');
				var self = this;
				var maybeWrapper = this;
				var unfoundWrapper = true;
				var id = $(this).attr('id');
				while (unfoundWrapper) {
					maybeWrapper = $(maybeWrapper).parent();
					if (maybeWrapper.hasClass('ois_wrapper')) {
						unfoundWrapper = false;
						var id = $(maybeWrapper).attr('data');
					}
				}
				if ($.inArray(id, submitted) == -1) {
					submitted.push(id);
					var ois_post_id = $('.ois_wrapper').attr('rel');
					var ois_skin_id = id;
					var ois_name = $('#' + ois_skin_id + '_name').val();
					var ois_email = $('#' + ois_skin_id + '_email').val();
					var submission_data = "action=ois_ajax" + "&ois_submission_nonce=" + ois.ois_submission_nonce + "&post_id=" + ois_post_id + "&skin_id=" + ois_skin_id + "&submit=yes" + "&name=" + ois_name + "&email=" + ois_email + "&service=" + ois_service;
					
					$.ajax({
						type: "POST",
						url: ois.ajaxurl,
						data: submission_data,
						success: function(data) {
							if (ois_service == 'feedburner') {
								if (data && data != 'no_redirect') {
									window.location.href = data;
								}
							} else {
								disable_submissions = 'yes'; // so no inf. loop
								$(self).unbind('submit');
								$('#' + selfId + ' :input[type="submit"]').click();
							}
						},
					});
				}
			});
		}
	}
});

// Add the "onScreen" attribute.
(function($) {
	$.expr[":"].onScreen = function(elem) {
		var $window = $(window);
		var viewport_top = $window.scrollTop();
		var viewport_height = $window.height();
		var viewport_bottom = viewport_top + viewport_height;
		var $elem = $(elem);
		var top = $elem.offset().top
		var height = $elem.height();
		var bottom = top + height
		return (top >= viewport_top && top < viewport_bottom) || (bottom > viewport_top && bottom <= viewport_bottom) || (height > viewport_height && top <= viewport_top && bottom >= viewport_bottom)
}
})(jQuery);