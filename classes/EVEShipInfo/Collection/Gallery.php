<?php

EVEShipInfo::getInstance()->loadClass('EVEShipInfo_Collection_List');

class EVEShipInfo_Collection_Gallery extends EVEShipInfo_Collection_List
{
	protected $options = array(
		'links' => 'no',
		'popups' => 'no',
		'debug' => 'no',
		'columns' => '3',
		'rows' => '',
		'thumbnail_size' => 280,
		'thumbnail_classes' => ''
	);
	
	public function getColumns()
	{
		return $this->getOption('columns');
	}
	
	public function setColumns($columns)
	{
		if(is_numeric($columns)) {
			return $this->setOption('columns', $columns);
		}
		
		return $this;
	}
	
	public function setRows($rows)
	{
		if(is_numeric($rows)) {
			return $this->setOption('rows', $rows);
		}
		
		return $this;
	}
	
	public function setThumbnailSize($size)
	{
		if(is_numeric($size) && $size > 10 && $size < $this->plugin->getImageWidth()) {
			return $this->setOption('thumbnail_size', $size);
		}
		
		return $this;
	}
	
	public function disableColumnHeaders()
	{
		// not used in galleries
	    return $this;
	}
	
	public function render()
	{
		/* @var $ship EVEShipInfo_Collection_Ship */
		
		$ships = $this->filter->getShips();
		
		// getting the ships does not gurantee they have screenshots,
		// so we go through them and only keep those that have one.
		$keep = array();
		foreach($ships as $ship) {
			if($ship->hasScreenshot('Front')) {
				$keep[] = $ship;
			}
		}
		
		$ships = $keep;
		
		$html = '';
		if($this->getOption('debug')=='yes') {
			$html .= $this->renderDebug();
		}
		
		if(empty($ships)) {
			return $html;
		}
		
		$total = count($ships);
		$cols = $this->getOption('columns');
		$rows = $this->getOption('rows');
		if(empty($rows)) {
			$rows = ceil($total/$cols);
		}
		
		$html .=
		'<table class="shipinfo-gallery rows-'.$rows.' cols-'.$cols.'">'.
			'<tbody>';
				for($row=0; $row<$rows; $row++) {
					$html .=
					'<tr class="shipinfo-row">';
						$items = array_slice($ships, $row*$cols, $cols);
						for($col=0; $col<$cols; $col++) {
							if(isset($items[$col])) {
								$html .=
								'<td class="shipinfo-cell image">'.
									'<div class="shipinfo-cell-wrapper">'.
										$this->renderThumbnail($items[$col]).
									'</div>'.
								'</td>';
							} else {
								$html .= '<td class="shipinfo-cell empty"></td>';
							}
						}
						$html .=
					'</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
				
		return $html;
	}
	
	protected function renderThumbnail(EVEShipInfo_Collection_Ship $ship)
	{
		$content = '<img class="shipinfo-thumbnail '.implode(' ', $this->thumbnailClasses).'" src="'.$ship->getScreenshotURL().'" width="'.$this->getOption('thumbnail_size').'"/>'; 
		
		if($this->getOption('links')=='yes') {
		    $popup = 'no';
		    if($this->getOption('popups')=='yes') {
		        $popup = 'yes';
		    }
		     
		    $tag = '[shipinfo popup="'.$popup.'" id="'.$ship->getID().'" is_thumbnail="yes"]'.$content.'[/shipinfo]';
		    $parsed = do_shortcode($tag);
		    return $parsed;
		}
		
		return $content;
	}
}