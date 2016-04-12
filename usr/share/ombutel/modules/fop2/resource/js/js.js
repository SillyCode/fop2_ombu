$(document).ready(function() {
	var iframe = $('iframe#fop2internal');
	$('button.fop2popout').bind( "click", function() {
		iframe.detach();
	});

	$('button.fop2model_close').bind( "click", function() {
		$('div#fop2general').append(iframe);
	});
});
