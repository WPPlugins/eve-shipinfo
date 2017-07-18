<?php

class EVEShipInfo_Collection_Filter
{
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
   /**
    * The key is whether the sort is the secondary sort field.
    * By default there is no secondary sort field.
    * @var array
    */
	protected $orderBy = array(
	    false => 'name'
    );
	
	protected $ascending = array(
	    false => true
	);
	
	protected $pilotable = null;
	
	public function __construct(EVEShipInfo_Collection $collection)
	{
		$this->collection = $collection;
		$this->initVirtualGroups();
	}
	
	public function getOrderBy()
	{
		return $this->orderBy;
	}
	
	public function getOrderDir()
	{
		if($this->ascending) {
			return 'ascending';
		}
		
		return 'descending';
	}
	
   /**
    * Orders the list by the ship's group, e.g. "Frigate", "Battleship".
    * 
    * @param string $ascending
    * @return EVEShipInfo_Collection_Filter
    * @see orderByVirtualGroup()
    */
	public function orderByGroup($ascending=true) {	return $this->setOrderBy('group', $ascending); }
	
   /**
    * Orders the list by the filter-specific virtual groups:
    * these allow a broader group selection than the regular
    * ship groups, by grouping hull sizes together. For example,
    * frigates and assault frigates are in the same frigate hull
    * group.
    * 
    * @param string $ascending
    * @return EVEShipInfo_Collection_Filter
    * @see orderByGroup()
    */
	public function orderByVirtualGroup($ascending=true) { return $this->setOrderBy('virtualgroup', $ascending); }
	public function orderByRace($ascending=true) { return $this->setOrderBy('race', $ascending); }
	public function orderByAgility($ascending=true) { return $this->setOrderBy('agility', $ascending); }
	public function orderByVelocity($ascending=true) { return $this->setOrderBy('velocity', $ascending); }
	public function orderByWarpSpeed($ascending=true) { return $this->setOrderBy('warpspeed', $ascending); }
	public function orderByHighSlots($ascending=true) { return $this->setOrderBy('highslots', $ascending); }
	public function orderByMedSlots($ascending=true) { return $this->setOrderBy('medslots', $ascending); }
	public function orderByLowSlots($ascending=true) { return $this->setOrderBy('lowslots', $ascending); }
	public function orderByName($ascending=true) { return $this->setOrderBy('name', $ascending); }
	public function orderByCargoBay($ascending=true) { return $this->setOrderBy('cargobay', $ascending); }
	public function orderByDroneBandwidth($ascending=true) { return $this->setOrderBy('dronebandwidth', $ascending); }
	public function orderByTurretSlots($ascending=true) { return $this->setOrderBy('turrets', $ascending); }
	public function orderByLauncherSlots($ascending=true) { return $this->setOrderBy('launchers', $ascending); }
	public function orderByTechLevel($ascending=true) { return $this->setOrderBy('techlevel', $ascending); }
	public function orderByMass($ascending=true) { return $this->setOrderBy('mass', $ascending); }
	
	public function secondOrderByGroup() { return $this->setSecondOrderBy('group'); }
	public function secondOrderByVirtualGroup() { return $this->setSecondOrderBy('virtualgroup'); }
	public function secondOrderByRace() { return $this->setSecondOrderBy('race'); }
	public function secondOrderByAgility() { return $this->setSecondOrderBy('agility'); }
	public function secondOrderByVelocity() { return $this->setSecondOrderBy('velocity'); }
	public function secondOrderByWarpSpeed() { return $this->setSecondOrderBy('warpspeed'); }
	public function secondOrderByHighSlots() { return $this->setSecondOrderBy('highslots'); }
	public function secondOrderByMedSlots() { return $this->setSecondOrderBy('medslots'); }
	public function secondOrderByLowSlots() { return $this->setSecondOrderBy('lowslots'); }
	public function secondOrderByCargoBay() { return $this->setSecondOrderBy('cargobay'); }
	public function secondOrderByDroneBandwidth() { return $this->setSecondOrderBy('dronebandwidth'); }
	public function secondOrderByTurretSlots() { return $this->setSecondOrderBy('turrets'); }
	public function secondOrderByLauncherSlots() { return $this->setSecondOrderBy('launchers'); }
	public function secondOrderByTechLevel() { return $this->setSecondOrderBy('techlevel'); }
	public function secondOrderByMass() { return $this->setSecondOrderBy('mass'); }
	
	public function setOrderBy($field, $ascending=true)
	{
		if(!isset($this->orderFields)) {
			$this->getOrderFields();
		}
		
		if(isset($this->orderFields[$field])) {
			$this->orderBy = $field;
			$this->ascending = $ascending;
		} else {
			$this->addWarning(sprintf(__('Unknown order field %1$s.', 'eve-shipinfo'), '['.$field.']'));
		}
		
		return $this;
	}
	
	protected $secondaryOrderBy;
	
	public function setSecondOrderBy($field)
	{
		if(!isset($this->orderFields)) {
			$this->getOrderFields();
		}
		
		if(isset($this->orderFields[$field])) {
			$this->secondaryOrderBy = $field;
		} else {
			$this->addWarning(sprintf(__('Unknown order field %1$s.', 'eve-shipinfo'), '['.$field.']'));
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
	
	public function hasMessages()
	{
		return !empty($this->messages);
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
	
	public function count()
	{
		return count($this->getShips());
	}
	
	protected $selectedGroups;
	
	public function selectGroup($groupID)
	{
		if(isset($this->virtualGroups[$groupID])) {
			return $this->selectGroups($this->virtualGroups[$groupID]['groups']);
		}

		if(!isset($this->selectedGroups)) {
			$this->selectedGroups = array();
		}
		
		if($this->groupIDExists($groupID) && !isset($this->selectedGroups[$groupID])) {
			$this->selectedGroups[$groupID] = true;
		}
		
		return $this;
	}
	
	protected $limit = 0;
	
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}
	
   /**
    * Selects a collection of ship groups at once.
    * 
    * @param array $groupIDs Indexed array with group IDs
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectGroups($groupIDs)
	{
		foreach($groupIDs as $groupID) {
			$this->selectGroup($groupID);
		}
		
		return $this;
	}
	
   /**
    * Helper method: selects all frigate ship groups, from
    * regular frigates to interceptors or expedition frigates.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectFrigates()
	{
		return $this->selectGroups($this->virtualGroups[9003]['groups']);
	}
	
   /**
    * Helper method: selects all battleship ship groups, from
    * regular battleships to marauders and black ops.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectBattleships()
	{
		return $this->selectGroups($this->virtualGroups[9002]['groups']);
	}
	
   /**
    * Retrieves the virtual group ID for the specified ship.
    * 
    * @param EVEShipInfo_Collection_Ship $ship
    * @return integer|NULL
    */
	public function getVirtualGroupID(EVEShipInfo_Collection_Ship $ship)
	{
		$groupID = $ship->getGroupID();
		foreach($this->virtualGroups as $virtualGroupID => $def) {
			if(in_array($groupID, $def['groups'])) {
				return $virtualGroupID;
			}
		}
		
		return null;
	}
	
	public function getVirtualGroupName(EVEShipInfo_Collection_Ship $ship)
	{
		$id = $this->getVirtualGroupID($ship);
		if($id) {
			return $this->virtualGroups[$id]['name'];
		}
		
		return '';
	}
	
	protected $virtualGroups;
	
	protected function initVirtualGroups()
	{
		$this->virtualGroups = array(
			9001 => array(
				'name' => __('All industrials'),
				'groups' => array(
					28, // industrial
					380, // deep space transport
					463, // mining barge
					513, // freighter
					543, // exhumer
					883, // capital industrial ships
					902, // jump freighter
					941, // industrial command ship
					1202, // blockade runner
				)
			),
			9002 => array(
				'name' => __('All battleships'),
				'groups' => array(
					27, // battleship
					898, // black ops
					900, // marauders
				) 
			),
			9003 => array(
				'name' => __('All frigates'),
				'groups' => array(
					25, // frigates
					324, // assault frigates
					541, // interdictor
					830, // covert ops
					831, // interceptor
					893, // electronic attack ship
					834, // stealth bombers
					1283, // expedition frigate
					1022, // prototype exploration ship
				)
			),
			9004 => array(
			    'name' => __('All capitals'),
			    'groups' => array(
			    	883, // capital industrial ships
			    	547, // carrier
			    	485, // dreadnought
			    	659, // supercarrier
			    	30, // titan
			    )
			),
			9005 => array(
			    'name' => __('All battlecruisers'),
			    'groups' => array(
			    	1201, // attack battlecruiser
			    	419, // combat battlecruiser
			    	540, // command ship
			    )
			),
			9006 => array(
			    'name' => __('All cruisers'),
			    'groups' => array(
			    	906, // combat recon ship
			    	26, // cruiser
			    	833, // force recon ship
			    	358, // heavy assault cruiser
			    	894, // heavy interdiction cruiser
			    	832, // logistics
			    	963, // strategic cruiser
			    )
			),
		    9007 => array(
		    	'name' => __('All destroyers'),
		        'groups' => array(
		        	420, // destroyer
		        )
		    ),
		    9008 => array(
		    	'name' => __('Miscellaneous'),
		        'groups' => array(
		        	31,
		            237,
		            29
		        )
		    )
	    );
	}
	
	protected $groups;
	
	public function getGroups()
	{
		if(isset($this->groups)) {
			return $this->groups;
		}
		
		$this->groups = $this->collection->getGroups();
		foreach($this->virtualGroups as $id => $def) {
			$this->groups[$id] = $def['name'];
		}
		
		asort($this->groups);
		
		return $this->groups;
	}
	
   /**
    * Helper method: selects all industrial ship groups, from regular industrials
    * to blockade runners.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectIndustrials()
	{
		return $this->selectGroups($this->virtualGroups[9001]['groups']);		
	}
	
	public function selectGroupNames($names)
	{
		foreach($names as $name) {
			$this->selectGroupName($name);
		}
		
		return $this;
	}
	
	public function selectGroupName($name)
	{
		$id = $this->getGroupIDByName($name);
		if($id) {
			$this->selectGroup($id);
		}
		
		return $this;
	}
	
	public function getGroupIDByName($name)
	{
		if(!isset($this->groups)) {
			$this->getGroups();
		}
		
		$name = strtolower($name);
		foreach($this->groups as $id => $groupName) {
			if(strtolower($groupName)==$name) {
				return $id;
			}
		}
		
		return null;
	}
	
	public function groupIDExists($groupID)
	{
		if(!isset($this->groups)) {
			$this->getGroups();
		}
		
		return isset($this->groups[$groupID]);
	}
	
   /**
    * Retrieves an indexed array with all ship object instances
    * matching the current criteria.
    * 
    * @return EVEShipInfo_Collection_Ship[]
    */
	public function getShips()
	{
		$ships = $this->collection->getShips();
		$total = count($ships);
		$result = array();
		for($i=0; $i < $total; $i++) {
			$ship = $ships[$i];
			if($this->isMatch($ship)) {
				$result[] = $ship;
			}
		}	
		
		usort($result, array($this, 'sortResults'));
		
		if($this->limit > 0 && $this->limit <= $total) {
		    return array_slice($result, 0, $this->limit);
		}
		
		return $result;
	}
	
   /**
    * Checks if the specified ship matches the currently selected
    * filter criteria.
    * 
    * @param EVEShipInfo_Collection_Ship $ship
    * @return boolean
    */
	protected function isMatch(EVEShipInfo_Collection_Ship $ship)
	{
		if(isset($this->search)) {
			if(!$ship->search($this->search)) {
				return false;
			}
		}
		
		if(!empty($this->ships) && !in_array($ship->getID(), $this->ships)) {
			return false;
		}
		
		// we want to limit by race
		if(isset($this->races) && !in_array($ship->getRaceID(), $this->races)) {
			return false;
		}
		
		if(isset($this->excludeRaces) && in_array($ship->getRaceID(), $this->excludeRaces)) {
			return false;
		}
		
		if(isset($this->selectedGroups) && !isset($this->selectedGroups[$ship->getGroupID()])) {
			return false;
		}
		
		$attribs = array_values($this->attributes);
		$total = count($attribs);
		for($i=0; $i<$total; $i++) {
			$attrib = $attribs[$i];
			$value = $ship->getAttributeValue($attrib['name']);
			switch($attrib['type']) {
				case 'numeric':
					if(!$this->matchNumber($value, $attrib['expression'])) {
						return false;
					}
					break;
			}
		}
		
		$props = array_values($this->properties);
		$total = count($props);
		for($i=0; $i<$total; $i++) {
			$prop = $props[$i];
			$value = $ship->getPropertyValue($prop['name']);
			switch($prop['type']) {
				case 'numeric':
					if(!$this->matchNumber($value, $prop['expression'])) {
						return false;
					}
					break;
			}
		}
		
	    if($this->pilotable===true && !$ship->isPilotable()) {
			return false;
	    } else if($this->pilotable===false && $ship->isPilotable()) {
	        return false;
		}
		
		return true;	
	}
	
   /**
    * Matches a number with an expression. Examples:
    * 
    * 4
    * bigger than 4
    * smaller than 8
    * bigger or equals 5
    * smaller or equals 4
    * between 5 and 7
    * 
    * @param unknown $number
    * @param unknown $expression
    * @return boolean
    */
	protected function matchNumber($number, $expression)
	{
		$expression = trim($expression);
		if(empty($expression)) {
			return true;
		}
		
		// match two numbers
		if(is_numeric($expression)) {
			if($number==$expression) {
				return true;
			}
			
			return false;
		}
		
		if(preg_match('/bigger[ ]*than[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number > $amount) {
		    	return  true;
		    }
		    
		    return false;
		}
		
		if(preg_match('/smaller[ ]*than[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number < $amount) {
		        return  true;
		    }
		
		    return false;
		}
		
		if(preg_match('/bigger[ ]*or[ ]*equals[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number >= $amount) {
		        return  true;
		    }
		
		    return false;
		}

		if(preg_match('/smaller[ ]*or[ ]*equals[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number <= $amount) {
		        return  true;
		    }
		
		    return false;
		}
		
		if(preg_match('/between[ ]*([0-9.]+)[ ]*and[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $from = $regs[1];
		    $to = $regs[2];
		    if($to < $from) {
		    	$from = $regs[2];
		    	$to = $regs[1];
		    }
		    
		    if($number >= $from && $number <= $to) {
		    	return true;
		    }
		
		    return false;
		}
		
		$this->addWarning(sprintf(__('The expression %1$s could not be recognized.', 'eve-shipinfo'), '['.$expression.']'));
		
		return true;
	}
	
	protected $orderFields;
	
   /**
    * Retrieves an associative array with field name > field label pairs
    * of all fields that it is possible to order the list by. The name
    * can be used as parameter when setting the order via the {@link setOrderBy()}
    * method.
    * 
    * @return multitype:<string, string>
    */
	public function getOrderFields()
	{
		if(!isset($this->orderFields)) {
			$this->orderFields = array(
				'agility' => __('Agility', 'eve-shipinfo'),
				'group' => __('Group', 'eve-shipinfo'),
				'name' => __('Name', 'eve-shipinfo'),
				'warpspeed' => __('Warp speed', 'eve-shipinfo'),
				'velocity' => __('Velocity', 'eve-shipinfo'),
				'highslots' => __('High slots', 'eve-shipinfo'),
				'medslots' => __('Med slots', 'eve-shipinfo'),
				'lowslots' => __('Low slots', 'eve-shipinfo'),
			    'race' => __('Race', 'eve-shipinfo'),
			    'virtualgroup' => __('Virtual group', 'eve-shipinfo'),
			    'cargobay' => __('Cargo bay', 'eve-shipinfo'),
			    'dronebandwidth' => __('Drone bandwidth', 'eve-shipinfo'),
			    'turrets' => __('Turret slots', 'eve-shipinfo'),
			    'launchers' => __('Launcher slots', 'eve-shipinfo'),
			    'techlevel' => __('Tech level', 'eve-shipinfo'),
				'mass' => __('Mass', 'eve-shipinfo')
			); 
		}
		
		return $this->orderFields;
	}
	
   /**
    * Retrieves an associative array with race name > race label pairs
    * for all races supported for filtering the list. Both the name and
    * label work when specifying a race.
    * 
    * @return multitype:<string,string>
    */
	public function describeRaces()
	{
		$names = $this->collection->getRaceNames();
		$races = array();
		foreach($names as $name) {
			$races[strtolower($name)] = $name;
		}
		
		return $races;
	}
	
	public function describeGroups()
	{
		$groups = $this->getGroups();
		$described = array();
		foreach($groups as $id => $name) {
			$described[strtolower($name)] = $name;
		}
		
		return $described;
	}
	
	protected function sortResults(EVEShipInfo_Collection_Ship $a, EVEShipInfo_Collection_Ship $b)
	{
		$dir = 1;
		if(!$this->ascending) {
			$dir = -1;
		}
		
		$values = $this->getOrderValues($a, $b, $this->orderBy);
		
		// for the secondary order field, we simply append
		// the string to the existing value to be compared,
		// this way the comparison will work naturally.
		if(isset($this->secondaryOrderBy) && $this->secondaryOrderBy != $this->orderBy) {
			$secondary = $this->getOrderValues($a, $b, $this->secondaryOrderBy);
			$values[0] .= $secondary[0];
			$values[1] .= $secondary[1];
		}
		
		return strnatcasecmp($values[0], $values[1])*$dir;
	}
	
   /**
    * Retrieves the comparison strings for the specified order field for
    * the specified ships. Returns an indexed array with string A, string B.
    * 
    * @param EVEShipInfo_Collection_Ship $a
    * @param EVEShipInfo_Collection_Ship $b
    * @param string $orderBy
    * @return multitype:string
    */
	protected function getOrderValues(EVEShipInfo_Collection_Ship $a, EVEShipInfo_Collection_Ship $b, $orderBy)
	{
		switch($orderBy) {
			case 'agility':
				$aVal = $a->getAgility();
				$bVal = $b->getAgility();
				break;
				
			case 'group':
				$aVal = $a->getGroupName();
				$bVal = $b->getGroupName();
				break;
				
			case 'warpspeed':
				$aVal = $a->getWarpSpeed();
				$bVal = $b->getWarpSpeed();
				break;
				
			case 'highslots':
				$aVal = $a->getHighSlots();
				$bVal = $b->getHighSlots();
				break;

			case 'medslots':
			    $aVal = $a->getMedSlots();
			    $bVal = $b->getMedSlots();
			    break;
				
		    case 'lowslots':
		        $aVal = $a->getLowSlots();
		        $bVal = $b->getLowSlots();
		        break;
		        
		    case 'race':
		        $aVal = $a->getRaceName();
		        $bVal = $b->getRaceName();
		        break;
		        
		    case 'virtualgroup':
		        $aVal = $this->getVirtualGroupName($a);
		        $bVal = $this->getVirtualGroupName($b);
		        break;
			         
			case 'cargobay':
			    $aVal = $a->getCargobaySize();
			    $bVal = $b->getCargobaySize();
			    break;
			    
			case 'dronebandwidth':
			    $aVal = $a->getDroneBandwidth();
			    $bVal = $b->getDroneBandwidth();
			    break;
			    
			case 'techlevel':
			    $aVal = $a->getTechLevel();
			    $bVal = $b->getTechLevel();
		        break;
		        
			case 'mass':
				$aVal = $a->getMass();
				$bVal = $b->getMass();
				break;
		        
	        case 'name':
			default:
				$aVal = $a->getName();
				$bVal = $b->getName();
				break;
		}
		
		return array($aVal, $bVal);
	}

	protected $races;
	
	public function selectRaceByID($raceID)
	{
		if($this->collection->raceIDExists($raceID)) {
			if(!isset($this->races)) {
				$this->races = array();
			}
			
			if(!in_array($raceID, $this->races)) {
				$this->races[] = $raceID;
			}
		}
		
		return $this;
	}
	
	public function selectRaceByName($raceName)
	{
		$id = $this->collection->getRaceIDByName($raceName);
		return $this->selectRaceByID($id);
	}
	
	public function selectRaceNames($names)
	{
		foreach($names as $name) {
			$this->selectRaceByName($name);
		}
		
		return $this;
	}
	
	protected $ships = array();
	
   /**
    * Selects a specific ship to be included in the
    * result list. If used in conjunction with other
    * filters, may be ignored anyway.
    * 
    * @param integer $shipID
    * @return EVEShipInfo_Collection_Filter
    * @see deselectShip()
    */
	public function selectShip($shipID)
	{
		if(!in_array($shipID, $this->ships)) {
			$this->ships[] = $shipID;
		}
		
		return $this;
	}
	
   /**
    * Removes a ship from the list of selected ships.
    * Has no effect if ithad not been selected.
    * 
    * @param integer $shipID
    * @return EVEShipInfo_Collection_Filter
    * @see selectShip()
    */
	public function deselectShip($shipID)
	{
		$idx = array_search($shipID, $this->ships);
		if($idx !== false) {
			unset($this->ships[$idx]);
			sort($this->ships); // reset the array keys
		}
		
		return $this;
	}
	
	public function selectRaceIDs($raceIDs)
	{
		foreach($raceIDs as $raceID) {
			$this->selectRaceByID($raceID);
		}
		
		return $this;
	}
	
	protected $attributes = array();

   /**
    * Selects an attribute to limit the list to. 
    * 
    * @param string $name The attribute name
    * @param string $type The type of comparison to apply to the attribute, i.e. 'numeric'
    * @param string $expression The value to compare, or an expression if the comparison type supports it
    * @return EVEShipInfo_Collection_Filter
    */
	protected function selectAttribute($name, $type, $expression)
	{
		$this->attributes[$name] = array(
			'name' => $name,
			'type' => $type,
			'expression' => $expression
		);
		
		return $this;
	}
	
	protected $properties = array();
	
	protected function selectProperty($name, $type, $expression)
	{
	    $this->properties[$name] = array(
	    	'name' => $name,
	    	'type' => $type,
	    	'expression' => $expression
	    );
	    
	    return $this;
	}
	
	public function selectAgility($expression) { return $this->selectAttribute('agility', 'numeric', $expression); }
	public function selectWarpSpeed($expression) { return $this->selectAttribute('baseWarpSpeed', 'numeric', $expression); }
	public function selectVelocity($expression)	{ return $this->selectAttribute('velocity', 'numeric', $expression); }
	public function selectHighSlots($expression) { return $this->selectAttribute('hiSlots', 'numeric', $expression); }
	public function selectMedSlots($expression) { return $this->selectAttribute('medSlots', 'numeric', $expression); }
	public function selectLowSlots($expression) { return $this->selectAttribute('lowSlots', 'numeric', $expression); }
	public function selectCargoBaySize($expression) { return $this->selectProperty('capacity', 'numeric', $expression); }
	public function selectDroneBandwidth($expression) { return $this->selectAttribute('droneBandwidth', 'numeric', $expression); }
	public function selectDroneBaySize($expression) { return $this->selectAttribute('droneCapacity', 'numeric', $expression); }
	public function selectTurretSlots($expression) { return $this->selectAttribute('turretSlotsLeft', 'numeric', $expression); }
	public function selectLauncherSlots($expression) { return $this->selectAttribute('launcherSlotsLeft', 'numeric', $expression); }
	public function selectTechLevel($expression) { return $this->selectAttribute('techLevel', 'numeric', $expression); }
	public function selectMass($expression) { return $this->selectProperty('mass', 'numeric', $expression); }
	
	protected $search;
	
   /**
    * Sets search terms to limit the selection to, as terms separated with spaces.
    * @param string $terms
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectSearch($terms)
	{
		$this->search = $terms;
		return $this;
	}
	
	public function getGroupNames()
	{
		
	}
	
	public function getVirtualGroupGroupNames($groupID)
	{
		if(!isset($this->virtualGroups[$groupID])) {
			return array();
		}
		
		$names = array();
		foreach($this->virtualGroups[$groupID]['groups'] as $gid) {
			$names[] = $this->collection->getGroupNameByID($gid);	
		} 
		
		return $names;
	}

   /**
    * Limits the selection to minmatar ships.
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectMinmatar()
	{
		return $this->selectRaceByName('Minmatar');
	}
	
   /**
    * Limits the selection to armarr ships.
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectAmarr()
	{
		return $this->selectRaceByName('Amarr');
	}
	
   /**
    * Limits the selection to gallente ships.
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectGallente()
	{
		return $this->selectRaceByName('Gallente');
	}
	
	/**
	 * Limits the selection to caldari ships.
	 * @return EVEShipInfo_Collection_Filter
	 */
	public function selectCaldari()
	{
		return $this->selectRaceByName('Caldari');
	}
	
	/**
	 * Limits the selection to ore ships.
	 * @return EVEShipInfo_Collection_Filter
	 */
	public function selectOre()
	{
	    return $this->selectRaceByName('Ore');
	}
	
	/**
	 * Limits the selection to jove ships.
	 * @return EVEShipInfo_Collection_Filter
	 */
	public function selectJove()
	{
	    return $this->selectRaceByName('Jove');
	}

   /**
    * Selects only ships that can actually be flown with the right skills.
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectPilotable()
	{
		$this->pilotable = true;
		return $this;
	}
	
   /**
    * Selects only ships that cannot actually be flown ingame. 
    * Note: a noteworthy exception is the pods, which are set
    * as not pilotable for some reason.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectUnpilotable()
	{
		$this->pilotable = false;
		return $this;
	}

   /**
    * Excludes minmatar ships from the selection.
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectMinmatar()
	{
		return $this->deselectRaceByName('Minmatar');
	}
	
   /**
    * Excludes amarr ships from the selection.
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectAmarr()
	{
		return $this->deselectRaceByName('Amarr');
	}
	
   /**
    * Excludes gallente ships from the selection.
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectGallente()
	{
		return $this->deselectRaceByName('Gallente');
	}
	
	/**
	 * Excludes caldari ships from the selection.
	 * @return EVEShipInfo_Collection_Filter
	 */
	public function deselectCaldari()
	{
		return $this->deselectRaceByName('Caldari');
	}
	
   /**
    * Excludes ore ships from the selection.
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectOre()
	{
		return $this->deselectRaceByName('Ore');
	}
	
   /**
    * Excludes jove ships from the selection.
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectJove()
	{
		return $this->deselectRaceByName('Jove');
	}
	
   /**
    * Excludes a ship race from the selection by its name.
    * @param string $name
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectRaceByName($name)
	{
	    $id = $this->collection->getRaceIDByName($name);
	    return $this->deselectRaceByID($id);
	}

	protected $excludeRaces;
	
   /**
    * Excludes a ship race from the selection by its ID.
    * @param string $raceID
    * @return EVEShipInfo_Collection_Filter
    */
	public function deselectRaceByID($raceID)
	{
		if($this->collection->raceIDExists($raceID)) {
			if(!isset($this->excludeRaces)) {
				$this->excludeRaces = array();
			}
			
			if(!in_array($raceID, $this->excludeRaces)) {
				$this->excludeRaces[] = $raceID;
			}
		}
		
		return $this;
	}
}