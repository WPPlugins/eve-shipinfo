<?php

class EVEShipInfo_Admin_Page_Main_EFTImport extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Fittings import', 'eve-shipinfo');
	}
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $eft;
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
	protected function _render()
	{
		if(!$this->plugin->isDatabaseUpToDate()) {
			return $this->renderUpdateDatabaseBox();
		}
		
		/* @var $fit EVEShipInfo_EFTManager_Fit */
		
		$this->eft = $this->plugin->createEFTManager();
		$this->collection = $this->plugin->createCollection();
		
		$this->createImportForm();
		
	    if($this->form->isSubmitted() && $this->form->validate()) {
			$this->processUpload();
		}
		
		if(isset($_REQUEST['confirmDelete']) && $_REQUEST['confirmDelete']) {
			$this->processDelete();
		}
		
		$html = 
		$this->renderForm().
		$this->renderMaintenance();
		
		return $html;
	}
	
	protected function renderMaintenance()
	{
	    if(!$this->eft->hasFittings()) {
	    	return '';
	    }
	    
	    $confirmText =
	    __('All existing fits will be deleted permanently.', 'eve-shipinfo').' '.
	    __('This cannot be undone, are you sure?', 'eve-shipinfo');
	    
	    $box = $this->ui->createStuffBox(__('Fittings maintenance', 'eve-shipinfo'));
	    $box->setCollapsed();
	    $box->setContent(
	    	'<script>'.
	    		'function ConfirmDeleteFits()'.
    			'{'.
    				"if(confirm('".$confirmText."')) {".
    					"document.location = '?page=eveshipinfo_eftimport&confirmDelete=yes'".
    				'}'.
    			'}'.
    		'</script>'.
	    	'<a href="javascript:void(0)" onclick="ConfirmDeleteFits()" class="button" title="'.__('Displays a confirmation dialog.', 'eve-shipinfo').'">'.
	   		 	__('Delete all fits...', 'eve-shipinfo').
	    	'</a>'
	    );
	    
	    return $box->render();
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Form
    */
	protected $form;
	
	protected function createImportForm()
	{
		$form = $this->createForm(
			'importFitting',
			array(
				'mode' => 'merge',
				'visibility' => EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC,
				'ignore_protected' => 'yes'
			)
		);
		
		$form->setSubmitLabel(__('Upload and import', 'eve-shipinfo'));
		$form->setSubmitIcon($this->ui->icon()->upload());
		
		$form->addUpload('file', __('EVE Export XML file', 'eve-shipinfo'))
		->setRequired()
		->setAccept('text/xml')
		->setDescription(
			'<b>'.__('Howto:', 'eve-shipinfo').'</b> '.
			sprintf(__('Open the fitting window in EVE, and open the %1$s menu on the bottom right.', 'eve-shipinfo'), '<code>Import &amp; Export</code>').' '.
			sprintf(__('Select %1$s.', 'eve-shipinfo'), '<code>Export fittings to file</code>').' '.
			__('In the popup that appears, you can choose a filename and the fittings you wish to include.', 'eve-shipinfo').' '.
			__('After confirming, EVE will show you a message with the path to the exported file: upload this file here.', 'eve-shipinfo').' '.
			__('Tip: copy and paste the link into the file browse dialog.', 'eve-shipinfo')
		);
		
		$form->addRadioGroup('mode', __('Import mode', 'eve-shipinfo'))
		->addItem('fresh', '<b>'.__('Clean:', 'eve-shipinfo').'</b> '.__('Delete all existing fits before the import', 'eve-shipinfo'))
		->addItem('merge', '<b>'.__('Merge:', 'eve-shipinfo').'</b> '.__('Add new fits, replace existing ones and keep all others', 'eve-shipinfo'))
		->addItem('new', '<b>'.__('New only:', 'eve-shipinfo').'</b> '.__('Only add new fits, leave existing untouched', 'eve-shipinfo'))
		->setDescription(
			__('Specifies what to do with already existing fits and those you are importing.', 'eve-shipinfo').' '.
			'<b>'.__('Note:', 'eve-shipinfo').'</b> '.
			__('The fitting names are used to match existing fittings.', 'eve-shipinfo').' '.
			__('If you changed some names in EVE, it is best to use the merge option.', 'eve-shipinfo')
		);
		
		$form->addSelect('visibility', __('Visibility'))
		->addOption(__('Public', 'eve-shipinfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC)
		->addOption(__('Private', 'eve-shipinfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE)
		->setDescription(__('The default visibility to use for all imported fits.', 'eve-shipinfo'));
		
		$form->addCheckbox('ignore_protected', __('Protection', 'eve-shipinfo'))
		->setInlineLabel(__('Ignore protected fittings', 'eve-shipinfo'))
		->setDescription(
			__('If checked, any fittings that are set as protected will be left entirely untouched by the import.', 'eve-shipinfo').' '.
			__('If a fitting to import has the same name as a protected one, it will not be imported.', 'eve-shipinfo').' '.
			__('If unchecked, protected fittings will be deleted and updated like any other.', 'eve-shipinfo')
		);
		
		$this->form = $form;
	}
	
	protected function renderForm()
	{
		return $this->ui->createStuffBox(
			'<span class="dashicons dashicons-upload"></span> '.
			__('Upload EVE export', 'eve-shipinfo')
		)
		->setAbstract(
			__('To easily share EVE fits with your readers in your posts, you can upload an EVE export here.', 'eve-shipinfo').' '.
			__('Once you have uploaded your fits, you can use the dedicated shortcodes to display them.', 'eve-shipinfo').' '.
			'<b>'.__('Note:', 'eve-shipinfo').'</b> '.
			__('The EVE export format is somewhat limited.', 'eve-shipinfo').' '.
			__('It does not include any implants or turret/launcher charges you may have used.', 'eve-shipinfo').' '.
			__('Alternatively, you can add single fittings manually or edit them after the import.')
		)
		->setContent($this->form->render())
		->render();
	}
	
	protected function processDelete()
	{
		$this->plugin->clearOption('fittings');

		$this->eft->reload();
		
		$this->addSuccessMessage(
			sprintf(
			    __('All fittings have been deleted successfully at %1$s','eve-shipinfo'), 
			    date('H:i:s')
		    )    
	    );
	}
	
	protected $nameHashes;
	
   /**
    * Processes the upload of an EVE fittings XML file: 
    * parses the XML, and stores the new data according
    * to the selected import mode.
    */
	protected function processUpload()
	{
		$values = $this->form->getValues();

		$xml = $this->form->getElementByName('file')->getContent();
		
		$root = @simplexml_load_string($xml);
		if(!$root) {
			$this->addErrorMessage(__('The uploaded XML file could not be read, it is possibly malformed or not an XML file.', 'eve-shipinfo'));
			return;
		}
		
		// to read the xml, we use the json encode + decode trick,
		// which magically creates an array with everything in it.
		$encoded = json_encode($root);
		$data = json_decode($encoded, true);
		if(!isset($data['fitting'])) {
			$this->addErrorMessage(__('The fitting data could not be found in the XML file.', 'eve-shipinfo'));
			return false;
		}
		
		$fits = array();
		foreach($data['fitting'] as $fit) {
			$def = $this->parseFit($fit);
			if($def===false) {
				continue;
			}
			
			$fits[] = $def;
		}
		
		if(empty($fits)) {
			$this->addErrorMessage(__('No fittings found in the XML file.', 'eve-shipinfo'));
			return false;
		}
		
		$ignore = false;
		if($values['ignore_protected']=='yes') {
			$ignore = true;
		}
		
		$this->processFits($fits, $values['visibility'], $ignore, $values['mode']);
		
		$this->eft->save();
	}
	
	protected function processFits($fits, $visibility, $ignoreProtected, $mode)
	{
		if($mode=='fresh') {
			$this->eft->clear($ignoreProtected);
		}
		
		$existing = $this->eft->countFittings();
		$new = 0;
		$updated = 0;
		$errors = 0;
		$protected = 0;
		
		foreach($fits as $def) {
			// check if a fit with the same name already exists
			$fit = $this->eft->getFittingByName($def['name'], $def['ship']);
			
			if(!$fit) {
				$fit = $this->eft->addFromFitString($def['fitString'], null, $visibility);
				if(!$fit) {
					$errors++;
					continue;
				}
				$new++;
			}
			// in new mode there are no updates, only new fits,
			// and in fresh mode only protected fits are there, 
			// and they should not be updated (in fresh mode with
			// the igore mode off, they will all have been deleted) 
			else if($mode != 'new') 
			{
				// if we are not ignoring protection and the fit is protected, 
				// do not modify it.
				if($ignoreProtected && $fit->isProtected()) {
					$protected++;
					continue;
				}
				
				// this fit must be a duplicate of an already imported
				// fit during this import session - can happen :)
				if($mode=='fresh') {
					$errors++;
					continue;
				}
				
				if($fit->updateFromFitString($def['fitString'])) {
					$updated++;
				}
			}
		}
		
		$kept = $existing - $updated;
		
		if($new==0) { $new = __('none', 'eve-shipinfo');	}
		if($updated==0) { $updated = __('none', 'eve-shipinfo');	}
		if($kept==0) { $kept = __('none', 'eve-shipinfo');	}
		if($protected==0) { $protected = __('none', 'eve-shipinfo');	}
		if($errors==0) { $protected = __('none', 'eve-shipinfo');	}
		
		$ignoreLabel = __('No, protected fits are overwritten', 'eve-shipinfo');
		if($ignoreProtected) {
			$ignoreLabel = __('Yes, protected fits are left unchanged', 'eve-shipinfo');
		}
		
		switch($mode) {
			case 'new':
				$modeLabel = __('New only', 'eve-shipinfo');
				break;
				
			case 'merge':
				$modeLabel = __('Merge', 'eve-shipinfo');
				break;
				
			case 'fresh':
				$modeLabel = __('Clean', 'eve-shipinfo');
				break;
		}
		
		$this->addSuccessMessage(
			sprintf(
				__('The file was imported successfully at %1$s.', 'eve-shipinfo'),
				date('H:i:s')
			).' '.
			'<br>'.
			'<br>'.
			'<b>'.__('Import summary:', 'eve-shipinfo').'</b>'.
			'<ul>'.
				'<li>'.__('Import mode:', 'eve-shipinfo').' <b>'.$modeLabel.'</b></li>'.
				'<li>'.__('Ignore protected fittings:', 'eve-shipinfo').' '.$ignoreLabel.'</li>'.
				'<li>'.__('Fittings in imported file:', 'eve-shipinfo').' '.count($fits).'</li>'.
				'<li>'.__('New:', 'eve-shipinfo').' '.$new.'</li>'.
				'<li>'.__('Updated:', 'eve-shipinfo').' '.$updated.'</li>'.
				'<li>'.__('Unchanged:', 'eve-shipinfo').' '.$kept.'</li>'.
				'<li>'.__('Protected:', 'eve-shipinfo').' '.$protected.'</li>'.
				'<li>'.__('Invalid:', 'eve-shipinfo').' '.$errors.' <span class="text-muted">('.__('Unknown ships, duplicates, etc.', 'eve-shipinfo').')</span></li>'.
			'</ul>'
		);
	}
	
	/**
	 * Goes through the raw imported data of a fit from the imported
	 * XML document and converts it to the internal storage format.
	 * 
	 * Returns an array with the following structure:
	 * 
	 * <pre>
	 * array(
	 *     'name' => 'Full rack Tachyons',
	 *     'ship' => 'Abaddon',
	 *     'hardware' => array(
	 *         'low' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'med' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'hi' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'rig' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'drone' => array(
     *             'Item One',
     *             'Item Two',
     *             ...
     *         )
	 *     )
	 * )
	 * </pre>
	 * 
	 * @param array $fit
	 * @return array
	 */
	protected function parseFit($fit)
	{
		$ship = $fit['shipType']['@attributes']['value'];
		if(!$this->collection->shipNameExists($ship)) {
			return false;
		}
		
		$name = str_replace($ship.' - ', '', $fit['@attributes']['name']);
		
		// fits without modules
		if(!isset($fit['hardware'])) {
			$fit['hardware'] = array();
		}
		
		// fits with a single module
		if(isset($fit['hardware']['@attributes'])) {
			$new = array(array('@attributes' => $fit['hardware']['@attributes']));
			$fit['hardware'] = $new;
		}
		
		$hardware = array();
		foreach($fit['hardware'] as $item) {
			$slot = $item['@attributes']['slot'];
			$type = $item['@attributes']['type'];
			
			$tokens = explode(' ', $slot);
			$slotType = $tokens[0];
			if(!isset($hardware[$slotType])) {
				$hardware[$slotType] = array();
			}

			if(isset($item['@attributes']['qty'])) {
				$type .= ' x '.$item['@attributes']['qty'];
			}
				
			$hardware[$slotType][] = $type;
		}
		
		// ensure all keys are present
		$keys = array('low', 'med', 'hi', 'rig', 'drone');
		foreach($keys as $key) {
			if(!isset($hardware[$key])) {
				$hardware[$key] = array();
			}
		}
		
		$fitString = 
		'['.$ship.', '.$name.']'.PHP_EOL;
		
		foreach($hardware as $items) {
			foreach($items as $item) {
				$fitString .= $item.PHP_EOL;
			}
			$fitString .= PHP_EOL;
		}
		
		return array(
			'name' => $name,
			'ship' => $ship,
			'hardware' => $hardware,
			'fitString' => $fitString
		);
	}
}