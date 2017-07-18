<?php

class EVEShipInfo_Admin_UI_Form_Element_Textarea extends EVEShipInfo_Admin_UI_Form_Element
{
	protected $matchRows = false;
	
	protected function _renderElement()
	{
		$value = $this->getValue();
		
		if($this->matchRows && !empty($value)) {
			$lineCount = count(explode("\n", $value));
			$rows = $this->getAttribute('rows', 0);
			if($lineCount > $rows) {
				$this->setAttribute('rows', $lineCount);
			}
		}
		
		$html = 
		'<textarea'.$this->renderAttributes().'>'.$value.'</textarea>';
			
		return $html;
	}
	
	public function matchRows()
	{
		$this->matchRows = true;
		return $this;
	}
	
	public function setRows($rows)
	{
		return $this->setAttribute('rows', $rows);
	}
}
