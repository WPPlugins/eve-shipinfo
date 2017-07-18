<?php

class EVEShipInfo_AjaxMethod_FittingSetVisibility extends EVEShipInfo_AjaxMethod
{
	protected function registerParams()
	{
		$this->registerParam('fitID');
		$this->registerParam('changeTo');
	}
	
	protected function _execute()
	{
		$eft = $this->plugin->createEFTManager();
		$fitID = $this->getParam('fitID');
		$changeTo = $this->getParam('changeTo');
		
		if(!is_numeric($fitID)) {
			$this->sendError('Not a valid fit ID.');
		}
		
		if(!in_array($changeTo, array(EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE, EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC))) {
			$this->sendError('Not a valid visibility.');
		}
		
		$fitting = $eft->getFittingByID($fitID);
		
		$fitting->setVisibility($changeTo);
		$eft->save();
		
		$response = array(
			'fitID' => $fitID,
			'visibility' => $fitting->getVisibility()
		);
		 
		$this->sendResponse($response);
	}
}