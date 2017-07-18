var EVEShipInfo_Admin =
{
	AJAX:function(method, payload, successHandler, failureHandler)
	{
		if(payload == null || typeof(payload) != 'object') {
			payload = {};
		}
		
		payload.action = 'eveshipinfo_' + method.toLowerCase();
		
		jQuery.ajax({
			'url':ajaxurl,
			'data':payload,
			'success':function(data) {
				// wordpress returns a 0 when a method does not exist
				if(data == '0') {
					failureHandler.call(undefined, 'Unknown AJAX method');
				} else {
					successHandler.call(undefined, data);
				}
			},
			'error':function(jqXHR, textStatus, errorThrown) {
				failureHandler.call(errorThrown);
			},
			'dataType':'json'
		});
	},
	
	ToggleStuffbox:function(id)
	{
		var container = jQuery('#'+id);
		var handle = container.find('.handlediv');
		var inside = jQuery('#' + id + '-inside');
		
		if(!container.hasClass('closed')) {
			container.addClass('closed');
			handle.attr('aria-expanded', 'false');
			inside.hide();
		} else {
			container.removeClass('closed');
			handle.attr('aria-expanded', 'true');
			inside.show();
		}
	}
};