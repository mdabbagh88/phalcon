<?php
namespace Cloud\Core\Controller;

use Cloud,
    Cloud\Core\Controller;
use Cloud\Core\Model\Sales\Order;

Class IndexController extends ControllerBase
{
    public function indexAction()
    {
        // Cloud::app()->getConfig()->getS
        echo $this->getDesign()->loadPage()->renderPage("test");
    }

    public function testAction()
    {
        $first_order = Order::findFirst();
        print_r($first_order);
    }
}