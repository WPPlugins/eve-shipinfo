<?php

abstract class EVEShipInfo_AjaxMethod
{
	const ERROR_UNKNOWN_PARAMETER = 19001;
	
	protected $plugin;
	
	public function __construct(EVEShipInfo $plugin)
	{
		$this->plugin = $plugin;
	}

	public function execute()
	{
		$this->registerParams();
		$this->_execute();
	}
	
	abstract protected function _execute();
	
	abstract protected function registerParams();
	
	protected function registerParam($name, $default=null, $required=true)
	{
		$this->params[$name] = array(
			'required' => $required,
			'default' => $default,
			'value' => null
		);
		
		if(isset($_REQUEST[$name])) {
			$this->params[$name]['value'] = $_REQUEST[$name];
		}
		
		if($required && !isset($this->params[$name]['value'])) {
			$this->sendError(sprintf('Parameter [%s] missing.', $name));
		}
	}

	protected $params = array();
	
	protected function getParam($name)
	{
		if(!isset($this->params[$name])) {
			throw new EVEShipInfo_Exception(
				'Unknown parameter', 
				sprintf(
					'The ajax method [%s] does not have the parameter [%s]. Registered parameters are: [%s].',
					$this->getID(),
					$name,
					implode(', ', array_keys($this->params))
				), 
				self::ERROR_UNKNOWN_PARAMETER
			);
		}
		
		return $this->params[$name]['value'];
	}
	
	protected function sendError($message, $data=null)
	{
		$this->sendResponse(array(
			'error' => $message,
			'data' => $data
		));
	}
	
	protected function sendResponse($response)
	{
		echo json_encode($response);
		wp_die();
	}
}