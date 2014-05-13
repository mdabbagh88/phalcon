<?php
namespace Cloud\Core\Library\ObjectTrait;
Trait EventingObject 
{
    protected $_moduleName = null;
    protected $_eventPrefix= null; 
    
    protected function _getEventPrefix()
    {
        if (is_null($this->_eventPrefix))
        { 
            /**
    	     * Format is Cloud\[Module]\...
    	     */
    	    $name = get_class($this);
    	    $parts = explode("\\", $name);
    	    array_shift($parts);
    	    $prefix = "";
    	    foreach($parts as &$p)
    	    {
    	        $p = lcfirst($p); 
    	    }
            $this->_eventPrefix = implode("_", $parts) . ":"; 
            if (strstr($this->_eventPrefix, "Controller")) {
                $this->_eventPrefix = str_replace("Controller", "", $this->_eventPrefix);
            }
        }
        return $this->_eventPrefix; 
    }
    
    public function getEventName($suffix)
    {
       return $this->_getEventPrefix() . $suffix; 
    } 
    
    public function getWebsiteEventName($component, $event=false)
    {
        if (!$event && strstr($component, ":")) {
            list($component, $event) = explode(":", $component); 
        } else if (!$event) {
            \Cloud::throwException("No : found in component name in " . __METHOD__); 
        }
        return \Cloud::app()->getWebsite()->getCode().'_'.$component.":".$event;
    }
}