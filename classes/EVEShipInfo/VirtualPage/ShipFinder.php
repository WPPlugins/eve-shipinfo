<?php

class EVEShipInfo_VirtualPage_ShipFinder extends EVEShipInfo_VirtualPage
{
	public function renderTitle()
	{
		return __('Ship finder', 'eve-shipinfo');
	}

   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
   /**
    * @var EVEShipInfo_Collection_Filter
    */
	protected $filter;
	
	public function renderContent()
	{
	    $this->collection = $this->plugin->createCollection();
	    $this->filter = $this->collection->createFilter();
	    
	    $html = 
	    $this->renderForm().
	    do_shortcode($this->renderShortcode());
	    
		return $html;
	}
	
	protected function renderShortcode()
	{
		return '[shipinfo_list show="10"][/shipinfo_list]';
	}

	public function getGUID()
	{
		return 'eveshipinfo-shipfinder';
	}
	
	public function getPostName()
	{
		return 'eve/shipfinder';
	}
	
	protected function renderForm()
	{
	    $races = $this->collection->getRaces();

	    $html =
   	    '<form method="post" id="eveshipinfo_shipfinder">'.
   	    	'<button type="button" class="btn-configure" onclick="EVEShipInfo_ShipFinder.DialogConfigure()">'.__('Configure the list...', 'eve-shipinfo').'</button>'.
	    '</form>';
	    
	    return $html;;
	}
	
}