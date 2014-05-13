<?php

$options = getopt('', array('module:', 'model:', 'table:'));
if (!$options || sizeof($options) < 3)
{
	echo 'Invalid arguments supplied ';
	exit;
}

$debug = true;

define("DS", "/");
define("CLOUD_ROOT",dirname(dirname(dirname(__FILE__)))); //Assuming we are in ROOT/developer/cloud-devtools/
define("ETC", CLOUD_ROOT . DS . "app" . DS . "etc");
define("CODE", CLOUD_ROOT . DS . "app" . DS . "code");
define("BUILDER_PATH", dirname(__FILE__) . DS . "/Builder"); 

$module			    = $options['module'];
$model  			= $options['model'];
$table				= $options['table'];

$module_location    = CODE . DS . $module;
if (!file_exists($module_location)) {
    echo 'No module folder found at: ' . $module_location;
    exit; 
}

$path = $module_location . DS . 'Model' . DS . implode("/", explode("\\",$model)) .'.php';
$dir  = preg_replace("/(.*)\/[^\.]*\.php$/", "$1", $path); 

if ($debug) {
    echo 'Attempting to add model at: ' . chr(10);
    echo "\t".$path . chr(10);
    echo "\t".$dir  . chr(10);
}

if (file_exists($path)) {
    if ($debug) {
        echo 'Found model at path...' . chr(10);
    }
} else {
    if (!file_exists($dir)) {
        if ($debug) {
            echo 'Creating directory...'.chr(10);
            echo $dir .chr(10);
        }
        mkdir($dir, 0755, true);
    }
    if ($debug) {
        echo "Creating file..." . chr(10);
        echo $path . chr(10);
    }
    touch ($path);
}

require_once("./Builder/Model.php");
try 
{
    if ($debug) {
        echo 'Creating model builder...'.chr(10);
    }
    $modelBuilder = new ModelBuilder($module, $model, $table, $path);
    if ($debug) {
        echo 'Invoking ::build() on model builder...' . chr(10); 
    }
    $modelBuilder->build(); 
    if ($debug) {
        echo "Succesfully updated model, exiting"; 
    }
}
Catch(Exception $e)
{
    echo 'ModelBuilder encountered the following exception: ' . $e->getMessage().chr(10);
    echo $e->getTraceAsString(); 
    if ($debug) {
        echo 'Encountered a model builder error, exiting'.chr(10);
    }
}