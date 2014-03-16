<?php
namespace Cloud\Core\Model\App;
Class Design
{
	use \Cloud\Core\Library\ObjectTrait\DataObject;
	use \Cloud\Core\Library\ObjectTrait\SingletonObject;
	
	const LAYOUT_HOMEPAGE 		= "homepage";
	const LAYOUT_ONECOL   		= "onecol";
	const LAYOUT_TWOCOL_LEFT 	= "twocol_left";
	
	const DEFAULT_PACKAGE		= "default";
	const LAYOUT_CONTAINER		= "container"; 
	
	protected function _construct()
	{
		$this->setMainLayout(self::LAYOUT_TWOCOL_LEFT); 
		$this->setDesignPackage(self::DEFAULT_PACKAGE);
		$this->setLayoutContainer(self::LAYOUT_CONTAINER); 
		return $this; 
	}
	
	public function getModuleViewsDir($module_name)
	{
		return \Cloud::registry("design_path") . DS . $this->getDesignPackage() . DS . \Cloud\Core\Model\App::MVC_ENTITY_VIEW . DS . ucfirst($module_name) . DS;
	}
	
	public function getLayoutsDirRelative()
	{
		return "../../layouts/"; 
	}
	
	public function getLayoutsDir()
	{
		return \Cloud::registry("design_path") . DS . $this->getDesignPackage() . DS . \Cloud\Core\Model\App::MVC_ENTITY_LAYOUT . DS;
	}
}	