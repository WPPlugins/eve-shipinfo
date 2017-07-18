<?php

abstract class EVEShipInfo_VirtualPage
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	public function __construct(EVEShipInfo $plugin)
	{
		$this->plugin = $plugin;
	}
	
	abstract public function renderTitle();
	
	abstract public function renderContent();
	
	abstract public function getGUID();
}