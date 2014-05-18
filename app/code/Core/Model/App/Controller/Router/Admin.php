<?php
namespace Cloud\Core\Model\App\Controller\Router;

Class Admin extends AbstractRouter
{
    const DEFAULT_ADMIN_FRONTNAME = "admin";

    public function getCode()
    {
        return self::ADMIN_ROUTER;
    }

    public function getDefaultFrontName()
    {
        return self::DEFAULT_ADMIN_FRONTNAME;
    }

    public function addRoutes()
    {
        $this->add(
            "/:controller/:action/:params",
            array(
                "module"     => self::DEFAULT_ADMIN_FRONTNAME,
                "controller" => 1,
                "action"     => 2,
                "params"     => 3
            )
        );
        $this->add(
            "/:controller",
            array(
                "module"     => self::DEFAULT_ADMIN_FRONTNAME,
                "controller" => 1,
                "action"     => "index"
            )
        );
        $this->add(
            "/",
            array(
                "module"     => self::DEFAULT_ADMIN_FRONTNAME,
                "controller" => "index",
                "action"     => "index"
            )
        );
        return $this;
    }
}