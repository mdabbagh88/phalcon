<?php
namespace Cloud\Core\Model;

use Phalcon;
use \Cloud\Core\Library\ObjectTrait as ObjectTraits;

Class AbstractModel extends Phalcon\Mvc\Model
{
    use ObjectTraits\FactoryObject;
}