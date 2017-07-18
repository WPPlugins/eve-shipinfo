<?php

class EVEShipInfo_AjaxMethod_CheckForUpdate extends EVEShipInfo_AjaxMethod
{
	protected function registerParams()
	{
	}
	
	protected function _execute()
	{
		$url = $this->plugin->getHomepageURL().'/api/getDataVersion.php';
		 
		$result = wp_remote_get($url);
		 
		$state = 'success';
		if(is_wp_error($result)) {
			$state = 'error';
			$data = $result->get_error_message();
		} else {
			$code = wp_remote_retrieve_response_code($result);
			if($code != 200) {
				$state = 'error';
				$data = wp_remote_retrieve_response_message($result);
			} else {
				$infoRemote = EVEShipInfo::parseVersion(trim($result['body']));
				$infoLocal = EVEShipInfo::parseVersion($this->plugin->getDataVersion());
				$update = false;
				if($infoRemote['date'] > $infoLocal['date']) {
					$update = true;
				}
		
				$data = array(
					'remoteVersion' => $infoRemote['version'],
					'updateAvailable' => $update,
				);
			}
		}
		
		$response = array(
			'url' => $url,
			'state' => $state,
			'data' => $data
		);
		 
		$this->sendResponse($response);
	}
}