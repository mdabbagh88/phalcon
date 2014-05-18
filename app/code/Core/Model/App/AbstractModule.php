<?php
namespace Cloud\Core\Model\App;

use Phalcon\Loader,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Mvc\View,
    Phalcon\Mvc\ModuleDefinitionInterface,
    Cloud\Core;

abstract class AbstractModule implements ModuleDefinitionInterface
{
    protected function _getModuleName()
    {
        $class = str_replace("\\", " ", get_class($this));
        if (isset($class[1])) {
            return $class[1];
        } //Get the Core from Cloud\Core\Module etc
        return '';
    }
    /**
     * Register a specific autoloader for the module
     */

    /**
     * This method was called too late in the bootup process for our needs. As this method is required, it is left blank here.
     * Autoloading has been moved to \Cloud\Core\Model\App
     * @see \Cloud\Core\Model\App::_registerAutoloader
     * @see \Phalcon\Mvc\ModuleDefinitionInterface::registerAutoloaders()
     */
    public function registerAutoloaders()
    {
        //The work for this function is handled elsewhere, see function definition
        //Do nothing
    }

    /**
     * Register specific services for the module
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param \Phalcon\DiInterface $di
     */
    public function registerServices($di)
    {
        //Registering a dispatcher
        $di->set(
            'dispatcher',
            function () {
                $dispatcher = new \Cloud\Core\Model\App\Controller\Dispatcher(array("module" => $this->_getModuleName()));
                $dispatcher->setDefaultNamespace(\Cloud::app()->getModuleEntityNamespace(\Cloud\Core\Model\App::MVC_ENTITY_CONTROLLER, $this->_getModuleName()));
                $dispatcher->setDefaultController("IndexController");
                return $dispatcher;
            }
        );
        //Registering the view component
        $di->set(
            'view',
            function () {
                $view = new \Cloud\Core\Model\App\View(array("module" => $this->_getModuleName()));
                $view->registerEngines(
                    array(
                        ".volt"  => 'Cloud\Core\Model\View\Engine\Volt',
                        ".phtml" => 'Phalcon\Mvc\View\Engine\Php'
                    )
                );
                return $view;
            }
        );
    }
}