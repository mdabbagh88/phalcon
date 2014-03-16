<?php
namespace Cloud\Core\Controller;
use Cloud,
	Cloud\Core\Controller; 
Class IndexController extends ControllerBase
{
	public function homepageAction()
	{
		$this->view->setLayout("homepage"); 
		$this->view->setVar("is_homepage", true); 
	}
}