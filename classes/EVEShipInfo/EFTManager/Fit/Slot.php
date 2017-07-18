<?php

class EVEShipInfo_EFTManager_Fit_Slot
{
	const ERROR_INVALID_SLOT_TYPE = 1401;
	
	const ERROR_INVALID_META_LEVEL = 1402;
	
	const ERROR_NO_SLOT_DETECTED = 1403;
	
	protected $id;
	
	protected $itemName;
	
	protected $type;
	
	protected $meta;
	
	protected $charge;
	
	protected $amount;
	
	protected static $types;
	
	protected static $metas;
	
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $manager;
	
	public function __construct(EVEShipInfo_EFTManager_Fit $fit, $id, $itemName, $chargeName=null, $amount=null)
	{
		$this->plugin = EVEShipInfo::getInstance();
		$this->manager = $fit->getManager();
		$this->id = $id;
		$this->itemName = $itemName;
		$this->charge = $chargeName;
		$this->type = $this->manager->getModuleSlot($this->itemName);
		$this->meta = $this->manager->getModuleMeta($this->itemName);
		
		if(!isset(self::$types)) {
			$this->initTypes();
			$this->initMetas();
		}
		
		if(empty($this->type)) {
			throw new EVEShipInfo_Exception(
				'No matching slot type found',
				sprintf(
					'No slot type detected for the item [%s]. It seems that it does not exist in the item database.',
					$itemName	
				),
				self::ERROR_NO_SLOT_DETECTED
			);
		}
		
		if(!in_array($this->type, self::$types)) {
			throw new EVEShipInfo_Exception(
				'Invalid slot type',
				sprintf(
					'The slot type [%s] does not exist for item [%s]. Valid slot types are [%s].',
					$this->type,
					$itemName,
					implode(', ', self::$types)
				),
				self::ERROR_INVALID_SLOT_TYPE	
			);
		}
		
		if(!in_array($this->meta, self::$metas)) {
			throw new InvalidArgumentException(
				'Invalid meta level',
				sprintf(
					'The meta level [%s] does not exist. Valid meta types are [%s].',
					$this->meta,
					implode(', ', self::$metas)
				),
				self::ERROR_INVALID_META_LEVEL	
			);
		}

		// the amount is only relevant for drones.
		if($this->isDrone()) {
			$this->amount = $amount;
		}
	}

   /**
    * Loads all slot names from the database (done only once per request).
    */
	protected static function initTypes()
	{
		$plugin = EVEShipInfo::getInstance();
		
		self::$types = $plugin->dbFetchAllKey(
			'alias',
			"SELECT
				`alias`
			FROM
				`".$plugin->getTableName('modules_slots')."`"
		);
	}
	
	protected static function initMetas()
	{
		$plugin = EVEShipInfo::getInstance();
		self::$metas = $plugin->dbFetchAllKey(
			'alias',
			"SELECT
				`alias`
			FROM
				`".$plugin->getTableName('meta')."`"
		);
	}
	
   /**
    * Retrieves a list of all meta type names
    * as an indexed array. For example:
    * <code>array('t1', 't2', 'storyline', ...)</code>
    * 
    * @return string[]
    */
	public static function getMetaTypes()
	{
		if(!isset(self::$metas)) {
			self::initMetas();
		}
		
		return self::$metas;
	}

   /**
    * Retrieves a list of slot type names
    * as an indexed array. For example:
    * <code>array('hiPower', 'lowPower', 'drone', ...)</code>
    * 
    * @return string[]
    */
	public static function getTypes()
	{
		if(!isset(self::$types)) {
			self::initTypes();
		}
		
		return self::$types;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getItemName()
	{
		return $this->itemName;
	}
	
	public function getAmount()
	{
		return $this->amount;
	}
	
	public function hasAmount()
	{
		return isset($this->amount);
	}
	
	public function hasCharge()
	{
		return isset($this->charge);
	}
	
	public function getSlotType()
	{
		return $this->type;
	}
	
	public function isSlotType($type)
	{
		if($this->type === $type) {
			return true;
		}
		
		return false;
	}
	
	public function isMedium() { return $this->isSlotType('medPower'); }
	public function isLow() { return $this->isSlotType('loPower'); }
	public function isHigh() { return $this->isSlotType('hiPower'); }
	public function isRig() { return $this->isSlotType('rigSlot'); }
	public function isSubsystem() { return $this->isSlotType('subSystem'); }
	public function isDrone() { return $this->isSlotType('drone'); }

	public function isModule() 
	{ 
		if($this->isMedium() || $this->isLow() || $this->isHigh()) {
			return true;
		} 
		
		return false; 
	}
	
	public function getMeta()
	{
		return $this->meta;
	}
	
	public function isMeta($meta)
	{
		if($this->meta === $meta) {
			return true;
		}
		
		return false;
	}
	
	public function isTech1() { return $this->isMeta('t1'); }
	public function isTech2() { return $this->isMeta('t2'); }
	public function isTech3() { return $this->isMeta('t3'); }
	public function isStoryline() { return $this->isMeta('storyline'); }
	public function isFaction() { return $this->isMeta('faction'); }
	public function isOfficer() { return $this->isMeta('officer'); }
	public function isDeadspace() { return $this->isMeta('deadspace'); }
	
   /**
    * Serializes the slot to an array for saving.
    * @return array
    */
	public function toArray()
	{
		return array(
			'id' => $this->id,
			'module' => $this->itemName,
			'charge' => $this->charge,
			'amount' => $this->amount
		);
	}
	
   /**
    * Returns the EFT compatible module line string to use in an EFT fit string.
    * @return string
    */
	public function toEFTString()
	{
		$string = $this->itemName;
		if(isset($this->charge)) {
			$string .= ', '.$this->charge;
		}
		
		if(isset($this->amount)) {
			$string .= ' x'.$this->amount;
		}
		
		return $string;
	}
}