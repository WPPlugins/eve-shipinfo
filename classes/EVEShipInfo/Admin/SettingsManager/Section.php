<?php

class EVEShipInfo_Admin_SettingsManager_Section
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
 
   /**
    * @var EVEShipInfo_Admin_SettingsManager
    */
	protected $manager;
	
	protected $content = '';
	
	public function __construct(EVEShipInfo_Admin_SettingsManager $manager, $id, $title)
	{
		$this->plugin = EVEShipInfo::getInstance();
		$this->manager = $manager;
		$this->id = $id;
		$this->title = $title;
	}
	
	public function init()
	{
		add_settings_section(
			$this->id,
			$this->title,
			array($this, 'display'),
			$this->manager->getID()
		);
		
		foreach($this->elements as $element) {
			$element->init();
		}
	}
	
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function render()
	{
		return $this->content;
	}
	
	public function display()
	{
		echo $this->render();
	}
	
   /**
    * Retrieves the instance of the settings manager this section is a part of.
    * @return EVEShipInfo_Admin_SettingsManager
    */
	public function getManager()
	{
		return $this->manager;
	}
	
   /**
    * @var EVEShipInfo_Admin_SettingsManager_Section_Element[]
    */
	protected $elements;
	
	/**
	 * Adds a radio group field, and returns the element instance
	 * to configure it further.
	 *
	 * @param string $name
	 * @param string $label
	 * @return EVEShipInfo_Admin_SettingsManager_Section_Element_RadioGroup
	 */
	public function addRadioGroup($name, $label)
	{
	    $element = $this->createElement('RadioGroup', $name, $label);
	    $this->elements[] = $element;
	    
	    return $element;
	}
	
	/**
	 * Creates a form element to use in the section.
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return EVEShipInfo_Admin_SettingsManager_Section_Element
	 */
	protected function createElement($type, $name, $label)
	{
	    $this->plugin->loadClass('EVEShipInfo_Admin_SettingsManager_Section_Element');
	
	    $className = 'EVEShipInfo_Admin_SettingsManager_Section_Element_'.$type;
	    $this->plugin->loadClass($className);
	
	    return new $className($this, $name, $label);
	}
	
}