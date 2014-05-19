<?php
namespace Cloud\Core\Model;

Class AbstractModel extends \Phalcon\Mvc\Model
{
    use \Lib\Core\ObjectTrait\FactoryObject;
    use \Lib\Core\ObjectTrait\EventingObject;
    use \Lib\Core\ObjectTrait\CachingObject;

    /**
     * Load a single model by an id an optional unique field
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param mixed $id
     * @param bool  $key If provided, should be a unique mysql column. Otherwise, the primary key is used
     *
     *
     * @return \Phalcon\Mvc\Model
     */
    public static function load($id, $key = false)
    {
        $params = $key ? array($key => $id) : $id;
        return self::findFirst($params);
    }
}