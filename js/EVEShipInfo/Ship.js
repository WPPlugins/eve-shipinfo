/**
 * Class handling a single ship: manages creating the required markup
 * for the ship's info window, as well as displaying it when needed.
 * 
 * @module EVEShipInfo
 * @class EVEShipInfo_Ship
 * @constructor
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 * @link http://eve.aeonoftime.com
 */
var EVEShipInfo_Ship = function(collection, data)
{
	this.collection = collection;
	this.data = data;
	this.namespace = 'shipinfo'+this.data.id;
	this.defaultTab = 'FrontView';
	this.rendered = false;
	this.renderedTabs = {};
	this.tabs = {
		'FrontView':'Front view',
		'SideView':'Side view',
		'Description':'Description',
		'Attributes':'Attributes',
		'Fittings':'Fittings'
	};
	
	console.log(this.data);
	
   /**
    * Opens the ship's information popup. Creates it as needed.
    * @method InfoPopup
    */
	this.InfoPopup = function()
	{
		if(!this.IsTabEnabled(this.defaultTab)) {
			this.defaultTab = 'Description';
		}
		
		if(!this.rendered) {
			this.RenderPopup();
			var ship = this;
			setTimeout(function() {
				ship.Handle_PostRender();
			}, 160);
			return;
		}
		
		var el = this.Element('popup');
		el.show();
		
		var ship = this;
		
		// add en event handler to close the popup if the user clicks
		// outside of it.
		jQuery(document).on('mouseup.'+this.namespace, function (e)
		{
		    if (!el.is(e.target) && el.has(e.target).length === 0) {
		        ship.ClosePopup();
		    }
		});
	};
	
	this.ClosePopup = function()
	{
		jQuery(document).off('mouseup.'+this.namespace); // clear the event handler
		this.Element('popup').hide();
	};
	
	this.RenderPopup = function()
	{
		console.log('Rendering the popup scaffold');
		
		this.rendered = true;
		var ship = this;
		
		var html = ''+
		'<div class="shipinfo-popup" id="'+this.ElementID('popup')+'" style="display:none;">'+
			'<div class="shipinfo-header">'+
				'<span class="shipinfo-shipname">'+this.data.name+'</span> '+
				'<span class="shipinfo-race-group">'+
					this.data.raceName+' '+this.data.groupName+
				'</span>'+
			'</div>'+
			'<div class="shipinfo-content-wrapper" id="'+this.ElementID('content_wrapper')+'">';
				jQuery.each(this.tabs, function(tabID, tabLabel) {
					if(ship.IsTabEnabled(tabID)) {
						html += ''+
						'<div class="shipinfo-tab shipinfo-'+tabID.toLowerCase()+'">'+
							'<div class="shipinfo-tab-wrap" id="'+ship.ElementID('tabcontent_'+tabID)+'">'+
								tabLabel+
							'</div>'+
						'</div>';
					}
				});
				html += ''+
			'</div>'+
			'<div class="shipinfo-nav-wrapper">'+
				'<div class="shipinfo-dismiss" id="'+this.ElementID('dismiss')+'">&times;</div>'+
				'<ul class="shipinfo-nav">';
					jQuery.each(this.tabs, function(tabID, tabLabel) {
						if(ship.IsTabEnabled(tabID)) {
							html += ''+
							'<li id="'+ship.ElementID('tablink_'+tabID)+'" class="shipinfo-nav-item">'+
								tabLabel+
							'</li>';
						}
					});
					html += ''+
				'</ul>'+
			'</div>'+
		'</div>';
		
		jQuery('body').append(html);
	};
	
	this.IsTabEnabled = function(tabID)
	{
		var method = 'IsTabEnabled_'+tabID;
		return this[method].call(this);
	};
	
	this.IsTabEnabled_FrontView = function()
	{
		if(typeof(this.data.screenshots.Front) == 'undefined' || !this.data.screenshots.Front.exists) {
			return false;
		}
		
		return true;
	};
	
	this.IsTabEnabled_SideView = function()
	{
		if(typeof(this.data.screenshots.Side) == 'undefined' || !this.data.screenshots.Side.exists) {
			return false;
		}
		
		return true;
	};
	
	this.IsTabEnabled_Attributes = function()
	{
		return true;
	};
	
	this.IsTabEnabled_Description = function()
	{
		return true;
	};
	
	this.IsTabEnabled_Fittings = function()
	{
		return this.HasFittings();
	};
	
	this.ElementID = function(part)
	{
		return this.namespace+'_'+part;
	};
	
	this.Element = function(part)
	{
		return jQuery('#'+this.ElementID(part));
	};
	
	this.Handle_PostRender = function()
	{
		console.log('Executing post render tasks');
		
		var ship = this;
		var el = this.Element('popup');
		var width = el.width();
		
		el.css({
			'position':'absolute',
			'left':'50%',
			'top':(jQuery(window).scrollTop()+50)+'px',
			'margin-left':-width/2,
			'margin-right':-width/2
		});
		
		jQuery.each(this.tabs, function(tabID, tabLabel) {
			tablink = ship.Element('tablink_'+tabID).click(function() {
				ship.Handle_ClickTab(tabID);
			});
		});

		this.Element('dismiss').click(function() {
			ship.ClosePopup();
		});
		
		this.ActivateTab(this.defaultTab);
		this.InfoPopup();
	};
	
	this.ActivateTab = function(tabID)
	{
		console.log('Activating tab '+tabID);
		
		var ship = this;
		var wrapper = this.Element('content_wrapper');
		
		jQuery.each(this.tabs, function(id, label) {
			linkEl = ship.Element('tablink_'+id);
			tabEl = wrapper.find('.shipinfo-'+id.toLowerCase());
			if(id != tabID) {
				tabEl.hide();
				linkEl.removeClass('shipinfo-active');
				wrapper.removeClass(id.toLowerCase());
			} else {
				tabEl.show();
				linkEl.addClass('shipinfo-active');
				wrapper.addClass(id.toLowerCase());
			}
		});
		
		this.RenderTab(tabID);
	};
	
	this.RenderTab = function(tabID)
	{
		// has already been rendered, ignore.
		if(typeof(this.renderedTabs[tabID]) != 'undefined') {
			return;
		}
		
		console.log('Rendering tab '+tabID);
		
		this.renderedTabs[tabID] = true;
		
		var method = 'RenderTab_'+tabID;
		var html = this[method].call(this);
		
		this.Element('tabcontent_'+tabID).html(html);
	};
	
	this.RenderTab_FrontView = function()
	{
		return '<img src="'+this.data.screenshots.Front.url+'"/>';
	};
	
	this.RenderTab_SideView = function(el)
	{
		return '<img src="'+this.data.screenshots.Side.url+'"/>';
	};
	
	this.RenderTab_Description = function(el)
	{
		return this.data.description;
	};
	
   /**
    * Renders the content for the attributes tab. All the available 
    * attributes are detailed in the ship class itself:
    * EVEShipInfo_Collection_Ship::exportData();
    * 
    * @method RenderTab_Attributes
    * @param {DOMElement} el
    * @return {String}
    */
	this.RenderTab_Attributes = function(el)
	{
		launchers = this.TL('No launchers');
		if(this.data.launcherHardpoints == 1) {
			launchers = this.TL('1 launcher');
		} else if(this.data.launcherHardpoints > 0) {
			launchers = this.TL('X launchers').replace('%s', this.data.launcherHardpoints);
		}
		
		turrets = this.TL('No turrets');
		if(this.data.turretHardpoints == 1) {
			turrets = this.TL('1 turret');
		} else if(this.data.turretHardpoints > 1) {
			turrets = this.TL('X turrets').replace('%s', this.data.turretHardpoints);
		}
		
		drones = this.TL('None');
		if(this.data.dronebaySize != '0 M3') {
			drones = this.data.dronebaySize+' / '+this.data.droneBandwidth
		}
		
		var html = ''+
		'<p>'+
			this.TL('Slots')+': '+
			this.data.hiSlots+' / '+
			this.data.medSlots+' / '+
			this.data.lowSlots+' - '+
			launchers+', '+
			turrets+
		'</p>'+
		'<p>'+
			this.TL('Cargo bay')+': '+this.data.cargobaySize+
		'</p>'+
		'<p>'+
			this.TL('Drones')+': '+drones+
		'</p>'+
		'<p>'+
			this.TL('Warp speed')+': '+this.data.warpSpeed+'<br/>'+
			this.TL('Max velocity')+': '+this.data.maxVelocity+'<br/>'+
			this.TL('Agility')+': '+this.data.agility+
		'</p>'+
		'<p>'+
			this.TL('Capacitor')+': '+
				this.TL('X power output').replace('%s', this.data.powerOutput)+' / '+
				this.TL('X capacitor capacity').replace('%s', this.data.capacitorCapacity)+' / '+
				this.TL('X recharge rate').replace('%s', this.data.capacitorRechargeRate)+
		'</p>'+
		'<p>'+
			this.TL('Shield')+': '+
				this.data.shieldHitpoints+' / '+
				this.TL('X recharge rate').replace('%s', this.data.shieldRechargeRate)+
				'<br/>'+
			this.TL('Armor')+': '+
				this.data.armorHitpoints+
				'<br/>'+
			this.TL('Structure')+': '+
				this.data.structureHitpoints+
				' / '+
				this.TL('X signature radius').replace('%s', this.data.signatureRadius)+
		'</p>'+
		'<p>'+
			this.TL('Max target range')+': '+this.data.maxTargetRange+' / '+this.TL('Max locked targets')+': '+this.data.maxLockedTargets+'<br/>'+
			this.TL('Scan speed')+': '+this.data.scanSpeed+' / '+
			this.TL('Scan resolution')+': '+this.data.scanResolution+
		'</p>';
		
		return html;
	};
	
	this.RenderTab_Fittings = function()
	{
		var html = '';
		var fittings = this.GetFittings();
		
		jQuery.each(fittings, function(idx, fit) {
			html += fit.GetName()+'<br/>'+
			'<pre>'+
				fit.ExportHTML()+
			'</pre>';
		});
		
		return html;
	};
	
	this.Handle_ClickTab = function(tabID)
	{
		this.ActivateTab(tabID);
	};
	
	this.TL = function(textID)
	{
		return EVEShipInfo_Translation.Translate(textID);
	};
	
	this.GetFittings = function()
	{
		return EVEShipInfo.GetFittingsByShip(this);
	};
	
	this.GetID = function()
	{
		return this.data.id;
	};
	
	this.GetName = function()
	{
		return this.data.name;
	};
	
	this.HasFittings = function()
	{
		var fittings = this.GetFittings();
		if(fittings.length > 0) {
			return true;
		}
		
		return false;
	};
};