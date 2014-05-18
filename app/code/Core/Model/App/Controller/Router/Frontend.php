<?php
namespace Cloud\Core\Model\App\Controller\Router;

use Cloud as Cloud;

Class Frontend extends AbstractRouter
{
    const DEFAULT_FRONTEND_FRONTNAME = "default";

    public function getCode()
    {
        return self::FRONTEND_ROUTER;
    }

    public function getDefaultFrontName()
    {
        return self::DEFAULT_FRONTEND_FRONTNAME;
    }

    public function addRoutes()
    {
        $this->add(
            "/:module/:controller/:action/:params",
            array(
                "module"     => 1,
                "controller" => 2,
                "action"     => 3,
                "params"     => 4
            )
        );
        $this->add(
            "/:module",
            array(
                "module"     => 1,
                "controller" => "index",
                "action"     => "index"
            )
        );
        $this->add(
            "/:module/:controller",
            array(
                "module"     => 1,
                "controller" => 2,
                "action"     => "index"
            )
        );
        $this->add(
            "/",
            array(
                "module"     => self::DEFAULT_FRONTEND_FRONTNAME,
                "controller" => "index",
                "action"     => "index"
            )
        );
        return $this;
    }
}