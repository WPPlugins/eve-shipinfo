<?php
/**
 * File containing the {@link EVEShipInfo_Exception} class.
 * 
 * @package EVEShipInfo
 * @see EVEShipInfo_Exception
 */

/**
 * Plugin-specific exception class that is used to store
 * additional information regarding an error.
 *
 * @package EVEShipInfo
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 */
class EVEShipInfo_Exception extends Exception
{
	protected $details = null;
	
	public function __construct($message, $details, $code)
	{
		parent::__construct($message, $code);
		$this->details = $details;
	}
	
	public function getDetails()
	{
		return $this->details;
	}
}