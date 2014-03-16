<?php
/**
 * @class Cloud\Cloud 
 * @author Alan Barber
 * This is a static class that allows access to all the operations of the application. The bootstrap script should always reference this class first.
 * The application is booted via the "run" method
 *
 */
Class Cloud
{
	
	/**
	 * Globally accessible registry
	 * @var array $_registry
	 */
	static private $_registry                   = array();
	/*
	 * @var Cloud_Core_Model_App $_app
	 * The current instance of the application (singleton)
	 */
	static private $_app						= null;
	
	/**
	 * Register a value (set) in the registry by key & value
	 * @param string $key
	 * @param multitype: $value
	 * @param bool $graceful
	 */
	public static function register($key, $value, $graceful = false)
    {
		self::$_registry[$key] = $value;
    }
    
    public static function unregister($key)
    {
		if (isset(self::$_registry[$key])) {
     		if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
     			self::$_registry[$key]->__destruct();
     		}
     		unset(self::$_registry[$key]);
		}
    }
    
    public static function dump($args, $exit=false)
    {
    	echo '<pre>';
    	if (is_array($args))
    		print_r($args);
    	else
    		echo (string)$args;
    	echo '</pre>';
    	if ($exit)
    		exit;
    }
    
    /**
     * Throw an exception
     * @param string $message
     * @throws Cloud_Core_Model_Exception
     */
	public static function throwException($message)
    {
		throw new Cloud_Core_Model_Exception ($message);
    }
    
    /**
     * Retrieve a value from registry by key
     * @param string $key
     * @return multitype:|NULL
     */
    public static function registry($key)
    {
    	if (isset(self::$_registry[$key])) {
    		return self::$_registry[$key];
    	}
    	return null; 
    }
    
    /**
     * Run the application
     * @param array $options
     */
    public static function run($options=array())
    {
    	return self::app($options)->run(); 
    }
    
    /**
     * Return a singleton representing the current application
     * @param array $options
     * @return Cloud_Core_Model_App
     */
    public static function app($options=array())
    {
    	if (!self::$_app) {
    		require_once(APP_PATH."/code/Core/Model/App.php");
    		self::$_app = new Cloud\Core\Model\App($options); 
    	}
    	return self::$_app; 
    }
    
    /**
     * Shortcut to get the dependency injector 
     * @return \Phalcon\DI\FactoryDefault
     */
    public static function di()
    {
        return self::app()->getPhalconDi(); 
    }
    
    /**
     * Shortcut to get the database connection
     * @return \Phalcon\Db\Adapter
     */
    public static function db()
    {
        return self::di()->getShared(\Cloud\Core\Model\App::SERVICE_DATABASE); 
    }
    
    /**
     * Check if the cache is enabled
     * @return boolean
     */
    public static function isCacheEnabled()
    {
    	return self::app()->isCacheEnabled();
    }
    
    public static function loadCache($key, $callback=false)
    {
    	return self::app()->loadCache($key, $callback); 
    }
    
    /**
     * Log the given error message
     * @param string $error_message
     * @return Cloud
     */
    public static function logError($error_message, $error_number='NONE', $file="error.log")
    {
    	$error_log 		= Cloud::registry("var_path") . DS . "log" . DS . $file;  
    	$logger 		= new \Phalcon\Logger\Adapter\File($error_log);
    	$logger->error('[' . $error_number . ']' . $error_message);
    } 
    
    /**
     * Log the given exception message
     * @param string $error_message
     * @return Cloud
     */
    public static function logException($err_message, $error_number='NONE')
    {
    	return self::logError("\n".$err_message."\n\n", $error_number, "exception.log"); 
    }
}
