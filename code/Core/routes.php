<?php
//Define a route
#$router->setDefaultModule("Core");
#$router->setDefaultNamespace("Cloud\Core\Controller");
#$router->setDefaultController("Index");

/** @var Phalcon\Mvc\Router $router */
$router->add(
    "/",
    array(
        "controller" => "Index",
        "action"     => "homepage",
        //"namespace"	 => "Cloud\Core\Controller",
        "module"     => "Core"
    )
);