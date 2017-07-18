<?php

class EVEShipInfo_Admin_UI_Form_Element_RadioGroup extends EVEShipInfo_Admin_UI_Form_Element
{
	protected $items = array();
	
	/**
	 * Adds an item to the radio group.
	 * @param string $value
	 * @param string $label
	 * @return EVEShipInfo_Admin_UI_Form_Element_RadioGroup
	*/
	public function addItem($value, $label)
	{
		$option = new EVEShipInfo_Admin_UI_Form_Element_RadioGroup_Item($this, $label, $value);
		$this->items[] = $option;
		return $this;
	}
	
	protected function _renderElement()
	{
		$items = array();
		foreach($this->items as $item) {
			$items[] = $item->render();
		}
			
		return implode('', $items);
	}
}

class EVEShipInfo_Admin_UI_Form_Element_RadioGroup_Item
{
	protected $value;

	protected $label;
	
   /**
    * @var EVEShipInfo_Admin_UI_Form_Element_RadioGroup
    */
	protected $element;

	public function __construct(EVEShipInfo_Admin_UI_Form_Element_RadioGroup $el, $label, $value=null)
	{
		$this->element = $el;
		$this->value = $value;
		$this->label = $label;
	}

	public function render()
	{
		$atts = array(
			'type' => 'radio',
			'name' => $this->element->getName(),
			'value' => $this->value
		);
		
		if($this->element->getValue()==$this->value) {
			$atts['checked'] = 'checked';
		}

		return 
		'<label class="radio-group-item">'.
			'<input'.$this->element->getPlugin()->compileAttributes($atts).'/> '.
			$this->label.
		'</label>';
	}
}
