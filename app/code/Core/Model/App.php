<?php
namespace Cloud\Core\Model;

use Cloud,
    Cloud\Core,
    Cloud\Core\Model,
    Cloud\Core\Model\App\Controller\Front as FrontController;
use Cloud\Core\Model\App\Cache;
use Cloud\Core\Model\App\ServiceMeta;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Exception as BaseException;

Class App
{
    /**
     * The singleton for the dependency injector
     * @var \Cloud\Core\Model\App\DependencyInjector
     */
    protected $_di = null;

    /**
     * @var array $_modules
     * An array holding the module names and paths
     */
    protected $_modules = array();

    /**
     * The front controller singleton (not a controller action)
     * @var FrontController
     */
    protected $_frontController = null;

    /**
     * The configuration object for the application
     * @var \Cloud\Core\Model\App\Config
     */
    protected $_config = null;

    const APP_STATUS_DEVELOPMENT = "development";
    const APP_STATUS_STAGING = "staging";
    const APP_STATUS_PRODUCTION = "production";

    const MVC_MODULE = "Module";
    const MVC_DEFAULT_MODULE = "Core";

    const MVC_ENTITY_CONTROLLER = "Controller";
    const MVC_ENTITY_MODEL = "Model";
    const MVC_ENTITY_WIDGET = "Widget";
    const MVC_ENTITY_HELPER = "Helper";
    const MVC_ENTITY_LIBRARY = "Library";

    const MVC_ENTITY_VIEW = "views";
    const MVC_ENTITY_LAYOUT = "layouts";

    const WEBSITE_DEFAULT = "www";

    const SERVICE_SESSION = "session";
    const SERVICE_DATABASE = "database";

    /**
     * Whether to force an exit upon the occurrence of an exception (note this only works in the handle exception method)
     * @see _handleException()
     * @var bool
     */
    protected $_exitOnException = false;

    /**
     *
     * @param array $_options
     */
    public function __construct($_options = array())
    {

    }

    /**
     * Initialize the application configuration so various models, helpers, controllers can be instantiated
     * For command line calls, this function is all that is needed to bootup the app, and it is called implicitly in Cloud::app()
     *
     * @param string $website_code
     * @param array  $_options
     *
     * @return \Cloud\Core\Model\App
     */
    public function init($website_code, $_options)
    {
        try {
            $this->loadEventsManager();
            if (is_null($website_code)) {
                $website_code = self::WEBSITE_DEFAULT;
            }
            $_options["website_code"] = $website_code;
            $this->setConfig(new App\Config(array("override" => $_options)));
            $this->getConfig()->load($this);
            return $this;
        } catch ( \Exception $e ) {
            Cloud::events()->fire("app:init_before_exception", $this);
            $this->_handleException($e);
        }
        return $this;
    }

    /**
     * This function does the meat of the MVC work.
     * It's separated from "init" in that init is supposed to perform a dry run, loading the configuration making the app accessible, etc.
     * This function passes control to the front controller to do routing, url rewriting, dispatching, etc
     * @see Cloud\Core\Model\App\Controller\Front::handle()
     */
    public function run()
    {
        try {
            $this->getFrontController()->handle();
        } catch ( \Exception $e ) {
            Cloud::events()->fire("app:run_before_exception", $this);
            $this->_handleException($e);
        }
        return;
    }

    /**
     * Either get the entire Config object or a specific value. Return NULL if the key does not exist
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param null $key
     * @param null $default
     *
     * @return App\Config|null|string
     */
    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->_config;
        } else {
            return $this->_config->getConfig($key, $default);
        }
        //$path = explode("/", $key);
    }

    /**
     * Set the configuration singleton
     *
     * @param \Cloud\Core\Model\App\Config $config
     *
     * @return \Cloud\Core\Model\App\Config
     */
    public function setConfig(App\Config & $config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * Return the DI singleton
     * @return \Cloud\Core\Model\App\DependencyInjector
     */
    public function getDi()
    {
        if (is_null($this->_di)) {
            $this->_di = new App\DependencyInjector();
        }
        return $this->_di;
    }

    /**
     * Return the current in-scope website
     * @return Cloud\Core\Model\App\Website
     */
    public function getWebsite()
    {
        return $this->getDi()->getShared(ServiceMeta::SERVICE_CURRENT_WEBSITE);
    }

    /**
     * Get the front controller singleton
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function getFrontController()
    {
        if (!$this->getDi()->has(ServiceMeta::SERVICE_FRONT_CONTROLLER)) {
            $this->getDi()->setShared(ServiceMeta::SERVICE_FRONT_CONTROLLER, new FrontController($this));
        }
        return $this->getDi()->getShared(ServiceMeta::SERVICE_FRONT_CONTROLLER);
    }

    /**
     * Load the events manager singleton
     * @return \Cloud\Core\Model\App
     */
    public function loadEventsManager()
    {
        $this->getDi()->setShared(ServiceMeta::SERVICE_EVENTS, new \Cloud\Core\Model\App\Events\Manager());
        return $this;
    }

    /**
     * Get the events manager singleton
     * @return \Cloud\Core\Model\App\Events\Manager
     */
    public function getEventsManager()
    {
        return $this->getDi()->getShared(ServiceMeta::SERVICE_EVENTS);
    }

    /**
     * Return whether the application is in production mode
     * @return boolean
     */
    public function isProduction()
    {
        return $this->getConfig()->whatIsAppStatus() == self::APP_STATUS_PRODUCTION;
    }

    /**
     * Return whether the application is in development mode
     * @return boolean
     */
    public function isDevelopment()
    {
        return $this->getConfig()->whatIsAppStatus() == self::APP_STATUS_DEVELOPMENT;
    }

    /**
     * Return whether the application is in staging mode
     * @return boolean
     */
    public function isStaging()
    {
        return $this->getConfig()->whatIsAppStatus() == self::APP_STATUS_STAGING;
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
     * Get the cache singleton
     * @return Cache
     */
    public function getCache()
    {
        return $this->getDi()->getShared(Cache::CACHE_KEY_DATA);
    }

    /**
     * Exception handling plus Whoops Integration
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param \Exception $e
     *
     * @return $this
     * @throws \Exception
     */
    protected function _handleException(\Exception $e)
    {
        $exception_number = 'ERR-' . \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 8);
        $exception_message = $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n\n" . $e->getTraceAsString();
        \Cloud::logException($exception_message, $exception_number);
        switch ($this->getConfig("application/status")) {
            case self::APP_STATUS_PRODUCTION:
                print "<table style='width:500px; margin:20px auto; border:1px solid #000; text-align:center; padding:10px;'>";
                print "<tr><td><strong>The Application Has Encountered An Exception</strong></td></tr>";
                print "<tr><td>Exception Number: <em>$exception_number</em></td></tr>";
                print "</table>";
                break;
            case self::APP_STATUS_DEVELOPMENT:
            case self::APP_STATUS_STAGING:
            default:
                /*echo '<pre>';
                print "Application Exception: " . $exception_number . "\n";
                print $exception_message;
                echo '</pre>';*/
                $run = new Run;
                $handler = new PrettyPageHandler;
                // Add a custom table to the layout:
                $run->pushHandler($handler);
                // Example: tag all frames with a comment
                $run->pushHandler(
                    function ($exception, $inspector, $run) {
                        $frames = $inspector->getFrames();
                        foreach ($frames as $i => $frame) {
                            $frame->addComment('This is frame number ' . $i, '');
                            if ($function = $frame->getFunction()) {
                                $frame->addComment("This frame is within function '$function'", '');
                            }
                        }
                    }
                );
                $run->register();
                $handler->addResourcePath(CLOUD_ROOT . DS . 'public');
                $handler->addCustomCss('assets/PrettyPageHandler.css');

                $handler->addDataTable(
                    "Application Exception: " . $exception_number,
                    explode("\n", $exception_message)
                );
                throw $e;
                break;

        }

        return $this;
    }

    /**
     *
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param int    $intVal
     * @param string $separator
     *
     * @return string
     */
    protected function _errorLevelToString($intVal, $separator = '|')
    {
        $errorLevels = array(
            2047 => 'E_ALL',
            1024 => 'E_USER_NOTICE',
            512  => 'E_USER_WARNING',
            256  => 'E_USER_ERROR',
            128  => 'E_COMPILE_WARNING',
            64   => 'E_COMPILE_ERROR',
            32   => 'E_CORE_WARNING',
            16   => 'E_CORE_ERROR',
            8    => 'E_NOTICE',
            4    => 'E_PARSE',
            2    => 'E_WARNING',
            1    => 'E_ERROR'
        );
        $result = '';
        foreach ($errorLevels as $number => $name) {
            if (($intVal & $number) == $number) {
                $result .= ($result != '' ? $separator : '') . $name;
            }
        }
        return $result;
    }
}