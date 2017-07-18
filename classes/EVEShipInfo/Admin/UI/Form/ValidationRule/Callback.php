<?php

class EVEShipInfo_Admin_UI_Form_ValidationRule_Callback extends EVEShipInfo_Admin_UI_Form_ValidationRule
{
	protected $callback;
	
	public function __construct(EVEShipInfo_Admin_UI_Form $form, EVEShipInfo_Admin_UI_Form_Element $element, $errorMessage, $callback)
	{
		parent::__construct($form, $element, $errorMessage);
		$this->callback = $callback;
	}
	
	public function validate($value)
	{
		$result = call_user_func($this->callback, $value, $this, $this->element);
		if($result === true) {
			return true;
		}
		
		return false;
	}
}