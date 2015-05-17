$(document).ready(function() {
		var boxHeight = 0;
		$(".planbox").each(function(){
			var currHeight = $(this).height();
			if(currHeight > boxHeight){
				boxHeight = currHeight;
			}
		});

		$('.planbox').css('min-height', '' + boxHeight + 'px');     
});