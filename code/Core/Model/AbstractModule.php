<?php

namespace Cloud\Core;

use Phalcon\Loader,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Mvc\View,
    Phalcon\Mvc\ModuleDefinitionInterface,
    Cloud\Core;

abstract class AbstractModule implements ModuleDefinitionInterface
{
    abstract protected function _getModuleName();
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
     * @param \Phalcon\DI\FactoryDefault $di
     */
    public function registerServices($di)
    {
        //Registering a dispatcher
        $di->set(
            'dispatcher',
            function () {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace(\Cloud::app()->getModuleEntityNamespace(Model\App::MVC_ENTITY_CONTROLLER, $this->_getModuleName()));
                $dispatcher->setDefaultController("IndexController");
                return $dispatcher;
            }
        );
        //Registering the view component
        $di->set(
            'view',
            function () {
                $view = new View();
                $view->setViewsDir(\Cloud::app()->getDesign()->getModuleViewsDir($this->_getModuleName()));
                $view->setLayoutsDir(\Cloud::app()->getDesign()->getLayoutsDirRelative()); // Last DS !Important
                $view->setLayout(\Cloud::app()->getDesign()->getMainLayout());
                $view->registerEngines(
                    array(
                        ".volt"  => 'Phalcon\Mvc\View\Engine\Volt',
                        ".phtml" => 'Phalcon\Mvc\View\Engine\Php'
                    )
                );
                return $view;
            }
        );
    }
}