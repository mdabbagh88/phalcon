<?php
namespace Cloud\Core\Model\App\View\Engine;

Use Cloud as Cloud;

Class Phtml extends \Phalcon\Mvc\View\Engine\Php
{
    /**
     * Return the design service
     * @return \Cloud\Core\Model\App\Design
     */
    public function getDesign()
    {
        return Cloud::app()->getFrontController()->getDesign();
    }
}
