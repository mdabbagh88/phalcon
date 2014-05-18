<?php
namespace Cloud\Core\Model;

Class AbstractModel extends \Phalcon\Mvc\Model
{
    use \Cloud\Core\Library\ObjectTrait\FactoryObject;
    use \Cloud\Core\Library\ObjectTrait\EventingObject;
    use \Cloud\Core\Library\ObjectTrait\CachingObject;

    /**
     * Load a single model by an id an optional unique field
     *
     * @param mixed  $id
     * @param string $key If provided, should be a unique mysql column. Otherwise, the primary key is used
     *
     * @return \Cloud\Core\Model\AbstractModel
     */
    public static function load($id, $key = false)
    {
        $params = $key ? array($key => $id) : $id;
        return $this->findFirst($params);
    }
}