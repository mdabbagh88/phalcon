<?php
namespace Lib\Core\ObjectTrait;

trait SingletonObject
{
    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected static $inst = null;

    protected function __clone()
    {

    }
}