<?php
/**
 *
 */
namespace Lib\Core\Xml;

class XmlObject
{
    public $data;

    public function __construct($fileName)
    {
        $this->data = self::XML2Object(file_get_contents($fileName));
    }

    /**
     * Data Setter
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param $key
     * @param $val
     */
    public function __set($key, $val)
    {
        $this->data->$key = $val;
    }

    /**
     * Data Getter
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param $key
     *
     * @return null
     */
    public function __get($key)
    {
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
        return null;
    }

    /**
     * Convert XML String to Object
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param      $xml
     * @param bool $recursive
     *
     * @return \stdClass
     */
    public static function XML2Object($xml, $recursive = false)
    {
        if (!$recursive) {
            $array = simplexml_load_string($xml);
        } else {
            $array = $xml;
        }

        $newObj = new \stdClass();
        $array = (array)$array;
        foreach ($array as $key => $value) {
            $value = ( array )$value;
            if (isset ($value[0])) {
                $newObj->{$key} = trim($value[0]);
            } else {
                $newObj->{$key} = self::XML2Object($value, true);
            }
        }
        return $newObj;
    }

}