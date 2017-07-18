<?php

EVEShipInfo::getInstance()->loadClass('EVEShipInfo_Shortcode_ShipList');

class EVEShipInfo_Shortcode_Gallery extends EVEShipInfo_Shortcode_ShipList
{
	public function getTagName()
	{
		return 'shipinfo_gallery';
	}
	
	public function getName()
	{
		return __('Ships gallery', 'eve-shipinfo');
	}
	
	public function getDescription()
	{
		return __('Allows displaying a thumbnail gallery of ships, customizable just like a regular list.', 'eve-shipinfo');
	}
	
	public function getDefaultAttributes()
	{
		$atts = parent::getDefaultAttributes();
		$atts['thumbnail_size'] = 280;
		$atts['columns'] = '3';
		$atts['rows'] = '';
		
		return $atts;
	}
	
	protected function _describeAttributes()
	{
		$atts = parent::_describeAttributes();
		
		unset($atts['settings']['attribs']['show']);
		unset($atts['settings']['attribs']['column_headers']);
		unset($atts['settings']['attribs']['template']);
		
		$atts['settings']['attribs']['columns'] = array(
		    'descr' => __('The amount of columns for the gallery grid.', 'eve-shipinfo'),
		    'optional' => true,
		    'type' => 'number'
		);
		
		$atts['settings']['attribs']['rows'] = array(
			'descr' => __('The amount of rows to limit the gallery grid to.', 'eve-shipinfo').' '.
					   __('If not specified, all available ships will be shown.', 'eve-shipinfo').' '.
					   sprintf(__('This is similar to the regular list\'s %1$s attribute, except the maximum amount of ships is determined by the columns multiplied by rows.', 'eve-shipinfo'), '<code>show</code>'),
			'optional' => true,
			'type' => 'number'
		);
		
		$atts['settings']['attribs']['thumbnail_size'] = array(
			'descr' => __('The pixel width for the thumbnails.', 'eve-shipinfo').' '.
					   sprintf(__('This can be any positive value up to the maximum size of %1$s.', 'eve-shipinfo'), '<code>'.$this->getMaximumThumbSize().'</code>'),
			'optional' => true,
			'type' => 'number'
		);
		
		$atts['settings']['attribs']['thumbnail_classes'] = array(
			'descr' => __('Custom class names to add to each thumbnail container element in the generated markup.', 'eve-shipinfo').' '.
					   __('Separate class names with spaces.', 'eve-shipinfo'),
			'optional' => true,
			'type' => 'text'
		);
		
		return $atts;
	}
	
	public function getMaximumThumbSize()
	{
		return $this->plugin->getImageWidth();
	}
	
	protected function process()
	{
		$galleryFolder = $this->plugin->getGalleryPath();
		if(!file_exists($galleryFolder)) {
			$this->content = '<p>'.__('Cannot show the ships gallery, the screenshots are missing.', 'eve-shipinfo').'</p>';
			return;
		}
		
		// we do not use this attribute, but we have to remove it to make
		// sure it is not used by the shared list configuration method.
		$this->attribs['show'] = '';
		
		parent::process();
	}
	
	protected function renderTemplate()
	{
		return false;
	}
	
	protected function renderFallback()
	{
		$gallery = $this->collection->createGallery($this->filter);
		
		$gallery->setColumns($this->getAttribute('columns'));
		$gallery->setRows($this->getAttribute('rows'));
		$gallery->setThumbnailSize($this->getSelectedThumbSize());
		$gallery->addThumbnailClasses($this->getSelectedClasses());
		
		if($this->getAttribute('linked')=='yes') {
			$gallery->enableLinks();
		}
		
		if($this->getAttribute('popup')=='yes') {
			$gallery->enablePopups();
		}
		
		if($this->getAttribute('debug')=='yes') {
			$gallery->enableDebug();
		}
		
		$this->content = $gallery->render();
	}
	
	protected function getSelectedClasses()
	{
		$classes = trim($this->getAttribute('thumbnail_classes'));
		if(empty($classes)) {
			return array();
		}
		
		$classes = explode(' ', $classes);
		return $classes;
	}
	
	protected function getSelectedThumbSize()
	{
		$size = $this->getAttribute('thumbnail_size');
		$maxSize = $this->getMaximumThumbSize();
		if($size > $maxSize) {
			$size = $maxSize;
		}
		
		if($size <= 10) {
			$size = 10;
		}

		return $size;
	}
}