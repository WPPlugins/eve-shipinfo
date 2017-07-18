<?php

class EVEShipInfo_Shortcode_EFTFit extends EVEShipInfo_Shortcode
{
	public function getTagName()
	{
		return 'shipinfo_fit';
	}
	
	public function getDescription()
	{
		return 
		__('Links a text to show details of one of your ship fittings.', 'eve-shipinfo').' '.
		'<b>'.__('Note:', 'eve-shipinfo').'</b> '.
		sprintf(
			__('You have to %simport%s your EVE ship fittings before you can use this feature.', 'eve-shipinfo'),
			'<a href="?page=eveshipinfo_eftimport">',
			'</a>'
		);
	}
	
	public function getName()
	{
		return __('Ship fitting link', 'eve-shipinfo');
	}
	
	protected function renderShortcode()
	{
		$fitID = $this->getAttribute('id');
		if(empty($fitID)) {
			return;
		}
		
		/* @var $ship EVEShipInfo_Collection_Ship */
		
		$eft = $this->plugin->createEFTManager();
		$fit = $eft->getFittingByID($fitID);
		if(!$fit) {
			return;
		}
		
		$classes = array(
			$this->plugin->getCSSName('fitlink')
		);
		
		$attribs = array(
		    'id' => $this->id,
			'href' => 'javascript:void(0)',
			'onclick' => sprintf("EVEShipInfo.ShowFitting('%s')", $fitID)
		);
		
		$attribs['class'] = implode(' ', $classes);
		
		if(empty($this->content)) {
			$this->content = $fit->getName();
		}
		
		$this->content =
		'<a'.$this->plugin->compileAttributes($attribs).'>'.
			$this->content.
		'</a>'.
		$fit->renderClientRegistration($this->id);
	}
	
	public function getDefaultAttributes()
	{
		return array(
			'id' => '',
		);
	}
	
	protected function _describeAttributes()
	{
		return array(
			'settings' => array(
				'group' => __('Settings'),
				'abstract' => __('Configuration settings for the link.'),
				'attribs' => array(
					'id' => array(
						'descr' => 
					    	__('The ID of the fit to link to.', 'eve-shipinfo').' '.
							sprintf(
							   __('Have a look at the %sfittings list%s to find out which IDs you can use.'),
							   '<a href="?page=eveshipinfo_eftfittings">',
							   '</a>'
							),
						'optional' => true,
						'type' => 'text'
					),
				)
			)
		);
	}
	
	protected function _getExamples()
	{
		return array(
			array(
				'shortcode' => '[TAGNAME id="2"]',
				'descr' => __('Insert a link to the target fit.', 'eve-shipinfo').' '.
						   __('Since the shortcode is empty, the name of the fit will be used for the link.', 'eve-shipinfo')
			),
			array(
				'shortcode' => '[TAGNAME id="2"]'.__('Custom link text', 'eve-shipinfo').'[/TAGNAME]',
				'descr' => __('For a custom link title, simply put the text within the shortcode.', 'eve-shipinfo')
			),
		);
	}
}