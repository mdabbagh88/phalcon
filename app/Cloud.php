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
    static private $_registry = array();
    /*
     * @var Cloud_Core_Model_App $_app
    * The current instance of the application (singleton)
    */
    static private $_app = null;

    /**
     * Run the application
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param null  $website_code
     * @param array $options
     *
     */
    public static function run($website_code = null, $options = array())
    {
        self::app($website_code, $options)->run();
        return ;
    }

    /**
     * Return a singleton representing the current application
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param null  $website_code
     * @param array $options
     *
     * @return \Cloud\Core\Model\App|null
     */
    public static function app($website_code = null, $options = array())
    {
        if (!self::$_app) {
            self::$_app = new Cloud\Core\Model\App();
            self::$_app->init($website_code, $options);
        }
        return self::$_app;
    }

    /**
     * Shortcut for the global DI
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @return \Cloud\Core\Model\App\DependencyInjector
     */
    public static function di()
    {
        return self::app()->getDi();
    }

    /**
     * Register a value (set) in the registry by key & value
     *
     * @param string $key
     * @param        multitype : $value
     * @param bool   $graceful
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

    public static function dump($args, $exit = false)
    {
        echo '<pre>';
        if (is_array($args)) {
            print_r($args);
        } else {
            echo (string)$args;
        }
        echo '</pre>';
        if ($exit) {
            exit;
        }
    }

    /**
     * Get the events manager singleton
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @return \Phalcon\Events\Manager
     */
    public static function events()
    {
        return Cloud::app()->getEventsManager();
    }

    /**
     * Throw an exception
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param $message
     *
     * @throws Cloud\Core\Model\Exception
     */
    public static function throwException($message)
    {
        throw new \Cloud\Core\Model\Exception ($message);
    }

    /**
     * Retrieve a value from registry by key
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param $key
     *
     * @return null
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
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param        $error_message
     * @param string $error_number
     * @param string $file
     */
    public static function logError($error_message, $error_number = 'NONE', $file = "error.log")
    {
        $error_log = CLOUD_ROOT . DS . 'var' . DS . "log" . DS . $file;
        if (!file_exists($error_log)) {
            if (!file_exists(CLOUD_ROOT . DS . 'var' . DS . "log")) {
                mkdir(CLOUD_ROOT . DS . 'var' . DS . "log", 0755, true);
            }
            if (!file_exists($error_log)) {
                touch($error_log);
            }
        }
        $logger = new \Phalcon\Logger\Adapter\File($error_log);
        $logger->error('[' . $error_number . ']' . $error_message);
    }

    /**
     * Log the given exception message
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param        $err_message
     * @param string $error_number
     */
    public static function logException($err_message, $error_number = 'NONE')
    {
        self::logError("\n" . $err_message . "\n\n", $error_number, "exception.log");
        return;
    }
}