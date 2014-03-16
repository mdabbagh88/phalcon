<?php 
namespace Cloud\Core\Library\ObjectTrait;
trait SingletonObject 
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