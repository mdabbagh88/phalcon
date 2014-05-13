<?php
namespace Cloud\Core\Model\App; 
use \Cloud as Cloud, 
    \Cloud\Core,
    \Cloud\Core\Model,
    \Cloud\Core\Model\App;
Class Config extends \Phalcon\Config
{
    use \Cloud\Core\Library\ObjectTrait\CachingObject;

    /**
     * Base directories
     * @var array
     */
    protected $_dirs = array();
    
    /**
     * Loaded Modules
     * @var array
     */
    protected $_modules = array();
    
    /**
     * The current app
     * @var App
     */
    protected $_app = null;
    
    /**
     * Get the app status, and throw an exception if you can't find it
     * @return Ambigous <string, NULL>
     */
    public function whatIsAppStatus()
    {
       if ( ($status = $this->getConfig("application/status", false)) ) {
           return $status; 
       }
       Cloud::throwException("No application status set in " . __METHOD__);
    }
    
    /**
     * Return a specific configuration value
     * @param string $key
     * @param string $path
     * @return string|null
     */
    public function get($key="", $default=null) {
        $path = explode("/", $key);
        $_c = $this;
        foreach($path as $part)
        {
            if (isset($_c->$part)){
                $_c = $_c->$part;
            } else {
                return $default;
            }
        }
        return $_c;
    }
    
    /**
     * Return a specific configuration value
     * @param string $key
     * @param string $path
     * @return string|null
     */
    public function getConfig($key="", $default=null)
    {
        return $this->get($key, $default); 
    }
    
    /**
     * Load all variable configurations and services related to the application
     *  - Config
     *  - Modules
     *  - Session
     *  - Cache
     *  - Database
     * @param App $app The application instance to load
     * @return \Cloud\Core\Model\App\Config
     */
    public function load(App & $app)
    {
        $this->_app = $app; 
        
        $this->loadBase()
            ->loadCache()
            ->loadModelsManager()
            ->loadModelsMetadata()
            ->loadDb()
            ->loadWebsite()
            ->loadModules()
            ;
        if (!$this->_app->isCli()) {
            $this->loadSession();
        }
        return $this; 
    }
    
    /**
     * Load the configuration from APP_PATH/config/*
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadBase()
    {
        $local = $this->getDir("config") . DS . "local.php";
        if (!file_exists($local)) {
            Cloud::throwException("Local configuration file not found in " . __METHOD__); 
        }
        $_local_config = new \Phalcon\Config(require_once($local)); 
        $this->merge($_local_config);
        
        $status = $this->getDir("config") . DS . $this->whatIsAppStatus() . ".php"; 
        $global = $this->getDir("config") . DS . "global.php";
        foreach(array($global, $status) as $file) {
            $_file_config = new \Phalcon\Config(require_once($file));
            $this->merge($_file_config);
        }
        
        /**
         * Allow config values passed in index.php to override those in the configuration files
         * @see CLOUD_ROOT/public/index.php
        */
        $override		= new \Phalcon\Config((array)$this->override);
        $this->merge($override);
    
        unset($this->override);
        return $this;
    }
    
    /**
     * Set the cache service
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadCache()
    {
        $_cloudDataCache = new Cache($this); 
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_DATACACHE, $_cloudDataCache);
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_MODELSCACHE, $_cloudDataCache);
        return $this; 
    }
    
    /**
     * Load the Models MetaData service
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadModelsMetadata()
    {
        // Set Models metadata
        $this->_app->getDi()->setShared(
            ServiceMeta::SERVICE_MODELS_METADATA,
            function()
            {
               return new \Phalcon\Mvc\Model\Metadata\Memory();
            }
        );
        return $this;
    }
    
    /**
     * Get the Models Metadata service
     * @return \Phalcon\Mvc\Model\Metadata\Memory
     */
    public function getModelsMetadata()
    {
        return $this->_app->getDi()->getShared(ServiceMeta::SERVICE_MODELS_METADATA);
    }
    
    /**
     * Load the models manager 
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadModelsManager()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_MODELS_MANAGER, function() {
            return new \Phalcon\Mvc\Model\Manager();
        });
        return $this;
    }
    
    /**
     * Return the models manager service
     * @return \Phalcon\Mvc\Model\Manager
     */
    public function getModelsManager()
    {
        return $this->_app->getDi()->getShared(ServiceMeta::SERVICE_MODELS_MANAGER);
    }

    /**
     * Set the session service
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadSession()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_SESSION, Session::getInstance($this));
        return $this;
    }
    
    /**
     * Load all modules and register the discovered autoloaders
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadModules()
    {
        $modules = $this->_app->getCache()->load($this->_getCacheKey("modules"), function(){
            $module_configs = glob($this->getDir("modules") . DS . '*.xml');
            $modules        = array();
            foreach($module_configs as $module_file)
            {
                $module_data           = simplexml_load_file($module_file);
                if ( "valid" !== ($validate = $this->_validateModuleConfiguration($module_data))) {
                    \Cloud::throwException("Invalid module configuration for file in: " . $module_file . " error: " . $validate);
                }
                $module_name 		   = preg_replace("/.*\/([^\.|\/]*)\.xml$/", "$1", $module_file);  // Converts app/modules/Core --> Core
                if ($this->isModuleActive($module_name, $module_data)) {
                    $modules[$module_name] = array(
                        'namespace'=> "Cloud\\{$module_name}",
                        'directory'=> $this->getDir("code") . DS . $module_name,
                        'version'  => $this->_extract("version", $module_name, $module_data),
                        'routes'   => $this->_extract('routes', $module_name, $module_data),
                        'observers'=> $this->_extract('observers', $module_name, $module_data)
                    );
                }
            }
            return $modules;
        });
        $this->modules  = $modules;
        Cloud::events()->attachObservers($modules);
        Cloud::events()->fire("app_config:after_load_modules", $this);
        return $this; 
    }
    
    public function loadWebsite()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_CURRENT_WEBSITE, App\Website::findByCode($this->getConfig("website_code")));
        Cloud::events()->fire("app_config:after_load_website", $this);
        return $this;
    }
    
    /**
     * 
     * @return \Cloud\Core\Model\App\Website
     */
    public function getWebsite()
    {
        return $this->_app->getDi()->getShared(ServiceMeta::SERVICE_CURRENT_WEBSITE); 
    }
    /**
     * Return the currently loaded application modules
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }
    
    /**
     * Load the database service
     * @throws \Exception
     * @return \Cloud\Core\Model\App\Config
     */
    public function loadDb()
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
	    $this->_app->getDi()->setShared(ServiceMeta::SERVICE_DATABASE, new $class($dbConfig)); 
	    return $this;
    }
    
    public function getDir($dir)
    {
        if (!isset($this->_dirs[$dir])){
            $path = false;
            switch($dir)
            {
            	case "var":
            	    return CLOUD_ROOT . DS . 'var';
            	    break;
            	case "lib":
            	    return CLOUD_ROOT . DS . 'lib';
            	    break;
            	case "code":
            	    return APP_PATH . DS . 'code';
            	    break;
            	case "design":
            	    return APP_PATH . DS . 'design';
            	    break;
            	case "design_compiled":
            	    return CLOUD_ROOT . DS . 'var' . DS . 'volt';
            	    break;
            	case "modules":
            	    return APP_PATH . DS .'etc' . DS . 'modules';
            	    break;
            	case "etc":
            	    return APP_PATH . DS .'etc';
            	    break;
            	case "session":
            	    return CLOUD_ROOT . DS . 'var' . DS . 'session';
            	    break;
            	case "config":
            	    return APP_PATH . DS .'etc' . DS . 'config';
            	    break;
            }
            $this->_dirs[$dir] = $path; 
        }
        return $this->_dirs[$dir];
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
        return $this->getDir("code") . '/' . $module_name . '/' . $entity;
    }
    
    public function isModuleActive($module_name, $simple_xml_node)
    {
        return $this->_extract('active', $module_name, $simple_xml_node) && $this->_isModuleActiveForAppStatus($module_name, $simple_xml_node);
    }
    
    protected function _isModuleActiveForAppStatus($module_name, $simple_xml_node)
    {
        if (!isset($simple_xml_node->module->app_status)) return true;
        switch((string)$simple_xml_node->module->app_status)
        {
        	case App::APP_STATUS_PRODUCTION:
        	    return $this->_app->isProduction();
        	    break;
        	case "!production":
        	    return !$this->_app->isProduction();
        	    break;
        	case App::APP_STATUS_DEVELOPMENT:
        	    return $this->_app->isDevelopment();
        	    break;
        	case App::APP_STATUS_STAGING:
        	    return $this->_app->isStaging();
        	    break;
        	case "all":
        	default:
        	    return true;
        	    break;
        }
    }
    
    protected function _extract($key, $module_name, $simple_xml_node)
    {
        switch($key)
        {
        	case "version":
        	    return (string)$simple_xml_node->module->version;
        	    break;
        	case "active":
        	    return ((string)$simple_xml_node->module->active) == "true";
        	    break;
        	case "routes":
                $routes = array();
                if (!isset($simple_xml_node->routes) || !sizeof($simple_xml_node->routes->children())) return $routes;
                foreach($simple_xml_node->routes->children() as $router => $route_data)
                {
                    $routes[$router] = array(
                    	"frontName" => (string)$route_data->frontName //Make sure to cast to string or it will break on serialization in cache
                    );
                }	    
                return $routes;
                break;
        	case "observers":
        	    $observers = array();
        	    if (!isset($simple_xml_node->observers) || !sizeof($simple_xml_node->observers->children())) return $observers;
        	    foreach($simple_xml_node->observers->children() as $obs_name => $obs)
        	    {
        	        if (isset($obs->area)) {
        	            $_area = (string)$obs->area;
        	            if ($_area != App\Website::AREA_GLOBAL && $_area != $this->_app->getWebsite()->getArea()) {
        	                continue;
        	            }
        	        }
        	        $observers[$obs_name] = array(
        	        	"event" => (string)$obs->event,
        	            "class" => (string)$obs->class,
        	            "method"=> (string)$obs->method
        	        );
        	        
        	    }
        	    return $observers;
        	    break;
        }
    }
    
    protected function _validateModuleConfiguration($simple_xml_node)
    {
        if (!isset($simple_xml_node->module)) {
            return "Module Meta Data Not Set";
        }
        if (!isset($simple_xml_node->module->version) || !isset($simple_xml_node->module->active) ) {
            return "Module Version or Status Missing";
        }
        if (isset($simple_xml_node->routes) && isset($simple_xml_node->routes->frontend)) {
            if (!isset($simple_xml_node->routes->frontend->frontName)) {
                return "Frontend Routes found, but No Frontend Frontname set";
            } 
        }
        if (isset($simple_xml_node->routes) && isset($simple_xml_node->routes->admin)) {
            if (!isset($simple_xml_node->routes->admin->frontName)) {
                return "Admin Routes found, but No Admin Frontname set";
            }
        }
        return "valid"; 
    }
    
    /**
     * Autoload all MVC_ENTITIES from APP_PATH/modules/*
     * @return \Cloud\Core\Model\App
     */
   /* protected function _registerAutoloaders()
    {
        $loader 				= new \Phalcon\Loader();
        $auto_loadable_entities = array(App::MVC_ENTITY_HELPER, App::MVC_ENTITY_CONTROLLER, App::MVC_ENTITY_MODEL, App::MVC_ENTITY_WIDGET, App::MVC_ENTITY_LIBRARY);
        $namespaces				= array();
        foreach(array_keys($this->_modules) as $module_name) {
            foreach($auto_loadable_entities as $entity)
            {
                $namespaces[$this->getModuleEntityNamespace($entity, $module_name)] = $this->getModuleEntityDir($entity, $module_name);
            }
        } 
        $namespaces["Lib"] = $this->getDir("lib"); 
        $loader->registerNamespaces($namespaces);
        $loader->register();
        return $this;
    }*/
    
}