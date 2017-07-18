<?php

class EVEShipInfo_Admin_UI_Form_Element_Checkbox extends EVEShipInfo_Admin_UI_Form_Element
{
	protected function _renderElement()
	{
		$atts = array(
			'name' => $this->name,
			'type' => 'checkbox',
			'value' => 'yes',
			'id' => $this->getID()
		);
		
		if($this->getValue()=='yes') {
			$atts['checked'] = 'checked';
		}
		
		return
		'<fieldset>'.
			'<legend class="screen-reader-text">'.
				$this->label.' '.$this->inlineLabel.
			'</legend>'.
			'<label>'. 
				'<input'.$this->plugin->compileAttributes($atts).'/> '.
				$this->inlineLabel.
			'</label>'.
		'</fieldset>';
	}
	
	protected $inlineLabel;
	
   /**
    * Sets the label to show next to the checkbox itself. 
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Checkbox
    */
	public function setInlineLabel($label)
	{
		$this->inlineLabel = $label;
		return $this;
	}
	
	public function getValue()
	{
		// when the form is submitted, we need to check only
		// the request, because otherwise the default value 
		// would be used, which could be "yes".
		if($this->form->isSubmitted()) {
			if(isset($_REQUEST[$this->name]) && $_REQUEST[$this->name]=='yes') {
				return 'yes';
			}
			
			return 'no';
		}
		
		$value = parent::getValue();
		if($value == 'yes') {
			return 'yes';
		}
		
		return 'no';
	}
}