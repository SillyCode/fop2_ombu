$(document).ready(function() {
	var iframe = $('iframe#fop2_userinternal');
	$('button.fop2user_popout').bind( "click", function() {
		iframe.detach();
	});

	$('button.fop2user_model_close').bind( "click", function() {
		$('div#fop2_usergeneral').append(iframe);
	});
});
