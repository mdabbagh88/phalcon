<?php
namespace Cloud\Core\Model\App\Controller\Action;

Use Cloud as Cloud;

Abstract Class AbstractAction extends \Phalcon\Mvc\Controller
{
    use \Lib\Core\ObjectTrait\EventingObject;

    public function initialize()
    {
        Cloud::events()->fire("controller_action:initialize", $this);
        Cloud::events()->fire($this->getWebsiteEventName("controller_action", "initialize"), $this);
        Cloud::events()->fire($this->getEventName("initialize"), $this);
    }

    public function beforeExecuteRoute($dispatcher)
    {
        Cloud::events()->fire("controller_action:before_execute_route", $this);
        Cloud::events()->fire($this->getWebsiteEventName("controller_action", "before_execute_route"), $this);
        Cloud::events()->fire($this->getEventName("before_execute_route"), $this);
    }

    /**
     * Return the HTTP Request Singleton
     * @return \Cloud\Core\Model\Http\Request
     */
    public function getRequest()
    {
        return \Cloud::app()->getFrontController()->getRequest();
    }

    /**
     * Return the HTTP Response Singleton
     * @return \Cloud\Core\Model\Http\Response
     */
    public function getResponse()
    {
        return \Cloud::app()->getFrontController()->getResponse();
    }

    /**
     * Send a json response and set the response content type
     * Optionally, you may choose to exit the program at this point
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param      $data
     * @param bool $sendAndExit
     * @param bool $sendOnly
     *
     * @return $this
     */
    public function jsonResponse($data, $sendAndExit = false, $sendOnly = false)
    {
        $this->getResponse()->setContentType('application/json', 'UTF-8');
        echo json_encode($data);
        if ($sendAndExit) {
            $this->getResponse()->send();
            exit;
        } else {
            if ($sendOnly) {
                $this->getResponse()->send();
            }
        }
        return $this;
    }

    /**
     * Return the dependency injector singleton
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @return Cloud\Core\Model\App\DependencyInjector
     */
    public function di()
    {
        return \Cloud::di();
    }

    /**
     * Return the current application singleton
     * @return \Cloud\Core\Model\App
     */
    public function app()
    {
        return \Cloud::app();
    }

    /**
     * Return the design singleton
     * @return \Cloud\Core\Model\App\Design
     */
    public function getDesign()
    {
        return \Cloud::app()->getFrontController()->getDesign();
    }
}