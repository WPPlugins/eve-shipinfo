<?php

class EVEShipInfo_Admin_UI_Button 
{
	protected $label;
	
   /**
    * @var UI
    */
	protected $ui;
	
	public function __construct(EVEShipInfo_Admin_UI $ui, $label=null)
	{
	    if($label==null) {
	        $label = '';
	    }
	    
		$this->ui = $ui;
		$this->label = $label;
		$this->id = 'btn'.EVEShipInfo::nextJSID();
	}
	
	protected $attributes = array();
	
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}
	
	protected $classes = array('btn');
	
	public function addClass($name)
	{
		if (!in_array($name, $this->classes)) {
			$this->classes[] = $name;
		}
		
		return $this;
	}
	
	protected $styles = array();
	
	public function addStyle($name, $value)
	{
		$this->styles[$name] = $value;
		return $this;
	}
	
	public function setID($id)
	{
		$this->id = $id;
		return $this;
	}

   /**
    * Styles the button as a primary button.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makePrimary()
	{
		return $this->makeType('primary');
	}
	
   /**
    * Styles the button as a button for a dangerous operation, like deleting records.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makeDangerous()
	{
		return $this->makeType('danger');
	}
	
   /**
    * Styles the button as an informational button.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makeInformational()
	{
		return $this->makeType('info');
	}
	
   /**
    * Styles the button as a success button.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makeSuccess()
	{
		return $this->makeType('success');
	}
	
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makeWarning()
	{
		return $this->makeType('warning');
	}
	
	protected $layout = 'default';
	
	protected function makeType($type)
	{
		$this->layout = $type;
		return $this;
	}
	
	protected $type = 'button';
	
   /**
    * Turns the button into a submit button.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makeSubmit($value=null)
	{
		$this->type = 'submit';
		
		if($value) {
			$this->setAttribute('value', $value);
		}
		
		return $this;
	}

   /**
    * Retrieves the button's ID attribute.
    * 
    * @return String
    */
	public function getID()
	{
		return $this->id;
	}
	
	public function click($statement)
	{
		return $this->setAttribute('onclick', $statement);
	}
	
   /**
    * @var UI_Icon
    */
	protected $icon;
	
	public function setIcon(EVEShipInfo_Admin_UI_Icon $icon)
	{
		$this->icon = $icon;
		return $this;
	}
	
	public function setTitle($title)
	{
		return $this->setAttribute('title', $title);
	}
	
	public function __toString()
	{
		return $this->render();
	}
	
	protected function getAttributes()
	{
		$atts = $this->attributes;
	
		$atts['id'] = $this->id;
		$atts['type'] = $this->type;
		$atts['autocomplete'] = 'off'; // avoid firefox autocompletion bug
	
		$classes = $this->classes;
		$classes[] = 'button';
		$classes[] = 'button-'.$this->layout;
	
		$atts['class'] = implode(' ', $classes);
	
		$title = '';
		if(isset($this->title)) {
			$title = $this->title;
		}
	
		if(!empty($title)) {
			$atts['title'] = $title;
		}
		
		if(isset($this->url)) {
			$atts['href'] = $this->url;
		}
	
		return $atts;
	}
	
   /**
    * Ensures that the text in the button does not wrap to the next line.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function setNowrap()
	{
	    return $this->addClass('text-nowrap');
	}
	
	public function render()
	{
		$atts = $this->getAttributes();
		$tokens = array();
		
		foreach($atts as $name => $value) {
			$tokens[] = $name.'="'.$value.'"';
		}
		
		$label = $this->label;
		if(isset($this->icon)) { 
			$label = $this->icon->render().' '.$label;
		}
		
		$tag = 'button';
		if(isset($this->url)) {
			$tag = 'a';
		}
		
		$html = 
		'<'.$tag.' '.implode(' ', $tokens).'>'.
			$label.
		'</'.$tag.'>';
		
		return $html;
	}
	
	public function display()
	{
		echo $this->render();
	}
	
	protected $url;
	
	public function link($url, $target=null)
	{
	    if(!empty($target)) {
	        $this->setAttribute('target', $target);
	    }
	    
		$this->url = $url;
		return $this;
	}
	
   /**
    * Sets the button as a block element that will fill 
    * all the available horizontal space.
    * 
    * @return EVEShipInfo_Admin_UI_Button
    */
	public function makeBlock()
	{
	    return $this->addClass('btn-block');
	}
	
	public function setName($name)
	{
		return $this->setAttribute('name', $name);
	}
}
