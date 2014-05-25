<?php
namespace Cloud\Core\Model\App\Controller\Router;

use Phalcon\Mvc\Model\Exception;
use \Phalcon\Mvc\Router as PhalconRouter,
    \Cloud\Core\Model\App\Config as Config;
use Cloud\Core\Model\App\Controller\Dispatcher;
use Cloud\Core\Model\App;
use Cloud as Cloud;
use Cloud\Core\Model\App\ServiceMeta;
use Phalcon\Mvc\Dispatcher as PhDispatcher;

Abstract Class AbstractRouter extends PhalconRouter
{
    use \Lib\Core\ObjectTrait\CachingObject;
    use \Lib\Core\ObjectTrait\EventingObject;

    const ADMIN_ROUTER = "admin";
    const FRONTEND_ROUTER = "frontend";

    /**
     * Configuration Singleton
     * @var Config
     */
    protected $_config = null;

    protected $_dispatcherNamespace = false;

    public function init()
    {
        $this->addRoutes();
        Cloud::events()->fire("router:after_add_routes", $this);
        Cloud::events()->fire($this->getWebsiteEventName("router", "after_add_routes"), $this);
        return $this;
    }
    public function getRewriteUri()
    {
        if(!isset($_GET['_url']) && isset($_SERVER['REQUEST_URI'])) {
            $_GET['_url'] = $_SERVER['REQUEST_URI'];
        }
        return parent::getRewriteUri();
    }

    public function prime(Dispatcher &$dispatcher)
    {
        Cloud::events()->fire(
            "router:before_prime_dispatcher",
            $this,
            array(
                "dispatcher" => $dispatcher
            )
        );
        Cloud::events()->fire(
            $this->getWebsiteEventName("router", "before_prime_dispatcher"),
            $this,
            array(
                "dispatcher" => $dispatcher
            )
        );
        $this->loadRoute();
        $this->addNotFound($dispatcher);
        $dispatcher->setDefaultNamespace($this->getDispatcherNamespace());
        $dispatcher->setControllerName($this->getControllerName());
        $dispatcher->setActionName($this->getActionName());
        $dispatcher->setParams($this->getParams());
        Cloud::events()->fire(
            "router:after_prime_dispatcher",
            $this,
            array(
                "dispatcher" => $dispatcher
            )
        );
        Cloud::events()->fire(
            $this->getWebsiteEventName("router", "after_prime_dispatcher"),
            $this,
            array(
                "dispatcher" => $dispatcher
            )
        );
        return $this;
    }

    public function loadRoute()
    {
        $moduleFrontName = $this->getModuleName();
        $modules = \Cloud::app()->getConfig("modules", array());
        $namespace = \Cloud::app()->getCache()->load(
            $this->_getCacheKey("namespace-for-" . $moduleFrontName),
            function () use ($modules, $moduleFrontName) {
                foreach ($modules as $module) {
                    if (isset($module["routes"]) && isset($module["routes"][$this->getCode()])) {
                        if ($module["routes"][$this->getCode()]["frontName"] == $moduleFrontName) {
                            return $module["namespace"];
                        }
                    }
                }
                return false;
            }
        );
        if ($namespace) {
            $this->setDispatcherNamespace($namespace . "\\" . App::MVC_ENTITY_CONTROLLER);
        }
        //Don't worry if we don't find a matching namespace. This will result in a "bad" dispatch which will trigger the not found handler
        return $this;
    }

    public function setDispatcherNamespace($namespace)
    {
        $this->_dispatcherNamespace = $namespace;
        return $this;
    }

    public function getDispatcherNamespace()
    {
        return $this->_dispatcherNamespace;
    }

    /**
     * Override in sub classes to add customized router logic
     * @return AbstractRouter
     */
    public function addCustomRoutes()
    {
        return $this;
    }

    /**
     * Add a not found route the to the given dispatcher in the event the module controller or action does not exist
     * @see \Cloud\Core\Model\App\Controller\Router\AbstractRouter::addNotFound()
     */
    public function addNotFound(\Cloud\Core\Model\App\Controller\Dispatcher &$dispatcher)
    {
        $evManager = Cloud::events();
        $evManager->attach(
            "dispatch:beforeException",
            function ($event, $dispatcher, Exception $exception) {
                switch ($exception->getCode()) {
                    case PhDispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    case PhDispatcher::EXCEPTION_ACTION_NOT_FOUND:
                        \Cloud::app()->getFrontController()->getResponse()
                            ->sendRedirectExit($this->getDefaultFrontName() . '/error/route404', false, 301);
                        return false;
                }
            }
        );
        return $this;
    }
    /***************** Protected Functions ******************/


    /***************** Abstract Functions ******************/
    /**
     * @return string
     */
    abstract public function getCode();

    /**
     * @return \Cloud\Core\Model\App\Controller\Router\AbstractRouter
     */
    abstract public function addRoutes();

    /**
     * @return string
     */
    abstract public function getDefaultFrontName();
}