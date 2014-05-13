<?php
namespace Cloud\Core\Model\Http;
Class Response extends \Phalcon\Http\Response
{
    public function sendRedirect($location, $externalRedirect=false, $statusCode=301)
    {
        parent::redirect($location, $externalRedirect, $statusCode);
        parent::send();
        return $this;
    }
    
    public function sendRedirectExit($location, $externalRedirect=false, $statusCode=301)
    {
        parent::redirect($location, $externalRedirect, $statusCode);
        parent::send();
        exit;
    }
}