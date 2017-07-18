<?php

class EVEShipInfo_VirtualPage_ShipDetail extends EVEShipInfo_VirtualPage
{
   /**
    * @var EVEShipInfo_Collection_Ship
    */
	protected $ship;
	
	public function __construct($plugin)
	{
		parent::__construct($plugin);
		
		$this->ship = $this->plugin->getActiveShip();
	}
	
	public function renderTitle()
	{
		return $this->ship->getName();
	}
	
	public function renderContent()
	{
		$html =
		'<div class="ship-tagline">'.
			$this->ship->getRaceName().' '.$this->ship->getGroupName().
		'</div>';
		
		if($this->ship->hasScreenshot('Front')) {
		    $html .= sprintf(
			    '<p>'.
				    '<img src="%s" alt="%s"/>'.
			    '</p>',
		    	$this->ship->getScreenshotURL('Front'),
		    	sprintf(__('%1$s frontal view', 'eve-shipinfo'), $this->ship->getName())
			);
		}
		
		$html .=
		'<p class="ship-description">'.
    		nl2br(strip_tags($this->ship->getDescription()), true).
    	'</p>';
		
		if($this->ship->hasScreenshot('Side')) {
		    $html .= sprintf(
			    '<p>'.
				    '<img src="%s" alt="%s"/>'.
			    '</p>',
		    	$this->ship->getScreenshotURL('Side'),
		    	sprintf(__('%1$s side view', 'eve-shipinfo'), $this->ship->getName())
			);
		}
		
		$launchers = __('No launchers', 'eve-shipinfo');
		$launcherAmount = $this->ship->getLauncherHardpoints();
		if($launcherAmount == 1) {
			$launchers = __('1 launcher', 'eve-shipinfo');
		} else if($launcherAmount > 1) {
			$launchers = sprintf(__('%s launchers', 'eve-shipinfo'), $launcherAmount);
		}
		
		$turrets = __('No turrets', 'eve-shipinfo');
		$turretAmount = $this->ship->getTurretHardpoints();
		if($turretAmount == 1) {
			$turrets = __('1 turret', 'eve-shipinfo');
		} else if($turretAmount > 1) {
			$turrets = sprintf(__('%s turrets', 'eve-shipinfo'), $turretAmount);
		}
		
		$drones = __('None', 'eve-shipinfo');
		if($this->ship->getDronebaySize() > 0) {
			$drones = 
    		$this->ship->getDronebaySize(true).' / '.
    		$this->ship->getDroneBandwidth(true);
		}
		
		$cargo = __('None', 'eve-shipinfo');
		if($this->ship->getCargobaySize() > 0) {
			$cargo = $this->ship->getCargobaySize(true);
		}
		
		$slots = __('None', 'eve-shipinfo');
		if($this->ship->getHighSlots() > 0) {
			$slots = 
			$this->ship->getHighSlots().' / '.
		    $this->ship->getMedSlots().' / '.
		    $this->ship->getLowSlots();
		}
		
		$html .=
    	'<p class="ship-slots">'.
    		__('Slots', 'eve-shipinfo').': '.
    		$slots.' - '.
    		$launchers.', '.
    		$turrets.
    	'</p>'.
    	'<p>'.
    		__('Cargo bay', 'eve-shipinfo').': '.
    		$cargo.
    	'</p>'.
    	'<p>'.
    		__('Drones', 'eve-shipinfo').': '.$drones.
    	'</p>'.
    	'<p>'.
    		__('Warp speed', 'eve-shipinfo').': '.
    		$this->ship->getWarpSpeed(true).'<br/>'.
    		__('Max velocity', 'eve-shipinfo').': '.
    		$this->ship->getMaxVelocity(true).'<br/>'.
    		__('Agility', 'eve-shipinfo').': '.
    		$this->ship->getAgility(true).
    	'</p>'.
    	'<p>'.
    		__('Capacitor', 'eve-shipinfo').': '.
    		sprintf(__('%s power output', 'eve-shipinfo'), $this->ship->getPowerOutput(true)).' / '.
    		sprintf(__('%s capacity', 'eve-shipinfo'), $this->ship->getCapacitorCapacity(true)).' / '.
    		sprintf(__('%s recharge rate', 'eve-shipinfo'), $this->ship->getCapacitorRechargeRate(true)).
    	'</p>'.
    	'<p>'.
    		__('Shield', 'eve-shipinfo').': '.$this->ship->getShieldHitpoints(true).' / '.
    		sprintf(__('%s recharge rate', 'eve-shipinfo'), $this->ship->getShieldRechargeRate(true)).'<br/>'.
    		__('Armor', 'eve-shipinfo').': '.$this->ship->getArmorHitpoints(true).'<br/>'.
    		__('Structure', 'eve-shipinfo').': '.$this->ship->getStructureHitpoints(true).' / '.
    		sprintf(__('%s  signature radius', 'eve-shipinfo'), $this->ship->getSignatureRadius(true)).
    	'</p>'.
		'<p>'.	
			__('Max target range', 'eve-shipinfo').': '.$this->ship->getMaxTargetingRange(true).' / '.
			__('Max locked targets', 'eve-shipinfo').': '.$this->ship->getMaxLockedTargets().'<br/>'.
			__('Scan speed', 'eve-shipinfo').': '.$this->ship->getScanSpeed(true).' / '.
			__('Scan resolution', 'eve-shipinfo').': '.$this->ship->getScanResolution(true).
		'</p>';
		
		return $html;
	}
	
	public function getGUID()
	{
		return $this->ship->getViewURL();	
	}
	
	public function getPostName()
	{
		return 'eve/ship/'.$this->ship->getName();
	}
	
}