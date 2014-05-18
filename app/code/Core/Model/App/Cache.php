<?php
namespace Cloud\Core\Model\App;

use
    Redis as Redis,
    Phalcon\Cache\Multiple as PhalconCache,
    Phalcon\Cache\Backend\Apc as ApcCache,
    Cloud\Core\Model\Cache\Backend\Redis as RedisCache,
    Phalcon\Cache\Backend\Memcache as MemcacheCache,
    Cloud\Core\Model\Cache\Backend\File as FileCache;

Class Cache extends PhalconCache
{

    const CACHE_KEY_DATA = "data_cache";

    const CACHE_BACKEND_REDIS = "Redis";
    const CACHE_BACKEND_MEMCACHED = "Memcache";
    const CACHE_BACKEND_FILE = "File";

    const CACHE_FRONTEND_OUTPUT = "Output";
    const CACHE_FRONTEND_DATA = "Data";

    const INTERVAL_HOUR = 3600;
    const INTERVAL_DAY = 86400;
    const DEFAULT_LIFETIME = 3600;

    const DEFAULT_REDIS_HOST = "localhost";
    const DEFAULT_REDIS_PORT = "6379";
    const DEFAULT_MEMCACHED_HOST = "localhost";
    const DEFAULT_MEMCACHED_PORT = "11211";
    const DEFAULT_FILE_DIR = "/tmp";

    protected $_cacheEnabled = false;

    public function __construct(Config & $config)
    {
        $cacheData = $config->get("application/cache", false);
        if (!$cacheData || !$config->get("application/cache/enabled")) {
            $this->setCacheEnabled(false);
        } else {
            $layers = (array)$config->get("application/cache/layers");
            usort(
                $layers,
                function ($a, $b) {
                    if (isset($a["priority"]) && isset($b["priority"])) {
                        if ($a["priority"] == $b["priority"]) {
                            return 0;
                        }
                        return $a["priority"] > $b["priority"] ? 1 : -1;
                    } else {
                        return 1;
                    }
                }
            );
            $backends = array();
            foreach ($layers as $layer) {
                $backends[] = $this->getCacheInstance($layer);
            }
            if (!sizeof($backends)) {
                $this->setCacheEnabled(false);
            } else {
                $this->setCacheEnabled(true);
                parent::__construct($backends);
            }
        }
    }

    public function getCacheInstance($configDescription, $frontendType = false)
    {
        if (!$frontendType) {
            $frontendType = self::CACHE_FRONTEND_DATA;
        }
        $frontName = "\Phalcon\Cache\Frontend\\$frontendType";
        $frontend = new $frontName(array(
            "lifetime" => isset($configDescription["lifetime"]) ? $configDescription["lifetime"] : self::DEFAULT_LIFETIME
        ));
        switch ($configDescription["backend"]) {
            case self::CACHE_BACKEND_REDIS:
                $redis = new Redis();
                $redis->connect(isset($configDescription["host"]) ? $configDescription["host"] : self::DEFAULT_REDIST_HOST, isset($configDescription["port"]) ? $configDescription["port"] : self::DEFAULT_REDIS_PORT);
                return new RedisCache($frontend, array(
                    "redis" => $redis
                ));
                break;
            case self::CACHE_BACKEND_MEMCACHED:
                return new MemcacheCache($frontend, array(
                    "host" => isset($configDescription["host"]) ? $configDescription["host"] : self::DEFAULT_MEMCACHED_HOST,
                    "port" => isset($configDescription["port"]) ? $configDescription["port"] : self::DEFAULT_MEMCACHED_PORT
                ));
                break;
            case self::CACHE_BACKEND_FILE:
            default:
                return new FileCache($frontend, array(
                    "cacheDir" => isset($configDescription["cacheDir"]) ? $configDescription["cacheDir"] : self::DEFAULT_FILE_DIR,
                ));
                break;
        }
    }

    /**
     * Lookup a given cache key. If it doesn't exist, run the callback function to lazy load the cache value and then store it in cache
     *
     * @param string  $keyName
     * @param closure $callback
     * @param long    $lifetime
     *
     * @return mixed
     */
    public function load($keyName, $callback, $lifetime = null)
    {
        if (!$this->exists($keyName)) {
            $_cached_value = $callback();
            $this->save($keyName, $_cached_value, $lifetime);
        } else {
            $_cached_value = $this->get($keyName, $lifetime);
        }
        return $_cached_value;
    }

    /**
     * Returns a cached content reading the internal backends
     *
     * @param    string $keyName
     * @param   long    $lifetime
     *
     * @return  mixed
     */
    public function get($keyName, $lifetime = null)
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }
        return parent::get($keyName, $lifetime);
    }


    /**
     * Stores cached content into all backends and stops the frontend
     *
     * @param string  $keyName
     * @param string  $content
     * @param long    $lifetime
     * @param boolean $stopBuffer
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = null)
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }
        return parent::save($keyName, $content, $lifetime, $stopBuffer);
    }


    /**
     * Deletes a value from each backend
     *
     * @param int|string $keyName
     *
     * @return boolean
     */
    public function delete($keyName)
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }
        return parent::delete($keyName);
    }


    /**
     * Checks if cache exists in at least one backend
     *
     * @param  string $keyName
     * @param  long   $lifetime
     *
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }
        return parent::exists($keyName, $lifetime);
    }

    /**
     * Flag specifiying whether cache is turned on
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return $this->_cacheEnabled;
    }

    /**
     * Set cache enabled/disabled
     *
     * @param unknown $flag
     *
     * @return \Cloud\Core\Model\App
     */
    public function setCacheEnabled($flag)
    {
        $this->_cacheEnabled = $flag;
        return $this;
    }

    /**
     * Print a message if cache debugging is allowed
     *
     * @param string $message
     *
     * @return \Cloud\Core\Model\App
     */
    public function debugCache($message)
    {
        if ($this->getDebugCache()) {
            print "<pre>";
            print $message . "\n";
            print "</pre>";
        }
        return $this;
    }

    public function setDebugCache($debug = true)
    {
        \Cloud::register("__cache/debug", $debug);
    }

    public function getDebugCache()
    {
        return \Cloud::registry("__cache/debug");
    }

    /**
     * Get all backend caches
     * @return array
     */
    public function getBackends()
    {
        return $this->_backends;
    }

    /**
     * Flush all backend caches
     * @return \Cloud\Core\Model\App\Cache
     */
    public function flush()
    {
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        foreach ($this->getBackends() as $backend) {
            $backend->flush();
        }
        return $this;
    }
}