<?php
namespace Cloud\Core\Model\App;
Class Design
{
	use \Cloud\Core\Library\ObjectTrait\DataObject;
	use \Cloud\Core\Library\ObjectTrait\SingletonObject;
	
    const DEFAULT_LAYOUT        = "twocol_left"; //Put your default layout here	
	const DEFAULT_PACKAGE		= "default";
	
	protected function _construct()
	{
		$this->setMainLayout(self::DEFAULT_LAYOUT); 
		$this->setDesignPackage(self::DEFAULT_PACKAGE);
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