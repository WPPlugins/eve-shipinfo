<?php

abstract class EVEShipInfo_Admin_Page_Tab
{
   /**
    * @var WP_Screen
    */
    protected $screen;
    
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
   /**
    * @var EVEShipInfo_Admin_Page
    */
    protected $page;
    
   /**
    * @var EVEShipInfo_Admin_UI
    */
    protected $ui;
    
    protected $activeAction = null;
    
    public function render()
    {
    	if(isset($_REQUEST['action']) && isset($this->actions[$_REQUEST['action']])) {
    		$this->activeAction = $_REQUEST['action'];
    		$method = 'renderAction_'.$this->activeAction;
    		if(method_exists($this, $method)) {
    			return $this->$method();
    		}
    	}
    	
    	return $this->_render();
    }
    
	abstract protected function _render();
	
	public function __construct(EVEShipInfo_Admin_Page $page)
	{
		$this->page = $page;
		$this->ui = $page->getUI();
		$this->plugin = EVEShipInfo::getInstance();
		
		$this->configure();
	}
	
	protected $actions = array();
	
	protected function configure()
	{
		// extensible to configure the tab
	}
	
	protected function registerAction($name, $label, EVEShipInfo_Admin_UI_Icon $icon=null, $showButton=true)
	{
		$this->actions[$name] = array(
			'label' => $label,
			'icon' => $icon,
			'showButton' => $showButton
		);
		
		return $this;
	}
	
	public function getID()
	{
		return str_replace('EVEShipInfo_Admin_Page_'.$this->page->getID().'_', '', get_class($this));
	}
	
	public function getURL($params=array())
	{
		return $this->page->getURL($this->getID(), $params);
	}
	
	protected $slug;
	
	public function getSlug()
	{
		if(!isset($this->slug)) {
			$page = $this->plugin->getPageDef($this->getID());
			$this->slug = $page['name'];
		}
		
		return $this->slug;
	}
	
   /**
    * Creates a new settings manager that can be used to manage
    * a set of configuration settings for the plugin.
    * 
    * @param string $id
    * @return EVEShipInfo_Admin_SettingsManager
    */
	protected function createSettings($id)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_SettingsManager');
		return new EVEShipInfo_Admin_SettingsManager($id);
	}
	
	abstract public function getTitle();

	public function getActionURL($action, $params=array())
	{
		$params['action'] = $action;
		return $this->getURL($params);
	}
	
	public function renderAlertSuccess($message)
	{
		return $this->renderAlert('updated', $message);
	}
	
	public function renderAlertError($message)
	{
		return $this->renderAlert('error', $message);
	}
	
	protected function renderAlert($type, $message)
	{
		return 
		'<div class="'.$type.'">'.
			$message.
		'</div>';
	}

	protected function addErrorMessage($message)
	{
		$this->page->addErrorMessage($message);
	}
	
	protected function addSuccessMessage($message)
	{
		$this->page->addSuccessMessage($message);
	}
	
	public function getActions()
	{
		return $this->actions;
	}
	
	protected function createForm($name, $defaultValues=array())
	{
		$form = $this->ui->createForm($name, $defaultValues);
		$form->addHiddenVar('page', $this->getSlug());

		if(isset($this->activeAction)) {
			$form->addHiddenVar('action', $this->activeAction);
		}
		
		return $form;
	}
	
	protected function renderRedirect($url, $buttonLabel, $boxTitle, $message)
	{
		$content = 
		'<script>setTimeout(function() { document.location=\''.$url.'\'; }, 6000);</script>'.
		'<p>'.	
			$message.
		'</p>'.
		'<hr>'.
		'<p>'.
			$this->ui->button($buttonLabel)
			->makePrimary()
			->link($url).
		'</p>'.
		'<p>'.
			'<span class="spinner" style="visibility:visible;float:left;margin:0px 10px 0px 0px;"></span>'.
			'<span class="text-muted">('.__('You  will be redirected automatically.', 'eve-shipinfo').')</span>'.
		'</p>';
		
		$box = $this->ui->createStuffBox($boxTitle);
		$box->setContent($content);
		
		return $box->render();
	}
	
	protected function renderUpdateDatabaseBox()
	{
		$box = $this->ui->createStuffBox(__('Database upgrade needed', 'eve-shipinfo'));
		$box->makeWarning();
		$box->setContent(
			__('Please set up the database first.', 'eve-shipinfo').' '.
			sprintf(
				__('You can do so in the %1$s.', 'eve-shipinfo'),
				'<a href="?page=eveshipinfo">'.__('dashboard', 'eve-shipinfo').'</a>'
				)
			);
		return $box->render();
	}

   /**
    * Called before headers are sent, can be extended by
    * tab classes to implement actions that may need to
    * redirect to another page.
    */
	public function handleActions()
	{
		
	}
	
	public function getNonceID()
	{
		return get_class($this);
	}
}