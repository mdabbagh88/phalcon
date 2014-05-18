<?php
namespace Cloud\Core\Model;

Class AbstractSingleton extends \Phalcon\Mvc\Model
{
    use \Cloud\Core\Library\ObjectTrait\SingletonObject;
    use \Cloud\Core\Library\ObjectTrait\EventingObject;
    use \Cloud\Core\Library\ObjectTrait\CachingObject;
}