<?php
/**
 * File containing the {@link EVEShipInfo} class.
 * 
 * @package EVEShipInfo
 * @see EVEShipInfo
 */

$eveshipinfo_root = dirname(__FILE__);

/**
 * The main plugin interface
 * @see EVEShipInfo_Plugin
 */
require_once $eveshipinfo_root.'/EVEShipInfo/Plugin.php';

/**
 * Plugin-specific Exception implementation
 * @see EVEShipInfo_Exception
 */
require_once $eveshipinfo_root.'/EVEShipInfo/Exception.php';

/**
 * Main plugin class for the EVE ShipInfo plugin. Registers
 * all required hooks, and implements most of the functionality.
 * Some special functionality is split into subclasses.
 * 
 * Generation of the virtual ship pages inspired by several
 * sources.
 * 
 * @package EVEShipInfo
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 * @link http://www.aeonoftime.com
 * @link https://gist.github.com/brianoz/9105004
 */
class EVEShipInfo extends EVEShipInfo_Plugin
{
	const ERROR_NOT_A_VALID_VIRTUAL_PAGE = 1301;
	
	const ERROR_CANNOT_UNPACK_DATA_FILES = 1302;
	
	const ERROR_COULD_NOT_CREATE_SQL_STRUCTURE = 1303;
	
	const ERROR_CANNOT_FIND_CLASS_FILE = 1304;
	
   /**
    * The name of the request variable which is used to
    * store the requested ship ID in the custom rewrite rule.
    * @var string
    * @see handle_initRewriteRules
    */
    const REQUEST_VAR_SHIP_ID = 'shipinfo_ship';
    
   /**
    * The name of the request variable which is used to
    * determine wether the ships list virtual page should
    * be shown.
    * 
    * @var string
    * @see handle_initRewriteRules
    */
    const REQUEST_VAR_SHIPSLIST = 'shipinfo_list';
    
   /**
    * The name of the plugin
    * @var string
    */
    const APPNAME = 'EVE ShipInfo';
    
   /**
    * The URL to the plugin's homepage
    * @var string
    */
    const APPURI = 'http://aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin';
    
   /**
    * @var EVEShipInfo
    */
	protected static $instance;
	
   /**
    * Retrieves/creates the global instance of the plugin.
    * Only one instance is needed per request.
    * 
    *  @return EVEShipInfo
    */
	public static function getInstance()
	{
		if(!isset(self::$instance)) {
			self::$instance = new EVEShipInfo();
		}
		
		return self::$instance;
	}
	
	protected $pluginFile;
	
   /**
    * The constructor sets up some vital properties and
    * registers essential hooks.
    */
	public function __construct()
	{
		$this->pluginFile = realpath(dirname(__FILE__).'/../eve-shipinfo.php');
		$this->dir = plugin_dir_path($this->pluginFile);
		$this->url = plugin_dir_url($this->pluginFile);
		$this->basename = plugin_basename($this->pluginFile);
		
		register_activation_hook($this->pluginFile, array($this, 'handle_activatePlugin'));
		register_deactivation_hook($this->pluginFile, array($this, 'handle_deactivatePlugin'));
		 
		add_action('init', array($this, 'handle_init'));
		add_action('wp_loaded', array($this, 'handle_actions'));

		$this->handle_initAJAXMethods();
		
		add_action('admin_init', array($this, 'handle_initPluginSettings'));
		add_action('admin_menu', array($this, 'handle_initAdminMenu'));
		add_action('parse_query', array($this, 'handle_resolveContentToDisplay'));
		 
		load_plugin_textdomain('eve-shipinfo', false, $this->dir.'/languages');
	}
	
   /**
    * Sets up all AJAX methods available for the plugin,
    * by checking which are available in the AjaxMethod
    * classes folder. Adds each as a plugin-specific
    * AJAX method.
    */
	protected function handle_initAJAXMethods()
	{
		$this->loadClass('EVEShipInfo_AjaxHandler');
		
		$names = $this->getClassNamesFromFolder($this->getClassesFolder().'/EVEShipInfo/AjaxMethod');
		
		foreach($names as $name) {
			// to avoid having to load the entire ajax class for each method on each request,
			// we use the lightweight handler, which loads the class on demand.
			$handler = new EVEShipInfo_AjaxHandler($this, $name);
			add_action('wp_ajax_eveshipinfo_'.strtolower($name), array($handler, 'execute'));
		}
	}
	
   /**
    * @var EVEShipInfo_Collection_Ship
    */
	protected $activeShip;
	
   /**
    * Retrieves the active ship, if any. 
    * @return EVEShipInfo_Collection_Ship|NULL
    */
	public function getActiveShip()
	{
	    if(!$this->isShipPage()) {
	        return null;
	    }
	    
	    if(!isset($this->activeShip)) {
	       $collection = $this->createCollection();
	       $this->activeShip = $collection->getShipByID($this->getShipID());
	    }
	    
	    return $this->activeShip;
	}
	
   /**
    * Generates a virtual post for the selected virtual page,
    * by delegating the rendering of the content  to one of
    * the virtual page classes.
    * 
    * Note: This hook is only added if a virtual page has been
    * requested. 
    * 
    * @param array $posts
    * @return array()
    * @hook the_post
    */
	public function handle_generateVirtualPost($posts)
	{
		// we need the base class for virtual pages
		$this->loadClass('EVEShipInfo_VirtualPage');
		
		$className = 'EVEShipInfo_VirtualPage_'.$this->virtualPageName;   
	    $this->loadClass($className);
	    
	    $page = new $className($this);
	    
	    // make sure the class is a valid virtual page class
	    if(!$page instanceof EVEShipInfo_VirtualPage) {
	    	$this->registerError(
	    		sprintf(
	    			__('The class %1$s is not a valid virtual page, it must extend the %2$s class.', 'eve-shipinfo'),
	    			'['.$className.']',
	    			'[EVEShipInfo_VirtualPage]'
	    		),
	    		self::ERROR_NOT_A_VALID_VIRTUAL_PAGE
	    	);
	    	return $posts;
	    }
	    
	    // create the post: we simply use the first page from the database
	    // that we find, and replace its contents with ours. This way we 
	    // don't mess around with any of the other post settings and variables,
	    // only the essentials like the title and content.
	    $virtual = $this->getDummyPage();
	    if(!$virtual) {
	    	return $posts;
	    }
	    
	    $virtual->post_title = $page->renderTitle();
	    $virtual->post_content = $page->renderContent();
	    $virtual->guid = $page->getGUID();
	    $virtual->post_name = $page->getPostName();
	    
	    /* @var $wp_query WP_Query */
	    global $wp_query;
	    
	    // make sure that wordpress treats this virtual post
	    // as a single page.
	    $wp_query->is_page = true;
	    $wp_query->is_singular = true;
	    $wp_query->is_home = false;
	    $wp_query->is_archive = false;
	    $wp_query->is_category = false;
	    $wp_query->is_404 = false;
	    
	    // and we return a posts collection containing only our virtual page.
	    return array($virtual);
	}
	
	public function getDummyPage()
	{
		$pages = get_pages(array('number' => 1));
		if(!empty($pages)) {
		    return $pages[0];
		}
		 
		return null;
	}	
	
   /**
    * Checks whether the blog has URL rewriting enabled.
    * @return boolean
    */
	public function isBlogURLRewritingEnabled()
	{
		$structure = get_option('permalink_structure');
		return !empty($structure);
	}
	
	protected $shipID = null;
	
	protected $virtualPageName;
	
   /**
    * Checks the request vars and stores the ID of the ship to show
    * if the user requested to view a ship's detail page. We do this
    * with the parse_query hook, so we only have to do this once in
    * the request.
    * 
    * @param WP_Query $wp_query
    * @hook parse_query
    */
	public function handle_resolveContentToDisplay($wp_query)
	{
		// due to how permalinks are created, the linking between
		// the virtual ship pages will only work correctly when
		// url rewriting is enabled (regardless of the chosen 
		// rewriting structure).
		if(!$this->isBlogURLRewritingEnabled()) {
			return;
		}
		
		if(!$this->isVirtualPagesEnabled()) {
			return;
		}
		
		// a specific ship ID has been requested.
	    if(isset($wp_query->query_vars[self::REQUEST_VAR_SHIP_ID])) 
	    {
	    	// identifier may be a ship ID or ship name
	    	$identifier = urldecode($wp_query->query_vars[self::REQUEST_VAR_SHIP_ID]);
	    	$collection = $this->createCollection();
	    	
	    	// we will only really display the ship page if 
	    	// the ship exists in the collection, otherwise
	    	// we simply ignore the request.
	    	if(is_numeric($identifier) && $collection->shipIDExists($identifier)) {
	    		$this->virtualPageName = 'ShipDetail';
	    		$this->shipID = $identifier;
	    	} else if($collection->shipNameExists($identifier)) {
	    		$this->virtualPageName = 'ShipDetail';
	    		$this->shipID = $collection->getShipByName($identifier)->getID();
	    	}
	    } 
	    // the ships overview list has been requested.
	    else if(isset($wp_query->query_vars[self::REQUEST_VAR_SHIPSLIST])) 
	    {
	    	$this->virtualPageName = 'ShipFinder';
	    	wp_enqueue_script('jquery');
	    	wp_enqueue_script('jquery-ui-dialog');
	    	wp_enqueue_script('eveshipinfo_shipfinder');
	    }
	     
	    // now that we know we want to display a virtual page, we 
	    // can add the filters we'll need. Fortunately these all 
	    // happen after the parse_query hook, so we can do this here.
	    if(isset($this->virtualPageName)) {
	    	add_filter('the_posts', array($this, 'handle_generateVirtualPost'));
	    	add_filter( 'template_include', array($this, 'handle_chooseTemplate') );
	    	add_filter( 'body_class', array($this, 'handle_initVirtualPage') );
	    }
	}
	
	public function handle_initVirtualPage($classes)
	{
		$classes[] = 'eveshipinfo';
		$classes[] = 'virtual-'.strtolower($this->virtualPageName);
		
		return $classes;
	}
	
	public function handle_chooseTemplate()
	{
		$template = locate_template('page.php');
		return $template;
	}
	
   /**
    * Called when the plugin is activated: sets up all database tables.
    */
	public function handle_activatePlugin()
	{
    	$this->handle_databaseInstallation();
	}

   /**
    * Called when the plugin is deactivated: removes all database tables.
    */
	public function handle_deactivatePlugin()
	{
	    $this->dropTables();
	    $this->setOption('installed_db_version', '');
	}
	
   /**
    * Checks whether the current page is a ships overview list.
    * @return boolean
    */
	public function isShipsList()
	{
		if($this->virtualPageName=='ShipsList') {
			return true;
		}
		
		return false;
	}
	
   /**
    * Checks whether the current page is a ship detail page.
    * @return boolean
    */
	public function isShipPage()
	{
	    if($this->virtualPageName == 'ShipDetail') {
	        return true;
	    }
	    
	    return false;
	}
	
   /**
    * Retrieves the ID of the ship that has been requested, or
    * NULL otherwise.
    * 
    * @return integer|NULL
    */
	public function getShipID()
	{
	    return $this->shipID;
	}
	
	public function handle_init()
	{
	    $this->handle_initRewriteRules();
	    $this->handle_initShortcodes();
	    $this->handle_initThemes();
	    $this->handle_initScripts();
	}
	
   /**
    * @var EVEShipInfo_Admin_Page
    */
	protected $activePage;
	
	public function handle_actions()
	{
		if(!$this->isAdmin()) {
			return;
		}
		
		$pages = $this->getAdminPages();
		$activePage = null;
		if(isset($_REQUEST['page'])) {
			$activePage = $_REQUEST['page'];
			foreach($pages as $pageDef) {
				if($activePage == $pageDef['name']) {
					$this->activePage = $this->createPage('Main', $pageDef['name'])
					->selectTab($pageDef['id']);
					$this->activePage->handleActions();
				}
			}
		}
	}
	
	public function getThemeID()
	{
		$themeID = strtolower($this->getOption('theme', 'light'));
		if(isset($this->themes[$themeID])) {
			return $themeID;
		}
		
		reset($this->themes);
		
		return key($this->themes);
	}
	
	public function setThemeID($id)
	{
		$this->setOption('theme', $id);
	}
	
	public function themeIDExists($id)
	{
		return isset($this->themes[$id]);
	}
	
	public function themeSubstyleExists($id, $substyle)
	{
		if(!isset($this->themes[$id])) {
			return false;
		}
		
		foreach($this->themes[$id]['substyles'] as $def) {
			if($def['name'] == $substyle) {
				return true;
			}
		}
		
		return false;
	}
	
	public function setThemeSubstyle($substyle)
	{
		$this->setOption('theme-substyle', $substyle);
	}
	
	public function getThemeSubstyle()
	{
		$themeID = $this->getThemeID();
		if(empty($this->themes[$themeID]['substyles'])) {
			return null;
		}
		
		$substyle = $this->getOption('theme-substyle', '');
		if($this->themeSubstyleExists($themeID, $substyle)) {
			return $substyle;
		}
		
		return $this->themes[$themeID]['substyles'][0]['name'];
	}
	
	public function getThemeLabel()
	{
		$id = $this->getThemeID();
		return $this->themes[$id]['label'];
	}
	
   /**
    * Initializes the plugin's themes. These are stored in the themes
    * subfolder, and have their own css file and images, and can have
    * any number of substyles, for example for different colourings.
    */
	protected function handle_initThemes()
	{
		$this->registerTheme(
			'light',
			__('Light', 'eve-shipinfo'),
			__('A minimalistic theme for light themed layouts.', 'eve-shipinfo')	
		);

		$this->registerTheme(
			'dark',
			__('Dark', 'eve-shipinfo'),
			__('A minimalistic theme for dark themed layouts.', 'eve-shipinfo')
		);
		
		$this->registerTheme(
			'sytek', 
			'Sytek', 
			__('A stylish dark theme with color substyles.', 'eve-shipinfo'),
			array(
				array(
					'name' => 'alien',
					'label' => 'Alien green' 
				),
				array(
					'name' => 'star-trek',
					'label' => 'Star Trek blue'
				),
				array(
					'name' => 'mars-attacks',
					'label' => 'Mars Attacks orange'
				),
				array(
					'name' => 'shaun-of-the-red',
					'label' => 'Shaun Of The Red'
				),
				array(
					'name' => 'power-rangers',
					'label' => 'Power Rangers pink'
				),
			)
		);
	}
	
	protected $themes = array();
	
   /**
    * Registers a frontend theme CSS.
    * 
    * @param string $id
    * @param string $label
    * @param string $description
    * @param array $substyles
    */
	protected function registerTheme($id, $label, $description, $substyles=array())
	{
		$this->themes[$id] = array(
			'label' => $label,
			'description' => $description,
			'substyles' => $substyles
		);
	}
	
   /**
    * Initializes all the shortcodes that come bundled with the plugin.
    * Each shortcode is in a separate class.
    * 
    * @hook init
    */
	protected function handle_initShortcodes()
	{
		// no need to register the shortcodes in the admin area
		if(is_admin()) {
			return;
		}
		
		$shortcodes = $this->getShortcodes();
		
		foreach($shortcodes as $instance) {
			add_shortcode($instance->getTagName(), array($instance, 'handle_call'));
		}
	}
	
   /**
    * Retrieves the definitions for all available themes.
    * @return array
    */
	public function getThemes()
	{
		return $this->themes;
	}
	
   /**
    * Retrieves an indexed array containing instances of 
    * each of all available shortcodes bundled with the plugin.
    * 
    * @return multitype:EVEShipInfo_Shortcode
    */
	public function getShortcodes()
	{
		$ids = $this->getShortcodeIDs();
		$shortcodes = array();
		foreach($ids as $id) {
			$shortcodes[] = $this->createShortcode($id);
		}
		
		return $shortcodes;
	}
	
	protected $shortcodeIDs;
	
	public function getShortcodeIDs()
	{
		if(isset($this->shortcodeIDs)) {
			return $this->shortcodeIDs;
		}
		
		$this->shortcodeIDs = array();
		
		$folder = $this->getClassesFolder().'/EVEShipInfo/Shortcode';
		if(!file_exists($folder)) {
		    return $this->shortcodeIDs;
		}
		
		$this->shortcodeIDs = $this->getClassNamesFromFolder($folder);

		return $this->shortcodeIDs;
	}
	
   /**
    * Retrieves all PHP file names from the specified folder,
    * without their extensions.
    * 
    * @param string $folder
    * @return string[]
    */
	public function getClassNamesFromFolder($folder)
	{
		$names = array();
		$d = new DirectoryIterator($folder);
		foreach($d as $item) {
			$file = $item->getFilename();
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			if($ext != 'php') {
				continue;
			}
			 
			$names[] = str_replace('.php', '', $file);
		}
		
		return $names;
	}
	
	public function createShortcode($id)
	{
		$this->loadClass('EVEShipInfo_Shortcode');
		
		$class = 'EVEShipInfo_Shortcode_'.$id;
		$this->loadClass($class);

		$instance = new $class($this);
		return $instance;    
	}
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $eftManager;
	
   /**
    * Creates/gets the helper class used to retrieve information
    * about the EFT XML export, when available (when the user has
    * uploaded one).
    * 
    * @return EVEShipInfo_EFTManager
    */
	public function createEFTManager()
	{
		if(isset($this->eftManager)) {
			return $this->eftManager;
		}
		
		$this->loadClass('EVEShipInfo_EFTManager');
		
		$this->eftManager = new EVEShipInfo_EFTManager($this);
		return $this->eftManager;
	}
	
	public function addScript($file, $dependencies=null, $handle=null)
	{
	    if(empty($handle)) {
	        $handle = 'eveshipinfo_'.$this->nextJSID();
	    }
	    
	    if(!is_array($dependencies)) {
	        $dependencies = array();
	    }
	    
	    wp_register_script($handle, $this->getScriptURL($file), $dependencies);
	    wp_enqueue_script($handle);
	}
	
	public function addStyle($file, $dependencies=null, $handle=null)
	{
	    if(empty($handle)) {
	        $handle = 'eveshipinfo_'.$this->nextJSID();
	    }
	    
	    if(!is_array($dependencies)) {
	        $dependencies = array();
	    }
	    
	    wp_register_style($handle, $this->getScriptURL($file), $dependencies);
	    wp_enqueue_style($handle);
	}
	
	protected function handle_initScripts()
	{
		if(is_admin()) {
			$this->addScript('admin/Admin.js', array('jquery'));
			$this->addScript('admin/FittingsList.js', array('jquery'));
			$this->addScript('admin/Themes.js', array('jquery'));
			$this->addStyle('admin.css');
			return;
		}
		
		add_action('wp_head', array($this, 'handle_renderJavascriptHead'));

		$this->addScript('EVEShipInfo.js', array('jquery'), 'eveshipinfo');
		$this->addScript('EVEShipInfo/Ship.js', array('eveshipinfo'));
		$this->addScript('EVEShipInfo/Fitting.js', array('eveshipinfo'));		
		$this->addScript('EVEShipInfo/ShipFinder.js', array('eveshipinfo'));
		
		$this->addStyle('EVEShipInfo.css', null, 'eveshipinfo');
		
		
		
		$themeID = $this->getThemeID();
		wp_register_style('eveshipinfo_theme', $this->getURL().'/themes/'.$themeID.'/'.$this->getScriptFilename($themeID.'.css'), array('eveshipinfo'));
		wp_enqueue_style('eveshipinfo_theme');
		
		$substyle = $this->getThemeSubstyle();
		if(!empty($substyle)) {
			$url = $this->getURL().'/themes/'.$themeID.'/'.$substyle.'/'.$this->getScriptFilename($substyle.'.css');
			wp_register_style('eveshipinfo_substyle', $url, array('eveshipinfo'));
			wp_enqueue_style('eveshipinfo_substyle');
		}
	}
	
   /**
    * Retrieves the name of a javascript or css include file
    * according to the minified plugin setting.
    *
    * @param string $fileName For example "admin.js" will be turned into "admin.min.js" if minification is enabled.
    * @return string
    */
	public function getScriptFilename($fileName)
	{
		if($this->isJSMinified()) {
			$fileName = str_replace(array('.js', '.css'), array('.min.js', '.min.css'), $fileName);
		}
		
		return $fileName;
	}
	
   /**
    * Renders and echos the javascript code required for the clientside
    * translations. This is added to the page header.
    * 
    * @hook wp_head
    */
	public function handle_renderJavascriptHead()
	{
		$strings = array(
	        'Slots' => __('Slots', 'eve-shipinfo'),
	        'Cargo bay' => __('Cargo bay', 'eve-shipinfo'),
	        'Drones' => __('Drones', 'eve-shipinfo'),
	        'No launchers' => __('No launchers', 'eve-shipinfo'),
	        'X launchers' => __('%s launchers', 'eve-shipinfo'),
	        '1 launcher' => __('1 launcher', 'eve-shipinfo'),
	        'No turrets' => __('No turrets', 'eve-shipinfo'),
	        'X turrets' => __('%s turrets', 'eve-shipinfo'),
	        '1 turret' => __('1 turret', 'eve-shipinfo'),
	        'Warp speed' => __('Warp speed', 'eve-shipinfo'),
	        'Agility' => __('Agility', 'eve-shipinfo'),
	        'Max velocity' => __('Max velocity', 'eve-shipinfo'),
	        'None' => __('None', 'eve-shipinfo'),
	        'Capacitor' => __('Capacitor', 'eve-shipinfo'),
	        'X recharge rate' => __('%s recharge rate', 'eve-shipinfo'),
	        'X power output' => __('%s power output', 'eve-shipinfo'),
	        'X capacitor capacity' => __('%s capacity', 'eve-shipinfo'),
	        'Shield' => __('Shield', 'eve-shipinfo'),
	        'Armor' => __('Armor', 'eve-shipinfo'),
	        'Structure' => __('Structure', 'eve-shipinfo'),
	        'X signature radius' => __('%s signature radius', 'eve-shipinfo'),
	        'Max target range' => __('Max target range', 'eve-shipinfo'),
	        'Max locked targets' => __('Max locked targets', 'eve-shipinfo'),
	        'Scan speed' => __('Scan speed', 'eve-shipinfo'),
	        'Scan resolution' => __('Scan resolution', 'eve-shipinfo'),
			'Edit' => __('Edit', 'eve-shipinfo'),
			'Copy' => __('Copy', 'eve-shipinfo')
		);
		
		$lines = array();
		foreach($strings as $key => $text) {
		    $lines[] = "'".$key."':'".addslashes($text)."'";
		}
		
		$content =
		"<script type=\"text/javascript\">
			EVEShipInfo.adminBaseURL = '".$this->getAdminURL()."';
/**
 * Container for localized clientside strings.
 * @module EVEShipInfo
 * @class EVEShipInfo_Translation
 * @static
 */
var EVEShipInfo_Translation = {".
	'translations:{'.
		implode(',', $lines).
	'},'.
	'Translate:function(name) {'.
		"if(typeof(this.translations[name]!='undefined')) {".
			'return this.translations[name];'.
		'}'.
		'return name;'.
	'}'.
'};'.
		'</script>';
		
		echo $content;
	}
	
   /**
    * Retrieves the absolute URL to a javascript or stylesheet file
    * from the plugin's folder.
    * 
    * @param string $file
    * @return string
    */
	protected function getScriptURL($file)
	{
		$folder = 'js';
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if($ext=='css') {
			$folder = 'css';
		}
		
		$file = $this->getScriptFilename($file);
		
		return rtrim($this->getURL(), '/').'/'.$folder.'/'.$file;
	}
	
   /**
    * Initializes the plugin's custom rewrite rules used to
    * display the special ship pages.
    * 
    * @hook init
    */
	protected function handle_initRewriteRules()
	{
	    add_rewrite_tag('%'.self::REQUEST_VAR_SHIP_ID.'%', '([0-9a-zA-Z \-\'%]+)');
	    add_rewrite_rule(
	       'eve/ship/([0-9a-zA-Z \-\'%]+)',
	       'index.php?'.self::REQUEST_VAR_SHIP_ID.'=$matches[1]',
	       'top'
	    );
	    
	    add_rewrite_tag('%'.self::REQUEST_VAR_SHIPSLIST.'%', '([1]{1})');
	    add_rewrite_rule(
	    	'eve/ships?',
	    	'index.php?'.self::REQUEST_VAR_SHIPSLIST.'=1',
	    	'top'
	    );
	}
	
	public function handle_initPluginSettings()
	{
		$basic = $this->createPage('Settings', '')->selectTab('Basic')->getActiveTab();
		$basic->initSettings();
	}
	
	protected $adminPages;
	
	protected function getAdminPages()
	{
		if(isset($this->adminPages)) {
			return $this->adminPages;
		}
		
		$this->adminPages = array(
			array(
				'id' => 'Dashboard',
				'navTitle' => __('Dashboard', 'eve-shipinfo'),
				'name' => 'eveshipinfo',
			),
			array(
				'id' => 'Themes',
				'navTitle' => __('Themes', 'eve-shipinfo'),
				'name' => 'eveshipinfo_themes',
			),
			array(
				'id' => 'Shortcodes',
				'navTitle' => __('Shortcodes', 'eve-shipinfo'),
				'name' => 'eveshipinfo_shortcodes',
			),
			array(
				'id' => 'EFTImport',
				'navTitle' => __('Fittings import', 'eve-shipinfo'),
				'name' => 'eveshipinfo_eftimport',
			),
			array(
				'id' => 'EFTFittings',
				'navTitle' => __('Ship fittings', 'eve-shipinfo'),
				'name' => 'eveshipinfo_eftfittings',
			),
			array(
				'id' => 'Database',
				'navTitle' => __('Database', 'eve-shipinfo'),
				'name' => 'eveshipinfo_database',
			),
			array(
				'id' => 'About',
				'navTitle' => __('About', 'eve-shipinfo'),
				'name' => 'eveshipinfo_about',
			)
		);
		
		return $this->adminPages;
	}
	
	public function getPageDef($pageID)
	{
		$pages = $this->getAdminPages();
		foreach($pages as $page) {
			if($page['id'] == $pageID) {
				return $page;
			}
		}
		
		return null;
	}
	
	public function handle_initAdminMenu()
	{
	    // Adds a link in the plugins list to the plugin's settings.
	    add_filter(
	       'plugin_action_links_'.$this->basename, 
	       array($this, 'handle_renderSettingsLink')
	    );
	    
	    // Adds an option page for the plugin under the "Settings" menu.
	    add_options_page(
	       sprintf(__('%1$s settings', 'eve-shipinfo'), EVEShipInfo::APPNAME), 
	       EVEShipInfo::APPNAME, 
	       'manage_options', 
	       'eveshipinfo_settings',
	       array($this, 'handle_displaySettingsPage')
	    );
	    
	    add_menu_page(
	    	EVEShipInfo::APPNAME,
	    	EVEShipInfo::APPNAME,
	    	'edit_posts',
	    	'eveshipinfo',
	    	array($this, 'handle_displayActivePage')
	    );
	    
	    $submenuPages = $this->getAdminPages();
	 
	    foreach($submenuPages as $page) {
	    	add_submenu_page(
	    		'eveshipinfo',
	    		$page['navTitle'],
	    		$page['navTitle'],
	    		'edit_posts',
	    		$page['name'],
	    		array($this, 'handle_displayActivePage')
	    	);
	    }
	}
	
	public function handle_displayActivePage()
	{
		if(isset($this->activePage)) {
			$this->activePage->display();
		}
	}
	
   /**
    * Renders and outputs the markup for the plugin's 
    * admin settings screen. This is delegated to a
    * separate class.
    * 
    * @see EVEShipInfo_Admin_SettingsPage
    */
	public function handle_displaySettingsPage()
	{
	    $settings = $this->createPage('Settings', '');
	    $settings->display();
	}
	
   /**
    * Creates an administration page instance.
    * @param string $id
    * @param string $slug
    * @return EVEShipInfo_Admin_Page
    */
	protected function createPage($id, $slug)
	{
		$this->loadClass('EVEShipInfo_Admin_Page');
		
		$class = 'EVEShipInfo_Admin_Page_'.$id;
		$this->loadClass($class);
		$page = new $class($this, $slug);
		
		return $page;
	}
	
   /**
    * Hook handler for the plugin_action_links hook, which adds
    * a link to the plugin's settings page in the plugins list.
    * 
    * @param array $links
    * @return array
    */
	public function handle_renderSettingsLink($links)
	{
	    if($this->isDatabaseUpToDate()) {
    	    $link = 
    	    '<a href="'.$this->getAdminSettingsURL().'">'.
    	        __('Settings', 'eve-shipinfo').
    	    '</a>';
    	   
    	    array_unshift($links, $link);
	    }
	    
	    $label = __('Dashboard', 'eve-shipinfo');
	    if(!$this->isDatabaseUpToDate()) {
	        $label = '<span style="color:#cc0000">'.$label.' <b>(!)</b></span>';
	    }
	    
	    $link =
	    '<a href="'.$this->getAdminDashboardURL().'">'.
	    	$label.
	    '</a>';
	    
	    array_unshift($links, $link);
	     
	    return $links;
	}

   /**
    * Retrieves the URL to the plugin settings screen in the administration.
    * @return string
    * @param string $tabID The tab in the settings screen to show
    * @param array $params Associative array with additional request parameters
    */
	public function getAdminSettingsURL($tabID='Basic', $params=array())
	{
		$params['page'] = 'eveshipinfo_settings';
		$params['tab'] = $tabID;
		
	    return 'options-general.php?'.http_build_query($params, null, '&amp;');
	}
	
	public function getAdminDashboardURL($params=array())
	{
		$params['page'] = 'eveshipinfo';
		return 'admin.php?'.http_build_query($params, null, '&amp;');
	}

	public function getAdminURL($params=array())
	{
		return rtrim(get_admin_url(), '/').'/admin.php?'.http_build_query($params, null, '&amp;');
	}
	
   /**
    * Retrieves the URL to the plugin's help page in the administration.
    * @param unknown $params
    */
	public function getAdminHelpURL($params=array())
	{
		return $this->getAdminSettingsURL('Help', $params);
	}
	
   /**
    * Loads a class from the plugin's classes repository.
    * @param string $className
    */
	public function loadClass($className)
	{
	    if(class_exists($className)) {
	        return;
	    }
	    
	    $file = $this->getClassesFolder().'/'.str_replace('_', '/', $className).'.php';
	    if(!file_exists($file)) {
	    	throw new EVEShipInfo_Exception(
	    		'Class file not found', 
	    		sprintf(
	    			'Tried loading the class [%s] from file [%s].',
	    			$className,
	    			$file
    			), 
	    		self::ERROR_CANNOT_FIND_CLASS_FILE
    		);
	    }
	    
	    require_once $file;
	}
	
   /**
    * Retrieves the full path to the plugin's "classes" folder.
    * @return string
    */
	public function getClassesFolder()
	{
		return $this->dir.'/classes';
	}
	
	protected static $jsIDCounter = 0;
	
	public static function nextJSID()
	{
		self::$jsIDCounter++;
		return 'esi'.self::$jsIDCounter;
	}
	
	protected $defaultSettings = array(
		'enable_virtual_pages' => 'yes',
		'use_minified_js' => 'yes'
	);
	
	public function getSetting($name)
	{
		$default = null;
		if(isset($this->defaultSettings[$name])) {
			$default = $this->defaultSettings[$name];
		}
		
		return get_option($name, $default);
	}
	
   /**
    * Whether the javascript includes the plugin uses
    * will use the minified version.
    * 
    * @return boolean
    */
	public function isJSMinified()
	{
		if($this->getSetting('use_minified_js') == 'yes') {
			return true;
		}
		
		return false;
	}
	
   /**
    * Checks whether virtual pages are enabled.
    * @return boolean
    */
	public function isVirtualPagesEnabled()
	{
		if($this->getSetting('enable_virtual_pages')=='yes') {
			return true;
		}
		
		return false;
	}
	
   /**
    * @var EVEShipInfo_Admin_UI
    */
	protected $adminUI;
	
	public function getAdminUI()
	{
		if(!isset($this->adminUI)) {
		    $this->loadClass('EVEShipInfo_Admin_UI');
		    $this->adminUI = new EVEShipInfo_Admin_UI($this);
		}
		
		return $this->adminUI;
	}

   /**
    * Converts an associative array to an HTML style attribute value string.
    * 
    * @param string $subject
    * @return string
    */
    public static function array2styleString($subject)
    {
        $tokens = array();
        foreach($subject as $name => $value) {
            $tokens[] = $name.':'.$value;
        }
        
        return implode(';', $tokens);
    }
    
   /**
    * Retrieves the absolute path to the plugin's data folder.
    * @return string
    */
    public function getDataFolder()
    {
    	return $this->getDir().'/data';
    }
    
   /**
    * Retrieves the absolute path to the specified data file.
    * Note: Does not check if it exists.
    * 
    * @param string $file
    * @return string
    */
    public function getDataFilePath($file)
    {
    	return $this->getDataFolder().'/'.$file;
    }
    
    public function hasDataArchive()
    {
    	$file = $this->getDataArchivePath();
    	return file_exists($file);
    }
    
    public function getDataArchivePath()
    {
    	return $this->getDataFilePath('data.zip');
    }
    
   /**
    * Retrieves the version of the currently installed data files
    * (that have been unpacked into the database tables). Can be 
    * null if the data files have not been installed yet.
    * 
    * @return string|null
    */
    public function getDataVersion()
    {
    	return $this->getOption('installed_db_version', null);
    }
    
    public function loadDataFile($file)
    {
    	$type = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    	$path = $this->getDataFilePath($file);
    	
    	if(!file_exists($path)) {
    		return false;
    	}
    	
    	switch($type) {
    		case 'txt':
    		case 'nfo':
    			$data = file_get_contents($path);
    			if(!$data) {
    				return false;
    			}
    			
    			return $data;
    			
    		case 'json':
    			$data = file_get_contents($path);
    			if(!$data) {
    				return false;
    			}
    			
    			return json_decode($data, true);
    			
    		case 'ser':
    			$data = file_get_contents($path);
    			if(!$data) {
    				return false;
    			}
    			 
    			return unserialize($data);
    	}
    	
    	return false;
    }
    
    public function saveDataFile($file, $data)
    {
    	$type = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    	$path = $this->getDataFilePath($file);
    	
    	switch($type) {
    		case 'json':
    			return file_put_contents($path, json_encode($data));
    			
    		case 'ser':
    			return file_put_contents($path, serialize($data));
    	}
    	
    	return false;
    }
    
    public static function bytes2mb($bytes, $decimals=2)
    {
    	return number_format($bytes/1000000, $decimals);
    }
    
    protected static $memoryLimit;
    
   /**
    * Retrieves the memory limit in bytes.
    * @return integer
    */
    public static function getMemoryLimit()
    {
    	if(isset(self::$memoryLimit)) {
    		return self::$memoryLimit;
    	}
    	
    	$memory = strtolower(ini_get('memory_limit'));
    	
    	$units = strtolower(substr($memory, -1));
    	$value = rtrim($memory, 'kgm');
    	$multipliers = array(
    		'k' => 1000,
    		'm' => 1000000,
    		'g' => 1000000000
    	);
    	 
    	$bytes = $value * $multipliers[$units];
    	self::$memoryLimit = $bytes;
    	return $bytes;
    }
    
    protected $cachedDBUpToDate;
    
   /**
    * Checks whether the ships and modules database is
    * up to date, or if it needs to be updated.
    * 
    * @return boolean
    */
    public function isDatabaseUpToDate($force=false)
    {
        if(isset($this->cachedDBUpToDate) && !$force) {
            return $this->cachedDBUpToDate;
        }
        
        $this->cachedDBUpToDate = false;
        
        $installedVersion = $this->getDataVersion();
        $newVersion = $this->loadDataFile('db-version.txt');
        
        if(empty($installedVersion) || empty($newVersion)) {
            return false;
        }
        
        // check that all required database tables exist, 
        // independently of the stored version string to
        // be able to react to external changes.
        if(!$this->dbTablesExist($this->getTableNames())) {
        	return false;
        }
        
        if($installedVersion == $newVersion) {
            $this->cachedDBUpToDate = true;
        }
        
        return $this->cachedDBUpToDate;
    }
    
   /**
    * Retrieves a list of all database tables used by the plugin,
    * with prefix.
    * 
    * @return string[]
    */
    public function getTableNames()
    {
    	$tables = array();
    	foreach($this->tables as $table) {
    		$tables[] = $this->getTableName($table);
    	}
    	
    	return $tables;
    }
        
   /**
    * Handles the initial database installation. Creates the 
    * tables required by the plugin, as well as filling them
    * with the data from the bundled data files.
    * 
    * Existing tables are dropped each time to avoid any
    * possible conflicts, since it's all only static data.
    * 
    * @param bool $replace Whether to replace the existing version if it's the same.
    */
    protected function handle_databaseInstallation($replace=false)
    {
        $this->handle_unpackDataFiles();
        
        if($this->isDatabaseUpToDate(true) && !$replace) {
            return;
        }
    	    	
    	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    	
    	global $wpdb;

    	$this->dropTables();
    	
    	// the SQL comes with placeholders to customize it to
    	// this wordpress' configuration settings.
    	$sqlPlaceholders = array(
    		'{WP_PREFIX}' => $this->getTablePrefix(),
    		'{WP_COLLATE}' => $wpdb->get_charset_collate()
    	);
    	
    	$sql = file_get_contents($this->getDataFilePath('db-structure.sql'));
    	$sql = str_replace(array_keys($sqlPlaceholders), array_values($sqlPlaceholders), $sql);
    	
    	dbDelta($sql);
    	
    	$this->handle_databasePopulate();

    	$this->cachedDBUpToDate = true;
    }
    
    protected function dropTables()
    {
        global $wpdb;
        
        // drop all existing tables for a fresh install
        foreach($this->tables as $table) {
            $wpdb->query('DROP TABLE IF EXISTS '.$this->getTableName($table));
        }
    }
    
    public function getTablePrefix()
    {
    	global $wpdb;
    	return $wpdb->prefix.'eveshipinfo_';
    }
    
    public function getTableName($id)
    {
    	return $this->getTablePrefix().$id;
    }
    
    public function install()
    {
    	$this->handle_activatePlugin();
    }
    
    protected $unpacked = false;
    
    protected function handle_unpackDataFiles()
    {
    	if($this->unpacked) {
    		return;
    	}
    	
    	WP_Filesystem();
    	
    	$dataFile = $this->getDataFilePath('data.zip');
    	$result = unzip_file($dataFile, dirname($dataFile));
    	if(is_wp_error($result)) {
    	    /* @var $result WP_Error */
    		throw new EVEShipInfo_Exception(
    			'Cannot unpack data files', 
    			sprintf(
    			    'Tried unzipping the file [%s]. Native error: #%s %s',
    			    $dataFile,
    			    $result->get_error_code(),
    			    $result->get_error_message()
			    ), 
    			self::ERROR_CANNOT_UNPACK_DATA_FILES
    		);
    	}

    	$this->unpacked = true;
    }
    
   /**
    * Called when a new data file has successfully been uploaded
    * via the data file upload form in the administration screens.
    * 
    * @param bool $replace Whether to replace the existing version if it's the same. 
    */
    public function handle_dataFileUploaded($replace=false)
    {
        $this->handle_databaseInstallation($replace);
    }

    protected $tables = array(
    	'meta',
    	'attributes',
    	'modules',
    	'modules_slots',
    	'ships',
    	'ships_attributes',
    	'ships_groups'
    );
    
   /**
    * Goes through all bundled data files, and inserts the according
    * data into the corresponding database tables. The data files are
    * split up into manageable chunks on purpose, to avoid the high
    * memory overhead of unserializing large files.
    */
    protected function handle_databasePopulate()
    {
    	$queries = array();
    	$queries[] = array(
    		'table' => 'meta',
    		'data' => 'meta',
    		'fields' => array(
    			'metaID=%d',
    			'alias=%s'
    		)
    	);

    	$queries[] = array(
    		'table' => 'attributes',
    		'data' => 'attributes',
    		'fields' => array(
    			'attributeName=%s',
    			'attributeID=%d',
    			'defaultValue=%s',
    			'displayName=%s',
    			'stackable=%d',
				'highIsGood=%d'    			
    		)
    	);
    	 
    	$queries[] = array(
    		'table' => 'modules_slots',
    		'data' => 'modules_slots',
    		'fields' => array(
	    		'slotID=%d',
	    		'alias=%s'
    		)
    	);
    	
    	$queries[] = array(
    		'table' => 'modules',
    		'data' => 'modules',
    		'fields' => array(
	    		'moduleID=%d',
	    		'label=%s',
	    		'metaID=%d',
	    		'slotID=%d'
    		) 
    	);
    	 
    	$queries[] = array(
    		'table' => 'ships_groups',
    		'data' => 'ships_groups',
    		'fields' => array(
    			'groupID=%d',
    			'alias=%s'
    		)
    	);
    	
    	$groups = $this->loadDataJSON('ships_groups');
    	foreach($groups as $group) {
    		$queries[] = array(
    			'table' => 'ships',
    			'data' => 'ships_'.$group['groupID'],
    			'fields' => array(
    				'typeID=%d',
    				'groupID=%d',
    				'typeName=%s',
    				'description=%s',
    				'mass=%d',
    				'volume=%d',
    				'capacity=%d',
    				'raceID=%d',
    				'published=%d',
    				'iconID=%d',
    				'categoryID=%d',
    				'metaID=%d'
    			)
    		);
    		
    		$queries[] = array(
    			'table' => 'ships_attributes',
    			'data' => 'ships_attributes_'.$group['groupID'],
    			'fields' => array(
    				'typeID=%d',
    				'attributeID=%d',
    				'valueInt=%d',
    				'valueFloat=%f'
    			)
    		);
    	}
    	
    	// go through all data files and insert the records
    	foreach($queries as $def) {
    		$query = "INSERT INTO ".$this->getTableName($def['table'])." SET ".implode(', ', $def['fields']);
	    	$entries = $this->loadDataJSON($def['data']);
	    	
    		foreach($entries as $entry) {
	    		$this->dbQuery($query, $entry);
	    	}
    	}
    	
    	$version = $this->loadDataFile('db-version.txt');
    	$this->setOption('installed_db_version', $version);
    }
    
   /**
    * Loads the unserialized data from a JSON data file.
    * @param string $dataFileID
    * @return array|NULL
    */
    protected function loadDataJSON($dataFileID)
    {
    	$path = $this->getDataFilePath($dataFileID.'.json');
    	if(file_exists($path)) {
    		return json_decode(file_get_contents($path), true);
    	}
    	
    	return null;
    }
    
    public static function parseVersion($version)
    {
    	$dateString = substr($version, strpos($version, '-')+1);
    	$year = substr($dateString, 0, 4);
    	$month = substr($dateString, 4, 2);
    	$day = substr($dateString, 6, 2);
    	
    	return array(
    		'version' => $version,
    		'year' => $year,
    		'month' => $month,
    		'day' => $day,
    		'date' => new DateTime($year.'-'.$month.'-'.$day)
    	);
    }
}
	