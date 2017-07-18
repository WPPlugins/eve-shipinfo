<?php

class EVEShipInfo_Admin_SettingsManager_Section_Element_RadioGroup extends EVEShipInfo_Admin_SettingsManager_Section_Element
{
	protected $items;
	
	public function addItem($value, $label)
	{
		$this->items[] = array(
			'value' => $value,
			'label' => $label
		);
		
		return $this;
	}
	
	protected function _render()
	{
		$value = $this->value;
		if(empty($value)) {
			$value = $this->items[0]['value'];
		}
		
		$html = 
		'<fieldset>'.
			'<legend class="screen-reader-text">'.$this->label.'</legend>';
			foreach($this->items as $item) {
				$checked = '';
				if($item['value'] == $value) {
					$checked = ' checked="checked"';
				}
				
				$html .=
				'<label title="'.$item['label'].'">'.
					'<input type="radio" id="'.$this->id.'_'.$item['value'].'" name="'.$this->name.'" value="'.$item['value'].'"'.$checked.'/> '.
					'<span>'.$item['label'].'</span>'.
				'</label>'.
				'<br/>';
			}
			$html .=
			$this->renderDescription().
		'</fieldset>';
		
		return $html;
	}
}