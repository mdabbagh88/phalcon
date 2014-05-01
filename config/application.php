<?php
/** @var $this \Cloud\Core\Model\App */
$_config = $this->getConfig();

/** Settings for local installation **/
if ($this->isServer("development")) {
    $_local_config = new Phalcon\Config(array(
        "application" => array(
            "status"   => \Cloud\Core\Model\App::APP_STATUS_DEVELOPMENT,
            "base_uri" => "/",
            "cache"    => array(
                "enabled"  => true,
                //"backend"  => \Cloud\Core\Model\App::CACHE_BACKEND_MEMCACHED,
                "port"     => "11211",
                "host"     => "localhost",
                "cacheDir" => APP_PATH . DS . "var" . DS . "cache" . DS, //files only
                "lifetime" => 3600
            ),
            "session"  => array(
                //"save_path" => \Cloud\Core\Model\App::SESSION_SAVE_MEMCACHED,
                "save_path" => \Cloud\Core\Model\App::SESSION_SAVE_FILE,
                //"host"      => "localhost",
                //"port"      => "11212",
                "lifetime"  => 3600 * 24 //One day sessions
            )
            /** CACHING EXAMPLES
             * File example:
             * cache => array(
             *    enabled => true,
             *  backend => \Cloud::CACHE_BACKEND_FILE,
             *  cacheDir=> Cloud::registry("var_path") . DS . "/cache",
             *  lifetime=> 3600
             * )
             *
             * Memcached Example
             * cache => array(
             *    enabled => true
             *  backend => \Cloud::CACHE_BACKEND_MEMCACHED,
             *  port    => 11211,
             *  host    => localhost,
             *  lifetime=> 3600
             * )
             */
        )
    ));

    $_config->merge($_local_config);
    /** END Local Installation Settings **/

    /** Settings for staging installation **/
} elseif ($this->isServer("staging")) {


    /** Settings for production installation **/
} elseif ($this->isServer("production")) {


}