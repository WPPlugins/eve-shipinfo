<?php

class EVEShipInfo_Admin_Page_Main_About extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return sprintf(
			__('About %1$s', 'eve-shipinfo'),
			EVEShipInfo::APPNAME
		);
	}
	
	protected function _render()
	{
		if(isset($_REQUEST['data-package']) && $_REQUEST['data-package'] == 'yes') {
			$this->sendDataPackage();
		}
		
		$html = 
		'<dd>'.
			'<dl>'.
				__('Official homepage:', 'eve-shipinfo').' '.
				'<a href="'.EVEShipInfo::APPURI.'" target="_blank">'.EVEShipInfo::APPURI.'</a>'.
			'</dl>'.
			'<dl>'.
				__('Author:', 'eve-shipinfo').' '.
				'Sebastian Mordziol aka AeonOfTime | <a href="mailto:eve@aeonoftime.com">eve@aeonoftime.com</a> | <a href="http://eve.aeonoftime.com">eve.aeonoftime.com</a>'.
			'</dl>'.
			'<dl>'.
				__('Plugin version:', 'eve-shipinfo').' '.
				$this->plugin->getVersion().
			'</dl>'.
			'<dl>'.
				__('Database version:', 'eve-shipinfo').' '.
				$this->plugin->getDataVersion().
			'</dl>'.
		'</dd>';
				
		$content = '';
		$content .= $this->ui->createStuffBox(__('Plugin details', 'eve-shipinfo'))
		->setContent($html);
		
		$html = 
		__('This allows to download a data package containing all your current plugin settings.', 'eve-shipinfo').' '.
		__('The plugin author may ask you to send him this file for debugging purposes.', 'eve-shipinfo').' '.
		__('It does not contain any information that could be used to identify either specific users or even the location of the blog itself.', 'eve-shipinfo').
		'<p>'.
			$this->ui->button(__('Download data package', 'eve-shipinfo'))
			->link($this->getURL(array('data-package' => 'yes'))).
		'</p>';
		
		$content .= $this->ui->createStuffBox(__('Debug data package', 'eve-shipinfo'))
		->setContent($html)
		->setCollapsed();
		
		return $content;
	}
	
	public function handleActions()
	{
		if(!isset($_REQUEST['data-package']) || $_REQUEST['data-package'] != 'yes') {
			return;
		}
		
		$package = array(
			'settings' => array(
				'isJSMinified' => $this->plugin->isJSMinified(),
				'isVirtualPagesEnabled' => $this->plugin->isVirtualPagesEnabled(),
				'isDatabaseUpToDate' => $this->plugin->isDatabaseUpToDate(),
				'isURLRewritingEnabled' => $this->plugin->isBlogURLRewritingEnabled(),
				'pluginVersion' => $this->plugin->getVersion(),
				'databaseVersion' => $this->plugin->getDataVersion()
			),
			'eft' => array()
		);
		
		$eft = $this->plugin->createEFTManager();
		$package['eft'] = $eft->getEFTData();
		
		$json = json_encode($package, JSON_PRETTY_PRINT);
		
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=eveshipinfo-settings.json");
        header("Content-Type: application/json");
        
        echo $json;
	} 
}