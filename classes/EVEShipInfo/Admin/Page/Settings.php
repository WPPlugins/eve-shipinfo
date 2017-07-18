<?php

class EVEShipInfo_Admin_Page_Settings extends EVEShipInfo_Admin_Page
{
	public function getTabs()
	{
		return array(
        	'Basic' => __('Basic settings', 'eve-shipinfo'),
        	//'Help' => __('Help', 'eve-shipinfo'),
        	//'Info' => __('Database reference', 'eve-shipinfo'),
        	//'Shortcodes' => __('Shortcordes reference', 'eve-shipinfo')
        );
	}
	
	public function getTitle()
	{
		return sprintf(__('%1$s Settings', 'eve-shipinfo'), EVEShipInfo::APPNAME);
	}
}