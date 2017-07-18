<?php

EVEShipInfo::getInstance()->loadClass('EVEShipInfo_EFTManager_Fit_Slot');
		
class EVEShipInfo_EFTManager_Fit
{
	const ERROR_INVALID_VISIBILITY = 1101;
	
	const ERROR_UNKNOWN_SHIP_NAME = 1102;
	
	const VISIBILITY_PUBLIC = 'public';
	
	const VISIBILITY_PRIVATE = 'private';
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $manager;
	
   /**
    * The name of the fit as specified by the user in EFT.
    * @var string
    */
	protected $name;
	
   /**
    * The name of the ship, e.g. <code>Abaddon</code>.
    * @var string
    */
	protected $shipName;
	
   /**
    * The fitting ID.
    * @var integer
    */
	protected $id;
	
   /**
    * @var string "public" or "private"
    * @see EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE
    * @see EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC
    */
	protected $visibility;
	
   /**
    * Timestamp of the creation date.
    * @var float
    */
	protected $added;
	
   /**
    * Timestamp of the last modification date.
    * @var float
    */
	protected $updated;
	
	protected $protection;
	
	protected $slotData;
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected static $collection;
	
	protected function __construct(EVEShipInfo_EFTManager $manager, $id, $visibility, $added, $updated, $name, $shipName, $slots, $protection=false)
	{
		$this->manager = $manager;
		$this->id = $id;
		$this->visibility = $visibility;
		$this->added = $added;
		$this->updated = $updated;
		$this->name = $name;
		$this->shipName = $shipName;
		$this->protection = $protection;
		
		if(!isset(self::$collection)) {
			self::$collection = EVEShipInfo::getInstance()->createCollection();
		}
		
		if(!self::$collection->shipNameExists($shipName)) {
			throw new EVEShipInfo_Exception(
				'Unknown ship name', 
				sprintf(
					'Tried adding a fit with the ship name [%s], but it does not exist in the collection.',
					$shipName	
				), 
				self::ERROR_UNKNOWN_SHIP_NAME
			);
		}
		
		if(false) {
			echo '<pre>'.print_r(array(
				'name' => $name,
				'shipName' => $shipName,
				'id'=> $id,
				'visibility' => $visibility,
				'added' => date('d.m.Y H:i:s', $added),
				'updated' => date('d.m.Y H:i:s', $updated),
				'protection' => $protection
			), true).'</pre>';
		}
		
		$this->slotData = $slots;
	}
	
   /**
    * Converts existing slots array to the new structure.
    * @param array $slots
    * @return array
    */
	protected function convertLegacySlots($slots)
	{
		$entries = array();
		foreach($slots as $items) {
			foreach($items as $item) {
				$amount = null;
				$tokens = explode(' ', $item);
				if(count($tokens) > 2) {
					$end = array_pop($tokens);
					$multi = array_pop($tokens);
					if($multi == 'x') {
						$amount = $end;
						$item = implode(' ', $tokens);
					}
				}
				
				$entries[] = array(
					'module' => $item,
					'charge' => null,
					'amount' => $amount
				);
			}
		}
		
		return $entries;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getVisibility()
	{
		return $this->visibility;
	}
	
   /**
    * Retrieves a pretty, human readable formatted creation date.
    * @return string
    */
	public function getDateAddedPretty()
	{
		return $this->date2pretty($this->getDateAdded());
	}
	
	/**
	 * Retrieves a pretty, human readable formatted creation date.
	 * @return string
	 */
	public function getDateUpdatedPretty()
	{
		return $this->date2pretty($this->getDateUpdated());
	}
	
	protected function date2pretty(DateTime $date)
	{
		if($date->format('d.m.Y')==date('d.m.Y')) {
			return __('Today', 'eve-shipinfo').' '.$date->format('H:i');
		}
		
		$format = 'd F Y H:i';
		if($date->format('Y')==date('Y')) {
			$format = 'd F H:i';
		}
		
		return $date->format($format);
	}
	
   /**
    * Whether this ship is public and will be shown in the
    * ship pages.
    * 
    * @return boolean
    */
	public function isPublic()
	{
		if($this->visibility==self::VISIBILITY_PUBLIC) {
			return true;
		}
		
		return false;
	}
	
   /**
    * Whether this fit is private and will not be listed 
    * publicly in the ship pages.
    * 
    * @return boolean
    */
	public function isPrivate()
	{
		if($this->visibility==self::VISIBILITY_PRIVATE) {
			return true;
		}
		
		return false;
	}
	
   /**
    * The date and time the fit was added to the collection.
    * @return DateTime
    */
	public function getDateAdded()
	{
		$date = new DateTime();
		$date->setTimestamp($this->added);
		return $date;
	}

	/**
	 * The date and time the fit was last modified.
	 * @return DateTime
	 */
	public function getDateUpdated()
	{
		$date = new DateTime();
		$date->setTimestamp($this->updated);
		return $date;
	}
	
   /**
    * The name of the fit.
    * @return string
    */
	public function getName()
	{
		return $this->name;	
	}
	
   /**
    * The name of the ship this fit is for.
    * @return string
    * @see getShip()
    */
	public function getShipName()
	{
		return $this->shipName;
	}
	
   /**
    * @var EVEShipInfo_Collection_Ship
    */
	protected $ship;
	
   /**
    * Retrieves the matching ship object instance for this fit,
    * if the ship can be found in the database. Note: always check
    * the return value of this method to avoid errors.
    * 
    * @return NULL|EVEShipInfo_Collection_Ship
    */
	public function getShip()
	{
		if(isset($this->ship)) {
			return $this->ship;
		}
		
		$this->ship = self::$collection->getShipByName($this->shipName);
		return $this->ship;
	}
	
   /**
    * Retrieves all high slots.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getHighSlots()
	{
		return $this->getSlotsByType('hiPower');
	}
	
   /**
    * Retrieves all low slots.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getLowSlots()
	{
		return $this->getSlotsByType('loPower');
	}
	
   /**
    * Retrieves all medium slots.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getMedSlots()
	{
		return $this->getSlotsByType('medPower');
	}
	
   /**
    * Retrieves all rig slots.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getRigs()
	{
		return $this->getSlotsByType('rigSlot');
	}
	
   /**
    * Retrieves all drone slots.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getDrones()
	{
		return $this->getSlotsByType('drone');
	}
	
   /**
    * Retrieves all subsystem slots.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getSubystems()
	{
		return $this->getSlotsByType('subSystem');
	}
	
   /**
    * Retrieves all available slots in the fitting.
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getSlots()
	{
		if(!isset($this->slots)) {
			$this->initSlots();
		}
		
		return $this->slots;
	}
	
   /**
    * Retrieves all available slots in the fitting by fit type.
    * 
    * Possible types are:
    * 
    * <ul>
    * <li>medPower</li>
    * <li>hiPower</li>
    * <li>loPower</li>
    * <li>rigSlot</li>
    * <li>subSystem</li>
    * <li>drone</li>
    * </ul>
    * 
    * @param string $type
    * @return EVEShipInfo_EFTManager_Fit_Slot[]
    */
	public function getSlotsByType($type)
	{
		if(!isset($this->slots)) {
			$this->initSlots();
		}
		
		$slots = array();
		foreach($this->slots as $slot) {
			if($slot->isSlotType($type)) {
				$slots[] = $slot;
			}
		}
		
		return $slots;
	}
	
	protected static $registeredFits = array();
	
   /**
    * Registers this fit clientside by adding the required 
    * javascript code to add the fit's data to the page and
    * enable showing it.
    * 
    * @param string $linkID The ID of the anchor element tied to this fit
    * @return string
    */
	public function renderClientRegistration($linkID)
	{
		$fitID = $this->getID();
		if(isset(self::$registeredFits[$fitID])) {
			return '';
		}
	
		self::$registeredFits[$fitID] = true;
	
		$ship = $this->getShip();
		$clientName = sprintf('fobj%s', $linkID);
	
		$code = 
		$ship->renderClientRegistration().
		$this->renderSlotsRegistration().
		'<script>'.
			sprintf(
				"var %s = EVEShipInfo.AddFit(%s, %s, %s, %s, %s);",
				$clientName,
				json_encode($linkID),
				json_encode($this->getID()),
				json_encode($this->getName()),
				json_encode($ship->getName()),
				json_encode($ship->getID())
			);
			$slots = $this->getSlots();
			foreach($slots as $slot) {
				$code .= sprintf(
					"%s.AddSlot(%s, %s, %s, %s, %s);",
					$clientName,
					json_encode($slot->getID()),
					json_encode($slot->getItemName()),
					json_encode($slot->getAmount()),
					json_encode($slot->getSlotType()),
					json_encode($slot->getMeta())
				);
			}
			$code .=
		'</script>';
			
		return $code;
	}
	
	protected static $slotsRegistered = false;
	
	protected function renderSlotsRegistration()
	{
		if(self::$slotsRegistered) {
			return;
		}
		
		self::$slotsRegistered = true;
		
		$slotTypes = EVEShipInfo_EFTManager_Fit_Slot::getTypes();
		$metaTypes = EVEShipInfo_EFTManager_Fit_Slot::getMetaTypes();

		$code = 
		'<script>'.
			'EVEShipInfo_SlotTypes = '.json_encode($slotTypes).';'.
			'EVEShipInfo_MetaTypes = '.json_encode($metaTypes).';'.
		'</script>';

		return $code;
	}

   /**
    * Serializes the fit to an array for saving.
    * @return array
    */
	public function toArray()
	{
		$result = array(
			'id' => $this->id,
			'visibility' => $this->visibility,
			'added' => $this->added,
			'updated' => $this->updated,
			'name' => $this->name,
			'ship' => $this->shipName,
			'protection' => $this->protection,
			'slots' => array()
		);
		
		if(!isset($this->slots)) {
			$this->initSlots();
		}
		
		foreach($this->slots as $slot) {
			$result['slots'][] = $slot->toArray();
		}
		
		return $result;
	}
	
   /**
    * Creates a fit using a previously stored fit data array.
    * 
    * @param EVEShipInfo_EFTManager $manager
    * @param array $data
    * @return EVEShipInfo_EFTManager_Fit
    */
	public static function fromArray(EVEShipInfo_EFTManager $manager, $data)
	{
		// legacy hardware
		if(isset($data['hardware'])) {
			$data['slots'] = $data['hardware'];
		}
		
		// legacy settings
		if(!isset($data['updated'])) { $data['updated'] = $data['added']; }
		if(!isset($data['protection'])) { $data['protection'] = false; }
		
		return new EVEShipInfo_EFTManager_Fit(
			$manager,
			$data['id'],
			$data['visibility'],
			$data['added'],
			$data['updated'],
			$data['name'],
			$data['ship'],
			$data['slots'],
			$data['protection']
		);
	}
	
	public function makePrivate()
	{
		return $this->setVisibility(self::VISIBILITY_PRIVATE);
	}
	
	public function makePublic()
	{
		return $this->setVisibility(self::VISIBILITY_PUBLIC);
	}
	
   /**
    * Sets the visibility setting.
    * 
    * @param string $visibility
    * @throws EVEShipInfo_Exception
    * @return boolean
    * @see makePrivate()
    * @see makePublic()
    */
	public function setVisibility($visibility)
	{
		if($visibility != self::VISIBILITY_PRIVATE && $visibility != self::VISIBILITY_PUBLIC) {
			throw new EVEShipInfo_Exception(
				'Invalid value for the visibility key.', 
				sprintf(
					'Tried setting visibility to [%s], valid values are [%s].',
					$visibility,
					implode(', ', array(self::VISIBILITY_PRIVATE, self::VISIBILITY_PUBLIC))
				),
				self::ERROR_INVALID_VISIBILITY
			);
		}
		
		if($this->visibility == $visibility) {
			return false;
		}
		
		$this->visibility = $visibility;
		$this->modified('visibility');
		
		return true;
	}
	
	protected $modified = false;
	
	protected function modified($part)
	{
		$this->updated = time();
		$this->modified = true;
	}
	
	public function isModified()
	{
		return $this->modified;
	}
	
	public function resetModified()
	{
		$this->modified = false;
	}
	
	public function setName($name)
	{
		if($this->name == $name) {
			return false;
		}
		
		$this->name = $name;
		$this->modified('name');
		
		return true;
	}
	
	public function setProtection($protection=true)
	{
		if($protection==='yes') { $protection = true; }
		if($protection==='no') { $protection = false; }
		
		if($protection === $this->protection) {
			return false;
		}
		
		$this->protection = $protection;
		$this->modified('protection');
		
		return true;
	}
	
	protected $invalidSlots;
	
   /**
    * Creates and adds a new slot with the specified module. If the slot
    * is invalid (unknown module for ex.), returns null.
    * 
    * @param string $moduleName
    * @param string $charge
    * @param string $amount
    * @param string $slid
    * @return EVEShipInfo_EFTManager_Fit_Slot|NULL
    */
	public function addSlot($moduleName, $charge=null, $amount=null, $slid=null)
	{
		if(!isset($this->slots)) {
			$this->initSlots();
		}
		
		if(empty($slid)) {
			$slid = $this->nextSlotID();
		}
		
		// try to add the slot for the module. This can fail for example
		// if a module has been renamed in an expansion, so we simply 
		// ignore invalid slots.
		try
		{
			$slot = new EVEShipInfo_EFTManager_Fit_Slot(
				$this, 
				$slid, 
				$moduleName, 
				$charge, 
				$amount
			);
		} 
		catch(EVEShipInfo_Exception $e) 
		{
			if($e->getCode()==EVEShipInfo_EFTManager_Fit_Slot::ERROR_NO_SLOT_DETECTED) {
				$this->invalidSlots[$moduleName] = array(
					'moduleName' => $moduleName,
					'charge' => $charge,
					'amount' => $amount,
					'slid' => $slid
				);
				return null;
			}
			
			throw $e;
		}
		
		$this->slots[] = $slot;
		
		return $slot;
	}
	
   /**
    * Checks whether the fit has some invalid slots.
    * @return boolean
    */
	public function hasInvalidSlots()
	{
		$this->initSlots();
		return !empty($this->invalidSlots);
	}
	
   /**
    * Retrieves a list with information on all invalid slots.
    * Each entry in the array has the following keys:
    * 
    * moduleName
    * charge
    * amount
    * slid
    * 
    * @return multitype:<string, string>
    */
	public function getInvalidSlots()
	{
		$this->initSlots();
		return array_values($this->invalidSlots);
	}
	
   /**
    * Determines the next slot ID to use.
    * @return integer
    */
	protected function nextSlotID()
	{
		$nextID = 0;
		foreach($this->slots as $slot) {
			$slid = $slot->getID();
			if($slid > $nextID) {
				$nextID = $slid;
			}
		}
		
		$nextID++;
		return $nextID;
	}
	
   /**
    * @var EVEShipInfo_EFTManager_Fit_Slot[]
    */
	protected $slots;
	
   /**
    * @return EVEShipInfo_EFTManager
    */
	public function getManager()
	{
		return $this->manager;
	}
	
   /**
    * Initializes the slots by creating all the required
    * slot objects. This is done on demand only for 
    * performance reasons.
    */
	protected function initSlots()
	{
		if(isset($this->slots)) {
			return;
		}
		
		$this->slots = array();
		$this->invalidSlots = array();
		
		if(isset($this->slotData['low'])) {
			$this->slotData = $this->convertLegacySlots($this->slotData);
		}
		
		foreach($this->slotData as $slot) {
			if(!isset($slot['id'])) {
				$slot['id'] = null;
			}
			
			$this->addSlot($slot['module'], $slot['charge'], $slot['amount'], $slot['id']);
		}
		
		unset($this->slotData);
	}
	
   /**
    * Creates an EFT compatible string with all fit details.
    * 
    * @param string $name Allows overriding the name of the fit.
    * @return string
    */
	public function toEFTString($name=null)
	{
		if(empty($name)) {
			$name = $this->getName();
		}
		
		$ship = $this->getShip();
		if(!$ship) {
			throw new EVEShipInfo_Exception('Arfgh', $this->shipName, 111111);
		}
		
		$string = 
		'['.$ship->getName().', '.$name.']'.PHP_EOL;
		
		$order = array(
			'loPower',
			'medPower',
			'hiPower',
			'rigSlot',
			'subSystem',
			'drone'
		);
		
		foreach($order as $slotType) {
			$slots = $this->getSlotsByType($slotType);
			if(empty($slots)) {
				continue;
			}
			uasort($slots, array($this, 'callback_sortSlotsByName'));
			foreach($slots as $slot) {
				$string .= $slot->toEFTString().PHP_EOL;
			}
			
			$string .= PHP_EOL;
		}
		
		return $string;
	}
	
	public function callback_sortSlotsByName(EVEShipInfo_EFTManager_Fit_Slot $a, EVEShipInfo_EFTManager_Fit_Slot $b)
	{
		return strnatcasecmp($a->getItemName(), $b->getItemName());
	}
	
   /**
    * Retrieves a unique hash of the fit that can be used to 
    * uniquely identify the setup.
    * 
    * @return string
    */
	public function getHash()
	{
		// we use the eft string with a dummy name,
		// since this is consistent accross all fits.
		// The modules are sorted, so that it is a 
		// great way to compare fits.
		return md5($this->toEFTString('dummy'));
	}
	
	public function updateFromFitString($fitString)
	{
		$oldHash = $this->getHash();
		
		$data = $this->manager->parseFit($fitString);
		if(!$data) {
			return false;
		}

		$this->name = $data['name'];
		$this->shipName = $data['ship'];
		$this->slotData = $data['modules'];
		
		unset($this->slots);
		unset($this->ship);
		
		$newHash = $this->getHash();
		
		if($oldHash != $newHash) {
			$this->modified('fitting');
			return true;
		}
		
		return false;
	}
	
   /**
    * Whether this fit is protected from the imports.
    * @return boolean
    */
	public function isProtected()
	{
		return $this->protection;
	}
	
	public function getAdminEditURL()
	{
		$params = array(
			'page' => 'eveshipinfo_eftfittings',
			'action' => 'edit',
			'fid' => $this->getID()
		);
		
		return admin_url('admin.php?'.http_build_query($params));
	}
	
	public function getShortcode($label=null)
	{
		if($label) {
			return '[shipinfo_fit id="'.$this->id.'"]'.$label.'[/shipinfo_fit]';	
		}	

		return '[shipinfo_fit id="'.$this->id.'"]';
	}
	
	public function isProtectedPretty()
	{
		$ui = EVEShipInfo::getInstance()->getAdminUI();
		if($this->isProtected()) {
			return $ui->icon()->protect()->makeSuccess();
		}
		
		return $ui->icon()->unprotect()->makeMuted();
	}
}