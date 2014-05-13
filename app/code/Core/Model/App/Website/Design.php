<?php 
namespace Cloud\Core\Model\App\Website;
Use \Cloud\Core\Model\AbstractModel;
Use \Phalcon\Mvc\Model as PhalconModel;
Class Design extends AbstractModel
{
    ##!generated
    /** Do not write custom code between the generated blocks, or it will be overwritten by the Model Generator **/
    
    /** @var int */ public $website_design_id; /** @var int */ public $website_id; /** @var string */ public $design_package; /** @var string */ public $date_from; /** @var string */ public $date_to;
    
    /**
     * Get the table source for this model
     * @return string
     */
    public function getSource()
    {
        return 'core_website_design';
    }
    /** End Generated **/
    ##!end-generated
    
    public function initialize() 
    {
        $this->belongsTo("website_id", "Cloud\\Core\\Model\\App\\Website", "website_id"); 
    }
}