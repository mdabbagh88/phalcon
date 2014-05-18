<?php
namespace Cloud\Core\Controller;

use Cloud,
    Cloud\Core\Controller;

Class ErrorController extends ControllerBase
{
    public function route404Action()
    {
        echo "IN 404";
        exit;
    }
}