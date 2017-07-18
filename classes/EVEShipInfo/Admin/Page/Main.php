<?php

class EVEShipInfo_Admin_Page_Main extends EVEShipInfo_Admin_Page
{
	public function getTabs()
	{
		$tabs = array(
			'Dashboard' => __('Dashboard', 'eve-shipinfo'),
			'Themes' => __('Themes', 'eve-shipinfo'),
        	'Shortcodes' => __('Shortcordes reference', 'eve-shipinfo'),
			'EFTImport' => __('Fittings import', 'eve-shipinfo'),
		    'EFTFittings' => __('Ship fittings', 'eve-shipinfo'),
        	'Database' => __('Database reference', 'eve-shipinfo'),
			'About' => __('About', 'eve-shipinfo')
		);
		
		return $tabs;
	}
	
	public function getTitle()
	{
		return EVEShipInfo::APPNAME;
	}
	
	protected function isTabEnabled($tabID)
	{
		return true;
	}
}