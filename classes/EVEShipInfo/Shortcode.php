<?php

abstract class EVEShipInfo_Shortcode
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	protected $attribs;
	
	protected $content;
	
	protected $id;
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
	public function __construct(EVEShipInfo $plugin)	
	{
		$this->plugin = $plugin;
		$this->collection = $this->plugin->createCollection();
		$this->id = $this->plugin->nextJSID();	
	}
	
	public function getDefaultAttributes()
	{
		return array();
	}
	
	public function getID()
	{
		return str_replace('EVEShipInfo_Shortcode_', '', get_class($this));
	}
	
	public function handle_call($attributes, $content=null)
	{
		if(!$this->plugin->isDatabaseUpToDate()) {
			return '<!-- EVEShipInfo Error: Database not set up. -->';
		}
		
		$this->attribs = shortcode_atts($this->getDefaultAttributes(), $attributes);
		$this->content = $content;

		try
		{
			$this->renderShortcode();
		} 
		catch(EVEShipInfo_Exception $e) 
		{
			$this->content = 'EVEShipinfo Error #'.$e->getCode().' '.$e->getMessage();
		}
		
		return $this->content;
	}
	
	abstract protected function renderShortcode();
	
	
	protected function getAttribute($name, $default=null)
	{
		if(isset($this->attribs[$name])) {
			return $this->attribs[$name];
		}
		
		return $default;
	}
	
   /**
    * Retrieves the URL to the help page for this shortcode in the administration area.
    * @return string
    */
	public function getAdminHelpURL()
	{
		return admin_url('admin.php?page=eveshipinfo_shortcodes&amp;shortcode='.$this->getID());
	}
	
	abstract public function getName();
	
	abstract public function getTagName();
	
	abstract public function getDescription();

	public function getExamples()
	{
		$examples = $this->_getExamples();
		$tagName = $this->getTagName();
		
		foreach($examples as $idx => $def) {
			$examples[$idx]['shortcode'] = str_replace('TAGNAME', $tagName, $def['shortcode']);
		}
		
		return $examples;
	}
	
	abstract protected function _getExamples();
	
	abstract protected function _describeAttributes();
	
	public function describeAttributes()
	{
		$defaults = $this->getDefaultAttributes();
		$attribs = $this->_describeAttributes();
		$names = array_keys($attribs);
		foreach($names as $name) {
			$default = null;
			if(isset($defaults[$name])) {
				$default = $defaults[$name];
			}
			
			$attribs[$name]['default'] = $default;
		}
		
		return $attribs;
	}
}