<?php 
namespace Cloud\Core\Model\App\Controller;
use 
    Cloud\Core\Model,
    Cloud\Core\Model\Url\Rewrite as UrlRewriter,
    Cloud\Core\Model\Http\Request as HttpRequest,
    Cloud\Core\Model\Http\Response as HttpResponse,
    Cloud\Core\Model\App,
    Cloud\Core\Model\App\Controller
    ;
use Cloud\Core\Model\AbstractModel;
use Cloud\Core\Model\Url\Rewrite;
use Cloud\Core\Model\App\ServiceMeta;
use Cloud\Core\Model\App\View;
use Cloud\Core\Model\App\Design;
use Cloud as Cloud;
Class Front 
{
    use \Cloud\Core\Library\ObjectTrait\EventingObject;
    /**
     * Application singleton
     * @var Cloud\Core\Model\App
     */
    protected $_app     = null;
    
    public function __construct(App & $app) 
    {
        $this->_app = $app;
    }
    
    /**
     * Handle an MVC request to the application.
     * Duties handled in this function
     *  - Load response & request
     *  - Rewrite urls (if applicable)
     *  - Choose a router and determine a route
     *  - Dispatch to that route
     *  - Start the view and collect output
     *  - Load output into a response
     *  - Send that response to the client
     *  @return \Cloud\Core\Model\App\Controller\Front
     */
    public function handle()
    {
        Cloud::events()->fire("controller_front:before_handle", $this);
        Cloud::events()->fire($this->getWebsiteEventName("controller_front", "before_handle"), $this);
        
        $this->loadHttpServices(); 
        $this->loadMvcServices();
        
        $router      = $this->getRouter();
        $dispatcher  = $this->getDispatcher();
        $design      = $this->getDesign();
        
        /** Check for a redirect / uri rewrite before we do anymore processing **/
        $uri         = $router->getRewriteUri(); 
        if ( ($rewriter = UrlRewriter::match($uri)) ) {
           if ($rewriter->isRedirect()) {
               $this->getResponse() 
                    ->sendRedirectExit($rewriter->getRedirectUrl(), $rewriter->isRedirectExternal(), $rewriter->getRedirectStatusCode())
                    ;
           } else {
               $uri = $rewriter->getRewrite();
           }
        }
        
        Cloud::events()->fire("controller_front:before_dispatch", $this, array(
        	"uri" => $uri
        ));
        Cloud::events()->fire($this->getWebsiteEventName("controller_front", "before_dispatch"), $this, array(
        	"uri" => $uri
        ));
        /** Match the uri to a route and update the result in the dispatcher **/
        $router->handle($uri); 
        $router->prime($dispatcher); 
        
        /** Start the view and dispatch the request **/
        $design->getView()->start();
        $dispatcher->dispatch(); 
        $design->getView()->end();
        
        /** Load the response object with the result from the view **/
        $response    = $this->getResponse();
        $response->setContent($design->getView()->getContent()); 
        
        Cloud::events()->fire("controller_front:before_send_response", $this);
        Cloud::events()->fire($this->getWebsiteEventName("controller_front", "before_send_response"), $this);
        /** Send the response back to the client **/
        $response->sendHeaders()
                 ->send();
        return $this;
        
    }
    
    /**
     * Load the HTTP Response & Request
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadHttpServices()
    {
        return $this->loadUrl()
             ->loadRequest()
             ->loadResponse();
    }
    
    /**
     * Load the router, dispatcher, and design singletons
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadMvcServices()
    {
        return $this->loadRouter()
              ->loadDispatcher()
              ->loadDesign()
              ;
    }
    
    /**
     * Load the router singleton
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadRouter()
    {
        $router = $this->_app->getWebsite()->getDefaultRouter(); 
        $router->init();
        $this->_app->getDI()->setShared(ServiceMeta::SERVICE_ROUTER, $router);
        return $this; 
    }
    
    /**
     * Return the current router
     * @return \Cloud\Core\Model\App\Controller\Router\AbstractRouter
     */
    public function getRouter()
    {
        return $this->_app->getDI()->getShared(ServiceMeta::SERVICE_ROUTER); 
    }
    
    /**
     * Load the dispatcher service
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadDispatcher()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_DISPATCHER, new Dispatcher($this));
        return $this;
    }
    
    /**
     * Get the dispatcher singleton
     * @return \Cloud\Core\Model\App\Controller\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->_app->getDI()->getShared(ServiceMeta::SERVICE_DISPATCHER);
    }
    
    /**
     * Load the design singleton
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadDesign()
    {
        $design = new Design();
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_DESIGN, new Design());
        return $this;
    }
    
    /**
     * Return the Design Singleton
     * @return \Cloud\Core\Model\App\Design
     */
    public function getDesign()
    {
        return $this->_app->getDi()->getShared(ServiceMeta::SERVICE_DESIGN);
    }
    
    /**
     * Set the service in the DI
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadResponse()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_HTTP_RESPONSE, new HttpResponse());
        return $this;
    }
    
    /**
     * @return \Cloud\Core\Model\Http\Response
     */
    public function getResponse()
    {
        return $this->_app->getDi()->getShared(ServiceMeta::SERVICE_HTTP_RESPONSE);
    }
    
    /**
     * Set the service in the DI
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadRequest()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_HTTP_REQUEST, new HttpRequest());
        return $this;
    }
    
    /**
     * Load the URL singleton in the DI
     * @return \Cloud\Core\Model\App\Controller\Front
     */
    public function loadUrl()
    {
        $this->_app->getDi()->setShared(ServiceMeta::SERVICE_URL, $url = new \Phalcon\Mvc\Url());
        return $this;
    }
    
    /**
     * @return \Cloud\Core\Model\Http\Request
     */
    public function getRequest()
    {
        return $this->_app->getDi()->getShared(ServiceMeta::SERVICE_HTTP_REQUEST);
    }
}