<?php

abstract class EVEShipInfo_Admin_UI_Form_Element extends EVEShipInfo_Admin_UI_Renderable
{
   /**
    * @var EVEShipInfo_Admin_UI_Form
    */
	protected $form;
	
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	protected $name;
	
	protected $label;
	
	protected $description = '';
	
	protected $attributes = array();
	
	public function __construct(EVEShipInfo_Admin_UI_Form $form, $name, $label)
	{
		$plugin = $form->getPlugin();
		
		parent::__construct($form->getUI(), $plugin->nextJSID());

		$this->plugin = $plugin;
		$this->form = $form;
		$this->name = $name;
		$this->label = $label;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
   /**
    * Sets the help text to describe the element.
    * @param string $description
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	public function getTypeID()
	{
		return str_replace('EVEShipInfo_Admin_UI_Form_Element_', '', get_class($this));
	}
	
	public function render()
	{
		if(!empty($this->description)) {
			$this->setAttribute('aria-describedby', 'tagline-'.$this->id);
		}
		
		$rowClasses = array('form-field', 'field-'.strtolower($this->getTypeID()));
		if($this->required) {
			$rowClasses[] = 'form-required';
		}
		
		$description = $this->description;
		
		if($this->validated && !$this->valid) {
			$rowClasses[] = 'form-invalid';
			$description = 
			'<b class="validation-message">'.
				__('Note:', 'eve-shipinfo').' '.
				$this->validationMessage.
			'</b>'.
			'<br/>'.
			$description;
		}
		
		$html =
		'<tr class="'.implode(' ', $rowClasses).'">'.
			'<th scope="row">'.
				'<label for="'.$this->id.'">'.
					$this->label;
					if($this->required) {
						$html .= ' <span class="description">('.__('required', 'eve-shipinfo').')</span>';
					}
					$html .=
				'</label>'.
			'</th>'.
			'<td>'.
				$this->_renderElement();
				if(!empty($description)) {
					$html .=
					'<p class="description" id="tagline-'.$this->id.'">'.
						$description.
					'</p>';
				}
				$html .=
			'</td>'.
		'</tr>';
		
		return $html;
	}
	
	protected $rules = array();
	
	protected $validated = false;

	protected $valid = true;
	
	protected $validationMessage;
	
	public function validate()
	{
		if($this->validated) {
			return $this->valid;
		}

		$this->validated = true;
		
		$value = $this->getValue();
		
		// special case: empty value
		if($value===null || $value==='') {
			if($this->required) {
				$this->valid = false;
				$this->validationMessage = __('This element is required.', 'eve-shipinfo');
			}
		} else {
			foreach($this->rules as $rule) {
				if($rule->validate($value)) {
					continue;
				}
				
				$this->valid = false;
				$this->validationMessage = $rule->getErrorMessage();
				break;
			}
		}
		
		return $this->valid;
	}
	
	protected $classes = array();
	
	public function addClass($className)
	{
		if(!in_array($className, $this->classes)) {
			$this->classes[] = $className;
		}
		
		return $this;
	}
	
	protected $styles = array();
	
	public function setStyle($name, $value)
	{
		$this->styles[$name] = $value;
		return $this;
	}
	
	protected $required = false;
	
	public function setRequired($required=true)
	{
		$this->required = $required;
		return $this;
	}
	
	protected $defaultValue = null;
	
	public function setDefaultValue($value)
	{
		$this->defaultValue = $value;
		return $this;
	}
	
	protected function renderAttributes()
	{
		$atts = $this->attributes;
		$atts['id'] = $this->id;
		$atts['name'] = $this->name;
		$atts['class'] = implode(' ', $this->classes);
		$atts['style'] = $this->plugin->compileStyles($this->styles);		
		
		return $this->plugin->compileAttributes($atts);	
	}
	
   /**
    * Sets an attribute of the element.
    * @param string $name
    * @param string $value
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}
	
	abstract protected function _renderElement();
	
	protected $value;
	
	public function getValue()
	{
		if(isset($this->value)) {
			return $this->filter($this->value);
		}
		
		if(isset($_REQUEST[$this->name])) {
			return $this->filter($_REQUEST[$this->name]);
		}
		
		return $this->filter($this->form->getDefaultValue($this->name));
	}
	
	protected $filters = array();
	
   /**
    * Adds a filtering function that will be applied to
    * the value before it is validated.
    * 
    * @param function $filter
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function addFilter($filter)
	{
		$this->filters[] = $filter;
		return $this;
	}
	
	protected function filter($value)
	{
		if($value===null || $value==='') {
			return $value;
		}
		
		foreach($this->filters as $filter) {
			$value = call_user_func($filter, $value);
		}
		
		return $value;
	}
	
   /**
    * Adds a callback as a validation rule. The callback function gets
    * two parameters: the rule instance, and the element being validated.
    * 
    * Example callback function:
    * 
    * <pre>
    * function validation_callback($value, EVEShipInfo_Admin_UI_Form_ValidationRule_Callback $rule, EVEShipInfo_Admin_UI_Form_Element $element)
    * {
    *     // your validation logic
    *     if($value) {
    *         return true;
    *     }
    * 
    *     return false;
    * }
    * </pre>
    * 
    * @param mixed $callback
    * @param string $errorMessage
    */
	public function addCallbackRule($callback, $errorMessage)
	{
		return $this->addRule($this->createCallbackRule($callback, $errorMessage));
	}
	
   /**
    * Creates a callback rule and returns it.
    * @param mixed $callback
    * @param string $errorMessage
    * @return EVEShipInfo_Admin_UI_Form_ValidationRule_Callback
    */
	public function createCallbackRule($callback, $errorMessage)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_ValidationRule');
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_ValidationRule_Callback');
		
		$rule = new EVEShipInfo_Admin_UI_Form_ValidationRule_Callback(
			$this->form,
			$this,
			$errorMessage,
			$callback
		);
		
		return $rule;
	}
	
	public function addRegexRule($regex, $errorMessage)
	{
		return $this->addRule($this->createRegexRule($regex, $errorMessage));
	}
	
	public function createRegexRule($regex, $errorMessage)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_ValidationRule');
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_ValidationRule_Regex');
	
		$rule = new EVEShipInfo_Admin_UI_Form_ValidationRule_Regex(
			$this->form,
			$this,
			$errorMessage,
			$regex
		);
	
		return $rule;
	}
	
	public function addRule(EVEShipInfo_Admin_UI_Form_ValidationRule $rule)
	{
		$this->rules[] = $rule;
		return $this;
	}
	
	protected function setSetting($name, $value)
	{
		return $this->plugin->setOption($this->getUID().'_'.$name, $value);
	}
	
	protected function getSetting($name)
	{
		return $this->plugin->getOption($this->getUID().'_'.$name);
	}
	
	protected function clearSetting($name)
	{
		return $this->plugin->clearOption($this->getUID().'_'.$name);
	}

   /**
    * Retrieves the element's user interface id, which uniquely 
    * identifies it, provided all forms have a unique ID specified.
    * It is a string comprising the form name and element name.
    * 
    * @return string
    */
	protected function getUID()
	{
		return $this->form->getID().'-'.$this->getName();
	}
	
	public function getAttribute($name, $default=null)
	{
		if(isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		
		return $default;
	}
}