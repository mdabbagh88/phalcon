<?php
namespace Cloud\Core\Model\App\Events;
use \Phalcon\Events\Manager as PhalconManager;
use \Cloud\Core\Model\App\Events\Observer as CloudObserver;
use \Cloud as Cloud;
Class Manager extends PhalconManager 
{
    /**
     * Attach all observers in the modules configuration array
     * @param array $modules
     * @return \Cloud\Core\Model\App\Events\Manager
     */
    public function attachObservers($modules)
    {
        $_required_keys = array(
        	"event", "class", "method"
        );
        foreach($modules as $module) {
            foreach ($module["observers"] as $obs_name => $obs) {
                foreach($_required_keys as $_key) {
                    if (!isset($obs[$_key])) {
                        Cloud::throwException("Observer missing required attribute: " . $_key . " in " . __METHOD__); 
                    }
                }
                $this->attach($obs["event"], function($event, $component, $extraData=false) use ($obs){
                    $observer = new $obs["class"]();
                    if (!$observer instanceof CloudObserver) {
                        Cloud::throwException("Observer not an instance of Cloud\Core\Model\Events\Observer in " . __METHOD__);
                    }
                    $observer->{$obs["method"]}($event, $component, $extraData);
                });
            }
        }
        return $this;
    }
}