<?php
namespace Lib\Core\ObjectTrait;

trait FactoryObject
{
    public static function instance()
    {
        return new self();
    }

    public static function getNew()
    {
        return self::instance();
    }
}