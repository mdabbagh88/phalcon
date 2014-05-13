<?php 
namespace Cloud\Core\Model\App;
use \Cloud\Core\Model\App;
use \Cloud as Cloud;
use \Lib\Phalcon\Session\Adapter\Memcache as MemcacheSession;
use \Lib\Phalcon\Session\Adapter\Redis as RedisSession;
use \Phalcon\Session\Adapter\Files as FileSession;
Abstract Class Session 
{
    const SESSION_SAVE_MEMCACHED = "Memcache";
    const SESSION_SAVE_REDIS     = "Redis";
    const SESSION_SAVE_FILE      = "File";

    const SESSION_PREFIX         = "cloud9living";
    
    const DEFAULT_SESSION_SAVE   = "File"; 
    const DEFAULT_LIFETIME       = 86400; //One day
    const DEFAULT_COOKIE_LIFETIME= 86400;
    const DEFAULT_COOKIE_NAME    = "cloud9living";
    /**
     * Session instance
     * @var \Phalcon\Session\Adapter
     */
    protected static $_instance = null;
    
    public static function getInstance(Config & $config)
    {
        if (is_null(self::$_instance)) {
            $session_type = $config->getConfig("application/session/save_path", self::DEFAULT_SESSION_SAVE);
            
            $website      = $config->getWebsite();
            $session_gc_lifetime     = intval($website->session_lifetime) ? $website->session_lifetime : self::DEFAULT_LIFETIME;
            $session_cookie_lifetime = intval($website->session_cookie_lifetime) ? $website->session_cookie_lifetime : self::DEFAULT_COOKIE_LIFETIME;
            $session_cookie_name     = $website->session_cookie_name && strlen($website->session_cookie_name) ? $website->session_cookie_name : self::DEFAULT_COOKIE_NAME;
            
            switch($session_type) {
            	case self::SESSION_SAVE_REDIS: 
            	    $host    = $config->getConfig("application/session/host");
            	    $port    = $config->getConfig("application/session/port");
            	    $session = new RedisSession(array(
            	       'path'              => "tcp://{$host}:{$port}?weight=1", //TCP socket for redis
            	       'lifetime'          => $session_gc_lifetime,
            	       'cookie_lifetime'   => $session_cookie_lifetime,
            	       'name'              => $session_cookie_lifetime
            	    ));
            	    break;
                case self::SESSION_SAVE_MEMCACHED:
                    $session = new MemcacheSession(array(
                        "host" 		=> $config->getConfig("application/session/host"),
                        "port" 		=> $config->getConfig("application/session/port"),
                        "lifetime" 	=> $config->getConfig("application/session/lifetime")
                    ));
                    break;
                case self::SESSION_SAVE_FILE:
                default:
                    $session = new FileSession(array(
                        "lifetime"	=> $config->getConfig("application/session/lifetime"),
                        "uniqueId"  => self::SESSION_PREFIX
                    ));
                    if (!file_exists($config->getDir("session"))) {
                        mkdir($config->getDir("session"), 0755, true);
                    }
                    ini_set("session.save_handler", "files"); //Manually force this. Phalcon assumes this is the default, so if memcached is actually the default you will have issues
                    session_save_path($config->getDir("session"));
                    break;
            }
            self::$_instance = $session;
            ini_set("session.name", $session_cookie_name);
            session_name($session_cookie_name);
            self::$_instance->start();
        }
        return self::$_instance;
    }   
    
}