<?php 
namespace Cloud\Core\Model\App;
Use \Cloud\Core\Model\AbstractModel;
Use \Phalcon\Mvc\Model as PhalconModel;
Use \Cloud\Core\Model\App\Controller\Router\AbstractRouter as RouterMeta,
    \Cloud\Core\Model\App\Controller\Router\Admin as AdminRouter,
    \Cloud\Core\Model\App\Controller\Router\Frontend as FrontendRouter,
    Cloud as Cloud,
    \Cloud\Core\Model\App\Website\Design as Design
    ;
Class Website extends AbstractModel
{
    ##!generated
    /** Do not write custom code between the generated blocks, or it will be overwritten by the Model Generator **/
    
    /** @var int */ public $website_id; /** @var string */ public $code; /** @var string */ public $area; /** @var int */ public $session_lifetime; /** @var int */ public $session_cookie_lifetime; /** @var string */ public $session_cookie_name; /** @var string */ public $default_design_package; /** @var string */ public $default_design_layout;
    
    /**
     * Get the table source for this model
     * @return string
     */
    public function getSource()
    {
        return 'core_website';
    }
    /** End Generated **/
    ##!end-generated
    
    /**
     * 
     * @var string
     */
    protected $_design_package = null;
    
    public function initialize()
    {
        $this->hasMany("website_id", "Cloud\\Core\\Model\\App\\Website\\Design", "website_id", array("alias" => "WebsiteDesigns"));
    }
    
    public function onConstruct()
    {
        
    }
    

    const WEBSITE_ADMIN     = "admin";
    const WEBSITE_WWW       = "www";
    const WEBSITE_MOBILE    = "mobile";
    
    const AREA_ADMIN        = "admin";
    const AREA_FRONTEND     = "frontend";
    const AREA_GLOBAL       = "global";
    
    /**
     * Return the current website based on the provided code
     * @param string $code
     * @return \Cloud\Core\Model\App\Website
     */
    public static function findByCode($code)
    {
        $instance = parent::findFirst(array(
        	"code" => $code
        ));
        if (!$instance) {
            \Cloud::throwException("Invalid website code: " . $code);
        }
        return $instance;
    }
    
    public function getCode()
    {
        return $this->code;
    }
     
    public function getDefaultRouter()
    {
        switch($this->code)
        {
        	case self::WEBSITE_ADMIN:
        	    return new AdminRouter();
        	    break;
        	default:
        	    return new FrontendRouter();
        	    break;
        }
    }
    
    public function getDesignDir()
    {
        return $this->getCode();
    }
    
    public function getArea()
    {
        return $this->area;
    }
    
    /**
     * @todo Hook this to the DB
     * @return string
     */
    public function getDefaultDesignPackage()
    {
        return $this->default_design_package;
    }
    
    /**
     * Get the current design package
     * @return string
     */
    public function getDesignPackage()
    {
        if (is_null($this->_design_package)) {
            $packages = $this->getWebsiteDesigns(array(
            	"date_from <= :date: AND date_to >= :date:",
                "bind" => array("date" => date("Y-m-d H:i:s"))
            )); 
            $this->_design_package = $this->getDefaultDesignPackage();
            foreach($packages as $override)
            {
                $this->_design_package = $override->design_package;
            }
        }
        return $this->_design_package;
    }
    
    /**
     * @todo Hook this in the db
     * @return string
     */
    public function getDesignLayout()
    {
        return $this->default_design_layout;
    }
    
}