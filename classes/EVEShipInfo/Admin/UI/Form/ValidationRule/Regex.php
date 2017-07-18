<?php

class EVEShipInfo_Admin_UI_Form_ValidationRule_Regex extends EVEShipInfo_Admin_UI_Form_ValidationRule
{
	protected $regex;
	
	public function __construct(EVEShipInfo_Admin_UI_Form $form, EVEShipInfo_Admin_UI_Form_Element $element, $errorMessage, $regex)
	{
		parent::__construct($form, $element, $errorMessage);
		$this->regex = $regex;
	}
	
	public function validate($value)
	{
		$result = preg_match($this->regex, $value);
		if($result !== false && $result > 0) {
			return true;
		}
		
		return false;
	}
}