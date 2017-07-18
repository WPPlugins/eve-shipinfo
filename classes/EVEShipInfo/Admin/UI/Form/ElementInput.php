<?php

abstract class EVEShipInfo_Admin_UI_Form_ElementInput extends EVEShipInfo_Admin_UI_Form_Element
{
	public function setPlaceholder($text)
	{
		return $this->setAttribute('placeholder', $text);
	}
	
	abstract function getType();

	protected function _renderElement()
	{
		$this->setAttribute('type', $this->getType());
		$this->setAttribute('value', $this->getValue());
		
		return '<input'.$this->renderAttributes().'/>';
	}
}