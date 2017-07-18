<?php

class EVEShipInfo_Admin_Page_Main_Themes extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Themes', 'eve-shipinfo');
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Form
    */
	protected $form;
	
	protected function _render()
	{
		$this->checkRequest();
		
		$box = $this->ui->createStuffBox(__('Frontend themes', 'eve-shipinfo'))
		->setIcon($this->ui->icon()->theme())
		->setAbstract(
			__('This lets you choose among one of the bundled frontend themes for the ship popups and ship fittings.', 'eve-shipinfo') . ' ' .
			'<b>'.__('Tip:', 'eve-shipinfo').'</b> '.
			__('Click on a theme preview thumbnail to maximize it.', 'eve-shipinfo')
		)
		->setContent($this->renderThemeSelection());
		
		return $box->render();
	}
	
	protected function checkRequest()
	{
		if(isset($_REQUEST['themeID']) && $this->plugin->themeIDExists($_REQUEST['themeID']))
		{
			$themeID = $_REQUEST['themeID'];
			$this->plugin->setThemeID($themeID);
				
			if(isset($_REQUEST['substyle']) && $this->plugin->themeSubstyleExists($themeID, $_REQUEST['substyle'])) {
				$this->plugin->setThemeSubstyle($_REQUEST['substyle']);
			}
				
			$this->addSuccessMessage(sprintf(
				__('The frontend theme was successfully set to %1$s at %2$s.', 'eve-shipinfo'),
				$this->plugin->getThemeLabel(),
				date('H:i:s')
			));
		}
	}
	
	protected $themes;
	
	protected $activeThemeID;
	
	protected $activeSubstyle;
	
	protected function renderThemeSelection()
	{
		$this->themes = $this->plugin->getThemes();
		$this->activeThemeID = $this->plugin->getThemeID();
		$this->activeSubstyle = $this->plugin->getThemeSubstyle();
		
		$html = 
		sprintf(
			'<script>EVEShipInfo_Themes.BaseURL=%s;EVEShipInfo_Themes.PageURL=%s</script>',
			json_encode($this->plugin->getURL().'/themes'),
			json_encode($this->getURL())
		).
		'<div class="themes-list">'.
			$this->renderThemeEntry($this->activeThemeID, true);
		
			$ids = array_keys($this->themes);
			foreach($ids as $themeID) {
				if($themeID != $this->activeThemeID) {
					$html .= $this->renderThemeEntry($themeID);
				}
			}
			$html .=
		'</div>';
		
		return $html;
	}
	
	protected function renderThemeEntry($themeID, $isActive=false)
	{
		$def = $this->themes[$themeID];
		$jsID = $this->plugin->nextJSID();
		$clientName = 'theme'.$jsID;
		
		$label = $def['label'];
		
		$active = '';
		if($isActive) {
			$active = ' active';
			$label .= ' <span class="theme-currentname">'.__('Current Theme', 'eve-shipinfo').'</span>';
		}
		
		$thumbURL = 'preview.jpg';
		if(!empty($def['substyles'])) {
			$activeSubstyle = $def['substyles'][0];
			if($themeID == $this->activeThemeID) {
				foreach($def['substyles'] as $subDef) {
					if($subDef['name'] == $this->activeSubstyle) {
						$activeSubstyle = $subDef;
						break;
					}
				}
			}

			$thumbURL = $activeSubstyle['name'].'/preview.jpg';
		}
		
		$html = sprintf(
			'<script>%s = EVEShipInfo_Themes.Register(%s, %s, %s)</script>',
			$clientName,
			json_encode($jsID),
			json_encode($themeID),
			json_encode($def['label'])
		). 
		'<div class="theme-entry '.$active.'">'.
			'<a class="button button-primary theme-button" href="javascript:void(0);" onclick="'.$clientName.'.Apply(\''.$themeID.'\');">'.__('Apply', 'eve-shipinfo').'</a>'.
			'<h3 class="theme-name">'.$label.'</h3>'.
			'<p class="theme-description">'.$def['description'].'</p>'.
			'<img src="'.$this->plugin->getURL().'/themes/'.$themeID.'/'.$thumbURL.'" class="theme-thumb" id="'.$jsID.'-thumb" onclick="'.$clientName.'.ToggleThumbnail();"/><br/>';
		
			if(!empty($def['substyles'])) {
				foreach($def['substyles'] as $substyle) {
					$activeS = false;
					if($substyle['name']==$activeSubstyle['name']) {
						$activeS = true;
					}
					
					$html .=
					sprintf(
						"<script>%s.RegisterSubstyle(%s, %s, %s)</script>",
						$clientName,
						json_encode($substyle['name']),
						json_encode($substyle['label']),
						json_encode($activeS)
					);
				}
				$html .=
				__('Substyle:', 'eve-shipinfo').' &quot;<span id="'.$jsID.'-substyle-label">'.$activeSubstyle['label'].'</span>&quot;<br/>'.
				'<a href="javascript:void(0);" onclick="'.$clientName.'.Previous();">&laquo; '.__('Previous', 'eve-shipinfo').'</a>'.
				' | <span id="'.$jsID.'-position">1</span>/'.count($def['substyles']).'  | '.
				'<a href="javascript:void(0);" onclick="'.$clientName.'.Next();">'.__('Next', 'eve-shipinfo').' &raquo;</a>';
			} 
			$html .=
			'<script>jQuery(document).ready(function() {'.$clientName.'.Start();})</script>'.
		'</div>'.
		'<div style="clear:both;"></div>';

		return $html;
	}
}