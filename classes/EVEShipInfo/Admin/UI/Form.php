<?php

class EVEShipInfo_Admin_UI_Form extends EVEShipInfo_Admin_UI_Renderable
{
	protected $submitLabel;
	
	protected $submittedVar;

   /**
    * 
    * @param EVEShipInfo_Admin_UI $ui
    * @param string $id Unique ID of the the form, also used for creating its nonce.
    */
	public function __construct(EVEShipInfo_Admin_UI $ui, $id)
	{
		parent::__construct($ui, $id);
		
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element');
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_ElementInput');
		
		$this->submitLabel = __('Save', 'eve-shipinfo');
		$this->submittedVar = $this->id.'_submitted';
	}
	
	protected $buttons = array();
	
   /**
    * Adds a button to the end of the form, next to the submit button.
    * Multiple buttons can be added this way.
    * 
    * @param EVEShipInfo_Admin_UI_Button $button
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function addButton(EVEShipInfo_Admin_UI_Button $button)
	{
		$this->buttons[] = $button;
		return $this;
	}
	
	public function isSubmitted()
	{
		if(isset($_REQUEST[$this->submittedVar]) && $_REQUEST[$this->submittedVar] == 'yes') {
			return true;
		}
		
		return false;
	}
	
	public function setSubmitLabel($label)
	{
		$this->submitLabel = $label;
		return $this;
	}
	
	public function setSubmitIcon(EVEShipInfo_Admin_UI_Icon $icon)
	{
		$this->submitIcon = $icon;
		return $this;
	}
	
	public function getNonceID()
	{
		return 'eveshipinfo-form-'.$this->id;
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Icon
    */
	protected $submitIcon;
	
	public function render()
	{
		$this->addHiddenVar($this->submittedVar, 'yes');
		
		$buttons = $this->buttons;
		$submit = $this->ui->button($this->submitLabel)
		->makeSubmit()
		->setName('save')
		->makePrimary();
		
		if(isset($this->submitIcon)) {
			$submit->setIcon($this->submitIcon);
		}
		
		array_unshift($buttons,	$submit);
		
		$formAtts = array(
			'method' => 'POST',
			'id' => $this->getID()
		);

		if($this->multipart) {
			$formAtts['enctype'] = 'multipart/form-data';
		}
		
		$html = 
		'<form'.$this->plugin->compileAttributes($formAtts).'>';
			foreach($this->hiddens as $name => $value) {
				$html .=
				'<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
			}
			$html .= wp_nonce_field($this->getNonceID());
			$html .=
			'<table class="form-table">'.
				'<tbody>';
					foreach($this->elements as $element) {
						$html .= $element->render();
					}
					$html .=
				'</tbody>'.
				'<tfoot>'.
					'<td></td>'.
					'<td>'.
						'<p class="submit">';
							foreach($buttons as $button) {
								$html .= $button->render();
							}
							$html .=
						'</p>'.
					'</td>'.		
				'</tfoot>'.
			'</table>'.
		'</form>';
		
		// focus on the default element if it has been specified 
		if(isset($this->defaultElement)) {
			$html .= sprintf(
				'<script type="text/javascript">'.
					"jQuery(document).ready(function() {".
						"setTimeout(function() {jQuery('#%s').focus();}, 500);".
					"})".
				'</script>',
				$this->defaultElement->getID()
			);
		}
							
		return $html;
	}
	
	protected $hiddens = array();
	
   /**
    * Adds a hidden form variable.
    * @param string $name
    * @param string $value
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function addHiddenVar($name, $value)
	{
		$this->hiddens[$name] = $value;
		return $this;
	}
	
   /**
    * Adds a regular text element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Text
    */
	public function addText($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Text');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Text($this, $name, $label));
	}
	
   /**
    * Adds a textarea element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Textarea
    */
	public function addTextarea($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Textarea');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Textarea($this, $name, $label));
	}
	
   /**
    * Adds a file upload element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Upload
    */
	public function addUpload($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Upload');
		$this->setMultipart();
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Upload($this, $name, $label));
	}
	
   /**
    * Adds a radio group element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_RadioGroup
    */
	public function addRadioGroup($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_RadioGroup');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_RadioGroup($this, $name, $label));
	}

   /**
    * Adds a checkbox element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Checkbox
    */
	public function addCheckbox($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Checkbox');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Checkbox($this, $name, $label));
	}
	
	protected $multipart = false;
	
   /**
    * Whether this is a multipart form (when using upload elements).
    * This is automatically set to true if an upload element is added
    * to the form.
    * 
    * @param bool $multipart
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function setMultipart($multipart=true)
	{
		$this->multipart = $multipart;
	}
	
   /**
    * Adds a select element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Select
    */
	public function addSelect($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Select');

		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Select($this, $name, $label));
	}
	
	public function addStatic($label, $content)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Static');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Static($this, $content, $label));
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Form_Element[]
    */
	protected $elements;
	
	protected function addElement(EVEShipInfo_Admin_UI_Form_Element $element)
	{
		$this->elements[] = $element;
		return $element;
	}
	
	protected $defaultValues = array();
	
	public function setDefaultValues($values)
	{
		$this->defaultValues = $values;
		return $this;
	}
	
	public function getDefaultValue($name)
	{
		if(isset($this->defaultValues[$name])) {
			return $this->defaultValues[$name];
		}
		
		return null;
	}
	
	protected $defaultElement;
	
   /**
    * Selects which element in the form will get the focus on page load.
    * 
    * @param EVEShipInfo_Admin_UI_Form_Element $element
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function setDefaultElement(EVEShipInfo_Admin_UI_Form_Element $element)
	{
		$this->defaultElement = $element;
		return $this;
	}
	
   /**
    * Validates the form if it has been submitted. Returns
    * whether it is valid, and automatically marks elements
    * as erroneous that have errors. 
    * 
    * @return boolean
    */
	public function validate()
	{
		if(!$this->isSubmitted()) {
			return false;
		}
		
		check_admin_referer($this->getNonceID());
		
		$valid = true;
		$total = count($this->elements);
		for($i=0; $i<$total; $i++) {
			if(!$this->elements[$i]->validate()) {
				$valid = false;
			}
		}
		
		return $valid;
	}
	
   /**
    * Retrieves an associative array with element name > value pairs.
    * @return multitype:<string,mixed>
    */
	public function getValues()
	{
		$values = array();
		$total = count($this->elements);
		for($i=0; $i<$total; $i++) {
			$element = $this->elements[$i];
			$values[$element->getName()] = $element->getValue();
		}
		
		return $values;
	}
	
   /**
    * Retrieves a form element instance by its name.
    * @param string $name
    * @return EVEShipInfo_Admin_UI_Form_Element|NULL
    */
	public function getElementByName($name)
	{
		$total = count($this->elements);
		for($i=0; $i<$total; $i++) {
			if($this->elements[$i]->getName() == $name) {
				return $this->elements[$i];
			}
		}
		
		return null;
	}
}
