var EVEShipInfo_SlotTypes = null; // set serverside
var EVEShipInfo_MetaTypes = null; // set serverside

/**
 * Class handling a single ship fitting: manages creating 
 * the fitting box that gets displayed when a fit link is
 * clicked.
 * 
 * @module EVEShipInfo
 * @class EVEShipInfo_Fitting
 * @constructor
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 * @link http://eve.aeonoftime.com
 */
var EVEShipInfo_Fitting = function(linkID, fittingID, name, shipName, shipID) 
{
	this.linkID = linkID;
	this.jsID = linkID + '-fit';
	this.id = fittingID;
	this.name = name;
	this.ship = {
		'name':shipName,
		'id':shipID
	};
	this.slots = [];
	this.shown = false;
	this.rendered = false;
	
	this.ElementID = function(part)
	{
		return this.jsID+'_'+part;
	};
	
	this.Element = function(part)
	{
		return jQuery('#'+this.ElementID(part));
	};

	this.Show = function()
	{
		if(this.shown) {
			this.Hide();
			return;
		}
		
		var link = jQuery('#'+this.linkID);
		if(link.length == 0) {
			return;
		}
		
		if(!this.rendered) {
			var html = ''+
			'<div id="'+this.jsID+'" class="shipinfo-fittingbox">'+
				'<div class="shipinfo-fittingbox-wrap">'+
					'<div class="shipinfo-fittingbox-header">'+
						'<span class="shipinfo-fittingbox-fitname">'+
							this.name+' '+
						'</span>'+
						' '+
						'<span class="shipinfo-fittingbox-shipname">'+
							'<a href="javascript:void(0)" class="shipinfo-shiplink" onclick="EVEShipInfo.InfoPopup(\''+this.ship.id+'\')">'+
								this.ship.name+
							'</a>'+
						'</span>'+
					'</div>'+
					'<div class="shipinfo-fittingbox-content" id="'+this.ElementID('content')+'">'+
						this.ExportTextonly(true)+
					'</div>'+
					'<div class="shipinfo-nav-wrapper shipinfo-fitting-nav">'+
						'<div class="shipinfo-dismiss" id="'+this.ElementID('dismiss')+'">&times;</div>'+
						'<ul class="shipinfo-nav">'+
							'<li class="shipinfo-nav-item" id="'+this.ElementID('nav-evepraisal')+'">'+
								'EVEPraisal'+
							'</li>'+
							'<li class="shipinfo-nav-item" id="'+this.ElementID('nav-copy')+'">'+
								EVEShipInfo_Translation.Translate('Copy')+
							'</li>';
			
							if(EVEShipInfo.IsAdmin()) {
								html += ''+
								'<li class="shipinfo-nav-item" id="'+this.ElementID('nav-edit')+'">'+
									EVEShipInfo_Translation.Translate('Edit')+
								'</li>';
							}
			
							html += ''+
						'</ul>'+
					'</div>'+
					'<div style="display:none">'+
						'<form action="http://evepraisal.com/estimate" method="post" target="_blank" id="'+this.ElementID('praisalform')+'">'+
							'<input type="hidden" name="raw_paste" value="'+this.ExportTextonly()+'"/>'+
							'<input type="hidden" name="hide_buttons" value="false"/>'+
							'<input type="hidden" name="paste_autosubmit" value="false"/>'+
							'<input type="hidden" name="market" value="30000142"/>'+
							'<input type="hidden" name="save" value="true"/>'+
						'</form>'+
					'</div>'+
				'</div>'+
			'</div>';
			
			link.after(html);
			this.rendered = true;
			
			var fit = this;
			setTimeout(function() {
				fit.PostRender();
			},80);
		} else {
			jQuery('#'+this.jsID).show();
		}
		
		this.shown = true;
	};
	
	this.PostRender = function()
	{
		var fit = this;
		
		this.Element('dismiss').on('click', function() {
			fit.Hide();
		});
		
		this.Element('nav-evepraisal').click(function() {
			jQuery('#'+fit.ElementID('praisalform')).submit();
		});
		
		this.Element('nav-copy').click(function() {
			fit.CopyToClipboard(fit.ElementID('content'));
		});

		if(EVEShipInfo.IsAdmin()) {
			this.Element('nav-edit').click(function() {
				document.location = EVEShipInfo.adminBaseURL + 'page=eveshipinfo_eftfittings&action=edit&fid='+fit.id;
			});
		}
	};
	
	this.Hide = function()
	{
		jQuery('#'+this.jsID).hide();
		this.shown = false;
	};
	
	this.ExportHTML = function(header)
	{
		if(header != false) {
			header = true;
		}
		
		return this.Export('<br/>', header);
	};
	
	this.ExportTextonly = function(header)
	{
		if(header != true) {
			header = false;
		}
		
		return this.Export('\n', header);
	};
	
	this.Export = function(lineSeparator, header)
	{
		var slots = this.slots;
		var html = '';
		
		if(header===true) {
			html += '[' + this.ship.name + ', '+this.GetName() + ']'+lineSeparator;
		}
		
		jQuery.each(EVEShipInfo_SlotTypes, function(idx, slotType) {
			var amount = 0;
			jQuery.each(slots, function(idx, slot) {
				if(slot.GetType() == slotType) {
					html += slot.Render() + lineSeparator;
					amount++;
				}
			});
			
			if(amount > 0) {
				html += lineSeparator;
			}
		});
		
		return html;
	};
	
	this.GetShipID = function()
	{
		return this.ship.id;
	};
	
	this.GetName = function()
	{
		return this.name;
	};

	this.AddSlot = function(id, itemName, amount, slotType, meta)
	{
		var slot = new EVEShipInfo_Fitting_Slot(id, itemName, amount, slotType, meta);
		this.slots.push(slot);
		return slot;
	};
	
	this.CopyToClipboard = function(containerid)
	{
		if (document.selection) 
		{ 
		    var range = document.body.createTextRange();
		    range.moveToElementText(document.getElementById(containerid));
		    range.select().createTextRange();
		    document.execCommand("Copy"); 
		} 
		else if (window.getSelection) 
		{
			window.getSelection().removeAllRanges();
			var range = document.createRange();
			range.selectNode(document.getElementById(containerid));
			window.getSelection().addRange(range);
			document.execCommand("Copy");
		}
    }
};

var EVEShipInfo_Fitting_Slot = function(id, itemName, amount, slotType, meta)
{
	this.id = id;
	this.itemName = itemName;
	this.amount = amount;
	this.slotType = slotType;
	this.meta = meta;
	
	this.Render = function()
	{
		var text = this.itemName;
		if(this.amount !== null && this.amount > 0) {
			text += ' x '+this.amount;
		}
		
		return text;
	},
	
	this.GetType = function()
	{
		return this.slotType;
	}
};