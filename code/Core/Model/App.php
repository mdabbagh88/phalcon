<?php
namespace Cloud\Core\Model;
use Cloud,
	Cloud\Core;
Class App
{
	/**
	 * @var \Phalcon\Mvc\Application $_phalconApp
	 * A singleton for the current phalcon application
	 */
	protected $_phalconApp 		= null;
	
	/**
	 * @var \Phalcon\Mvc\Router
	 * A singleton for the current request router
	 */
	protected $_phalconRouter 	= null;
	
	/**
	 * The autoloader for Phalcon
	 * @var \Phalcon\Loader\
	 */
	protected $_phalconLoader	= null;
	
	/**
	 * @var Phalcon\DI\FactoryDefault $_di
	 * A singleton for the current dependency injector singleton
	 */
	protected $_phalconDi		= null;
	
	/**
	 * @var array $_modules
	 * An array holding the module names and paths
	 */
	protected $_modules			= array();
	
	/**
	 * The configuration object for the application
	 * @var \Phalcon\Config
	 */
	protected $_config			= null;
	
	/**
	 * The current Phalcon backend cache type
	 * @var string $_cacheType
	 */
	protected $_cacheType		= null;
	
	/**
	 * Prefix for cached variables
	 * @var string $_cachePrefix
	 */
	protected $_cachePrefix     = "default|cloud_core_model_app";
	
	/**
	 * Supported frontend cache types
	 * Initialized later
	 * @var array $_cacheFrontendTypes
	 */
	protected $_cacheFrontendTypes = array();
	
	/**
	 * The current layout singleton
	 * @var Cloud\Core\Model\App\Design
	 */
	protected $_design			= null;
	
	/**
	 * Boolean representing whether the cache is enabled or not
	 * @var boolean $_cacheEnabled
	 */
	protected $_cacheEnabled	= null;
	
	/**
	 * Top level directories in the application
	 * @var array $_baseDirs
	 */
	protected $_baseDirs        = array("code", "public", "config", "var", "lib", "design"); 
	
	/**
	 * Whether the application should exit when it encounters an uncaught exception
	 * (You still have the opportunity to implement your own try catch blocks in the code)
	 * @var boolean $_exitOnException
	 */
	protected $_exitOnException  = true;

	/**
	 * For development purposes only
	 */
	protected $_debugCache			= false; 
	protected $_emptyCacheOnStartup = false;
	
	const APP_STATUS_DEVELOPMENT	= "development";
	const APP_STATUS_STAGING		= "staging";
	const APP_STATUS_PRODUCTION 	= "production";
	
	const MVC_MODULE			= "Module";
	const MVC_DEFAULT_MODULE	= "Core"; 
	
	const MVC_ENTITY_CONTROLLER = "Controller";
	const MVC_ENTITY_MODEL	    = "Model";
	const MVC_ENTITY_WIDGET		= "Widget";
	const MVC_ENTITY_HELPER	    = "Helper";
	const MVC_ENTITY_LIBRARY    = "Library"; 
	
	const MVC_ENTITY_VIEW		= "views"; 
	const MVC_ENTITY_LAYOUT		= "layouts"; 
	
	/**
	 * Constants for Phalcon's cache
	 */
	const CACHE_BACKEND_MEMCACHED = "Memcache";
	const CACHE_BACKEND_FILE	  = "File";
	const CACHE_FRONTEND_OUTPUT	  = "Output";
	const CACHE_FRONTEND_DATA	  = "Data";
	
	/**
	 * Constants for sessions
	 */
	const SESSION_SAVE_MEMCACHED  = "Memcache";
	const SESSION_SAVE_FILE		  = "File";
	/**
	 * Only used for file sessions. Prefixes the string to each variable in the session
	 * @var string SESSION_PREFIX
	 */
	const SESSION_PREFIX		  = "cloud-phalcon-";
	const SESSION_DEFAULT_NAME	  = "cloud9living"; 
	
	/**
	 * Phalcon DI service keys
	 */
	const SERVICE_MODELS_CACHE    = "modelsCache";
	const SERVICE_SESSION         = "session";
	const SERVICE_ROUTER          = "router";
	const SERVICE_URL             = "url";
	const SERVICE_DATABASE        = "database";
	
	public function __construct($options=array())
	{
		$this->_config = new \Phalcon\Config(array("override" => $options)); 
		try 
		{
			$this->_initialize();
		}
		catch(\Exception $e)
		{
			$this->_handleException($e); 
		}
	}
	
	/**
	 * Initialize the application
	 * This loads configuration, sets error handling, intializes the cache, 
	 * registers all modules, registers autoloaders for modules + libraries
	 * Connects to the database and registers phalcon services
	 */
	protected function _initialize()
	{
	    $this->_setPaths()
	         ->_loadConfiguration()
	         ->_setErrorHandling()
	         ->_registerCacheService() //Register the cache service before modules/core services as some of the work from these can be cached
	         ->_registerModules()
	         ->_registerAutoloaders()
             ->_registerCoreServices()
             ;
	}
	
	public function run()
	{
		try 
		{
			echo $this->getPhalconApp()->handle()->getContent();
		}
		catch(\Exception $e)
		{
			$this->_handleException($e); 
		}
	}
	
	/**
	 * Return the design singleton for the application
	 * @return \Cloud\Core\Model\App\Design
	 */
	public function getDesign()
	{
		if (is_null($this->_design))
		{
			$this->_design = new \Cloud\Core\Model\App\Design(); 
		}
		return $this->_design;
	}
	
	/**
	 * Get a specific module's [controller, view, helper, model, widget]'s namespace
	 * @param string $entity
	 * @param string $module_name
	 * @return string
	 */
	public function getModuleEntityNamespace($entity, $module_name)
	{
		return 'Cloud\\' . $module_name . '\\' . $entity;
	}
	
	/**
	 * Get a specific module's [controller, view, helper, model, widget]'s folder
	 * @param string $entity
	 * @param string $module_name
	 * @return string
	 */
	public function getModuleEntityDir($entity, $module_name)
	{
		return \Cloud::registry("code_path") . '/' . $module_name . '/' . $entity;
	}
	
	/**
	 * Return the singleton for the phalcon app
	 * @return \Phalcon\Mvc\Application
	 */
	public function getPhalconApp()
	{
		if (is_null($this->_phalconApp))
		{
			$this->_phalconApp = new \Phalcon\Mvc\Application($this->getPhalconDi());
		}
		return $this->_phalconApp;
	}
	
	/**
	 * Return the singleton for the phalcon router
	 * @return \Phalcon\Mvc\Router
	 */
	public function getPhalconRouter()
	{
		if (is_null($this->_phalconRouter))
		{
			$this->_phalconRouter = new \Phalcon\Mvc\Router(false);
		}
		return $this->_phalconRouter;
	}
	
	public function setPhalconRouter(\Phalcon\Mvc\Router $router)
	{
		$this->_phalconRouter = $router;
		return $this; 	
	}
	
	public function getPhalconLoader()
	{
		if (is_null($this->_phalconLoader))
		{
			$this->_phalconLoader = new \Phalcon\Loader();
		}
		return $this->_phalconLoader;
	}
	
	/**
	 * Return the Phalcon Dependency Injector Singleton
	 * @return \Phalcon\DI\FactoryDefault
	 */
	public function getPhalconDi()
	{
		if (is_null($this->_phalconDi)) {
			$this->_phalconDi = new \Phalcon\DI\FactoryDefault();
		}
		return $this->_phalconDi;
	}
	
	/**
	 * Either get the entire \Phalcon\Config object or a specifc value. Return NULL if the key doesn't exist
	 * @param string $key
	 * @return \Phalcon\Config|NULL|string
	 */
	public function getConfig($key=null, $default=null)
	{
		if (is_null($key)) 
			return $this->_config;
		$path = explode("/", $key);
		$_c = $this->_config; 
		foreach($path as $part)
		{
			if (isset($_c->$part)){
				$_c = $_c->$part; 
			} else {
				return $default;
			}
		}
		return (string)$_c; 
	}
	
	/**
	 * Return if the current application is running on the test, production, or staging server
	 * **PUT YOUR OWN LOGIC HERE**
	 * @param string $type
	 * @return boolean
	 */
	public function isServer($type="production")
	{
	    if ($this->isCli()) {
	        return $this->getConfig("application/status", self::APP_STATUS_DEVELOPMENT) == $type; 
	    }
		switch($type)
		{
			case "development":
				$hostname = $_SERVER["HTTP_HOST"];
				if (preg_match("/^(www\.)?local\..*$/i", $hostname)) {
					return true; 
				}
				break;
		}
	}
	
	/**
	 * Return whether the application is in production mode
	 * @return boolean
	 */
	public function isProduction()
	{
	    return $this->getConfig("application/status") == self::APP_STATUS_PRODUCTION;
	}
	
	/**
	 * Alias of \Cloud\Model\Core\App::isProduction()
	 * @return boolean
	 */
	public function isLiveMode()
	{
	    return $this->isProduction();
	}
	
	/**
	 * Determine if the current application is being run via CLI
	 * @return boolean
	 */
	public function isCli()
	{
		return php_sapi_name() == 'cli';
	}
	
	/**
	 * Flag specifiying whether cache is turned on
	 * @return boolean
	 */
	public function isCacheEnabled()
	{
		return $this->_cacheEnabled; 
	}
	
	/**
	 * Set cache enabled/disabled
	 * @param unknown $flag
	 * @return \Cloud\Core\Model\App
	 */
	public function setCacheEnabled($flag)
	{
		$this->_cacheEnabled = $flag;
		return $this; 
	}
	
	/**
	 * Load a variable piece of data from the cache. You may optionally pass in a callback which will be invoked
	 * 	if the requested key is NOT found in the cache 
	 *     (this saves you from having to do a separate "saveCache" call)
	 * @param string $key
	 * @param function $callback
	 * @return boolean|mixed
	 */
	public function loadCache($key, $callback=false)
	{
		$_cacheInstance = $this->getDataCache();
		$_cached_value  = null;
		if (!$this->isCacheEnabled() || is_null( ($_cached_value = $_cacheInstance->get($key)) )) {
			$this->debugCache("CACHE MISS: ");
			if ($callback) {
				$this->debugCache("\tRunning callback to load cache: " . $key);
				$_cached_value = $callback();
				$this->debugCache("\tRetrieved value from callback: " . print_r($_cached_value, 1));
				if($this->isCacheEnabled()){
					$this->saveCache($key, $_cached_value);
				}
			}
		} else {
		    $this->debugCache("CACHE HIT: " . $key);
		}
		return $_cached_value;
	}
	
	/**
	 * Get the service singleton for the data cache type
	 * @return \Phalcon\Cache\Backend
	 */
	public function getDataCache()
	{
	    return $this->getPhalconDi()->getShared("cache".self::CACHE_FRONTEND_DATA);
	}
	
	/**
	 * Save a specific key/value pair in the DATA cache
	 * @param string $key
	 * @param mixed $value
	 * @return \Cloud\Core\Model\App
	 */
	public function saveCache($key, $value)
	{
	    if (!$this->isCacheEnabled()) return $this; 
	    
		$this->debugCache("CACHE SAVE: " . $key . "<br/>\n");
		$_cacheInstance = $this->getDataCache();
		$_cacheInstance->save($key, $value);
		
		return $this; 
	}
	
	/**
	 * Remove all cache entries for a specific frontend type
	 * @param string $frontendType
	 * @return \Cloud\Core\Model\App
	 */
	public function cleanCache($frontendType=self::CACHE_FRONTEND_DATA)
	{
	    $cache_instance = $this->getPhalconDi()->getShared("cache".$frontendType);
		foreach($cache_instance->queryKeys() as $key) {
			$cache_instance->delete($key); 
		}
		return $this; 
	}
	
	/**
	 * Clean all caches
	 * @return \Cloud\Core\Model\App
	 */
	public function emptyCache()
	{
		foreach($this->_cacheFrontendTypes as $type) {
			$this->cleanCache($type); 
		}
		return $this; 
	}
	
	/**
	 * Print a message if cache debugging is allowed
	 * @param string $message
	 * @return \Cloud\Core\Model\App
	 */
	public function debugCache($message)
	{
	    if ($this->getDebugCache()) {
	        print "<pre>"; 
	        print $message . "\n";
	        print "</pre>"; 
	    }
	    return $this; 
	}
	
	/**
	 * Return the appropraite cache type from Phalcon based on the frontend and backend cache
	 * @param string $frontendType
	 * @param string $backendType
	 * @return false|Phalcon\Cache\Backend\Libmemcached|Phalcon\Cache\Backend\File
	 */
	public function getCacheInstance($frontendType=false)
	{
		switch($this->_cacheType)
		{
			case self::CACHE_BACKEND_MEMCACHED:
				return self::getMemcachedCacheInstance($frontendType);
				break;
			case self::CACHE_BACKEND_FILE:
			default:
				return self::getFileCacheInstance($frontendType);
				break;
		}
	}
	
	/**
	 *
	 * @param unknown $frontendType
	 * @return Phalcon\Cache\Backend\Libmemcached
	 */
	public function getMemcachedCacheInstance($frontendType)
	{
	    $frontName  = "\Phalcon\Cache\Frontend\\$frontendType";
		$frontCache = new $frontName(array(
			'lifetime' => $this->getConfig("application/cache/lifetime")
		));
		$cache 	    = new \Phalcon\Cache\Backend\Memcache($frontCache, array(
				"host" => $this->getConfig("application/cache/host"),
				"port" => $this->getConfig("application/cache/port")
		));
		return $cache; 
	}
	
  /**
	* Get the file cache instance
	* @param string $frontendType
	* @return Phalcon\Cache\Backend\File
	*/
	public function getFileCacheInstance($frontendType)
	{
		$frontName  = "\Phalcon\Cache\Frontend\\$frontendType";
		$frontCache = new $frontName(array(
			'lifetime' => $this->getConfig("application/cache/lifetime")
		));
		if (!file_exists($this->getConfig('application/cache/cacheDir'))) {
			mkdir($this->getConfig('application/cache/cacheDir'),0755,true); 
		}
		$cache 	    = new \Phalcon\Cache\Backend\File($frontCache, array(
						"cacheDir" => $this->getConfig('application/cache/cacheDir')
		));
		return $cache; 
	}
	
	public function setDebugCache($debug=true)
	{
		\Cloud::register("__cache/debug", $debug);
	}
	
	public function getDebugCache()
	{
		return \Cloud::registry("__cache/debug");
	}
	
	/**
	 * Load the configuration from APP_PATH/config/*
	 * @return \Cloud\Core\Model\App
	 */
	protected function _loadConfiguration()
	{
		$all = glob(Cloud::registry("config_path") . '/*.php');
		foreach($all as $path)
		{
			require_once($path); 
		}
		$current_config = $this->getConfig(); 
		/**
         * Allow config values passed in index.php to override those in the configuration files
         * @see APP_PATH/public/index.php
		 */
		$override		= new \Phalcon\Config((array)$current_config->override);
		$current_config->merge($override); 
		
		unset($current_config->override); 
		return $this; 
	}
	
	/**
	 * Register all modules from APP_PATH/code/*
	 * This function stores the result of the glob / parse call in cache
	 * @return \Cloud\Core\Model\App
	 */
	protected function _registerModules()
	{
		require_once(Cloud::registry("app_path") . '/code/Core/Model/AbstractModule.php'); //Have to include this by hand		
		$modules = $this->loadCache($this->_cachePrefix . '-' . 'modules', function(){
			$directories = glob(Cloud::registry("app_path") . '/code/*', GLOB_ONLYDIR);
			$modules     = array(); 
			foreach($directories as $module_dir)
			{
				$module_name 		   = preg_replace("/.*\/([^\/]*)$/", "$1", $module_dir);  // Converts app/modules/Core --> Core
				$modules[$module_name] = array(
					'className' => "Cloud\\{$module_name}\\Module",
					'path'	    => $module_dir . '/' . \Cloud\Core\Model\App::MVC_MODULE . '.php',
					'directory' =>  $module_dir
				);
			}
			return $modules; 
		}); 
		$this->getPhalconApp()->registerModules($modules);
		$this->_modules = $modules;
		return $this; 
	}
	
	/**
	 * Register services required to run the app here.
	 * Please encapsulate non-essential services elsewhere in the application
	 * @return \Cloud\Core\Model\App
	 */
	protected function _registerCoreServices()
	{
	    $this->_registerDatabaseService();
		$this->_registerRoutesService(); 
		$this->_registerUrlService();
		$this->_registerSessionService();
		return $this; 
	}
	
	/**
	 * Register the database adapter
	 * @throws \Exception
	 * @return \Cloud\Core\Model\App
	 */
	protected function _registerDatabaseService()
	{
	    $adapter = $this->getConfig("database/adapter", "pdo_mysql");
	    $suffix  = str_replace(" ", "\\", ucwords(str_replace("_", " ", $adapter))); 
	    $class   = "\Phalcon\Db\Adapter\\{$suffix}"; 
	    if (!class_exists($class)) {
	        $class = "\Lib" . $class; 
	        if (!class_exists($class))
	           throw new \Exception("Invalid database adapter: " . $suffix . " specified. Not found in Phalcon or lib/Phalcon");
	    } 
	    $required_config_values = array("database/host", "database/username", "database/password", "database/dbname");
	    $dbConfig               = array(); 
	    foreach($required_config_values as $cv) {
	        if (!strlen($this->getConfig($cv, ""))) {
	            throw new \Exception("No configuration value for: " . $cv . " supplied"); 
	        }
	        $dbKey            = preg_replace("/(.*)\/([^\/]*)$/i", "$2", $cv); 
	        $dbConfig[$dbKey] = $this->getConfig($cv); 
	    }
	    $dbConfig["options"]  = array(
	        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
	    );
	    $this->getPhalconDi()->setShared(self::SERVICE_DATABASE, new $class($dbConfig));
	    return $this; 
	}
	
	protected function _registerRoutesService()
	{
		$di 	= $this->getPhalconDi(); 
		$self   = $this; 
		$router = $this->loadCache($this->_cachePrefix.'-'.'router', function(){
			$router = $this->getPhalconRouter();
			foreach($this->_modules as $moduleName => $moduleData)
			{
				if (file_exists($moduleData["directory"] . DS . 'routes.php'))
					require_once($moduleData["directory"] . DS . 'routes.php');
			}
			return $router; 
		});
		$this->setPhalconRouter($router);
		$di->setShared(self::SERVICE_ROUTER, $router);
		return $this; 
	}
	
	protected function _registerUrlService()
	{
		/**
		 * The URL component is used to generate all kind of urls in the application
		 */
		$this->getPhalconDi()->set(self::SERVICE_URL, function(){
			$url = new \Phalcon\Mvc\Url(); 
			$url->setBaseUri($this->getConfig("application/base_uri"));
			return $url;
				
		});
		return $this; 
	}
	
	protected function _registerSessionService()
	{
		$this->getPhalconDi()->setShared("session", function () {
			switch($this->getConfig("application/session/save_path"))
			{
				case \Cloud\Core\Model\App::SESSION_SAVE_MEMCACHED:
					$session = new \Lib\Phalcon\Session\Adapter\Memcache(array(
						"host" 		=> $this->getConfig("application/session/host"),
						"port" 		=> $this->getConfig("application/session/port"),
						"lifetime" 	=> $this->getConfig("application/session/lifetime")
					));
					break;
				case \Cloud\Core\Model\App::SESSION_SAVE_FILE:
					$session = new \Phalcon\Session\Adapter\Files(array(
						"lifetime"	=> $this->getConfig("application/session/lifetime"),
						"uniqueId"  => \Cloud\Core\Model\App::SESSION_PREFIX
					));
					if (!file_exists(\Cloud::registry("var_path") . DS . "session")) {
						mkdir(\Cloud::registry("var_path") . DS . "session", 0755, true); 
					}
					ini_set("session.save_handler", "files"); //Manually force this. Phalcon assumes this is the default, so if memcached is actually the default you will have issues
					session_save_path(\Cloud::registry("var_path") . DS . "session"); 
					break;
			}
			$s_name		= $this->getConfig("application/session/name", self::SESSION_DEFAULT_NAME); 
			$s_lifetime = $this->getConfig("application/session/lifetime", 3600); 
			$s_path		= $this->getConfig("application/session/cookie_path", "/");
			$s_domain   = $this->getConfig("application/session/cookie_domain", "");
			$s_secure	= false;
			$s_httponly = true; 
			ini_set("session.gc_maxlifetime", $s_lifetime); //This should matchup to the cookie lifetime	
			session_set_cookie_params(
				$s_lifetime, $s_path, $s_domain, $s_secure, $s_httponly
			); 
			session_name($s_name);
		    $session->start();
		    return $session;
		});
		return $this; 
	}
	
	/**
	 * Initialize the cache backend type, cache debug settings AND models ORM cache
	 * @return \Cloud\Core\Model\App
	 */
	protected function _registerCacheService()
	{
		$this->_cacheType 	 = $this->getConfig("application/cache/backend") ? $this->getConfig("application/cache/backend") : self::CACHE_BACKEND_FILE;
		$this->_cacheEnabled = $this->getConfig("application/cache/enabled");
		if (!$this->isProduction() && ($this->_debugCache || $this->_debugCacheRequested())) {
			$this->setDebugCache(true); 
			$this->debugCache("Debug cache request received");
		}
		$this->debugCache("Initializing model cache"); 
		$this->getPhalconDi()->set(self::SERVICE_MODELS_CACHE, $this->getCacheInstance(self::CACHE_FRONTEND_DATA));
		$this->_cacheFrontendTypes = array(self::CACHE_FRONTEND_DATA, self::CACHE_FRONTEND_OUTPUT);
		foreach($this->_cacheFrontendTypes as $type)
		{
		    $this->debugCache("Initializing " . $type . " cache");
		    $this->getPhalconDi()->setShared("cache" . $type, $this->getCacheInstance($type));
		}
		if (!$this->isProduction() && ($this->_emptyCacheOnStartup || $this->_emptyCacheRequested())) {
		    $this->debugCache("Empty cache request received");
		    $this->emptyCache();
		}
		return $this; 
	}
	
	/**
	 * Autoload all MVC_ENTITIES from APP_PATH/modules/*
	 * @return \Cloud\Core\Model\App
	 */
	protected function _registerAutoloaders()
	{
		$loader 				= $this->getPhalconLoader();
		$auto_loadable_entities = array(App::MVC_ENTITY_HELPER, App::MVC_ENTITY_CONTROLLER, App::MVC_ENTITY_MODEL, App::MVC_ENTITY_WIDGET, App::MVC_ENTITY_LIBRARY);
		$namespaces				= array();
		foreach(array_keys($this->_modules) as $module_name) {
			foreach($auto_loadable_entities as $entity)
			{
				$namespaces[$this->getModuleEntityNamespace($entity, $module_name)] = $this->getModuleEntityDir($entity, $module_name);
			}
		}
		$namespaces["Lib"] = Cloud::registry("lib_path"); 
		$loader->registerNamespaces($namespaces);
		$loader->register();
		return $this;
	}
	
	/**
	 * Set some handy registry paths for frequently accessed areas of the system
	 * @return \Cloud\Core\Model\App
	 */
	protected function _setPaths()
	{
		Cloud::register("app_path", APP_PATH);
		foreach($this->_baseDirs as $p)
		{
			Cloud::register("{$p}_path", Cloud::registry("app_path") . "/{$p}");
		}
		return $this; 
	}
	
	/**
	 * Return whether user attached ?clearCache to the url
	 * @return boolean
	 */
	protected function _emptyCacheRequested()
	{
	    return isset($_GET["clearCache"]);
	}
	
	/**
	 * Return whether the user attached ?debugCache to the url
	 * @return boolean
	 */
	protected function _debugCacheRequested()
	{
	    return isset($_GET["debugCache"]);
	}
	
	/**
	 * Set the error handler for the application as well as error level
	 * Based on the error level, it may print errors to the screen.
	 * It will always log the error using \Cloud::logError
	 * @return \Cloud\Core\Model\App
	 */
	protected function _setErrorHandling()
	{
	    switch($this->getConfig()->application->status)
	    {
	    	case self::APP_STATUS_PRODUCTION:
	    	    ini_set("display_errors", false);
	    	    ini_set("display_startup_errors", false);
	    	    error_reporting(0);
	    	    break;
	    	case self::APP_STATUS_DEVELOPMENT:
	    	case self::APP_STATUS_STAGING:
	    	default:
	    	    ini_set("display_errors", true);
	    	    ini_set("display_startup_errors", true);
	    	    error_reporting(E_ALL);
	    	    break;
	    }	
		set_error_handler(function ($errno, $errstr, $errfile, $errline) 
		{
			$error_number = 'ERR-'.$this->_getRandomString();
			$errno        = $this->_errorLevelToString($errno); 
			$error_message= "[{$errno}] - {$errstr} File: $errfile Line: $errline";
			\Cloud::logError($error_message, $error_number); 
			switch($this->getConfig("application/status"))
			{
				case self::APP_STATUS_PRODUCTION:
					print "<table style='width:500px; margin:20px auto; border:1px solid #000; text-align:center; padding:10px;'>";
						print "<tr><td><strong>The Application Has Encountered An Error</strong></td></tr>";
						print "<tr><td>Error Number: <em>$error_number</em></td></tr>"; 
					print "</table>";
					break;
				case self::APP_STATUS_DEVELOPMENT:
				case self::APP_STATUS_STAGING:
				default:
					$e = new \Exception(); 
					echo '<pre>'; 
					print "Application Error: " . $error_number . "\n\n";
					if (!(error_reporting() & $errno)) {
						print $error_message; 
					}
					
					print "\n\nTrace\n"; 
					print $e->getTraceAsString();
					echo '</pre>'; 
					break;
			}
		}); 
		return $this; 
	}
	
	protected function _handleException(\Exception $e)
	{
		$exception_number = 'ERR-'.$this->_getRandomString();
		$exception_message= $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n\n" . $e->getTraceAsString(); 
		\Cloud::logException($exception_message, $exception_number);
		switch($this->getConfig("application/status"))
		{
			case self::APP_STATUS_PRODUCTION:
				print "<table style='width:500px; margin:20px auto; border:1px solid #000; text-align:center; padding:10px;'>";
				print "<tr><td><strong>The Application Has Encountered An Exception</strong></td></tr>";
				print "<tr><td>Exception Number: <em>$exception_number</em></td></tr>";
				print "</table>";
				break;
			case self::APP_STATUS_DEVELOPMENT:
			case self::APP_STATUS_STAGING:
			default:
				echo '<pre>';
				print "Application Exception: " . $exception_number . "\n";
				print $exception_message; 
				echo '</pre>';
				break;
		}
		if ($this->_exitOnException) {
		    exit; 
		}
		return $this; 
	}
	
	protected function _errorLevelToString($intval, $separator='|')
	{
	    $errorlevels = array(
	        2047 => 'E_ALL',
	        1024 => 'E_USER_NOTICE',
	        512 => 'E_USER_WARNING',
	        256 => 'E_USER_ERROR',
	        128 => 'E_COMPILE_WARNING',
	        64 => 'E_COMPILE_ERROR',
	        32 => 'E_CORE_WARNING',
	        16 => 'E_CORE_ERROR',
	        8 => 'E_NOTICE',
	        4 => 'E_PARSE',
	        2 => 'E_WARNING',
	        1 => 'E_ERROR');
	    $result = '';
	    foreach($errorlevels as $number => $name)
	    {
	        if (($intval & $number) == $number) {
	            $result .= ($result != '' ? $separator : '').$name; }
	    }
	    return $result;
	}
	
	/**************************************************
	 * Unfortunately this block of code has to live here.
	 * It is duplicated in \Cloud\Core\Helper\Data which is the correct location.
	 * We need it here on the event that an error/exception happens before the autoloader registers each module
	 * To get a random string elsewhere please use the function in the core helper
	 * 
	 **************************************************/
	/*
	 * For random number generation
	*/
	const CHARSET_ALPHANUM 		= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	const CHARSET_ALPHANUM_CASE = 'abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	protected function _getRandomString($valid_chars=self::CHARSET_ALPHANUM, $length=8)
    {
        // start with an empty random string
        $random_string = "";
    
        // count the number of chars in the valid chars string so we know how many choices we have
        $num_valid_chars = strlen($valid_chars);
    
        // repeat the steps until we've created a string of the right length
        for ($i = 0; $i < $length; $i++)
        {
        // pick a random number from 1 up to the number of valid chars
        $random_pick = mt_rand(1, $num_valid_chars);
    
        // take the random character out of the string of valid chars
        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
        $random_char = $valid_chars[$random_pick-1];
    
        // add the randomly-chosen char onto the end of our string so far
        $random_string .= $random_char;
        }
    
        // return our finished random string
        return $random_string;
    }
}