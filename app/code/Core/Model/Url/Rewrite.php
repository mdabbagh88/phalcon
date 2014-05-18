<?php
namespace Cloud\Core\Model\Url;

Use \Cloud\Core\Model\AbstractModel;
Use \Phalcon\Mvc\Model as PhalconModel;

Class Rewrite extends AbstractModel
{
    ##!generated
    /** Do not write custom code between the generated blocks, or it will be overwritten by the Model Generator **/

    /** @var int */
    public $url_rewrite_id;
    /** @var string */
    public $source_path;
    /** @var string */
    public $rewrite_path;
    /** @var int */
    public $redirect;
    /** @var int */
    public $redirect_external;
    /** @var string */
    public $redirect_status;

    /**
     * Get the table source for this model
     * @return string
     */
    public function getSource()
    {
        return 'core_url_rewrite';
    }
    /** End Generated **/
    ##!end-generated

    /**
     * Transform the given uri fragment for matching against the DB
     *
     * @param string $uri
     *
     * @return Rewrite
     */
    public static function match($uri)
    {
        $uri = preg_replace("/^([^\?]*)(\?.*)+$/", "$1", $uri); //Remove the query string, we don't evaluate that for rewriting urls
        if (substr($uri, -1) == "/") {
            $uri = substr($uri, 0, -1);
        } //Remove trailing slash, as we have chosen that source_paths in the table shouldn't contain one
        return self::findByUri($uri);
    }

    /**
     *
     * @param string $uri
     *
     * @return Rewrite
     */
    public static function findByUri($uri)
    {
        return parent::findFirst(
            array(
                "source_path = :uri:",
                "bind" => array(
                    "uri" => $uri
                )
            )
        );
    }

    /**
     * Return whether this should be redirected or just rewritten
     * @return boolean
     */
    public function isRedirect()
    {
        return intval($this->redirect) > 0;
    }

    /**
     * Return the written path for the source uri
     * @return string
     */
    public function getRewrite()
    {
        if (substr($this->rewrite_path, 0, 1) == "/") {
            $this->rewrite_path = substr($this->rewrite_path, 1); //Remove the beginning slash. We want the rewrite & redirect url pattern consistent.
            $this->save(); //Since Phalcon requires redirect (internal) urls to not have beginning slashes, we thought it best to follow in those footsteps
        }
        return "/" . $this->rewrite_path; //But for the sake of the router, we need to add back in the beginning slash (I know it seems dumb, but we want consistent data in the db)
    }

    /**
     * Return whether the redirect is external
     * @return boolean
     */
    public function isRedirectExternal()
    {
        return intval($this->redirect_external) > 0;
    }

    /**
     * Return the redirect url
     * @todo Put logic into place for transformations on the rewrite path (for full redirects)
     * @return string
     */
    public function getRedirectUrl()
    {
        if (substr($this->rewrite_path, 0, 1) == "/") {
            $this->rewrite_path = substr($this->rewrite_path, 1); //Remove a beginning slash
            $this->save();
        }
        return $this->rewrite_path;
    }

    /**
     * Return the redirect status code
     * @return string
     */
    public function getRedirectStatusCode()
    {
        return $this->redirect_status;
    }

}