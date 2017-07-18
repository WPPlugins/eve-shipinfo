<?php

class EVEShipInfo_AjaxHandler
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	protected $methodName;
	
	public function __construct(EVEShipInfo $plugin, $methodName)
	{
		$this->plugin = $plugin;
		$this->methodName = $methodName;
	}
	
	public function execute()
	{
		$this->createMethod()->execute();
	}
	
   /**
    * @return EVEShipInfo_AjaxMethod
    */
	protected function createMethod()
	{
		$this->plugin->loadClass('EVEShipInfo_AjaxMethod');
		
		$class = 'EVEShipInfo_AjaxMethod_'.$this->methodName;
		$this->plugin->loadClass($class);

		$method = new $class($this->plugin);
		return $method;
	}
}