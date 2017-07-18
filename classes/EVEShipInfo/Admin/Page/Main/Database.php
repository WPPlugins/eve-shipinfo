<?php

class EVEShipInfo_Admin_Page_Main_Database extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Database reference', 'eve-shipinfo');
	}
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
   /**
    * @var EVEShipInfo_Collection_Filter
    */
	protected $filter;
	
   /**
    * @var string
    */
	protected $show;
	
	protected $dataLists = array(
		'ships', 
		'groups', 
		'races'
	);
	
	protected function _render()
	{
		if(!$this->plugin->isDatabaseUpToDate()) {
			return $this->renderUpdateDatabaseBox();
		}
		
		if(isset($_REQUEST['show']) && in_array($_REQUEST['show'], $this->dataLists)) {
			$this->show = $_REQUEST['show'];
		}
		
		$this->collection = $this->plugin->createCollection();
		
		if(isset($_REQUEST['view']) && $this->collection->shipIDExists($_REQUEST['view'])) {
		    return $this->renderShip($_REQUEST['view']);
		}
		
		$this->filter = $this->collection->createFilter();
		
		$html = 
		'<p>'.
			__('The following is a <b>reference for items</b> you can use in combination with the plugin\'s shortcodes.', 'eve-shipinfo').' '.
			__('Whenever you need to specify names of things like races or ship groups, you can look them up here.', 'eve-shipinfo').' '.
			__('Note:', 'eve-shipinfo').' '.__('These lists are generated dynamically from the integrated ships database, so they will always be accurate for the version of the plugin you have installed.', 'eve-shipinfo').
		'</p>'.
		$this->renderRaces().
		$this->renderShipGroups().
		$this->renderShips();
		
		return $html;
	}
	
	protected function renderRaces()
	{
		$races = $this->collection->getRaces();
		
		$boxHTML = 
		'<a id="races"></a>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.	
				'<tr>'.
					'<th>'.__('ID', 'eve-shipinfo').'</th>'.
					'<th>'.__('Name', 'eve-shipinfo').'</th>'.
					'<th>'.__('Shortcode name', 'eve-shipinfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($races as $id => $name) {
					$boxHTML .=
					'<tr>'.
						'<td>'.$id.'</td>'.
						'<td>'.$name.'</td>'.
						'<td><code>'.strtolower($name).'</code></td>'.
					'</tr>';
				}
				$boxHTML .=
			'</tbody>'.
		'</table>';
				
		return $this->ui->createStuffBox(__('Races', 'eve-shipinfo').' <span class="text-muted">('.count($races).')</span>')
			->setContent($boxHTML)
			->setCollapsed($this->isCollapsed('races'))
			->render();
	}
	
	protected function renderShipGroups()
	{
		$groups = $this->filter->getGroups();
		
		$html =
		'<a id="groups"></a>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('ID', 'eve-shipinfo').'</th>'.
					'<th>'.__('Name', 'eve-shipinfo').'</th>'.
					'<th>'.__('Shortcode name', 'eve-shipinfo').'</th>'.
					'<th>'.__('Special', 'eve-shipinfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($groups as $id => $name) {
					$special = '';
					$virtual = $this->filter->getVirtualGroupGroupNames($id);
					if($virtual) {
						$special = implode(', ', $virtual);
					}
					
				    $html .=
				    '<tr>'.
					    '<td>'.$id.'</td>'.
					    '<td>'.$name.'</td>'.
					    '<td><code>'.strtolower($name).'</code></td>'.
					    '<td>'.$special.'</td>'.
				    '</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
				
		return $this->ui->createStuffBox(__('Ship groups', 'eve-shipinfo').' <span class="text-muted">('.count($groups).')</span>')
			->setAbstract(
			    __('These are all available ship groups in the database.', 'eve-shipinfo').' '.
		    	__('Note:', 'eve-shipinfo').' '.__('The first groups in the list are special convenience groups that automatically select all ship groups of the same hull size.', 'eve-shipinfo')
		    )
			->setContent($html)
			->setCollapsed($this->isCollapsed('groups'))
			->render();
	}
	
	protected function renderShips()
	{
		$ships = $this->filter->getShips();
		
		$html =
		'<a id="ships"></a>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('ID', 'eve-shipinfo').'</th>'.
					'<th>'.__('Name', 'eve-shipinfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($ships as $ship) {
				    $html .=
				    '<tr>'.
					    '<td>'.$ship->getID().'</td>'.
					    '<td><a href="'.$this->getURL(array('view' => $ship->getID())).'">'.$ship->getName().'</a></td>'.
				    '</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
					
		return $this->ui->createStuffBox(__('Ships', 'eve-shipinfo').' <span class="text-muted">('.count($ships).')</span>')
			->setAbstract(
			    __('These are all available ships in the database.', 'eve-shipinfo').' '.
			    __('Click a ship name to view the raw available database information for it.', 'eve-shipinfo')
		    )
			->setCollapsed($this->isCollapsed('ships'))
			->setContent($html)
			->render();
	}
	
	protected function isCollapsed($dataList)
	{
		if($this->show == $dataList) {
			return false;
		}
		
		return true;
	}
	
	protected function renderShip($shipID)
	{
	    $ship = $this->collection->getShipByID($shipID);
	    
	    $atts = $ship->getAttributes();
	    
	    $html =
	    '<table class="wp-list-table widefat">'.
	       '<thead>'.
	           '<tr>'.
	               '<th>'.__('Name', 'eve-shipinfo').'</th>'.
	               '<th>'.__('Value', 'eve-shipinfo').'</th>'.
	               '<th>'.__('Units', 'eve-shipinfo').'</th>'.
	               '<th>'.__('ID', 'eve-shipinfo').'</th>'.
	           '</tr>'.
	       '</thead>'.
    	    '<tbody>';
        	    foreach($atts as $attribute) {
        	        $html .=
        	        '<tr>'.
            	        '<td>'.$attribute->getName().'</td>'.
            	        '<td>'.$attribute->getValue().'</td>'.
            	        '<td>'.$attribute->getUnitName().'</td>'.
            	        '<td>'.$attribute->getID().'</td>'.
        	        '</tr>';
        	    }
        	    $html .=
    	    '</tbody>'.
	    '</table>';

	    $pageHTML = '';
	     
	    if($ship->hasScreenshot()) {
	        $pageHTML .=
	        '<h3>'.$ship->getName().'</h3>'.
	        '<p>'.
	           '<img src="'.$ship->getScreenshotURL().'"/ alt="'.$ship->getName().'">'.
	        '</p>';
	    }
	     
	    $pageHTML .= $this->ui->createStuffBox(__('Attributes', 'eve-shipinfo'))
	    ->setAbstract(
	        sprintf(__('These are all available attributes for the %1$s.', 'eve-shipinfo'), $ship->getName()) . ' ' .
	        __('You can use these attribute names and IDs in the ship database attribute-related methods.', 'eve-shipinfo').
	    	'<br/><br/>'.
	    	__('Example:', 'eve-shipinfo').
	    	'<br/>'.
	    	'<pre>'.
	    		'$ship->getAttributeValue(\'agility\'); // without units'.PHP_EOL.
	    		'$ship->getAttributeValue(\'agility\', true); // with units, if available'.PHP_EOL.
	    	'</pre>'
	    )
	    ->setCollapsed()
	    ->setContent($html)
	    ->render();
	    
	    return $pageHTML;
	}
}