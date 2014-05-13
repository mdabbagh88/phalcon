<?php
namespace Cloud\Core\Model\App\View\Engine\Volt;
use Cloud as Cloud;
Class Compiler extends \Phalcon\Mvc\View\Engine\Volt\Compiler
{
    /**
     * Compile a given template file. This override adds a package to the file path
     * @see \Phalcon\Mvc\View\Engine\Volt\Compiler::compileFile()
     */
    public function compileFile($path, $compiledPath, $extendsMode = null)
    {
        $path = $this->getDesign()->addPackage($path, $this);
        return parent::compileFile($path, $compiledPath, $extendsMode);
    }
    
    /**
     * Return the design service
     * @return \Cloud\Core\Model\App\Design
     */
    public function getDesign()
    {
        return Cloud::app()->getFrontController()->getDesign();
    }
}