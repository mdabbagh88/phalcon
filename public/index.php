<?php
define("APP_PATH", dirname(dirname(__FILE__)));
define("DS", "/"); 

require_once(APP_PATH."/Cloud.php"); 
Cloud::run(array(
	/* Options here wll override those in the configuration files */ 
));  