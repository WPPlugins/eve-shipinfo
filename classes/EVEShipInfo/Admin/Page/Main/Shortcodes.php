<?php

class EVEShipInfo_Admin_Page_Main_Shortcodes extends EVEShipInfo_Admin_Page_Tab
{
	protected $shortcodes = array();
	
   /**
    * @var EVEShipInfo_Shortcode
    */
	protected $shortcode;
	
	public function __construct(EVEShipInfo_Admin_Page $page)
	{
	    parent::__construct($page);
	    
	    $ids = $this->plugin->getShortcodeIDs();
	    if(isset($_REQUEST['shortcode']) && in_array($_REQUEST['shortcode'], $ids)) {
	    	$this->shortcode = $this->plugin->createShortcode($_REQUEST['shortcode']);
	    }
	}
	
	public function getTitle()
	{
		if(isset($this->shortcode)) { 
			return __('Shortcode reference:', 'eve-shipinfo').' '.$this->shortcode->getName();
		}
		
		return __('Shortcodes reference', 'eve-shipinfo');
	}
	
	protected function _render()
	{
		if(isset($this->shortcode)) {
			return $this->renderShortcode();
		}
		
		$shortcodes = $this->plugin->getShortcodes();
		
		/* @var $shortcode EVEShipInfo_Shortcode */
		
		$html = 
		'<p>'.
			__('Click on a shortcode to view a detailed explanation on how to use it.', 'eve-shipinfo').
		'</p>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('Name', 'eve-shipinfo').'</th>'.
					'<th>'.__('Shortcode', 'eve-shipinfo').'</th>'.
					'<th>'.__('Description', 'eve-shipinfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($shortcodes as $shortcode) {
					$html .=
					'<tr>'.
						'<td>'.
							'<a href="'.$shortcode->getAdminHelpURL().'">'.
								$shortcode->getName().
							'</a>'.
						'</td>'.
						'<td><code>['.$shortcode->getTagName().']</code></td>'.
						'<td>'.$shortcode->getDescription().'</td>'.
					'</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
				
		return $html;
	}
	
	protected function renderShortcode()
	{
		$groups = $this->shortcode->describeAttributes();
		$defaults = $this->shortcode->getDefaultAttributes();
		
		ksort($groups);
		
		$html =
		'<p>'.
			__('Shortcode:', 'eve-shipinfo').' <code>['.$this->shortcode->getTagName().']</code><br/>'.
		'</p>'.
		'<p>'.
			$this->shortcode->getDescription().
		'</p>';
		
		foreach($groups as $groupDef) {
		    $boxHTML =
			'<table class="wp-list-table widefat">'.
				'<thead>'.
					'<tr>'.
						'<th>'.__('Attribute', 'eve-shipinfo').'</th>'.
						'<th width="20%">'.__('Description', 'eve-shipinfo').'</th>'.
						'<th>'.__('Optional', 'eve-shipinfo').'</th>'.
						'<th>'.__('Type', 'eve-shipinfo').'</th>'.
						'<th>'.__('Default value', 'eve-shipinfo').'</th>'.
						'<th>'.__('Values', 'eve-shipinfo').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody>';
		    		
		    		ksort($groupDef['attribs']);
		    		
					foreach($groupDef['attribs'] as $name => $def) {
						$optional = 'No';
						if($def['optional']) {
							$optional = 'Yes';
						}
						
						$values = '';
						if(isset($def['values'])) {
						    ksort($def['values']);
							$values = array();
							foreach($def['values'] as $value => $label) {
								$values[] = '<code>'.$value.'</code> <i>'.$label.'</i>';
							}
							
							$values = implode('<br/>', $values);
						}
						
						$default = '';
						if(isset($defaults[$name]) && !empty($defaults[$name])) {
							$default = '<code>'.$defaults[$name].'</code>';
						}
						
						$type = __('Unknown', 'eve-shipinfo');
						switch($def['type']) {
							case 'number':
							    $type = __('Number', 'eve-shipinfo');
							    break;
							    
							case 'enum':
							    $type = __('Multiple choice', 'eve-shipinfo');
							    break;
							    
							case 'text':
							    $type = __('Text', 'eve-shipinfo');
							    if(isset($def['values'])) {
							    	$type = __('Text/Expression', 'eve-shipinfo');
							    }
							    break;
							    
							case 'commalist':
							    $type = __('Comma-separated list', 'eve-shipinfo');
							    break;
						}
						
					    $boxHTML .=
					    '<tr>'.
					    	'<td><code>'.$name.'</code></td>'.
					    	'<td>'.$def['descr'].'</td>'.
						    '<td>'.$optional.'</td>'.
						    '<td>'.$type.'</td>'.
						    '<td>'.$default.'</td>'.
						    '<td>'.$values.'</td>'.
					    '</tr>';
					}
					$boxHTML .=
				'</tbody>'.
			'</table>';
					
			$html .= $this->ui->createStuffBox($groupDef['group'])
				->setAbstract($groupDef['abstract'])	
				->setContent($boxHTML)
				->setCollapsed()
				->render();
		}

		$exHTML = '';
		$examples = $this->shortcode->getExamples();
		foreach($examples as $example) {
			$exHTML .=
			'<code>'.$example['shortcode'].'</code>'.
			'<p class="description">'.$example['descr'].'</p><br/>';
		}
				
		$html .= $this->ui->createStuffBox(__('Examples', 'eve-shipinfo'))
			->setContent($exHTML)
			->setCollapsed()
			->render();
						
		return $html;
	}
}