<?php
define('APP_PATH', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('CLOUD_ROOT', dirname(dirname(__FILE__)));

require_once(APP_PATH . DS . "bootstrap" . DS . "autoload.php");

Final Class Cloud 
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
     * Run the application
     * @param array $options
     */
    public static function run($website_code=null,$options=array())
    {
        return self::app($website_code, $options)->run();
    }
    
    /**
     * Return a singleton representing the current application
     * @param array $options
     * @return \Cloud\Core\Model\App
     */
    public static function app($website_code=null, $options=array())
    {
        if (!self::$_app) {
            self::$_app = new Cloud\Core\Model\App();
            self::$_app->init($website_code,$options); 
        }
        return self::$_app;
    }
    
    /**
     * Shortcut for the global DI
     * @return \Cloud\Core\Model\Di
     */
    public static function di()
    {
        return self::app()->getDi();
    }
    
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
     * Get the events manager singleton
     * @return \Cloud\Core\Model\Phalcon\Events\Manager
     */
    public static function events()
    {
        return Cloud::app()->getEventsManager();
    }
    
    /**
     * Throw an exception
     * @param string $message
     * @throws Cloud_Core_Model_Exception
     */
    public static function throwException($message)
    {
        throw new \Cloud\Core\Model\Exception ($message);
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
     * Log the given error message
     * @param string $error_message
     * @return Cloud
     */
    public static function logError($error_message, $error_number='NONE', $file="error.log")
    {
        $error_log 		= CLOUD_ROOT . DS . 'var' . DS . "log" . DS . $file;
        if (!file_exists($error_log)) {
            if (!file_exists(CLOUD_ROOT . DS . 'var' . DS . "log")) {
                mkdir(CLOUD_ROOT . DS . 'var' . DS . "log", 0755, true);
            }
            if (!file_exists($error_log)) {
                touch($error_log);
            }
        }
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