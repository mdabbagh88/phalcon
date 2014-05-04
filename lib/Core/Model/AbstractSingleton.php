<?php
namespace Cloud\Core\Model;

use Phalcon;
use \Cloud\Core\Library\ObjectTrait as ObjectTraits;

Class AbstractSingleton extends Phalcon\Mvc\Model
{
    use ObjectTraits\SingletonObject;
}