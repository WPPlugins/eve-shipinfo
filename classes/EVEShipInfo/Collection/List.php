<?php
class EVEShipInfo_Collection_List
{
   /**
    * @var EVEShipInfo_Collection_Filter
    */
	protected $filter;
	
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
	public function __construct(EVEShipInfo_Collection_Filter $filter)
	{
		$this->plugin = EVEShipInfo::getInstance();
		$this->collection = $this->plugin->createCollection();
		$this->filter = $filter;
		
		$this->columns = array(
			'name' => __('Name', 'eve-shipinfo'),
			'agility' => __('Agility', 'eve-shipinfo'),
			'warpspeed' => __('Warp speed', 'eve-shipinfo'),
			'race' => __('Race', 'eve-shipinfo'),
			'highslots' => __('High slots', 'eve-shipinfo'),
			'medslots' => __('Med slots', 'eve-shipinfo'),
			'lowslots' => __('Low slots', 'eve-shipinfo'),
			'group' => __('Group', 'eve-shipinfo'),
		    'cargobay' => __('Cargo bay', 'eve-shipinfo'),
		    'dronebandwidth' => __('Drone bandwidth', 'eve-shipinfo'),
		    'dronebay' => __('Drone bay', 'eve-shipinfo'),
		    'turrets' => __('Turret slots', 'eve-shipinfo'),
		    'launchers' => __('Launcher slots', 'eve-shipinfo'),
		    'velocity' => __('Max velocity', 'eve-shipinfo'),
		    'techlevel' => __('Tech level', 'eve-shipinfo'),
			'mass' => __('Mass', 'eve-shipinfo')
		);
	}
	
	public function getColumns()
	{
		return $this->columns;		
	}
	
	protected $options = array(
		'links' => 'no',
		'popups' => 'no',
		'debug' => 'no',
		'column_headers' => 'yes'
	);
	
	protected $activeColumns = array();
	
	public function enableLinks()
	{
		return $this->setOption('links', 'yes');
	}
	
	public function enablePopups()
	{
		return $this->setOption('popups', 'yes');
	}
	
	public function disableColumnHeaders()
	{
		return $this->setOption('column_headers', 'no');
	}
	
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
		return $this;
	}
	
	public function getOption($name)
	{
		if(isset($this->options)) {
			return $this->options[$name];
		}
		
		return null;
	}
	
	protected $columns;
	
	public function render()
	{
		/* @var $ship EVEShipInfo_Collection_Ship */
		
		if(empty($this->activeColumns)) {
			$this->enableColumn('name');
		}
		
		$ships = $this->filter->getShips();
		$html = '';
		
		if($this->getOption('debug')=='yes') {
			$html .= $this->renderDebug();
		}
		
		$html .=
		'<table class="'.$this->plugin->getCSSName('list').'">';
			if($this->getOption('column_headers')=='yes') {
				$html .=
				'<thead>'.
					'<tr>';
						foreach($this->activeColumns as $name) {
							$html .=
							'<th class="column-'.$name.'">'.$this->columns[$name].'</th>';
						}
						$html .=
					'</tr>'.
				'</thead>';
			}
			$html .=
			'<tbody>';
				foreach($ships as $ship) {
				    $html .=
				    '<tr>';
				    	$first = true;
				    	foreach($this->activeColumns as $name) {
				    		if($first) {
				    			$content = $this->renderLinkedColumn($name, $ship);
				    			$first = false;
				    		} else {
				    			$content = $this->renderColumn($name, $ship);
				    		}
				    		$html .=
						    '<td class="column-'.$name.'">'.
				    			$content.
				    		'</td>';
				    	}
				    	$html .=
				    '</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
				
		return $html;
	}
	
	protected function renderLinkedColumn($name, EVEShipInfo_Collection_Ship $ship)
	{
		$content = $this->renderColumn($name, $ship);
		
		if($this->getOption('links')=='yes') {
		    $popup = 'no';
		    if($this->getOption('popups')=='yes') {
		        $popup = 'yes';
		    }
		    	
		    $tag = '[shipinfo popup="'.$popup.'" id="'.$ship->getID().'"]'.$content.'[/shipinfo]';
		    $parsed = do_shortcode($tag);
		    return $parsed;
		}
		
		return $content;
	}
	
	protected function renderColumn($name, EVEShipInfo_Collection_Ship $ship)
	{
		$method = 'renderColumn_'.$name;
		if(!method_exists($this, $method)) {
			return '';
		}
		
		return $this->$method($ship);
	}
	
	protected $showUnits = true;
	
   /**
    * Disables unit labels for attributes that have specific units, like "M3".
    * @return EVEShipInfo_Collection_List
    */
	public function disableUnits()
	{
		$this->showUnits = false;
		return $this;
	}
	
	protected function renderColumn_name(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getName();
	}
	
	protected function renderColumn_agility(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getAgility($this->showUnits);
	}
	
	protected function renderColumn_mass(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getMass($this->showUnits);
	}
	
	protected function renderColumn_warpspeed(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getWarpSpeed($this->showUnits);
	}
	
	protected function renderColumn_race(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getRaceName();
	}
	
	protected function renderColumn_highslots(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getHighSlots();	
	}
	
	protected function renderColumn_medslots(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getMedSlots();	
	}
	
	protected function renderColumn_lowslots(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getLowSlots();	
	}
	
	protected function renderColumn_group(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getGroupName();
	}
	
	protected function renderColumn_cargobay(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getCargobaySize($this->showUnits);
	}
	
	protected function renderColumn_dronebandwidth(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getDroneBandwidth($this->showUnits);
	}
	
	protected function renderColumn_dronebay(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getDronebaySize($this->showUnits);
	}
	
	protected function renderColumn_turrets(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getTurretHardpoints();
	}
	
	protected function renderColumn_launchers(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getLauncherHardpoints();
	}
	
	protected function renderColumn_velocity(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getVelocity($this->showUnits);
	}

	protected function renderColumn_techlevel(EVEShipInfo_Collection_Ship $ship)
	{
		return $ship->getTechLevel();
	}
	
	protected $thumbnailClasses = array();
	
	public function addThumbnailClasses($classes)
	{
		foreach($classes as $className) {
			$this->addThumbnailClass($className);
		}
		
		return $this;
	}
	
	public function addThumbnailClass($className)
	{
		if(!in_array($className, $this->thumbnailClasses)) {
			$this->thumbnailClasses[] = $className;
		}
		
		return $this;
	}
	
	public function enableColumns($columns)
	{
		foreach($columns as $column) {
			$this->enableColumn($column);
		}
		
		return $this;
	}
	
	public function enableColumn($column)
	{
		if($this->columnExists($column)) {
			if(!in_array($column, $this->activeColumns)) {
				$this->activeColumns[] = $column;
			}
		} else {
			$this->addWarning(sprintf(__('Column %1$s is not known.', 'eve-shipinfo'), $column));
		}
		
		return $this;
	}
	
	protected $messages = array();
	
	protected function addWarning($message)
	{
		if(!in_array($message, $this->messages)) {
			$this->messages[] = $message;
		}
	}
	
	public function columnExists($column)
	{
		return isset($this->columns[$column]);
	}
	
	public function enableDebug()
	{
		return $this->setOption('debug', 'yes');
	}
	
	protected function renderDebug()
	{
		$html =
		'<div style="background:#fff;color:#000;padding:0 15px 15px 15px;border:solid 2px #000;margin:15px 0;">'.
			'<h3>'.__('List debug console', 'eve-shipinfo').'</h3>'.
			'<p><b>'.__('Configuration', 'eve-shipinfo').'</b></p>'.
			'<pre>'.
				'Ordering: '.$this->filter->getOrderBy().', '.$this->filter->getOrderDir().PHP_EOL.
				'Columns: '.implode(', ', $this->activeColumns).PHP_EOL.
			'</pre>';
			
			if($this->hasMessages()) {
				$html .=
				'<p><b>'.__('Warning messages', 'eve-shipinfo').'</b></p>'.
				'<pre>';
					foreach($this->messages as $message) {
						$html .= $message.PHP_EOL;
					}
					$html .=
				'</pre>';
			}
			
			if($this->filter->hasMessages()) {
				$html .=
				'<p><b>'.__('Filter warning messages', 'eve-shipinfo').'</b></p>'.
				'<pre>';
					$messages = $this->filter->getMessages();
					foreach($messages as $message) {
						$html .= $message.PHP_EOL;
					}
					$html .=
				'</pre>';
			}
			
			$html .=
		'</div>';

		return $html;
	}
	
	protected function hasMessages()
	{
		return !empty($this->messages);
	}
}