<?php
namespace Cloud\Core\Model\App;
use \Phalcon\Mvc\View\Simple as PhalconView;
Class View extends PhalconView
{
    /**
     * View content 
     * @var string
     */
    protected $_content = "";

    public function __construct($options=array()) 
    {
        parent::__construct($options);
    }
    
    /**
     * Return the design singleton
     * @return \Cloud\Core\Model\App\Design
     */
    public function getDesign()
    {
        return \Cloud::app()->getFrontController()->getDesign();
    }

    /**
     * Start the output buffer
     * @return \Cloud\Core\Model\App\View
     */
    public function start()
    {
        ob_start(); 
        return $this;
    }
    
    /**
     * Stop output buffering
     * @return \Cloud\Core\Model\App\View
     */
    public function end()
    {
        $content = ob_get_contents();
        ob_end_clean();
        $this->setContent($content); 
        return $this;
    }
    
    public function render($path, $parameters=array())
    {
        $path = $this->getDesign()->addPackage($path, $this);
        return parent::render($path, $parameters);
    }
    
    public function partial($path, $parameters=array())
    {
        $path = $this->getDesign()->addPackage($path, $this); 
        return parent::partial($path, $parameters); 
    }

}