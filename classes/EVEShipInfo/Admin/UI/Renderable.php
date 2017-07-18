<?php

abstract class EVEShipInfo_Admin_UI_Renderable
{
   /**
    * @var EVEShipInfo_Admin_UI
    */
    protected $ui;
    
    protected $id;
    
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
	public function __construct(EVEShipInfo_Admin_UI $ui, $id)
	{
		$this->plugin = $ui->getPlugin();
		$this->ui = $ui;
		$this->id = $id;
	}

	public function getID()
	{
		return $this->id;
	}
	
   /**
    * @return EVEShipInfo_Admin_UI
    */
	public function getUI()
	{
		return $this->ui;
	}
	
   /**
    * @return EVEShipInfo
    */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
    abstract public function render();
    
    public function display()
    {
    	echo $this->render();
    }
    
    public function toString()
    {
    	return $this->render();
    }
}