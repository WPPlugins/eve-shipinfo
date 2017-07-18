var FittingsList = 
{
	'allSelected':false,
	
	ToggleAll:function()
	{
		jQuery('.fits-toggler').prop('checked', false);
		
		if(this.allSelected) {
			jQuery('.fit-checkbox').prop('checked', false);
			this.allSelected = false;
			return;
		}
		
		jQuery('.fit-checkbox').prop('checked', true);
		this.allSelected = true;
	},
	
	ToggleVisibility:function(fitID, jsID)
	{
		// avoid the text being selected after the doubleclick
		var sel = window.getSelection();
		if(sel) { sel.removeAllRanges(); }
		
		var elPublic = jQuery('#'+jsID+'-public');
		var elPrivate = jQuery('#'+jsID+'-private');
		var elLoader = jQuery('#'+jsID+'-loading');

		var payload = {
			'fitID':fitID,
			'changeTo':'private'
		};
		
		if(elPrivate.is(':visible')) {
			payload.changeTo = 'public';
		}

		elPublic.hide();
		elPrivate.hide();
		elLoader.show();
		
		EVEShipInfo_Admin.AJAX(
			'FittingSetVisibility', 
			payload,
			function(data) {
				FittingsList.Handle_ToggleVisibilitySuccess(data, jsID);
			},
			function(errorMessage) {
				FittingsList.Handle_ToggleVisibilityFailure(errorMessage, payload, jsID);
			}
		);
	},
	
	Handle_ToggleVisibilityFailure:function(errorMessage, payload, jsID)
	{
		console.log('ERROR | Could not update visibility | '+errorMessage);
		
		jQuery('#'+jsID+'-loading').hide();
		
		if(payload.changeTo == 'public') {
			jQuery('#'+jsID+'-private').show();
		} else {
			jQuery('#'+jsID+'-public').show();
		}
	},
	
	Handle_ToggleVisibilitySuccess:function(response, jsID)
	{
		jQuery('#'+jsID+'-loading').hide();
		
		if(response.visibility == 'private') {
			jQuery('#'+jsID+'-private').show();
		} else {
			jQuery('#'+jsID+'-public').show();
		}
	}
};