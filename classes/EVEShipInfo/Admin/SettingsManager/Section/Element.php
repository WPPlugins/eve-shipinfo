<?php

abstract class EVEShipInfo_Admin_SettingsManager_Section_Element
{
	protected $name;
	
   /**
    * @var EVEShipInfo_Admin_SettingsManager_Section
    */
	protected $section;
	
	protected $value;
	
	protected $label;
	
	protected $description;
	
   /**
    * @var EVEShipInfo_Admin_SettingsManager
    */
	protected $manager;
	
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	public function __construct(EVEShipInfo_Admin_SettingsManager_Section $section, $name, $label)
	{
		$this->plugin = EVEShipInfo::getInstance();
		$this->manager = $section->getManager();
		$this->section = $section;
		$this->name = $name;
		$this->label = $label;
		$this->id = $name;
	}
	
	public function init()
	{
		add_settings_field(
			$this->name,
			$this->label,
			array($this, 'display'),
			$this->manager->getID(),
			$this->section->getID()
		);
		
		register_setting($this->manager->getID(), $this->name);
	}
	
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}
	
	public function setDescription($text)
	{
		$this->description = $text;
		return $this;
	}
	
	public function render()
	{
		if(!isset($this->value)) {
			$this->value = $this->plugin->getSetting($this->name);
		}
		
		return $this->_render();
	}
	
	
	abstract protected function _render();
	
	public function display()
	{
		echo $this->render();
	}
	
	protected function renderDescription()
	{
		if(!isset($this->description)) {
			return '';
		}
		
		return 
		'<p class="description">'.
			$this->description.
		'</p>';
	}
}