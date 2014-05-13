<?php 
namespace Cloud\Core\Library\ObjectTrait;
trait SingletonObject 
{
	protected static $_instance = null;
	
	public static function instance()
	{
		if (is_null(self::$_instance))
		{
			self::$_instance = new self(); 
		}
		return self::$_instance;
	}
	protected static $inst = null;
   
   	protected function __clone()
   	{
   	    \Cloud::throwException("Cloning of Singletons not allowed in " . __METHOD__); 
   	}
}