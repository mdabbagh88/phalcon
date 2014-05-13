<?php
require_once(dirname(dirname(__FILE__)) . '/app/Cloud.php'); 
Cloud::run('www', array(
	/* Options here wll override those in the configuration files */ 
));  