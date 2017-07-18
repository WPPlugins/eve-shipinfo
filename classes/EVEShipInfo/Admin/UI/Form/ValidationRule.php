<?php

abstract class EVEShipInfo_Admin_UI_Form_ValidationRule
{
	protected $form;
	
	protected $element;
	
	protected $errorMessage;
	
	public function __construct(EVEShipInfo_Admin_UI_Form $form, EVEShipInfo_Admin_UI_Form_Element $element, $errorMessage='')
	{
		$this->form = $form;
		$this->element = $element;
		$this->errorMessage = $errorMessage;
	}
	
   /**
    * Validates the specified value.
    * @param mixed $value
    * @return boolean
    */
	abstract public function validate($value);
	
   /**
    * Allows changing the error message that will be displayed
    * if the rule fails its validation.
    * 
    * @param string $message
    * @return EVEShipInfo_Admin_UI_Form_ValidationRule
    */
	public function setErrorMessage($message)
	{
		$this->errorMessage = $message;
		return $this;
	}
	
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}
}