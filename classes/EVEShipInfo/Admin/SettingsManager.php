<?php

class EVEShipInfo_Admin_SettingsManager
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	protected $id;
	
   /**
    * @var EVEShipInfo_Admin_SettingsManager_Section[]
    */
	protected $sections = array();
	
	public function __construct($id)
	{
		$this->plugin = EVEShipInfo::getInstance();
		$this->id = $id;
	}
	
	public function initSettings()
	{
		foreach($this->sections as $section) {
			$section->init();
		}
	}
	
	public function getID()
	{
		return $this->id;
	}
	
   /**
    * Creates a new section instance, adds it to the collection and returns it.
    * @param string $id
    * @param string $title
    * @return EVEShipInfo_Admin_SettingsManager_Section
    */
	public function addSection($id, $title)
	{
		$section = $this->createSection($id, $title);
		$this->sections[] = $section;
		return $section;
	}
	
   /**
    * Creates a new settings section.
    * @param string $id
    * @param string $title
    * @return EVEShipInfo_Admin_SettingsManager_Section
    */
	protected function createSection($id, $title)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_SettingsManager_Section');
		return new EVEShipInfo_Admin_SettingsManager_Section($this, $id, $title);
	}
	
	public function render()
	{
		$html =
		'<form method="post" action="options.php">';
			ob_start();
			settings_fields($this->id);
			do_settings_sections($this->id);
			submit_button();
			$html .=
			ob_get_clean().
		'</form>';
		
		return $this->plugin->getAdminUI()->createStuffBox()
		->setContent($html)
		->render();
	}
}