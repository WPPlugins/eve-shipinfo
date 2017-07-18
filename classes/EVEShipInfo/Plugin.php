<?php

abstract class EVEShipInfo_Plugin implements EVEShipInfo_PluginInterface
{
    protected $dir;
    
    protected $url;
    
	public function getURL()
	{
		return $this->url;
	}
	
	public function getHomepageURL()
	{
		return 'http://www.aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin/';
	}
	
	public function getHomepageDownloadURL()
	{
		return $this->getHomepageURL().'/download.php';
	}
	
	protected $galleryURL;
	
	public function getGalleryURL()
	{
		if(isset($this->galleryURL)) {
			return $this->galleryURL;
		}
		
		$upload_dir = wp_upload_dir();
		$path = $upload_dir['basedir'].'/eve-shipinfo';
		if(is_dir($path)) {
			
			return $upload_dir['baseurl'].'/eve-shipinfo';
		} else {
			$url = $this->url.'/gallery';
		}
		
		$this->galleryURL = $url;
		return $url;
	}
	
	protected $galleryPath;
	
	public function getGalleryPath()
	{
		if(isset($this->galleryPath)) {
			return $this->galleryPath;
		}
		
		$upload_dir = wp_upload_dir();
		$path = $upload_dir['basedir'].'/eve-shipinfo';
		
		if(!is_dir($path)) {
			$path = $this->dir.'/gallery';
		}
		
		$this->galleryPath = $path;
		return $path;
	}
    
    public function getDir()
	{
	    return $this->dir;
	}
	
    public function loadClass($className)
    {
    	if(class_exists($className)) {
    		return;
    	}
    
    	$file = $this->getDir().'/classes/'.str_replace('_', '/', $className).'.php';
    	require_once $file;
    }
    
    public function registerError($errorMessage, $errorCode)
    {
    	trigger_error('EVEShipInfo plugin error #'.$errorCode.': '.$errorMessage, E_USER_ERROR);
    }
    
    protected $collection;
    
    /**
     * Returns the ships collection instance that can be used to
     * access the entire ships collection and retrieve information
     * about ships.
     *
     * @return EVEShipInfo_Collection
     */
    public function createCollection()
    {
    	if(!isset($this->collection)) {
    		$this->loadClass('EVEShipInfo_Collection');
    		$this->collection = new EVEShipInfo_Collection($this);
    	}
    		
    	return $this->collection;
    }
    
    public function getImageWidth()
    {
    	return 750;
    }
    
    public function getCSSName($part)
    {
    	return 'shipinfo-'.$part;
    }
    
    public function compileAttributes($attributes)
    {
    	$tokens = array();
    	foreach($attributes as $name => $value) {
    		if($value===null) {
    			continue;
    		}
    			
    		$value = str_replace('&#039;', "'", htmlspecialchars($value, ENT_QUOTES));
    			
    		$tokens[] = $name.'="'.$value.'"';
    	}
    
    	if(!empty($tokens)) {
    		return ' '.implode(' ', $tokens).' ';
    	}
    	
    	return '';
    }
    
    public function compileStyles($styles)
    {
    	$tokens = array();
    	foreach($styles as $name => $value) {
    		if($value===null) {
    			continue;
    		}
    		$tokens[] = $name . ':' . $value;	
    	}
    	
    	if(!empty($tokens)) {
	    	return ' '.implode(';', $tokens).' ';
    	}
    	
    	return '';
    }
    
    public function getOption($name, $default='')
    {
    	$internalName = $this->resolveInternalOptionName($name);
    	
    	$data = get_option($internalName, false);
    	if($data===false) {
    		add_option($internalName, $default);
    		$data = $default;
    	}
    	
    	return $data;
    }
    
   /**
    * Sets a plugin option that is persisted in the database, using
    * the wordpress options table.
    *  
    * @param string $name
    * @param string $value
    * @throws EVEShipInfo_Exception
    */
    public function setOption($name, $value)
    {
    	$this->getOption($name); // to initialize it
    	update_option($this->resolveInternalOptionName($name), $value);
    }
    
   /**
    * Clears a plugin option.
    * @param string $name
    */
    public function clearOption($name)
    {
    	$internalName = $this->resolveInternalOptionName($name);
    	delete_option($internalName);
    }
    
    protected function resolveInternalOptionName($name)
    {
    	$internalName = 'eveshipinfo_'.$name;
    	 
    	// automatically use an md5 hash for option names that are too long
    	// for the available name length
    	if(strlen($internalName) > 64) {
    		$internalName = 'eveshipinfo_'.md5($name);
    	}
    	
    	return $internalName;
    }
    
    public function relativizePath($path)
    {
    	$path = str_replace('\\', '/', $path);
    	$root = str_replace('\\', '/', get_home_path());
    	
    	return str_replace($root, '', $path);
    }
    
   /**
    * Retrieves the plugin version. Note that this only works
    * in the administration, not in the frontend.
    * 
    * @return string 0 if not in the administration
    */
    public function getVersion()
    {
    	if(is_admin()) {
    		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    		$data = get_plugin_data($this->getDir().'/eve-shipinfo.php', false);
    		return $data['Version'];
    	}
    	
    	return '0';
    }

    public function dbQuery($query, $params=array(), $dump=false)
    {
    	global $wpdb;

        if(!empty($params)) {
    		$query = $wpdb->prepare($query, $params);
    	}
    	
    	if($dump) {
    		echo '<pre style="background:#fff;color:#000;padding:17px;">'.print_r($query, true).'</pre>';
    	}
    	
    	return $wpdb->query($query);
    }
    
   /**
    * Checks if the specified table exists in the wordpress
    * database. Can be specified either with or without the 
    * WP table prefix.
    * 
    * @param string $table
    * @return boolean
    */
    public function dbTableExists($table)
    {
    	return $this->dbTablesExist(array($table));
    }
    
    protected $cachedDBTables;
    
   /**
    * Checks if the specified tables exist in the wordpress database.
    * The table names can be specified either with or without the
    * WP table prefix. All of the tables must exist.
    * 
    * @param string[] $tables
    * @return boolean
    */
    public function dbTablesExist($tables)
    {
    	global $wpdb;
   		$length = strlen($wpdb->prefix);
   		
   		// retrieve the tables list from the database, and
   		// cache the list for fast access. The names are 
   		// stored without the WP prefix.
    	if(!isset($this->cachedDBTables)) {
    		$this->cachedDBTables = array();
	    	$items = $this->dbFetchAll("SHOW TABLES");
	    	$total = count($items);
	    	for($i=0; $i < $total; $i++) {
	    		$table = substr($items[$i][key($items[$i])], $length);
	    		$this->cachedDBTables[$table] = true; // to be able to use isset instead of in_array (faster) 
	    	}
    	}
    	
    	foreach($tables as $table) {
    		if(substr($table, 0, $length) == $wpdb->prefix) {
    			$table = substr($table, $length);
    		}
    		
    		if(!isset($this->cachedDBTables[$table])) {
    			return false;
    		}
    	}
    	 
    	return true; 
    }

   /**
    * Fetches the specified data key from the result set, if any.
    * 
    * @param string $key
    * @param string $query
    * @param array $params
    * @return string|NULL
    */
    public function dbFetchKey($key, $query, $params=array())
    {
    	$entry = $this->dbFetch($query, $params);
    	if(isset($entry[$key])) {
    		return $entry[$key];
    	}
    	
    	return null;
    }
    
    public function dbFetch($query, $params=array(), $dump=false)
    {
    	global $wpdb;
    	 
    	if(!empty($params)) {
    		$query = $wpdb->prepare($query, $params);
    	}
    	
    	if($dump) {
            echo '<pre style="background:#fff;color:#000;padding:17px;">'.print_r($query, true).'</pre>';
    	}
    	 
    	return $wpdb->get_row($query, ARRAY_A);
    }
    
    public function dbFetchAll($query, $params=array())
    {
    	global $wpdb;
    	
    	if(!empty($params)) {
    		$query = $wpdb->prepare($query, $params);
    	}
    	
    	return $wpdb->get_results($query, ARRAY_A);
    }
    
    public function dbFetchAllKey($key, $query, $params=array())
    {
    	$results = $this->dbFetchAll($query, $params);
    	
    	$result = array();
    	$total = count($results);
    	for($i=0; $i < $total; $i++) {
    		if(isset($results[$i][$key])) {
    			$result[] = $results[$i][$key];
    		}
    	}
    	
    	return $result;
    }
    
   /**
    * {@inheritDoc}
    * @see EVEShipInfo_PluginInterface::isAdmin()
    */
    public function isAdmin()
    {
    	return is_admin();
    }
}

interface EVEShipInfo_PluginInterface
{
	public function getDir();
	
	public function loadClass($className);
	
	public function registerError($errorMessage, $errorCode);
	
	public function createCollection();
	
	public function getImageWidth();
	
	public function getCSSName($part);
	
	public function getGalleryPath();

	public function getGalleryURL();
	
	public function compileAttributes($attributes);
	
   /**
    * Check if currently in the administration interface.
    * @return boolean
    */
	public function isAdmin();
}