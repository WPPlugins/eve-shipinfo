<?php
/**
 * File containing the {@link EVEShipInfo_Collection_Ship} class.
 * 
 * @package EVEShipInfo
 * @subpackage Collection
 * @see EVEShipInfo_Collection_Ship
 */

/**
 * Container for a single ship in the collection. This is the
 * main hub to retrieve information about individual ships.  
 * 
 * @package EVEShipInfo
 * @subpackage Collection
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 */
class EVEShipInfo_Collection_Ship
{
   /**
	* @var multitype:<EVEShipInfo_Collection_Ship>
	*/
	protected static $instances = array();
	
   /**
	* Factory for creating a specific ship. Note: make sure
	* that the specified ship ID exists before calling this,
	* as it will throw an exception if the ship does not exist.
	* 
	* Each ship instance is only created once to avoid any
	* overhead.
	* 
	* @param integer $shipID
	* @return EVEShipInfo_Collection_Ship
	*/
	public static function create(EVEShipInfo_Plugin $plugin, EVEShipInfo_Collection $collection, $shipID)
	{
		if(!isset(self::$instances[$shipID])) {
			self::$instances[$shipID] = new EVEShipInfo_Collection_Ship($plugin, $collection, $shipID);
		}
		
		return self::$instances[$shipID];
	}
	
	protected $id;

	protected $data;
	
   /**
	* @var EVEShipInfo
	*/
	protected $plugin;
	
   /**
	* @var EVEShipInfo_Collection
	*/
	protected $collection;
	
	const ERROR_SHIP_DOES_NOT_EXIST = 1501;
	
	protected function __construct(EVEShipInfo_Plugin $plugin, EVEShipInfo_Collection $collection, $shipID)
	{
		$this->plugin = $plugin;
		$this->collection = $collection;
		$this->id = $shipID;
		
		$this->data = $this->plugin->dbFetch(
			"SELECT
				ships.*,
				groups.alias AS groupName
			FROM
				".$this->plugin->getTableName('ships')." AS ships
			JOIN
				".$this->plugin->getTableName('ships_groups')." AS groups
			ON
				groups.groupID = ships.groupID
			WHERE
				ships.typeID=%d",
			array(
				$shipID
			)	
		);
		
		if(empty($this->data)) {
			throw new EVEShipInfo_Exception(
				'Unknown ship',
				sprintf(
					'The ship with ID [%s] could not be found in the collection.',
					$shipID
				),
				self::ERROR_SHIP_DOES_NOT_EXIST	
			);
		}
	}
	
   /**
	* The ship ID. This is the EVE typeID from the invtypes table.
	* @return integer
	*/
	public function getID()
	{
		return $this->id;
	}
	
   /**
	* @return string
	*/
	public function getName()
	{
		return $this->data['typeName'];
	}
	
	public function getDescription()
	{
		return $this->getPropertyValue('description');
	}
	
	public function getMass($units=false)
	{
		$value = $this->getPropertyValue('mass');
		if(!$units) {
			return $value;
		}
		
		return number_format($value).' '.__('KG', 'eve-shipinfo');
	}
	
	public function getVolume($units=false)
	{
		$value = $this->getPropertyValue('volume');
		if(!$units) {
			return $value;
		}
		
		return number_format($value).' '.__('M3', 'eve-shipinfo');
	}
	
	public function getCargobaySize($units=false)
	{
		$value = $this->getPropertyValue('capacity');
		if(!$units) {
			return $value;
		}
		
		return number_format($value).' '.__('M3', 'eve-shipinfo');
	}
	
	public function getRaceID()
	{
		return $this->getPropertyValue('raceID');
	}
	
	public function getRaceName()
	{
		$id = $this->getRaceID();
		if($id) {
			return $this->collection->getRaceName($id);
		}
		
		return null;
	}
	
	public function getGroupName()
	{
		return $this->getPropertyValue('groupName');
	}
	
	public function getGroupID()
	{
		return $this->getPropertyValue('groupID');
	}
	
	public function getLowSlots()
	{
		return $this->getAttributeValue('lowSlots');
	}
	
	public function getMedSlots()
	{
		return $this->getAttributeValue('medSlots');
	}
	
	public function getHighSlots()
	{
		return $this->getAttributeValue('hiSlots');
	}
	
	public function getStructureHitpoints($units=false)
	{
		return $this->getAttributeValue('hp', $units);
	}
	
	public function getPowerOutput($units=false)
	{
		return $this->getAttributeValue('powerOutput', $units);
	}
	
	public function getPowerLoad()
	{
		return $this->getAttributeValue('powerLoad');
	}
	
	public function getPowerToSpeed()
	{
		return $this->getAttributeValue('powerToSpeed');
	}
	
	public function getStructureKineticResistance($units=false)
	{
		return $this->getXYResistance('structure', 'kinetic', $units);
	}
	
	public function getStructureEmResistance($units=false)
	{
		return $this->getXYResistance('structure', 'em', $units);
	}
	
	public function getStructureThermalResistance($units=false)
	{
		return $this->getXYResistance('structure', 'thermal', $units);
	}
	
	public function getStructureExplosiveResistance($units=false)
	{
		return $this->getXYResistance('structure', 'explosive', $units);
	}
	
	public function getArmorKineticResistance($units=false)
	{
		return $this->getXYResistance('armor', 'kinetic', $units);
	}

	public function getArmorEmResistance($units=false)
	{
		return $this->getXYResistance('armor', 'em', $units);
	}
	
	public function getArmorThermalResistance($units=false)
	{
		return $this->getXYResistance('armor', 'thermal', $units);
	}
	
	public function getArmorExplosiveResistance($units=false)
	{
		return $this->getXYResistance('armor', 'explosive', $units);
	}
	
	public function getShieldKineticResistance($units=false)
	{
		return $this->getXYResistance('shield', 'kinetic', $units);
	}
	
	public function getShieldEmResistance($units=false)
	{
		return $this->getXYResistance('shield', 'em', $units);
	}
	
	public function getShieldThermalResistance($units=false)
	{
		return $this->getXYResistance('shield', 'thermal', $units);
	}
	
	public function getShieldExplosiveResistance($units=false)
	{
		return $this->getXYResistance('shield', 'explosive', $units);
	}
	
   /**
	* Retrieves the resistance value for the specified ship area
	* and the specified damage type.
	* 
	* @param string $area The ship area: armor, shield, structure
	* @param string $type The damage type: kinetic, em, thermal, explosive
	* @param string $units
	* @return EVEShipInfo_Collection_Ship_Attribute
	*/
	public function getXYResistance($area, $type, $units=false)
	{
		$area = strtolower($area);
		$type = ucfirst($type);
		
		if($area=='structure') {
			$area = '';
			$type = strtolower($type);
		}
		 
		$name = $area.$type.'DamageResonance';
		
		return $this->getAttributeValue($name, $units);
	}
	
	public function getMeanKineticResistance($units=false)
	{
		return $this->getMeanXResistance('kinetic', $units);
	}
	
	public function getMeanEmResistance($units=false)
	{
		return $this->getMeanXResistance('em', $units);
	}
	
	public function getMeanThermalResistance($units=false)
	{
		return $this->getMeanXResistance('thermal', $units);
	}
	
	public function getMeanExplosiveResistance($units=false)
	{
		return $this->getMeanXResistance('explosive', $units);
	}
	
   /**
	* Retrieves the average resistance value for the specified
	* damage type.
	* 
	* @param string $type The damage type: kinetic, em, thermal, explosive
	* @param boolean $units
	* @return string
	*/
	public function getMeanXResistance($type, $units=false)
	{
		$total =
		$this->getXYResistance('structure', $type)+
		$this->getXYResistance('armor', $type)+
		$this->getXYResistance('shield', $type);
		 
		$average = number_format($total/3, 2);
		 
		if($units) {
			$average .= ' %';
		}
		 
		return $average;
	}
	
   /**
	* The amount of launcher slots on the ship.
	* @return EVEShipInfo_Collection_Ship_Attribute
	*/
	public function getLauncherSlots()
	{
		return $this->getAttribute('launcherSlotsLeft');
	}
	
	protected $attributes = array();
	
   /**
	* Retrieves the value of a ship's attribute. Available attributes are:
	* 
	* @param string $name
	* @param boolean $units Whether to append the measurement units to the resulting value (if applicable)
	* @return EVEShipInfo_Collection_Ship_Attribute
	*/
	public function getAttributeValue($name, $units=false)
	{
		$attr = $this->getAttribute($name);
		if(!$attr) {
			return '';
		}
		
		if($units) {
			return $attr->getValue(true).' '.$attr->getUnitName();
		}
		 
		return $attr->getValue();
	}
	
   /**
    * @param string $name
    * @return EVEShipInfo_Collection_Ship_Attribute
    */
	protected function getAttribute($name)
	{
		if(array_key_exists($name, $this->attributes)) {
			return $this->attributes[$name];
		}
		
		try{
   		   $attr = $this->createAttribute($name);
		} catch(EVEShipInfo_Exception $e) {
   		   $attr = null;
		}
		
		$this->attributes[$name] = $attr;
		
		return $attr;
	}
	
   /**
    * Retrieves a list of all attributes available for the ship,
    * as an associative array with the attribute names as keys.
    * 
    * @return EVEShipInfo_Collection_Ship_Attribute[]
    */
	public function getAttributes()
	{
	    $names = $this->getAttributeNames();
	    
	    $result = array();
	    foreach($names as $name) {
	        $result[$name] = $this->getAttribute($name);
	    }
	    
	    return $result;
	}
	
	protected $cachedAttributeNames;
	
   /**
    * Retrieves the full list of attribute names available for the ship.
    * @return string[]
    */
	public function getAttributeNames()
	{
	    if(isset($this->cachedAttributeNames)) {
	        return $this->cachedAttributeNames;
	    }
	    
	    $this->cachedAttributeNames = array();
	    
	    $result = $this->plugin->dbFetchAllKey(
	        'attributeName', 
	        "SELECT
	           atts.attributeName
	        FROM
	           ".$this->plugin->getTableName('ships_attributes')." AS shipAtts
	        JOIN
	           ".$this->plugin->getTableName('attributes')." AS atts
	        ON
	           atts.attributeID = shipAtts.attributeID
	        WHERE
	           shipAtts.typeID=%d
	        ORDER BY
	           atts.attributeName ASC",
	        array(
	            $this->id
	        )
        );
	    
	    if(is_array($result)) {
	        $this->cachedAttributeNames = $result;
	    }
	    
	    return $this->cachedAttributeNames;
	}
	
	protected function createAttribute($name)
	{
		$this->plugin->loadClass('EVEShipInfo_Collection_Ship_Attribute');
		return new EVEShipInfo_Collection_Ship_Attribute($this, $name);
	}
	
	protected $propertiesInitialized = false;
	
	public function getPropertyValue($name)
	{
		if(isset($this->data[$name])) {
			return $this->data[$name];
		}	
		
		return null;
	}
	
	protected $hasScreenshot = array();
	
	public function hasScreenshot($view='Front')
	{
		if(!isset($this->hasScreenshot[$view])) {
			$this->hasScreenshot[$view] = file_exists($this->getScreenshotPath($view));
		}
		
		return $this->hasScreenshot[$view];
	}
	
	protected $screenshotPath = array();
	
	public function getScreenshotPath($view='Front')
	{
		if(!isset($this->screenshotPath[$view])) {
			$this->screenshotPath[$view] = $this->plugin->getGalleryPath().'/'.$this->getName().' '.$view.'.jpg';
		}
		
		return $this->screenshotPath[$view];
	}
	
	public function getScreenshotSize($view='Front')
	{
		$path = $this->getScreenshotPath($view);
		if(file_exists($path)) {
			$size = getimagesize($path);
			return array($size[0], $size[1]);
		}
		
		return array(0,0);
	}
	
	protected $screenshotURL = array();
	
	public function getScreenshotURL($view='Front')
	{
		if(!isset($this->screenshotURL[$view])) {
			$this->screenshotURL[$view] = $this->plugin->getGalleryURL().'/'.$this->getName().' '.$view.'.jpg';
		}
		 
		return $this->screenshotURL[$view];
	}
	
   /**
	* @return EVEShipInfo_Collection
	*/
	public function getCollection()
	{
		return $this->collection;
	}
	
	public function getViewURL()
	{
		$name = str_replace(array(' ', "'"), array('-', ''), $this->getName());
		return rtrim(get_site_url(), '/').'/eve/ship/'.$name;
	}
	
	public function getMaxVelocity($units=false)
	{
		return $this->getAttributeValue('maxVelocity', $units);
	}
	
	public function getCPUOutput($units=false)
	{
		return $this->getAttributeValue('cpuOutput', $units);
	}
	
	public function getCapacitorRechargeRate($units=false)
	{
		return $this->getAttributeValue('rechargeRate', $units);
	}
	
	public function getAgility($units=false)
	{
		return $this->getAttributeValue('agility', $units);
	}
	
	public function getMaxTargetingRange($units=false)
	{
		return $this->getAttributeValue('maxTargetRange', $units);
	}
	
	public function getScanSpeed($units=false)
	{
		return $this->getAttributeValue('scanSpeed', $units);
	}
	
	public function getLauncherHardpoints()
	{
		return $this->getAttributeValue('launcherSlotsLeft');
	}
	
	public function getTurretHardpoints()
	{
		return $this->getAttributeValue('turretSlotsLeft');
	}
	
	public function getMaxLockedTargets()
	{
		return $this->getAttributeValue('maxLockedTargets');
	}
	
	public function getShieldHitpoints($units=false)
	{
		return $this->getAttributeValue('shieldCapacity', $units);
	}
	
	public function getArmorHitpoints($units=false)
	{
		return $this->getAttributeValue('armorHP', $units);
	}
	
	public function getDronebaySize($units=false)
	{
		return $this->getAttributeValue('droneCapacity', $units);
	}
	
	public function getTechLevel()
	{
		return $this->getAttributeValue('techLevel');
	}
	
	public function getShieldRechargeRate($units=false)
	{
		return $this->getAttributeValue('shieldRechargeRate', $units);
	}
	
	public function getCapacitorCapacity($units=false)
	{
		return $this->getAttributeValue('capacitorCapacity', $units);
	}
	
	public function getSignatureRadius($units=false)
	{
		return $this->getAttributeValue('signatureRadius', $units);
	} 
	
	public function getScanResolution($units=false)
	{
		return $this->getAttributeValue('scanResolution', $units);
	}
	
   /**
    * Calculates the total hitpoints from the ship's structure,
    * base shield HP and base armor HP.
    * 
    * @param boolean $units
    * @return string
    */
	public function getTotalHitpoints($units=false)
	{
		$total = 
		$this->getStructureHitpoints() + 
		$this->getShieldHitpoints() + 
		$this->getArmorHitpoints();
		
		if($units) {
			$attr = $this->getAttribute('armorHP');
			$unitName = '';
			if($attr) {
				$unitName = ' '.$attr->getUnitName();
			}
			
			return number_format($total).$unitName;
		}
		
		return $total;
	}
	
	public function getWarpSpeed($units=false)
	{
		$value = $this->getAttributeValue('baseWarpSpeed') * $this->getAttributeValue('warpSpeedMultiplier');
		
		if($units) {
			return number_format($value, 2).' '.__('AU/S', 'eve-shipinfo');
		}
		
		return $value;
	}
	
	public function getDroneBandwidth($units=false)
	{
		return $this->getAttributeValue('droneBandwidth', $units);
	}

   /**
	* Retrieves the ship's max velocity.
	* @param boolean $units Whether to append the measurement units to the value
	* @return string
	*/
	public function getVelocity($units=false)
	{
		return $this->getAttributeValue('velocity', $units);
	}
	
   /**
	* Alias for <code>getVelocity</code>. 
	* @param boolean $units Whether to append the measurement units to the value
	* @return string
	* @see getVelocity()
	*/
	public function getSpeed($units=false)
	{
		return $this->getVelocity($units);
	}
	
   /**
	* The amount of rig slots available on the ship.
	* @return string
	*/
	public function getRigSlots()
	{
		return $this->getAttributeValue('rigSlots');
	}
	
   /**
	* The size of rig that can be fitted to the ship.
	* @return string 
	*/
	public function getRigSize()
	{
		// FIXME add a reference for the possible return values
		return $this->getAttributeValue('rigSize');
	}
	
	public function exportData()
	{
		$data = array(
			'id' => $this->getID(),
			'name' => $this->getName(),
			'description' => nl2br($this->getDescription()),
			'mass' => $this->getMass(true),
			'volume' => $this->getVolume(true),
			'cargobaySize' => $this->getCargobaySize(true),
			'raceID' => $this->getRaceID(),
			'raceName' => $this->getRaceName(),
			'groupID' => $this->getGroupID(),
			'groupName' => $this->getGroupName(),
			'lowSlots' => $this->getLowSlots(),
			'hiSlots' => $this->getHighSlots(),
			'medSlots' => $this->getMedSlots(),
			'maxVelocity' => $this->getMaxVelocity(true),
			'powerOutput' => $this->getPowerOutput(true),
			'capacitorRechargeRate' => $this->getCapacitorRechargeRate(true),
			'capacitorCapacity' => $this->getCapacitorCapacity(true),
			'shieldRechargeRate' => $this->getShieldRechargeRate(true),
			'shieldHitpoints' => $this->getShieldHitpoints(true),
			'agility' => $this->getAgility(true),
			'maxTargetRange' => $this->getMaxTargetingRange(true),
			'scanSpeed' => $this->getScanSpeed(true),
			'launcherHardpoints' => $this->getLauncherHardpoints(),
			'turretHardpoints' => $this->getTurretHardpoints(),
			'maxLockedTargets' => $this->getMaxLockedTargets(),
			'structureHitpoints' => $this->getStructureHitpoints(true),
			'armorHitpoints' => $this->getArmorHitpoints(true),
			'totalHitpoints' => $this->getTotalHitpoints(true),
			'techLevel' => $this->getTechLevel(),
			'signatureRadius' => $this->getSignatureRadius(true),
			'scanResolution' => $this->getScanResolution(true),
			'warpSpeed' => $this->getWarpSpeed(true),
			'dronebaySize' => $this->getDronebaySize(true),
			'droneBandwidth' => $this->getDroneBandwidth(true),
			'powerLoad' => $this->getPowerLoad(),
			'powerToSpeed' => $this->getPowerToSpeed(),
			'structureKineticResistance' => $this->getStructureKineticResistance(true),
			'structureEmResistance' => $this->getStructureEmResistance(true),
			'structureThermalResistance' => $this->getStructureThermalResistance(true),
			'structureExplosiveResistance' => $this->getStructureExplosiveResistance(true),
			'armorKineticResistance' => $this->getArmorKineticResistance(true),
			'armorEmResistance' => $this->getArmorEmResistance(true),
			'armorThermalResistance' => $this->getArmorThermalResistance(true),
			'armorExplosiveResistance' => $this->getArmorExplosiveResistance(true),
			'shieldKineticResistance' => $this->getShieldKineticResistance(true),
			'shieldEmResistance' => $this->getShieldEmResistance(true),
			'shieldThermalResistance' => $this->getShieldThermalResistance(true),
			'shieldExplosiveResistance' => $this->getShieldExplosiveResistance(true),
			'screenshots' => array()
		);
		
		$views = $this->collection->getViewIDs();
		foreach($views as $view) {
			$data['screenshots'][$view] = array(
				'exists' => $this->screenshotExists($view),
				'url' => $this->getScreenshotURL($view),
				'size' => $this->getScreenshotSize($view)
			);
	   	}
		
		return $data;
	}
	
   /**
	* Checks whether a screenshot exists for this ship and the specified view.
	* @param string $view
	* @return boolean
	*/
	public function screenshotExists($view='Front')
	{
		return file_exists($this->getScreenshotPath($view));
	}
	
	public function search($terms)
	{
		if(stristr($this->getName(), $terms)) {
			return true;
		}
		
		if(stristr($this->getDescription(), $terms)) {
			return true;
		}
		
		return false;
	}
	
   /**
	* Checks whether this is a pirate faction ship.
	* @return boolean
	*/
	public function isPirate()
	{
		if($this->getRaceID()==32) {
			return true;
		}
		
		return false;
	}
	
   /**
	* Checks whether this is a jove faction ship.
	* @return boolean
	*/
	public function isJove()
	{
		if($this->getRaceID()==16) {
			return true;
		}
		
		return false;
	}
	
   /**
	* Checks whether this is a ship that can actually be
	* flown in the game.
	* 
	* @return boolean
	*/
	public function isPilotable()
	{
		if($this->getPropertyValue('published') != 1) {
			return false;
		}
		
		return true;
	}
	
	protected static $registeredShips = array();
	
	public function renderClientRegistration()
	{
		$shipID = $this->getID();
		if(isset(self::$registeredShips[$shipID])) {
			return '';
		}
		
		self::$registeredShips[$shipID] = true;
		
		$encoded = json_encode($this->exportData());
		if(!$encoded) {
			return '';
		}
		
		return
		'<script>'.
			sprintf(
				"EVEShipInfo.AddShip(%s);",
				$encoded
			).
		'</script>';
	}
}