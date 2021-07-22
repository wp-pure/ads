

// on load
jQuery(document).ready(function($){

	// get all the ad group blocks and loop through them
	$( ".promos" ).each(function(){

		var ad_group = $(this);

		// change ads on an interval
		var ad_interval = setInterval(function(){

			// select the current ad
			var current_ad = ad_group.find( '.promo:visible' );

			// select the next ad
			var next_ad = current_ad.next('.promo');

			// if the next ad is not empty
			if ( next_ad.length > 0 ) {

				// hide the current ad
				current_ad.hide();

				// show the next ad
				next_ad.show();		

			} else {

				// hide visible ad
				current_ad.hide();

				// show the first ad in the group
				ad_group.find( '.promo:first-child' ).show();

			}

		}, 5000 );

	});

});

