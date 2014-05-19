<?php
namespace Cloud\Core\Model;

Class AbstractSingleton extends \Phalcon\Mvc\Model
{
    use \Lib\Core\ObjectTrait\SingletonObject;
    use \Lib\Core\ObjectTrait\EventingObject;
    use \Lib\Core\ObjectTrait\CachingObject;
}