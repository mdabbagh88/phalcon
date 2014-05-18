<?php
namespace Cloud\Test\Controller;

use Cloud,
    Cloud\Core\Controller\ControllerBase as ControllerBase;

Class IndexController extends ControllerBase
{
    public function indexAction()
    {
        echo "IN TEST INDEX";
        exit;
    }

    public function testAction()
    {
        echo "HERE DOOOOD";
        exit;
    }
}