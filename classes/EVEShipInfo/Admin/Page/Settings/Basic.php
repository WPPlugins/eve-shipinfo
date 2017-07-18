<?php

class EVEShipInfo_Admin_Page_Settings_Basic extends EVEShipInfo_Admin_Page_Tab
{
   /**
    * @var EVEShipInfo_Admin_SettingsManager
    */
	protected $form;
	
	public function __construct($page)
	{
		parent::__construct($page);
		
		$this->form = $this->createSettings('eveshipinfo_settings');
		
		$section = $this->form->addSection('basic', __('Basic settings', 'eve-shipinfo'));
		$section->addRadioGroup('enable_virtual_pages', __('Enable virtual pages?', 'eve-shipinfo'))
		->addItem('yes', __('Yes, allow both virtual pages and popups', 'eve-shipinfo'))
		->addItem('no', __('No, only use info popups', 'eve-shipinfo'))
		->setDescription(__('When disabled, ship links will only point to popups, and the ship pages will show the blog\'s homepage.', 'eve-shipinfo'));
		
		$section = $this->form->addSection('options', __('Options', 'eve-shipinfo'));
		$section->addRadioGroup('use_minified_js', __('Use minified JS?', 'eve-shipinfo'))
		->setDescription(
			__('When disabled, uses the original javascript files.', 'eve-shipinfo').' '.
			__('Useful for developers to fiddle with the source code.', 'eve-shipinfo')
		)
		->addItem('yes', __('Yes (helps the page load times)', 'eve-shipinfo'))
		->addItem('no', __('No', 'eve-shipinfo'));
	}
	
	public function getTitle()
	{
		return '';
	}
	
	protected function _render()
	{
		return $this->form->render();
	}
	
	protected $formSection = 'eveshipinfo_settings_section';
	
	protected $formPage = 'eveshipinfo_settings';
	
	public function initSettings()
	{
		$this->form->initSettings();
	}
}