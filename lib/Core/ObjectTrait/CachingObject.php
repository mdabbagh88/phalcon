<?php
namespace Lib\Core\ObjectTrait;

use Lib\Core\ObjectTrait;

Trait CachingObject
{
    protected $_caching_prefix_separator = "-";

    /**
     * Get the cache key for this class
     *
     * @param string $value
     *
     * @return string
     */
    protected function _getCacheKey($value)
    {
        $_prefix = isset($this->_cachePrefix) ? $this->_cachePrefix : $this->_getDefaultPrefix();
        return $_prefix . $this->_caching_prefix_separator . $value;
    }

    protected function _getDefaultPrefix()
    {
        $class = get_class($this);
        $class = strtolower(str_replace("\\", "_", $class));
        return $class;
    }
}