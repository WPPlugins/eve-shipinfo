<?php

class EVEShipInfo_Shortcode_ShipList extends EVEShipInfo_Shortcode
{
	public function getTagName()
	{
		return 'shipinfo_list';
	}
	
	public function getDescription()
	{
	    return __('Allows displaying fully customizable ship lists.', 'eve-shipinfo');
	}
	
	public function getName()
	{
	    return __('Ships list', 'eve-shipinfo');
	}
	
   /**
    * @var EVEShipInfo_Collection_Filter
    */
	protected $filter;
	
	protected function renderShortcode()
	{
		// is this list disabled?
		if($this->getAttribute('enabled')=='no') {
			return '';
		}
		
		$this->filter = $this->collection->createFilter();
		$this->configureFilter();
		
		$this->list = $this->collection->createList($this->filter);
		$this->configureList();
		
		if(!$this->renderTemplate()) {
			$this->renderFallback();
		}		
	}
	
	public function getDefaultAttributes()
	{
		return array(
			'enabled' => 'yes',
			'template' => 'no',
			'linked' => 'yes',
			'popup' => 'yes',
			'order_by' => 'name',
			'order_dir' => 'ascending',
			'show' => 'all',
			'thumbnail_classes' => '',
			'columns' => 'name',
			'races' => '',
			'highslots' => '',
			'medslots' => '',
			'lowslots' => '',
			'search' => '',
			'groups' => '',
			'debug' => 'no',
			'column_headers' => 'yes',
			'agility' => '',
			'warpspeed' => '',
			'velocity' => '',
		    'pilotable' => 'all',
		    'cargobay' => '',
		    'dronebandwidth' => '',
		    'dronebay' => '',
		    'show_units' => 'yes',
		    'launchers' => '',
		    'turrets' => '',
		    'techlevel' => '',
			'ships' => '',
			'mass' => '',
		);
	}
	
	protected function _describeAttributes()
	{
		$filter = $this->collection->createFilter();
		$list = $this->collection->createList($filter);

		$massRange = $this->collection->getPropertyValueRange('mass');
		
	    $attribs = array(
	        'settings' => array(
	            'group' => __('Settings'),
	            'abstract' => __('Configuration settings for the list.'),
		        'attribs' => array(
		            'show_units' => array(
		            	'descr' => sprintf(__('Whether to display unit labels for ships attributes that have specific units, like %s or %s.', 'eve-shipinfo'), '<code>M3</code>', '<code>AU</code>'),
		            	'optional' => true,
		            	'type' => 'enum',
		            	'values' => array(
		            		'no' => __('No, don\'t show any', 'eve-shipinfo'),
		            		'yes' => __('Yes, show when applicable', 'eve-shipinfo')
		            	)
		            ),
			    	'enabled' => array(
			    	    'descr' => __('Whether the list is enabled:', 'eve-shipinfo').' '.
			    	    		   __('Disabling a list allows you to keep its shortcode intact in your post without showing it.', 'eve-shipinfo'),
			    	    'optional' => true,
			    	    'type' => 'enum',
			    		'values' => array(
			    			'no' => __('List is enabled', 'eve-shipinfo'),
			    			'yes' => __('List is disabled', 'eve-shipinfo')
			    		)
			    	),
			        'template' => array(
			            'descr' => __('The theme template file to use to render the list.', 'eve-shipinfo').' '.
			        			   sprintf(__('Set to %1$s to disable.', 'eve-shipinfo'), '<code>no</code>').' '.
			        			   sprintf(__('The template gets the filtered ships list in the %1$s variable.', 'eve-shipinfo'), '<code>$ships</code>'),
			            'optional' => true,
			            'type' => 'text'
			        ),
			        'linked' => array(
			            'descr' => __('Whether to link the ship names.', 'eve-shipinfo'),
			            'optional' => true,
			            'type' => 'enum',
			        	'values' => array(
			        		'yes' => __('Yes, link the names.', 'eve-shipinfo'),
			        		'no' => __('No, don\'t link any names.', 'eve-shipinfo')
			        	)
			        ),
			        'popup' => array(
			            'descr' => __('Whether to show the ship popup when clicked.', 'eve-shipinfo'),
			            'optional' => true,
			            'type' => 'enum',
			            'values' => array(
			                'yes' => __('Yes, show a popup', 'eve-shipinfo'),
			                'no' => __('No, link to the virtual page', 'eve-shipinfo')
			            )
			        ),
			    	'order_by' => array(
			    	    'descr' => __('The ship attribute to sort the list by.', 'eve-shipinfo'),
			    	    'optional' => true,
			    	    'type' => 'enum',
			    	    'values' => $filter->getOrderFields()
			    	),
			    	'order_dir' => array(
			    	    'descr' => __('The direction in which to sort the list.', 'eve-shipinfo'),
			    	    'optional' => true,
			    	    'type' => 'enum',
			    	    'values' => array(
			    	    	'desc' => __('In descending order', 'eve-shipinfo'),
			    	    	'descending' => __('In descending order', 'eve-shipinfo'),
			    	    	'asc' => __('In ascending order', 'eve-shipinfo'),
			    	    	'ascending' => __('In ascending order', 'eve-shipinfo')
			    	    )
			    	),
			    	'show' => array(
			    	    'descr' => __('The amount of ships to limit the list to.', 'eve-shipinfo').' '.
			    				   sprintf(__('Set to %1$s to show all available ships.', 'eve-shipinfo'), '<code>all</code>'),
			    	    'optional' => true,
			    	    'type' => 'number'
			    	),
			    	'columns' => array(
			    	    'descr' => __('The column(s) to show in the list.', 'eve-shipinfo').' '.
			    				   __('They are shown in the exact order that you specify them.', 'eve-shipinfo').' '.
			    				   __('Example:', 'eve-shipinfo').' <code>name, race, group</code>',
			    	    'optional' => true,
			    	    'type' => 'commalist',
			    		'values' => $list->getColumns()
			    	),
		            'debug' => array(
		            	'descr' => __('Whether to display debugging information above the list.', 'eve-shipinfo').' '.
		            	__('Useful when something does not work as expected, since this will also show any list validation messages.', 'eve-shipinfo'),
		            	'optional' => true,
		            	'group' => __('Settings'),
		            	'type' => 'enum',
		            	'values' => array(
		            		'yes' => __('Yes, show', 'eve-shipinfo'),
		            		'no' => __('No, don\'t show', 'eve-shipinfo')
		            	)
		            ),
		            'column_headers' => array(
		            	'descr' => __('Whether to display the column headers.', 'eve-shipinfo'),
		            	'optional' => true,
		            	'group' => __('Settings'),
		            	'type' => 'enum',
		            	'values' => array(
		            		'yes' => __('Yes, show', 'eve-shipinfo'),
		            		'no' => __('No, don\'t show', 'eve-shipinfo')
		            	)
		            ),
	            ),
            ),
            'filters' => array(
            	'group' => __('Filters'),
                'abstract' => __('These are all available filtering options to limit the list to ships matching the criteria you specify.'),
                'attribs' => array(
		        	'ships' => array(
		        		'descr' => __('Specific selection of ships to select.', 'eve-shipinfo').' '.
		        		__('Supersedes any other filters.', 'eve-shipinfo').' '.
		        		__('You may use ship names and/or IDs interchangeably.', 'eve-shipinfo'),
		        		'optional' =>true,
		        		'type' => 'commalist',
		        		'values' => array(
		        			'' => sprintf(
		        				__('See the %1$sships list%2$s', 'eve-shipinfo'),
		        				'<a href="admin.php?page=eveshipinfo_database&show=ships#ships" target="_blank">',
		        				'</a>'
	        				)
		        		)
		        	),
                	'turrets' => array(
                    	'descr' => __('The amount of turret slots to limit the list to.', 'eve-shipinfo').' '.
                    			   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
                    'launchers' => array(
                    	'descr' => __('The amount of launcher slots to limit the list to.', 'eve-shipinfo').' '.
                    			   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
                    'dronebay' => array(
                    	'descr' => __('The size of the dronebay to limit the list to.', 'eve-shipinfo').' '.
                        		   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
                    'dronebandwidth' => array(
                    	'descr' => __('The drone bandwidth to limit the list to.', 'eve-shipinfo').' '.
                        		   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
                    'cargobay' => array(
                    	'descr' => __('The size of the cargobay to limit the list to.', 'eve-shipinfo').' '.
                        		   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
                    'pilotable' => array(
                    	'descr' => __('Whether to include or exclude private ships that cannot be flown by players, like the game developer test ships.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'enum',
                    	'values' => array(
                    		'all' => __('Selects both private and public ships', 'eve-shipinfo'),
                    	    'private' => __('Only select private ships', 'eve-shipinfo'),
                    	    'public' => __('Only select public ships', 'eve-shipinfo')
                    	)
                    ),
			    	'races' => array(
			    	    'descr' => __('The race(s) to limit the list to.', 'eve-shipinfo').' '.
			    				   __('Example:', 'eve-shipinfo').' <code>minmatar, caldari</code>',
			    	    'optional' => true,
			    	    'type' => 'commalist',
			    		'values' => $filter->describeRaces()
			    	),
			    	'highslots' => array(
			    		'descr' => sprintf(__('The amount of %1$s slots to limit the list to.', 'eve-shipinfo'), __('high', 'eve-shipinfo')).' '.
			    				   __('This allows complex selections using expressions.', 'eve-shipinfo'),
			    		'optional' => true,
			    		'type' => 'text',
			    		'values' => $this->describeNumericExpressions()
			    	),
			    	'lowslots' => array(
			    	    'descr' => sprintf(__('The amount of %1$s slots to limit the list to.', 'eve-shipinfo'), __('low', 'eve-shipinfo')).' '.
			    	    		   __('This allows complex selections using expressions.', 'eve-shipinfo'),
			    	    'optional' => true,
			    	    'type' => 'text',
			    	    'values' => $this->describeNumericExpressions()
			    	),
			    	'medslots' => array(
			    	    'descr' => sprintf(__('The amount of %1$s slots to limit the list to.', 'eve-shipinfo'), __('med', 'eve-shipinfo')).' '.
			    	    __('This allows complex selections using expressions.', 'eve-shipinfo'),
			    	    'optional' => true,
			    	    'type' => 'text',
			    	    'values' => $this->describeNumericExpressions()
			    	),
			    	'search' => array(
			    	    'descr' => __('Limits the list to ships matching the search term either in their name or their description.', 'eve-shipinfo'),
			    	    'optional' => true,
			    	    'type' => 'text'
			    	),
			    	'groups' => array(
			    		'descr' => __('The ship group(s) to limit the list to.', 'eve-shipinfo').' '.
			    				   __('The first groups in the list are special convenience groups that automatically select all ship groups of the same hull size.', 'eve-shipinfo').' '.
			    				   __('Example:', 'eve-shipinfo').' <code>cruiser, command ship</code>',
			    		'optional' => true,
			    		'type' => 'commalist',
			    		'values' => $filter->describeGroups()
			    	),
			    	'agility' => array(
		    	        'descr' => __('The ship agility values to limit the list to.', 'eve-shipinfo').' '.
		    	        		   __('This allows complex selections using expressions.', 'eve-shipinfo'),
		    	        'optional' => true,
		    	        'type' => 'text',
		    	        'values' => $this->describeNumericExpressions()
			    	),
			    	'warpspeed' => array(
		    	        'descr' => __('The ship warp speed values to limit the list to.', 'eve-shipinfo').' '.
		    	        		   __('This allows complex selections using expressions.', 'eve-shipinfo'),
		    	        'optional' => true,
		    	        'type' => 'text',
		    	        'values' => $this->describeNumericExpressions()
			    	),
			    	'velocity' => array(
			    		'descr' => __('The ship\'s maximum velocity to limit the list to.', 'eve-shipinfo').' '.
			    				   __('This allows complex selections using expressions.', 'eve-shipinfo'),
			    		'optional' => true,
			    		'type' => 'text',
			    		'values' => $this->describeNumericExpressions()
			    	),
                    'techlevel' => array(
                    	'descr' => __('The ship\'s tech level to limit the list to.', 'eve-shipinfo').' '.
                    			   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
                	'mass' => array(
                    	'descr' => __('The ship\'s mass to limit the list to.', 'eve-shipinfo').' '.
                				   sprintf(
                				   		__('The values range from %1$s to %2$s according to the current database.', 'eve-shipinfo'),
                				   		number_format($massRange['min'], 0, ' ', ' '),
                				   		number_format($massRange['max'], 0, ' ', ' ')
                				   	).' '.
                    			   __('This allows complex selections using expressions.', 'eve-shipinfo'),
                    	'optional' => true,
                    	'type' => 'text',
                    	'values' => $this->describeNumericExpressions()
                    ),
	            )
            )
	    );
	    
	    return $attribs;
	}
	
	protected $numericExpressionsDescribed;
	
	protected function describeNumericExpressions()
	{
		if(!isset($this->numericExpressionsDescribed)) {
			$this->numericExpressionsDescribed = array(
			    'x' => sprintf(__('Exactly %1$s', 'eve-shipinfo'), '<span style="font-family:monospace;font-style:normal;">x</span>'),
			    'bigger than x' => sprintf(__('Any number above %1$s', 'eve-shipinfo'), '<span style="font-family:monospace;font-style:normal;">x</span>'),
			    'smaller than x' => sprintf(__('Any number below %1$s', 'eve-shipinfo'), '<span style="font-family:monospace;font-style:normal;">x</span>'),
			    'bigger or equals x' => sprintf(__('Any number above or exactly %1$s', 'eve-shipinfo'), '<span style="font-family:monospace;font-style:normal;">x</span>'),
				'smaller or equals x' => sprintf(__('Any number below or exactly %1$s', 'eve-shipinfo'), '<span style="font-family:monospace;font-style:normal;">x</span>'),
				'between x and y' => sprintf(__('Any number including and between %1$s and %2$s', 'eve-shipinfo'), '<span style="font-family:monospace;font-style:normal;">x</span>', '<span style="font-family:monospace;font-style:normal;">y</span>')
			);
		}
		
		return $this->numericExpressionsDescribed;
	}
	
	protected function renderTemplate()
	{
		if($this->getAttribute('template')=='no') {
			return false;
		}
		
		// is a template for the list present in the theme? Use that.
		$tmpl = locate_template('shipinfo_list.php');
		if(empty($tmpl)) {
			return false;
		}
		
		// make the relevant variables available to the template
		$collection = $this->collection;
		$filter = $this->filter;
		$ships = $this->filter->getShips();
		
		ob_start();
		require $tmpl;
	    $this->content = ob_get_clean();
	    
	    return true;
	}
	
	protected function isAscending()
	{
		if(in_array($this->getAttribute('order_dir'), array('descending', 'desc'))) {
			return false;
		}
		
		return true;
	}
	
	protected function renderFallback()
	{
		$this->content = $this->list->render();
	}
	
   /**
    * @var EVEShipInfo_Collection_List
    */
	protected $list;
	
	protected function configureList()
	{
		if($this->getAttribute('debug')=='yes') {
		    $this->list->enableDebug();
		}
		
		if($this->getAttribute('linked')=='yes') {
		    $this->list->enableLinks();
		}
		
		if($this->getAttribute('popup')=='yes') {
		    $this->list->enablePopups();
		}
		
		if($this->getAttribute('column_headers')=='no') {
			$this->list->disableColumnHeaders();
		}
		
		if($this->getAttribute('show_units')=='no') {
			$this->list->disableUnits();
		}
		
		$thumbClasses = $this->getAttribute('thumbnail_classes');
		if(!empty($thumbClasses)) {
		    $classes = array_map('trim', explode(' ', $thumbClasses));
		    $this->list->addThumbnailClasses($classes);
		}
		
		$this->list->enableColumns($this->parseCommaAttribute('columns'));
	}

	protected function parseCommaAttribute($attribName)
	{
		$value = trim($this->getAttribute($attribName));
		if(empty($value)) {
		    return array();
		}
		
		$items = array_map('strtolower', array_map('trim', explode(',', $value)));
		return $items;
	}
	
	protected function configureFilter()
	{
		$this->filter->setOrderBy($this->getAttribute('order_by'), $this->isAscending());

		$show = $this->getAttribute('show');
		if(is_numeric($show)) {
		    $this->filter->setLimit($show);
		}
		
		$ships = $this->parseCommaAttribute('ships');
		if(!empty($ships)) {
			foreach($ships as $shipID) {
				if(!is_numeric($shipID)) {
					$shipID = $this->collection->getShipIDByName($shipID);
				}
				
				$this->filter->selectShip($shipID);
			}
		}
		
		$this->filter->selectRaceNames($this->parseCommaAttribute('races'));
		$this->filter->selectGroupNames($this->parseCommaAttribute('groups'));
		
		$highslots = trim($this->getAttribute('highslots'));
		if(!empty($highslots)) {
			$this->filter->selectHighSlots($highslots);
		}

		$medslots = trim($this->getAttribute('medslots'));
		if(!empty($medslots)) {
			$this->filter->selectMedSlots($medslots);
		}
	
		$lowslots = trim($this->getAttribute('lowslots'));
		if(!empty($lowslots)) {
			$this->filter->selectLowSlots($lowslots);
		}
		
		$search = trim($this->getAttribute('search'));
		if(!empty($search)) {
			$this->filter->selectSearch($search);	
		}
		
		$agility = trim($this->getAttribute('agility'));
		if(!empty($agility)){
			$this->filter->selectAgility($agility);
		}
		
		$warpspeed = trim($this->getAttribute('warpspeed'));
		if(!empty($warpspeed)){
		    $this->filter->selectWarpSpeed($warpspeed);
		}
		
		$velocity = trim($this->getAttribute('velocity'));
		if(!empty($velocity)) {
			$this->filter->selectVelocity($velocity);
		}
		
		$pilotable = $this->getAttribute('pilotable');
		if($pilotable=='private') {
			$this->filter->selectUnpilotable();
		} else if($pilotable=='public') {
			$this->filter->selectPilotable();
		}
		
		$cargobay = trim($this->getAttribute('cargobay'));
		if(!empty($cargobay)) {
			$this->filter->selectCargoBaySize($cargobay);
		}
		
		$droneBandwidth = trim($this->getAttribute('dronebandwidth'));
		if(!empty($droneBandwidth)) {
			$this->filter->selectDroneBandwidth($droneBandwidth);
		}
		
		$dronebay = trim($this->getAttribute('dronebay'));
		if(!empty($dronebay)) {
			$this->filter->selectDroneBaySize($dronebay);
		}
		
		$turrets = trim($this->getAttribute('turrets'));
		if(!empty($turrets)) {
			$this->filter->selectTurretSlots($turrets);
		}
		
		$launchers = trim($this->getAttribute('launchers'));
		if(!empty($launchers)) {
			$this->filter->selectLauncherSlots($launchers);
		}
		
		$techlevel = trim($this->getAttribute('techlevel'));
		if(!empty($techlevel)) {
			$this->filter->selectTechLevel($techlevel);
		}
		
		$mass = trim($this->getAttribute('mass'));
		if(!empty($mass)) {
			$this->filter->selectMass($mass);
		}
	}
	
	protected function _getExamples()
	{
		return array(
			array(
				'shortcode' => '[TAGNAME]',
				'descr' => __('Lists all ships in the database.', 'eve-shipinfo')
			),
			array(
				'shortcode' => '[TAGNAME show="10"]',
				'descr' => sprintf(__('Lists the first %1$s ships from the database.', 'eve-shipinfo'), '10')
			),
			array(
				'shortcode' => '[TAGNAME races="minmatar"]',
				'descr' => sprintf(__('Lists all %1$s ships.', 'eve-shipinfo'), 'Minmatar')
			),
			array(
			    'shortcode' => '[TAGNAME groups="assault frigate,interceptor"]',
			    'descr' => __('Lists all assault frigates and interceptors.', 'eve-shipinfo')
			),
			array(
				'shortcode' => '[TAGNAME search="stabber"]',
				'descr' => sprintf(__('Lists all ships with the search term %1$s in their name or description.', 'eve-shipinfo'), 'stabber')
			),
			array(
				'shortcode' => '[TAGNAME show="10" columns="name, agility" order_by="agility" order_dir="descending"]',
				'descr' => sprintf(__('Lists the %1$s most agile ships in the database, showing the agility values.', 'eve-shipinfo'), '10')
			),
			array(
				'shortcode' => '[TAGNAME columns="name, highslots" races="minmatar" highslots="bigger than 6"]',
				'descr' => sprintf(__('Lists all %1$s ships with over %2$s high slots.', 'eve-shipinfo'), 'Minmatar', 6)
			),
			array(
			    'shortcode' => '[TAGNAME columns="name, group" highslots="3" medslots="3" lowslots="3"]',
			    'descr' => sprintf(__('Lists all ships with exactly %1$s high, med and low slots.', 'eve-shipinfo'), 3)
			),
		);
	} 
}