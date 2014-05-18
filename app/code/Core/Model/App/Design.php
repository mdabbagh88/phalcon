<?php
namespace Cloud\Core\Model\App;

use Cloud as Cloud;
use Cloud\Core\Model\App\ServiceMeta as ServiceMeta;
use Cloud\Core\Model\App\Design\Assets as Assets;
use Cloud\Core\Model\App\Design\Seo as Seo;

Class Design
{
    use \Cloud\Core\Library\ObjectTrait\EventingObject;

    /**
     * The default package folder. In the event a file isn't found in the current package, system will attempt to "fall back" to defautl
     * @var string
     */
    protected $_defaultPackage = null;

    /**
     * The current package folder
     * @var string
     */
    protected $_currentPackage = null;

    /**
     * The default layout file to use
     * @var string
     */
    protected $_defaultLayout = null;

    /**
     * The layout to be loaded
     * @var string
     */
    protected $_layout = "";

    /**
     * Rendered content
     * @var string
     */
    protected $_content = "";

    const LAYOUT_CONTENT = "content";
    const LAYOUT_SUBDIR = "page";
    const DEFAULT_VIEW_EXTENSION = ".volt";

    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the design
     * @return \Cloud\Core\Model\App\Design
     */
    public function init()
    {
        $this->loadView();
        $this->loadWebsiteDesign();
        $this->loadAssets();
        $this->loadSeo();
        return $this;
    }

    /**
     * Load the assets service
     * @return \Cloud\Core\Model\App\Design
     */
    public function loadAssets()
    {
        Cloud::di()->setShared(ServiceMeta::SERVICE_ASSETS, new Assets());
        return $this;
    }

    /**
     * Return the assets service
     * @return \Cloud\Core\Model\App\Design\Assets
     */
    public function getAssets()
    {
        return Cloud::di()->getShared(ServiceMeta::SERVICE_ASSETS);
    }

    /**
     * Load the SEO service
     * @return \Cloud\Core\Model\App\Design
     */
    public function loadSeo()
    {
        Cloud::di()->setShared(ServiceMeta::SERVICE_SEO, new Seo());
        return $this;
    }

    /**
     * Return the SEO service
     * @return \Cloud\Core\Model\App\Design\Seo
     */
    public function getSeo()
    {
        return Cloud::di()->getShared(ServiceMeta::SERVICE_SEO);
    }

    /**
     * Prepare the page for render. This function handles assigning important variables to the View
     * @return \Cloud\Core\Model\App\Design
     */
    public function loadPage()
    {
        Cloud::events()->fire("design:before_page_load", $this);
        Cloud::events()->fire($this->getWebsiteEventName("design:before_page_load"), $this);
        $this->getView()->setVars(
            array(
                "seo"    => $this->getSeo(),
                "assets" => $this->getAssets()
            )
        );
        Cloud::events()->fire("design:after_page_load", $this);
        Cloud::events()->fire($this->getWebsiteEventName("design:after_page_load"), $this);
        return $this;
    }

    /**
     * Render the given template file inside the currently set layout.
     * Return the fully rendered html page
     *
     * @param string $template_file
     *
     * @return string
     */
    public function renderPage($template_file)
    {
        $this->getView()->setVar(self::LAYOUT_CONTENT, $template_file);

        /** Fire global and website specific **/
        Cloud::events()->fire("design:before_page_render", $this);
        Cloud::events()->fire($this->getWebsiteEventName("design:before_page_render"), $this);

        $this->setContent($this->getView()->render($this->getLayoutPath($this->getLayout())));

        Cloud::events()->fire("design:after_page_render", $this);
        Cloud::events()->fire($this->getWebsiteEventName("design:after_page_render"), $this);

        $this->_cleanupRender();
        return $this->getContent();
    }

    /**
     * Add the correct package prefix to a template file.
     * This function allows for an "override" package. If the requested template file isn't found in the override, it checks the default package.
     * If the file exists no where, an exception is triggered.
     *
     * This function also checks if the package has _already_ been added. Because of the way volt works, on the initial call to view::render,
     * this function is hit twice for the same file, once from the view and once from the compiler engine. For that reason, the first line checks that a package is not already in place.
     *  *** I recognize that isn't the ideal way to handle this, but I couldn't find an easy solution to get around it. It's a minimal performance hit at most
     * @see Cloud\Core\Model\App\View::render()
     * @see Cloud\Core\Model\App\View\Engine\Volt\Compiler::compileFile
     *
     * @param string $template_file_path
     *
     * @throws Cloud\Core\Model\Exception
     *
     * @param mixed  $context
     *
     * @return unknown|Ambigous <string, mixed>
     */
    public function addPackage($template_file_path, $context)
    {
        if ($this->fileHasPackage($template_file_path)) {
            return $template_file_path;
        }
        $file = str_replace($this->getWebsiteDesignDir(), "", $template_file_path);
        $match = false;
        foreach ($this->getPackages() as $package) {
            $in_package = $this->getWebsiteDesignDir() . $package . DS . $file . self::DEFAULT_VIEW_EXTENSION;
            if (file_exists($in_package)) {
                if ($context instanceof \Cloud\Core\Model\App\View) {
                    $file = $package . DS . $file;
                } else {
                    if ($context instanceof \CLoud\Core\Model\App\View\Engine\Volt\Compiler) {
                        $file = $in_package;
                    }
                }
                $match = true;
                break;
            }
        }
        if (!$match) {
            \Cloud::throwException('Template file not found in default package: ' . __METHOD__);
        }
        return $file;
    }

    /**
     * Check if the given template file has a package prefix
     *
     * @param string $file
     *
     * @return boolean
     */
    public function fileHasPackage($file)
    {
        $match = false;
        foreach ($this->getPackages() as $package) {
            if (strstr($file, $this->getWebsiteDesignDir() . $package)) {
                $match = true;
            }
        }
        return $match;
    }

    /**
     * Return the package list
     * @return array
     */
    public function getPackages()
    {
        return array($this->getCurrentPackage(), $this->getDefaultPackage());
    }

    /**
     * Set the layout to be rendered
     *
     * @param string $layout
     *
     * @return \Cloud\Core\Model\App\Design
     */
    public function setLayout($layout = false)
    {
        if (!$layout) {
            $layout = $this->getDefaultLayout();
        }
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Return the layout to be rendered
     * @return string
     */
    public function getLayout()
    {
        if (!$this->_layout) {
            $this->setLayout(false);
        }
        return $this->_layout;
    }

    /**
     * Set the rendered content
     *
     * @param string $content
     *
     * @return \Cloud\Core\Model\App\Design
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * Get the rendered content
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Load the current design based on the in-scope website
     * @return \Cloud\Core\Model\App\Design
     */
    public function loadWebsiteDesign()
    {
        $this->getView()->setViewsDir($this->getWebsiteDesignDir());
        $this->setCurrentPackage($this->getWebsite()->getDesignPackage());
        $this->setDefaultPackage($this->getWebsite()->getDefaultDesignPackage());
        $this->setDefaultLayout($this->getWebsite()->getDesignLayout());
        return $this;
    }

    /**
     * Get the default package
     * @return string
     */
    public function getDefaultPackage()
    {
        return $this->_defaultPackage;
    }

    /**
     * Set the default package
     *
     * @param string $package
     *
     * @return \Cloud\Core\Model\App\Design
     */
    public function setDefaultPackage($package)
    {
        $this->_defaultPackage = $package;
        return $this;
    }

    /**
     * Return the current layout (if there is one)
     * @return string
     */
    public function getCurrentLayout()
    {
        return $this->_currentLayout;
    }

    /**
     * Set the current layout
     *
     * @param string $layout
     *
     * @return \Cloud\Core\Model\App\Design
     */
    public function setCurrentLayout($layout)
    {
        $this->_currentLayout = $layout;
        return $this;
    }

    /**
     * Return the in scope package
     * @return string
     */
    public function getCurrentPackage()
    {
        return $this->_currentPackage;
    }

    /**
     * Set the in scope package
     *
     * @param string $package
     *
     * @return \Cloud\Core\Model\App\Design
     */
    public function setCurrentPackage($package)
    {
        $this->_currentPackage = $package;
        return $this;
    }

    /**
     * Return the default layout
     * @return string
     */
    public function getDefaultLayout()
    {
        return $this->_defaultLayout;
    }

    /**
     * Set the default layout
     *
     * @param string $layout
     *
     * @return \Cloud\Core\Model\App\Design
     */
    public function setDefaultLayout($layout)
    {
        $this->_defaultLayout = $layout;
        return $this;
    }

    /**
     * Load the view service
     * @return \Cloud\Core\Model\App\Design
     */
    public function loadView()
    {
        $view = new View();
        $view->registerEngines(
            array(
                ".volt" => function ($view, $di) {
                        $volt = new \Cloud\Core\Model\App\View\Engine\Volt($view, $di);
                        $volt->setOptions(
                            array(
                                "compiledPath" => $this->getCompiledViewsDir()
                            )
                        );
                        return $volt;
                    }

            )
        );
        Cloud::app()->getDi()->setShared(ServiceMeta::SERVICE_VIEW, $view);
        return $this;
    }

    /**
     * Return the view service
     * @return \Cloud\Core\Model\App\View
     */
    public function getView()
    {
        return Cloud::app()->getDi()->getShared(ServiceMeta::SERVICE_VIEW);
    }

    /**
     * Get the path to the views dir, which is the design directory + the website view dir
     * @return string
     */
    public function getWebsiteDesignDir()
    {
        $design = $this->getBaseDir();
        $dir = $design . DS . $this->getWebsite()->getDesignDir();
        if (substr($dir, 0, -1) != DS) {
            $dir .= DS;
        }
        return $dir;
    }

    /**
     * Return the relative path from the package to the layouts directory
     *
     * @param string $layout
     *
     * @return string
     */
    public function getLayoutPath($layout)
    {
        return self::LAYOUT_SUBDIR . DS . $layout;
    }

    /**
     * Return the in scope website
     * @return \Cloud\Core\Model\App\Website
     */
    public function getWebsite()
    {
        return Cloud::app()->getWebsite();
    }

    public function getCompiledViewsDir()
    {
        $dir = Cloud::app()->getConfig()->getDir("design_compiled") . DS . $this->getWebsite()->getCode() . DS;
        return $dir;
    }

    /**
     * Get the path the design directory
     * @return string
     */
    public function getBaseDir()
    {
        return Cloud::app()->getConfig()->getDir("design");
    }

    /**
     * Cleanup function
     * @return \Cloud\Core\Model\App\Design
     */
    protected function _cleanupRender()
    {
        return $this;
    }
}