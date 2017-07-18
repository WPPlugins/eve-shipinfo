/**
 * Clientside script handling the information on ships in a page,
 * and used as a hub to display the information dialogs.
 * 
 * @module EVEShipInfo
 * @class EVEShipInfo
 * @static
 * @main EVEShipInfo
 * @author Sebastian Mordziol <eve@aeonoftime.com> 
 */
var EVEShipInfo =
{
	'ships':{},
	'fittings':{},
	'adminBaseURL':null,
	
	AddShip:function(shipData)
	{
		this.ships[shipData.id] = new EVEShipInfo_Ship(this, shipData);
		return this.ships[shipData.id];
	},
	
	InfoPopup:function(shipID)
	{
		if(typeof(this.ships[shipID])=='undefined') {
			return;
		}
		
		this.ships[shipID].InfoPopup();
	},
	
	AddFit:function(linkID, fittingID, name, shipName, shipID, highSlots, medSlots, lowSlots, rigs, drones)
	{
		this.fittings[fittingID] = new EVEShipInfo_Fitting(linkID, fittingID, name, shipName, shipID, highSlots, medSlots, lowSlots, rigs, drones);
		return this.fittings[fittingID];
	},
	
	ShowFitting:function(fittingID)
	{
		if(typeof(this.fittings[fittingID])=='undefined') {
			return;
		}
		
		this.fittings[fittingID].Show();
	},
	
	GetFittingsByShip:function(ship)
	{
		var fittings = [];
		jQuery.each(this.fittings, function(idx, fitting) {
			if(fitting.GetShipID() == ship.GetID()) {
				fittings.push(fitting);
			}
		});
		
		return fittings;
	},
	
	IsAdmin:function()
	{
		if(jQuery('BODY.admin-bar').length > 0) {
			return true;
		}
		
		return false;
	}
};