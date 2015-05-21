/**
 * This file contains our custom JavaScript files and functions for the theme
 */

// Load up Foundation
(function(jQuery) {
	jQuery(document).foundation();
	jQuery(document).on('replace', '#interchangemap', function() {
		InitializeMaps();
	});
	jQuery.fn.cleardefault = function() {
    	return this.focus(function() {
    		if( this.value == this.defaultValue ) {
    			this.value = "";
    		}
    	}).blur(function() {
    		if( !this.value.length ) {
    			this.value = this.defaultValue;
    		}
    	});
    };
    jQuery(".gform_wrapper input[type='text'], .gform_wrapper input[type='email'], .gform_wrapper input[type='tel'], .gform_wrapper input[type='search'], .gform_wrapper input[type='url'], .gform_wrapper input[type='number'], .gform_wrapper textarea").cleardefault();

})(jQuery);

function InitializeMaps() {
	var marker_icon = maps_vars.template_dir_uri + '/images/map-pin.png';

	var mapOptions = {
	zoom: 3,
	center: new google.maps.LatLng(-26.70636,153.142548),
	disableDefaultUI: true,
	backgroundColor: '#fff',
	panControl: true,
	zoomControl: true,
	scrollwheel: false
	};

	var map = new google.maps.Map(document.getElementById('map'),mapOptions);

	google.maps.event.addDomListener(window, 'load', mapOptions);

	var marker = new google.maps.Marker({
	position: mapOptions.center,
	icon: marker_icon,
	map: map
	});
}