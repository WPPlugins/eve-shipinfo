var EVEShipInfo_Dashboard = 
{
	BaseURL:null, // set serverside
	
	CheckForUpdate:function()
	{
		jQuery('#updatecheck-uptodate').hide();
		jQuery('#updatecheck-error').hide();
		jQuery('#updatecheck-available').hide();
		
		EVEShipInfo_Admin.AJAX(
			'CheckForUpdate',
			null,
			function(data) {
				EVEShipInfo_Dashboard.Handle_UpdateCheckSuccess(data);
			},
			function(errorMessage) {
				EVEShipInfo_Dashboard.Handle_UpdateCheckFailure(errorMessage);
			}
		);
	},
	
	Handle_UpdateCheckSuccess:function(data)
	{
		if(data.state=='error') {
			jQuery('#updatecheck-error').show();
			return;
		}
		
		var result = data.data;
		
		if(result.updateAvailable == true) {
			jQuery('#updatecheck-remoteversion').html(result.remoteVersion);
			jQuery('#updatecheck-available').show();
			return;
		}
		
		jQuery('#updatecheck-uptodate').show();
	},
	
	Handle_UpdateCheckFailure:function(errorText)
	{
		console.log(errorText);
	}
};