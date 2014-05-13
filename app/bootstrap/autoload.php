<?php
$loader 	= new \Phalcon\Loader();
$namespaces	= array(
    "Cloud"  => APP_PATH   . DS . "code", 
    "Lib"    => CLOUD_ROOT . DS . "lib"
);
$loader->registerNamespaces($namespaces);
$loader->register(); 