jQuery(document).ready(function ($) {
	var options = {
		prevText: "Previous",
		nextText: "Next",
		start: function(slider) {
			// Add a Foundation Row Class
			$('.flex-control-paging').addClass('row');
			// Add a column class and a grid
			var foundationClass;
			switch (slider.count){
				case 1:
					foundationClass = "column small-12";
					break;
				case 2:
					foundationClass = "columns small-6";
					break;
				case 3:
					foundationClass = "columns small-4";
					break;
				case 4:
					foundationClass = "columns small-3";
					break;
				default:
					break;
			}
			$('.flex-control-paging li').addClass(foundationClass);
		}
	};
	$('.flexslider').flexslider( options );
});