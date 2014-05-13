<?php
namespace Cloud\Core\Model\App\View\Engine;
Use Cloud as Cloud;
Use Cloud\Core\Model\App\View\Engine\Volt\Compiler as CloudCompiler;
Class Volt extends \Phalcon\Mvc\View\Engine\Volt
{
    /**
     * Return the design service
     * @return \Cloud\Core\Model\App\Design
     */
    public function getDesign()
    {
       return Cloud::app()->getFrontController()->getDesign(); 
    }
    
    // Override default Volt getCompiler method
    public function getCompiler()
    {
        if (!$this->_compiler) {
            $this->_compiler = new CloudCompiler($this->getView());
            $this->_compiler->setOptions($this->getOptions());
            $this->_compiler->setDI(Cloud::di());
        }
        return $this->_compiler;
    }
    
    public function getOptions()
    {
        return is_array(parent::getOptions()) ? parent::getOptions() : array();
    }
}