<?php
namespace Cloud\Core\Model\App\Design;
Class Seo
{
    /**
     * Page meta variables 
     * @var string
     */
    protected $_canonical, $_metaDescription, $_title = "";
    
    public function setCanonical($canonical)
    {
        $this->_canonical = $canonical;
        return $this;
    }
    
    public function getCanonical()
    {
        return $this->_canonical;
    }
    
    public function setMetaDescription($desc)
    {
        $this->_metaDescription = $desc;
        return $this;
    }
    
    public function getMetaDescription()
    {
        return $this->_metaDescription;
    }
    
    public function setTitle($title, $include_suffix=true)
    {
        $this->_title = $title . ($include_suffix ? \Cloud::app()->getWebsite()->getData("meta_title_suffix") : "");
        return $this;
    }
    
    public function getTitle()
    {
        return $this->_title;
    }
}