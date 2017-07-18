<?php

class EVEShipInfo_EFTManager
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	public function __construct(EVEShipInfo $plugin)
	{
		$this->plugin = $plugin;
		$this->plugin->loadClass('EVEShipInfo_EFTManager_Fit');
	}
	
   /**
    * Checks whether the fittings data file exists. 
    * @return boolean
    */
	public function hasFittings()
	{
		$this->load();
		return !empty($this->fittings);
	}
	
   /**
    * Retrieves all available fittings.
    * @return multitype:EVEShipInfo_EFTManager_Fit
    */
	public function getFittings()
	{
		$this->load();
		return array_values($this->fittings);
	}
	
   /**
    * Retrieves a ship fit by its ID.
    * @param integer $id
    * @return EVEShipInfo_EFTManager_Fit|NULL
    */
	public function getFittingByID($id)
	{
		$this->load();
		if(isset($this->fittings[$id])) {
			return $this->fittings[$id];
		}
		
		return null;
	}
	
   /**
    * Retrieves a fitting by its name. This requires a ship name
    * to be specified as well, since multiple fits can exist with
    * the same name but for different ships.
    * 
    * @param string $name
    * @param string $shipName
    * @return EVEShipInfo_EFTManager_Fit|NULL
    */
	public function getFittingByName($name, $shipName)
	{
		foreach($this->fittings as $fit) {
			if($fit->getName() == $name && $fit->getShipName() == $shipName) {
				return $fit;
			}
		}
		
		return null;
	}
	
   /**
    * Retrieves the last modification date of the EFT export XML file.
    * Note: returns null if the file does not exist, so always check the
    * return value or use the {@link hasFittings()} method first.
    * 
    * @return NULL|DateTime
    */
	public function getLastModified()
	{
		if(!$this->hasFittings()) {
			return null;
		}
		
		$date = new DateTime();
		$date->setTimestamp($this->modtime);
		return $date;
	}
	
	protected $fittings = array();
	
	protected $loaded = false;
	
	protected $modtime;
	
	protected function load()
	{
		if($this->loaded) {
			return;
		}
		
		$this->loaded = true;
		
		$data = $this->plugin->getOption('fittings');
		if(!$data) {
			return;
		}
		
		$this->data = unserialize($data);
		
		foreach($this->data['fits'] as $item) {
			$fit = EVEShipInfo_EFTManager_Fit::fromArray($this, $item);
			$this->addFit($fit);
		}
	}
	
	protected $data = array();
	
   /**
    * Retrieves the raw EFT fittings data.
    * @return array
    */
	public function getEFTData()
	{
		$this->load();
		return $this->data;
	}
	
	protected function addFit(EVEShipInfo_EFTManager_Fit $fit)
	{
		$this->fittings[$fit->getID()] = $fit;
		return $this;
	}
	
   /**
    * Counts the amount of available fittings.
    * @return integer
    */
	public function countFittings()
	{
		$this->load();
		return count($this->fittings);
	}
	
   /**
    * Tells the manager it should refresh its data collection next
    * time information is accessed. This is used by the import function
    * to clear the internal cache.
    * 
    * @return EVEShipInfo_EFTManager
    */
	public function reload()
	{
		$this->loaded = false;
		$this->fittings = array();
		return $this;
	}
	
   /**
    * Deletes a fitting from the collection. Note that the
    * change does not get saved automatically: the {@link save()}
    * method has to be called to commit the changes.
    * 
    * @param EVEShipInfo_EFTManager_Fit $fit
    * @return boolean
    */
	public function deleteFitting(EVEShipInfo_EFTManager_Fit $fit)
	{
		$this->load();
		
		$id = $fit->getID();
		
		if(isset($this->fittings[$id])) {
			unset($this->fittings[$id]);
			return true;
		}
		
		return false;
	}

   /**
    * Checks whether the specified fit ID exists.
    * @param integer $id
    * @return boolean
    */
	public function idExists($id)
	{
		$this->load();
		return isset($this->fittings[$id]);
	}
	
   /**
    * Saves all existing fits and any changes that may have
    * been made to fits into the database.
    * 
    * @return boolean
    */
	public function save()
	{
		if(!$this->loaded) {
			return false;
		}
		
		$data = array(
			'updated' => $this->modtime,
			'fits' => array()
		);
		
		foreach($this->fittings as $fit) {
			$data['fits'][] = $fit->toArray();
			$fit->resetModified();
		} 
		
		$this->plugin->setOption('fittings', serialize($data));
		
		return true;
	}
	
   /**
    * Creates an returns an instance of the helper class that can be
    * used to retrieve a list of fittings matching a number of criteria.
    * Also supports ordering the list.
    * 
    * @return EVEShipInfo_EFTManager_Filters
    */
	public function getFilters()
	{
		$this->plugin->loadClass('EVEShipInfo_EFTManager_Filters');
		return new EVEShipInfo_EFTManager_Filters($this);
	}
	
	protected $testFits = array(
		"[Legion, Complex Specialist]
Centum A-Type Medium Armor Repairer
Armor Thermic Hardener II
Armor EM Hardener II
Tairei's Modified Energized Adaptive Nano Membrane
Imperial Navy Heat Sink
		
Federation Navy Stasis Webifier
Republic Fleet 10MN Afterburner
Data Analyzer II
Relic Analyzer II
		
Heavy Pulse Laser II, Conflagration M
Heavy Pulse Laser II, Conflagration M
Heavy Pulse Laser II, Conflagration M
Improved Cloaking Device II
Salvager II
Core Probe Launcher II, Core Scanner Probe I
Small Tractor Beam II
		
Medium Capacitor Control Circuit I
Medium Energy Burst Aerator I
Medium Nanobot Accelerator I
		
Legion Defensive - Adaptive Augmenter
Legion Electronics - Emergent Locus Analyzer
Legion Engineering - Capacitor Regeneration Matrix
Legion Propulsion - Fuel Catalyst
Legion Offensive - Drone Synthesis Projector
		
Valkyrie II x5
Hammerhead II x5
	"
	);
	
	public function parseFit($fitString)
	{
		if(substr($fitString, 0, 1) != '[') {
			return false;
		}
		
		$lines = array_map('trim', explode("\n", $fitString));
		
		$name = array_shift($lines);
		$result = array();
		preg_match_all('/\A\[([^,]+),([^]]+)\]\z/', $name, $result, PREG_PATTERN_ORDER);
		if(!isset($result[0]) || !isset($result[0][0]) || empty($result[0][0])) {
			return false;
		}

		$ship = trim($result[1][0]);
		$label = trim($result[2][0]);
		
		$this->load();
		
		$modules = array();
		foreach($lines as $line) {
			if(empty($line)) {
				continue;
			}
			
			if(substr($line, 0, 6) == '[empty') {
				continue;
			}
			
			// the name can have the notation name, foo
			// where foo is the charge to use. We don't use that.
			$tokens = explode(',', $line);
			$name = trim(array_shift($tokens));
			$charge = null;
			if(count($tokens) != 0) {
				$charge = array_shift($tokens);
			}
			
			$amount = null;
			$tokens = explode(' ', $name);
			if(count($tokens) > 1) {
				$last = array_pop($tokens);
				
				// The amount of drones, in the notation "x5"
				if (preg_match('/\Ax[0-9]+\z/si', $last)) {
					$amount = ltrim($last, 'x');
					$name = implode(' ', $tokens);
				}
			}
			
			$slot = $this->getModuleSlot($name);
			if(!$slot) {
				continue;
			}
			
			$modules[] = array(
				'module' => $name,
				'charge' => $charge,
				'amount' => $amount
			); 
		}
		
		if(empty($modules)) {
			return false;
		}
		
		return array(
			'name' => $label,
			'ship' => $ship,
			'modules' => $modules
		);
	}
	
	public function getModuleSlot($name)
	{
		$module = $this->getModule($name);
		
		if($module) {
			return $module['slotAlias'];
		}
		
		return null;
	}
	
	public function getModuleMeta($name)
	{
		$module = $this->getModule($name);
		
		if($module) {
			return $module['metaAlias'];
		}
		
		return null;
	}
	
	protected static $modules = array();
	
	protected function getModule($name)
	{
		if(isset(self::$modules[$name])) {
			return self::$modules[$name];
		}
		
		$module = $this->plugin->dbFetch(
			"SELECT
				mods.*,
				slots.slotID,
				slots.alias AS slotAlias,
				meta.alias AS metaAlias
			FROM 
				".$this->plugin->getTableName('modules')." AS mods
			JOIN
				".$this->plugin->getTableName('modules_slots')." AS slots
			ON
				mods.slotID = slots.slotID
			JOIN
				".$this->plugin->getTableName('meta')." AS meta
			ON
				mods.metaID = meta.metaID
			WHERE
				mods.label=%s",
			array(
				$name
			)
		);
		
		if(empty($module)) {
			$module = false;
		}
		
		self::$modules[$name] = $module;
		
		return $module;
	}
	
   /**
    * Adds a new fit from an EFT copy+paste string.
    * 
    * @param string $fitString
    * @param string $label
    * @param string $visibility
    * @param boolean $protection
    * @return boolean|EVEShipInfo_EFTManager_Fit
    */
	public function addFromFitString($fitString, $label=null, $visibility='public', $protection=false)
	{
		$this->load();
		
		$id = $this->nextID();
		$data = $this->parseFit($fitString);
		if(!$data) {
			return false;
		}
		
		if(empty($label)) {
			$label = $data['name'];
		}
		
		$fit = EVEShipInfo_EFTManager_Fit::fromArray(
			$this, 
			array(
				'id' => $id,
				'visibility' => $visibility,
				'added' => time(),
				'updated' => time(),
				'name' => $label,
				'ship' => $data['ship'],
				'slots' => $data['modules'],
				$protection
			)
		);
		
		$this->addFit($fit);
		return $fit;
	}
	
   /**
    * Creates a new ID for a fit.
    * @return integer
    */
	protected function nextID()
	{
		$this->load();
		
		$id = $this->plugin->getOption('fittings_idcounter', 0);
		$id++;
		
		// legacy check because the option name changed
		foreach($this->fittings as $fit) {
			$fitID = $fit->getID();
			if($fitID > $id) {
				$id = $fitID + 1;
			}
		}
		
		$this->plugin->setOption('fittings_idcounter', $id);
		return $id;
	}
	
	public function clear($ignoreProtected=true)
	{
		$this->load();
		
		$keep = array();
		
		if($ignoreProtected) {
			foreach($this->fittings as $fit) {
				if($fit->isProtected()) {
					$keep[] = $fit;
				}
			}
		}
		
		$this->fittings = $keep;
	}
}