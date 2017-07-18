<?php

class EVEShipInfo_Shortcode_ShipInfo extends EVEShipInfo_Shortcode
{
	public function getTagName()
	{
		return 'shipinfo';
	}
	
	public function getDescription()
	{
		return __('Links ship names to either show an information popup, or to go to the virtual ship page.', 'eve-shipinfo');
	}
	
	public function getName()
	{
		return __('Ship info links', 'eve-shipinfo');
	}
	
	protected function renderShortcode()
	{
		$name = $this->getAttribute('name');
		if(empty($name)) {
			$name = trim($this->content);
		}
		
		$id = $this->getAttribute('id');
		
		if(empty($name) && empty($id)) {
			return;
		}
		
		/* @var $ship EVEShipInfo_Collection_Ship */
		
		$ship = null;
		$collection = $this->plugin->createCollection();

		if(!empty($id) && $collection->shipIDExists($id)) {
			$ship = $collection->getShipByID($id);
			if(empty($this->content)) {
				$this->content = $ship->getName();
			}
		} else if(!empty($name) && $collection->shipNameExists($name)) {
			$ship = $collection->getShipByName($name);
		}
		
		if(!$ship) {
			return;
		}
		
		$classes = array(
			$this->plugin->getCSSName('shiplink')
		);
		
		$attribs = array(
			'href' => $ship->getViewURL()
		);
		
		if($this->getAttribute('popup', 'yes')=='yes') {
			$attribs['href'] = 'javascript:void(0)';
			$attribs['onclick'] = sprintf("EVEShipInfo.InfoPopup('%s')", $ship->getID());
			$classes[] = 'popup';
		} else {
			$classes[] = 'virtualpage';
		}
		
		if($this->getAttribute('is_thumbnail')=='yes') {
			$classes[] = 'thumbnail';
		}
		
		$attribs['class'] = implode(' ', $classes);
		
		$this->content =
		'<a'.$this->plugin->compileAttributes($attribs).'>'.
			$this->content.
		'</a>'.
		$ship->renderClientRegistration();
	}
	
	public function getDefaultAttributes()
	{
		return array(
			'name' => '',
			'id' => '',
			'popup' => 'yes',
			'is_thumbnail' => 'no' // private: only used by the gallery
		);
	}
	
	protected function _describeAttributes()
	{
		return array(
	        'settings' => array(
	            'group' => __('Settings'),
	            'abstract' => __('Configuration settings for the link.'),
		        'attribs' => array(
					'name' => array(
						'descr' => __('The name of the ship to link to.', 'eve-shipinfo'),
						'optional' => true,
						'type' => 'text'
					),
					'id' => array(
						'descr' => __('The ID of the ship to link to (takes precedence over the name).', 'eve-shipinfo'),
						'optional' => true,
						'type' => 'text'
					),
					'popup' => array(
						'descr' => __('Whether to show the ship popup or its virtual page when clicked.', 'eve-shipinfo'),
						'optional' => true,
						'type' => 'enum',
						'values' => array(
							'yes' => __('Yes, show a popup', 'eve-shipinfo'), 
							'no' => __('No, link to the virtual page', 'eve-shipinfo')
						)
					)
	            )
            )
		);
	}
	
	protected function _getExamples()
	{
		return array(
			array(
				'shortcode' => '[TAGNAME]Abaddon[/TAGNAME]',
				'descr' => __('Link a ship name to a ship info popup.', 'eve-shipinfo').' '.__('The name of the ship must match exactly, but is case insensitive.', 'eve-shipinfo')
			),
			array(
				'shortcode' => '[TAGNAME name="Abaddon"]'.__('The renowned Abaddon', 'eve-shipinfo').'[/TAGNAME]',
				'descr' => sprintf(__('For a custom link title, specify the ship name using the %1$s attribute.', 'eve-shipinfo'), '<code>name</code>')
			),
			array(
				'shortcode' => '[TAGNAME id="24692"]'.__('The renowned Abaddon', 'eve-shipinfo').'[/TAGNAME]',
				'descr' => __('It is also possible to link a ship by its ID: performance-wise this is slightly better.', 'eve-shipinfo')
			),
			array(
				'shortcode' => '[TAGNAME popup="no"]Abaddon[/TAGNAME]',
				'descr' => __('Link to the ship\'s virtual page instead of the popup.', 'eve-shipinfo').' '.__('This is only possible when the virtual pages are enabled.', 'eve-shipinfo')
			)
		);
	}
}