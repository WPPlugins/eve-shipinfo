<?php

class EVEShipInfo_Admin_UI
{
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
    public function __construct(EVEShipInfo $plugin)
    {
    	$this->plugin = $plugin;
    	
    	$this->plugin->loadClass('EVEShipInfo_Admin_UI_Button');
    	$this->plugin->loadClass('EVEShipInfo_Admin_UI_Icon');
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Renderable');
    	$this->plugin->loadClass('EVEShipInfo_Admin_UI_StuffBox');
    }
    
   /**
    * @return EVEShipInfo
    */
    public function getPlugin()
    {
    	return $this->plugin;
    }
    
   /**
    * Creates a new stuff box UI element that can be used to 
    * create static or collapsible box elements.
    * 
    * @param string $title
    * @return EVEShipInfo_Admin_UI_StuffBox
    */
    public function createStuffBox($title=null)
    {
    	$box = new EVEShipInfo_Admin_UI_StuffBox($this, $this->plugin->nextJSID());
    	
    	if(!empty($title)) {
    		$box->setTitle($title);
    	}
    	
    	return $box;
    }
    
    public function createForm($name, $defaultValues=array())
    {
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Renderable');
    	$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form');
    	
    	$form = new EVEShipInfo_Admin_UI_Form($this, $name);
    	$form->setDefaultValues($defaultValues);
    	return $form;
    }
    
    public function renderAlertError($text)
    {
    	return $this->renderAlert('error', $text);
    }

    public function renderAlertUpdated($text)
    {
    	return $this->renderAlert('updated', $text);
    }
    
    public function renderAlertWarning($text)
    {
    	return $this->renderAlert('update-nag', $text);
    }
    
    public function renderAlert($type, $text)
    {
    	$html =
    	'<div class="'.$type.'">'.
        	'<p>'.$text.'</p>'.
    	'</div>';
    	
    	return $html;
    }
    
   /**
    * Creates and returns a new button.
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Button
    */
    public function button($label=null)
    {
    	return new EVEShipInfo_Admin_UI_Button($this, $label);
    }
    
   /**
    * Creates and returns a new icon instance.
    * @return EVEShipInfo_Admin_UI_Icon
    */
    public function icon()
    {
    	return new EVEShipInfo_Admin_UI_Icon();
    }
    
    protected $jsOnload = array();
    protected $jsHead = array();
    
   /**
    * Adds a javascript statement intended to be executed on page load. 
    * 
    * @param string $statement
    * @return EVEShipInfo_Admin_UI
    */
    public function addJSOnload($statement)
    {
    	$this->jsOnload[] = rtrim(trim($statement), ';');
    	return $this;
    }
    
   /**
    * Adds a javascript statement intended to to be executed before the page loads.
    * 
    * @param string $statement
    * @return EVEShipInfo_Admin_UI
    */
    public function addJSHead($statement)
    {
    	$this->jsHead[] = rtrim(trim($statement), ';');
    	return $this;
    }
    
    public function renderJS()
    {
    	if(empty($this->jsOnload) && empty($this->jsHead)) {
    		return '';
    	}
    	
    	$head = $this->jsHead;
    	if(!empty($this->jsOnload)) {
    		$head[] = 'jQuery(document).ready(function() {'.implode(';', $this->jsOnload).';})';
    	}
    	
    	return '<script>'.implode(';', $head).';</script>';
    }
}