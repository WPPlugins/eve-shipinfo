var EVEShipInfo_Themes = 
{
	BaseURL:null, // set serverside
	
	themes:[],
	
	Register:function(jsID, themeID, label)
	{
		var theme = new EVEShipInfo_Themes_Theme(jsID, themeID, label);
		this.themes.push(theme);
		return theme;
	}
};

var EVEShipInfo_Themes_Theme = function(jsID, themeID, label)
{
	this.init(jsID, themeID, label);
};

var EVEShipInfo_Themes_ThemeClass = 
{
	jsID:null,
	themeID:null,
	label:null,
	substyles:null,
	active:null,
	
	init:function(jsID, themeID, label)
	{
		this.jsID = jsID;
		this.themeID = themeID;
		this.label = label;
		this.substyles = [];
		this.active = 0;
	},
	
	Start:function()
	{
		if(this.substyles.length < 1) {
			return;
		}
		
		var active = 0;
		jQuery.each(this.substyles, function(idx, substyle) {
			if(substyle.active) {
				active = idx;
				return false;
			}
		});
		
		this.active = active;
		this.ShowSubstyle(this.active);
	},	
	
	RegisterSubstyle:function(name, label, active)
	{
		this.substyles.push({
			'name':name,
			'label':label,
			'active':active
		});
	},
	
	Next:function()
	{
		this.active++;
		if(this.active >= this.substyles.length) {
			this.active = 0;
		}
		
		this.ShowSubstyle(this.active);
	},
	
	Previous:function()
	{
		this.active--;
		if(this.active < 0) {
			this.active = this.substyles.length - 1;
		}
		
		this.ShowSubstyle(this.active);
	},
	
	ShowSubstyle:function(number)
	{
		var substyle = this.substyles[number];
		jQuery('#'+this.jsID+'-thumb').attr('src', EVEShipInfo_Themes.BaseURL+'/'+this.themeID+'/'+substyle.name+'/preview.jpg');
		jQuery('#'+this.jsID+'-substyle-label').html(substyle.label);
		jQuery('#'+this.jsID+'-position').html((number+1));
	},
	
	ToggleThumbnail:function()
	{
		var thumb = jQuery('#'+this.jsID+'-thumb');
		if(thumb.hasClass('maximized')) {
			thumb.removeClass('maximized');
		} else {
			thumb.addClass('maximized');
		}
	},
	
	Apply:function()
	{
		 var url = EVEShipInfo_Themes.PageURL + '&themeID='+this.themeID;
		 if(this.substyles.length > 0) {
			 url += '&substyle=' + this.substyles[this.active].name;
		 }
		
		 document.location.href = url;
	}
};

jQuery.extend(EVEShipInfo_Themes_Theme.prototype, EVEShipInfo_Themes_ThemeClass);