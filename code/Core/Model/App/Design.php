<?php
namespace Cloud\Core\Model\App;

use \Cloud\Core as Core;
use \Cloud\Core\Library\ObjectTrait as ObjectTraits;

Class Design
{
    use ObjectTraits\DataObject;
    use ObjectTraits\SingletonObject;

    const DEFAULT_LAYOUT = "base"; //Put your default layout here
    const DEFAULT_PACKAGE = "default";

    protected function _construct()
    {
        $this->setMainLayout(self::DEFAULT_LAYOUT);
        $this->setDesignPackage(self::DEFAULT_PACKAGE);
        return $this;
    }

    public function getModuleViewsDir($module_name)
    {
        return \Cloud::registry("design_path") . DS . $this->getDesignPackage() . DS . Core\Model\App::MVC_ENTITY_VIEW . DS . ucfirst($module_name) . DS;
    }

    public function getLayoutsDirRelative()
    {
        return "../../layouts/";
    }

    public function getLayoutsDir()
    {
        return \Cloud::registry("design_path") . DS . $this->getDesignPackage() . DS . Core\Model\App::MVC_ENTITY_LAYOUT . DS;
    }
}	