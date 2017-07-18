<?php

class EVEShipInfo_Admin_Page_Main_EFTFittings extends EVEShipInfo_Admin_Page_Tab
{
	const ERROR_INVALID_FITTING_FORM_ACTION = 1601;
	
	public function getTitle()
	{
		return __('Ship fittings', 'eve-shipinfo');
	}
	
   /**
    * Only present if the fid request parameter is present.
    * @var EVEShipInfo_EFTManager_Fit
    */
	protected $fit;
	
	protected function configure()
	{
		if(!$this->plugin->isDatabaseUpToDate()) {
			return;
		}
		
		$this->eft = $this->plugin->createEFTManager();
		
		if(isset($_REQUEST['fid'])) {
			$this->fit = $this->eft->getFittingByID($_REQUEST['fid']);
		}
		
		$this->registerAction(
			'edit', 
			__('Edit fitting', 'eve-shipinfo'),
			$this->ui->icon()->edit(),
			false
		);
		
		$this->registerAction(
			'add', 
			__('Add new', 'eve-shipinfo'), 
			$this->ui->icon()->add()
		);
	}
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $eft;
	
	protected function _render()
	{
		/* @var $fit EVEShipInfo_EFTManager_Fit */
		
		if(isset($_REQUEST['list_submitted']) && $_REQUEST['list_submitted']=='yes') {
			check_admin_referer($this->getNonceID());
			if(isset($_REQUEST['fittings']) && !empty($_REQUEST['fittings'])) {
				$this->handleActions();
			}
		}
		
		return $this->renderList();
	}
	
	protected function renderList()
	{
		if(!$this->plugin->isDatabaseUpToDate()) {
			return $this->renderUpdateDatabaseBox();
		}
		
		$filters = $this->configureFilters();
		$fits = $filters->getFittings();
		
		$boxHTML = 
		'<p>'.
			__('The following is a list of all known fits, and the name to use with the shortcode to insert it in your posts.', 'eve-shipinfo').' '.
			sprintf(
				__('Have a look at the %sshortcode reference%s for examples on how to use this.', 'eve-shipinfo'),
				'<a href="admin.php?page=eveshipinfo_shortcodes&shortcode=EFTFit">',
				'</a>'
			).' '.
			__('If you mark fits as private, they will not be shown in the fittings tab in the ship info windows.', 'eve-shipinfo').' '.
			__('You can still link them manually with the fitting shortcodes though.', 'eve-shipinfo').
		'</p>'.
		'<form method="post" id="form_fittings">'.
			wp_nonce_field($this->getNonceID()).
			'<input type="hidden" name="list_submitted" value="yes"/>'.
			'<div class="shipinfo-table-controls">'.
				'<table class="shipinfo-form-table shipinfo-list-ordering">'.
					'<tbody>'.
						'<tr>'.
							'<td>'.__('Order by:', 'eve-shipinfo').'</td>'.
							'<td>'.$filters->renderOrderBySelect().'</td>'.
							'<td>'.$filters->renderOrderDirSelect().'</td>'.
							'<td>'.
								'<button type="submit" name="apply_sort" class="button">'.
									__('Apply', 'eve-shipinfo').
								'</button>'.
							'</td>'.
						'</tr>'.
					'</tbody>'.
				'</table>'.
				'<table class="shipinfo-form-table shipinfo-list-filtering">'.
					'<tbody>'.
						'<tr>'.
							'<td><input type="text" name="filter" id="field_filter" value="'.$filters->getSearch().'" placeholder="'.__('Search, e.g. Abaddon', 'eve-shipinfo').'"/></td>'.
							'<td>'.$filters->renderVisibilitySelect('list_visibility').'</td>'.
							'<td>'.
								'<button type="submit" name="apply_filter" value="yes" class="button"/>'.
									'<span class="dashicons dashicons-update"></span> '.
									__('Apply', 'eve-shipinfo').
								'</button>'.
							'</td>'.
							'<td>'.
								'<button type="button" class="button" onclick="jQuery(\'#field_filter\').val(\'\');jQuery(\'#form_fittings\').submit();">'.
									'<span class="dashicons dashicons-no-alt"></span> '.
									__('Reset', 'eve-shipinfo').
								'</button>'.
							'</td>'.
						'</tr>'.
					'</tbody>'.
				'</table>'.
			'</div>'.
			'<table class="wp-list-table widefat fixed">'.
				'<thead>'.
					'<tr>'.
						'<th style="width:30px;padding-left:3px;">'.
							'<input type="checkbox" onclick="FittingsList.ToggleAll()" class="fits-toggler" title="'.__('Select / deselect all', 'eve-shipinfo').'"/>'.
						'</th>'.
						'<th>'.__('Fit name', 'eve-shipinfo').'</th>'.
						'<th>'.__('Ship', 'eve-shipinfo').'</th>'.
						'<th>'.__('Visibility', 'eve-shipinfo').'</th>'.
						'<th>'.__('Modified', 'eve-shipinfo').'</th>'.
						'<th style="width:8%">'.__('Fit ID', 'eve-shipinfo').'</th>'.
						'<th style="text-align:center;">'.__('Protected', 'eve-shipinfo').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody>';
					if(empty($fits)) {
						$boxHTML .=
						'<tr>'.
							'<td colspan="7" class="text-info">'.
								'<span class="dashicons dashicons-info"></span> '.
								'<b>'.__('No fittings found matching these criteria.', 'eve-shipinfo').'</b>'.
							'</td>'.
						'</tr>';	
					} else {
						foreach($fits as $fit) 
						{
							$jsID = EVEShipInfo::nextJSID();
							$displayPrivate = 'none';
							$displayPublic = 'none';
							
							$this->ui->addJSOnload(sprintf(
								"jQuery('#%s-private, #%s-public').dblclick(function() {FittingsList.ToggleVisibility('%s', '%s');}).addClass('shipinfo-clickable')",
								$jsID,
								$jsID,
								$fit->getID(),
								$jsID
							));
							
							if($fit->isPublic()) { $displayPublic = 'block'; }
							if($fit->isPrivate()) { $displayPrivate = 'block'; }
							
							$public = 
							'<div id="'.$jsID.'-private" class="fit-visibility-toggle" style="display:'.$displayPrivate.'" title="'.__('Double-click to toggle.', 'eve-shipinfo').'">'.
								$this->ui->icon()->visibilityPrivate()
								->makeDangerous().
								' '.
								__('Private', 'eve-shipinfo').
							'</div>'.
							'<div id="'.$jsID.'-public" class="fit-visibility-toggle" style="display:'.$displayPublic.'" title="'.__('Double-click to toggle.', 'eve-shipinfo').'">'.
								$this->ui->icon()->visibilityPublic()
								->makeSuccess().
								' '.
								__('Public', 'eve-shipinfo').
							'</div>'.
							'<div id="'.$jsID.'-loading" style="display:none">'.
								$this->ui->icon()->spinner()
								->addClass('spinner-datagrid').
								' '.
								__('Updating...', 'eve-shipinfo').
							'</div>';
							
							$invalid = '';
							if($fit->hasInvalidSlots()) {
								$invalid = $this->ui->icon()->warning()
								->makeDangerous()
								->cursorHelp()
								->setTitle(__('This fitting has some invalid slots.', 'eve-shipinfo'));
							}
							
							$boxHTML .=
							'<tr>'.
								'<td>'.
									'<input type="checkbox" name="fits[]" class="fit-checkbox" value="'.$fit->getID().'"/>'.
								'</td>'.
								'<td>'.
									'<a href="'.$fit->getAdminEditURL().'">'.
										$fit->getName().
									'</a> '.
									$invalid.
								'</td>'.
								'<td>'.$fit->getShipName().'</td>'.
								'<td>'.$public.'</td>'.
								'<td>'.$fit->getDateUpdatedPretty().'</td>'.
								'<td>'.$fit->getID().'</td>'.
								'<td style="text-align:center;">'.$fit->isProtectedPretty().'</td>'.
							'</tr>';
						}
					}
					$boxHTML .=
				'</tbody>'.
			'</table>'.
			'<br>'.
			__('With selected:', 'eve-shipinfo').'<br/>'.
			'<ul class="list-toolbar">'.
				'<li>'.
					$this->ui->button(__('Delete', 'eve-shipinfo'))
					->makeDangerous()
					->setIcon($this->ui->icon()->delete())
					->setName('action')
					->makeSubmit('delete').
				'</li>'.
				'<li class="list-toolbar-separator"></li>'.
				'<li>'.
					$this->ui->button(__('Make private', 'eve-shipinfo'))
					->setIcon($this->ui->icon()->visibilityPrivate())
					->setName('action')
					->makeSubmit('makePrivate').
				'</li>'.
				'<li>'.
					$this->ui->button(__('Make public', 'eve-shipinfo'))
					->setIcon($this->ui->icon()->visibilityPublic())
					->setName('action')
					->makeSubmit('makePublic').
				'</li>'.
				'<li class="list-toolbar-separator"></li>'.
				'<li>'.
					$this->ui->button(__('Protect', 'eve-shipinfo'))
					->setIcon($this->ui->icon()->protect())
					->setName('action')
					->makeSubmit('protect').
				'</li>'.
				'<li>'.
					$this->ui->button(__('Unprotect', 'eve-shipinfo'))
					->setIcon($this->ui->icon()->unprotect())
					->setName('action')
					->makeSubmit('unprotect').
				'</li>'.
			'</ul>'.
			'<div style="clear:both"></div>'.
		'</form>';
		
		$html = $this->ui->createStuffBox(__('Available fittings', 'eve-shipinfo'))
		->setIcon($this->ui->icon()->listView())
		->setContent($boxHTML)
		->render();
		
		return $html;
	}
	
   /**
    * Handles the list being submitted. Collects selected fits and dispatches 
    * to the method according to the selected list action.
    */
	public function handleActions()
	{
		if(!isset($_REQUEST['fits']) || !isset($_REQUEST['action'])) {
			return;
		}
		
		$selected = array();
		foreach($_REQUEST['fits'] as $fitID) {
			if($this->eft->idExists($fitID)) {
				$selected[] = $this->eft->getFittingByID($fitID);
			}
		}
		
		if(empty($selected)) {
			$this->addErrorMessage(__('No valid ships were selected, no changes made.', 'eve-shipinfo'));
			return;
		}
		
		$method = 'handleListAction_'.$_REQUEST['action'];
		if(method_exists($this, $method)) {
			$this->$method($selected);
		}
	}
	
   /**
    * Handles deleting a collection of fits.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleListAction_delete($selected)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($this->eft->deleteFitting($fit)) {
				$total++;
			}
		}
		
		if($total > 0) {
			$this->eft->save();
		}
		
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was deleted successfully at %2$s.', 'eve-shipinfo'),
				$selected[0]->getName(),
				date('H:i:s')
			));
		}

		if($total==0) {
			return $this->addErrorMessage(
				__('All the selected fittings were already deleted.', 'eve-shipinfo')
			);
		}
		
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were deleted successfully at %2$s.', 'eve-shipinfo'),
			count($selected),
			date('H:i:s')
		));
	}
	
   /**
    * Handles making a collection of fits private.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleListAction_makePrivate($selected)
	{
		$this->handleListAction_visibility($selected, EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE);
	}

	/**
	 * Handles making a collection of fits protected from import.
	 * @param EVEShipInfo_EFTManager_Fit[] $selected
	 */
	protected function handleListAction_protect($selected)
	{
		$this->handleListAction_protection($selected, true);
	}

	/**
	 * Handles making a collection of fits not protected from import.
	 * @param EVEShipInfo_EFTManager_Fit[] $selected
	 */
	protected function handleListAction_unprotect($selected)
	{
		$this->handleListAction_protection($selected, false);
	}
	
	protected function handleListAction_protection($selected, $protect)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($fit->setProtection($protect)) {
				$total++;
			}
		}
		
		$label = __('protected', 'eve-shipinfo');
		if(!$protect) {
			$label = __('not protected', 'eve-shipinfo');
		}
	
		if($total > 0) {
			$this->eft->save();
		}
	
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was successfully marked as %2$s at %3$s.', 'eve-shipinfo'),
				$selected[0]->getName(),
				$label,
				date('H:i:s')
			));
		}
	
		if($total==0) {
			return $this->addErrorMessage(sprintf(
				__('All the selected fittings were already marked as %1$s.', 'eve-shipinfo'),
				$label
			));
		}
	
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were successfully marked as %2$s at %3$s.', 'eve-shipinfo'),
			count($selected),
			$label,
			date('H:i:s')
		));
	}
	
   /**
    * Handles making a collection of fits public.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleListAction_makePublic($selected)
	{
		$this->handleListAction_visibility($selected, EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC);
	}
	
	protected function handleListAction_visibility($selected, $visibility)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($fit->setVisibility($visibility)) {
				$total++;
			}
		}		
		
		if($total > 0) {
			$this->eft->save();
		}
		
		$label = __('public', 'eve-shipinfo');
		if($visibility == EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE) {
			$label = __('private', 'eve-shipinfo');
		}
		
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was successfully marked as %2$s at %3$s.', 'eve-shipinfo'),
				$selected[0]->getName(),
				$label,
				date('H:i:s')
			));
		} 
		
		if($total==0) {
			return $this->addErrorMessage(sprintf(
				__('All the selected fittings were already marked as %1$s.', 'eve-shipinfo'),
				$label	
			));
		} 
		
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were successfully marked as %2$s at %3$s.', 'eve-shipinfo'),
			count($selected),
			$label,
			date('H:i:s')
		));
	}
	
   /**
    * Creates and configures the filters used for the fittings list.
    * @return EVEShipInfo_EFTManager_Filters
    */
	protected function configureFilters()
	{
		$filters = $this->eft->getFilters();
		
		$filter = '';
		if(isset($_REQUEST['filter'])) {
			$filter = htmlspecialchars(trim(strip_tags($_REQUEST['filter'])), ENT_QUOTES, 'UTF-8');
			if(!empty($filter)) {
				$filters->setSearch($filter);
			}
		}
		
		if(isset($_REQUEST['order_by']) && $filters->orderFieldExists($_REQUEST['order_by'])) {
			$filters->setOrderBy($_REQUEST['order_by']);
		}
		
		if(isset($_REQUEST['order_dir']) && $filters->orderDirExists($_REQUEST['order_dir'])) {
			$filters->setOrderDir($_REQUEST['order_dir']);
		}
		
		if(isset($_REQUEST['list_visibility']) && $filters->visibilityExists($_REQUEST['list_visibility'])) {
			$filters->setVisibility($_REQUEST['list_visibility']);
		}

		return $filters;
	}
	
	public function renderAction_add()
	{
		$html = $this->renderFittingForm('add');
		return $html;
	}
	
	public function renderAction_edit()
	{
		$html = $this->renderFittingForm('edit');
		
		if($this->fit->hasInvalidSlots()) {
			$message = 
			'<p>'.
				'<b>'.__('Some module slots in this fit could not be recognized.', 'eve-shipinfo').'</b> '.
				__('This can happen for example when a fit uses old modules that have been renamed or removed from the game.', 'eve-shipinfo').' '.
				__('The following modules could not be recognized:', 'eve-shipinfo').
			'</p>'.
			'<ul>';
				$invalid = $this->fit->getInvalidSlots();
				foreach($invalid as $item) {
					$message .= '<li>'.$item['moduleName'].'</li>';
				} 
				$message .=
			'</ul>'.
			'<p>'.
				__('These modules have already been stripped from the fit below.', 'eve-shipinfo').' '.
				'<b>'.__('To remove this notice, simply save the fit as is to confirm removing the obsolete modules.', 'eve-shipinfo').'</b>'.
			'</p>';
				
			$sect = $this->ui->createStuffBox(__('Invalid slots detected', 'eve-shipinfo'));
			$sect->makeError();
			$sect->setContent($message);
			
			$html = $sect->render().$html;
		}
		
		return $html;
	}
	
	protected function createFittingForm($action, $defaultValues=array())
	{
		$form = $this->createForm('createNewFitting', $defaultValues)
		->addButton(
			$this->ui->button(__('Cancel', 'eve-shipinfo'))
			->link($this->getURL())
		)
		->setSubmitLabel(__('Add now', 'eve-shipinfo'))
		->setSubmitIcon($this->ui->icon()->add());
		
		if($action=='edit') {
			$form->setSubmitLabel(__('Save now', 'eve-shipinfo'));
			$form->setSubmitIcon($this->ui->icon()->edit());
			$form->addStatic(__('Fit ID', 'eve-shipinfo'), '<code>'.$this->fit->getID().'</code>');
			$form->addStatic(__('Date added', 'eve-shipinfo'), $this->fit->getDateAddedPretty());
			$form->addStatic(__('Last modified', 'eve-shipinfo'), $this->fit->getDateUpdatedPretty());
			$form->addStatic(__('Shortcode', 'eve-shipinfo'), '<code>'.$this->fit->getShortcode().'</code>');
			$form->addStatic(__('Shortcode (custom name)', 'eve-shipinfo'), '<code>'.$this->fit->getShortcode(__('Custom name', 'eve-shipinfo')).'</code>');
		}
		
		$fitting = $form->addTextarea('fitting', __('Ship fitting', 'eve-shipinfo'))
		->addFilter('trim')
		->addCallbackRule(array($this, 'validateFit'), __('Could not recognize the format as a ship fitting.'))
		->setRows(15)
		->matchRows()
		->setRequired()
		->setDescription(
			'<b>'.__('Howto:', 'eve-shipinfo').'</b> '.
			__('In EVE, open the fitting window.', 'eve-shipinfo').' '.
			__('Navigate to the fit you want to export - you can even use a simulated fitting.', 'eve-shipinfo').' '.
			sprintf(__('Your saved fittings will be under the %1$s tab.', 'eve-shipinfo'), '<code>Hulls &amp; Fits</code>').' '.
			sprintf(
				__('In the bottom right of the window, click on %1$s, then %2$s.', 'eve-shipinfo'),
				'<code>Import & Export</code>',
				'<code>Copy to clipboard</code>'
			).' '.
			__('Paste the fit here in the text field.', 'eve-shipinfo').' '.
			__('All information, from the ship to the fit label will be detected automatically.', 'eve-shipinfo').
			'<br/>'.
			'<br/>'.
			__('When manually adding modules, ensure you write the name exactly as used ingame, including capitalization.').' '.
			__('The order of modules is irrelevant: they are are sorted automatically.').' '.
			__('Note:', 'eve-shipinfo').' '.__('The available slots on the ship are not checked, so you can add too many modules here.')
		);
		
		$labelEl = $form->addText('label', __('Label', 'eve-shipinfo'))
		->addFilter('trim')
		->addFilter('strip_tags')
		->addRegexRule('/\A[^,]+\z/', __('May not contain commas.'));
		
		if($action=='add') {
			$labelEl->setDescription(
				__('Optional:', 'eve-shipinfo').' '.
				__('Specify this if you wish to overwrite the label that comes with the fit.', 'eve-shipinfo')
			);
		} else {
			$labelEl->setRequired();
		}
		
		$form->addSelect('visibility', __('Visibility', 'eve-shipinfo'))
		->addOption(__('Public', 'eve-shipinfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC)
		->addOption(__('Private', 'eve-shipinfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE);
		
		$form->addCheckbox('protection', __('Protection', 'eve-shipinfo'))
		->setInlineLabel(__('Protect fit from import', 'eve-shipinfo'))
		->setDescription(__('If checked, this fit will be protected from any changes when importing fits.', 'eve-shipinfo'));
		
		$form->setDefaultElement($fitting);
		
		return $form;
	}		
		
	public function validateFit($value, EVEShipInfo_Admin_UI_Form_ValidationRule_Callback $rule, EVEShipInfo_Admin_UI_Form_Element $element)
	{
		$manager = $this->plugin->createEFTManager();
		$fit = $manager->parseFit($value);
		if($fit) {
			return true;
		}
		
		return false;
	}
	
	protected function renderFittingForm($action)
	{
		$defaultValues = array(
			'fitting' => '',
			'label' => '',
			'visibility' => EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC,
			'protection' => 'no'
		);
		
		$boxTitle = __('Add a new fit', 'eve-shipinfo');
		$boxIcon = $this->ui->icon()->add();
		
		if($action == 'edit') {
			$boxTitle = sprintf(
				__('Edit the %1$s fitting %2$s', 'eve-shipinfo'), 
				$this->fit->getShipName(), 
				'<b>"'.$this->fit->getName().'"</b>'
			);
			
			$boxIcon = $this->ui->icon()->edit();
			$defaultValues['fitting'] = $this->fit->toEFTString();
			$defaultValues['label'] = $this->fit->getName();
			$defaultValues['visibility'] = $this->fit->getVisibility();
			
			if($this->fit->isProtected()) {
				$defaultValues['protection'] = 'yes';
			}
		}
		
		$form = $this->createFittingForm($action, $defaultValues);

		if($action == 'edit') {
			$form->addHiddenVar('fid', $this->fit->getID());
		}
		
		if($form->validate()) {
			$values = $form->getValues();
			$method = 'handleFormAction_'.$action;
			if(!method_exists($this, $method)) {
				throw new EVEShipInfo_Exception(
					'Invalid fitting form action',
					sprintf(
						'The form hanlding method [%s] does not exist in the class [%s].',
						$method,
						get_class($this)
					),
					self::ERROR_INVALID_FITTING_FORM_ACTION	
				);
			}
			return $this->$method($form, $values);
		}
		
		$boxHTML = '';
		
		switch($action) {
			case 'add':
				$boxHTML .=
				'<p>'.
					__('The following lets you manually add a new fit to the ship fittings collection.', 'eve-shipinfo').
				'</p>';
				break;
		}
		
		$boxHTML .= $form->render();
		
		$html = $this->ui->createStuffBox($boxTitle)
		->setIcon($boxIcon)
		->setContent($boxHTML)
		->render();
		
		return $html;
	}
	
	protected function handleFormAction_add($form, $values)
	{
		$fit = $this->eft->addFromFitString(
			$values['fitting'], 
			$values['label'], 
			$values['visibility'], 
			true
		);
		
		$this->eft->save();
		
		$message = sprintf(
			__('The fitting %1$s was added successfully at %2$s.', 'eve-shipinfo'),
			'<i>'.$fit->getName().'</i>',
			date('H:i:s')
		);
		
		return $this->renderRedirect(
			$this->getURL(), 
			__('Back to the list', 'eve-shipinfo'), 
			__('Add a new fit', 'eve-shipinfo'), 
			$message
		);
	}
	
	protected function handleFormAction_edit($form, $values)
	{
		$this->fit->updateFromFitString($values['fitting']);
		$this->fit->setVisibility($values['visibility']);
		$this->fit->setName($values['label']);
		$this->fit->setProtection($values['protection']);
				
		if($this->fit->isModified()) 
		{
			$this->eft->save();
			
			$message = sprintf(
				__('The fitting %1$s was updated successfully at %2$s.', 'eve-shipinfo'),
				'<i>'.$this->fit->getName().'</i>',
				date('H:i:s')
			);
		} else {
			$message = sprintf(
				__('The fitting %1$s had no edits, and was not modified.', 'eve-shipinfo'),
				'<i>'.$this->fit->getName().'</i>'
			);
		}
		
		return $this->renderRedirect(
			$this->getURL(), 
			__('Back to the list', 'eve-shipinfo'),
			sprintf(
				__('Edit the %1$s fitting %2$s', 'eve-shipinfo'), 
				$this->fit->getShipName(), 
				'<b>"'.$this->fit->getName().'"</b>'
			),
			$message
		);
	}
}