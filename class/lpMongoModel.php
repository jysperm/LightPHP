<?php

/**
 * 该类提供了对 Mongo 数据库的访问
 *
 * 该类提供了与 lpPDOModel 几乎完全一致的接口。
 */
abstract class lpMongoModel
{
    // ----- 实例化部分

    /** @var array */
    protected $data = [];
    /** @var int */
    protected $id = null;

    /**
     * @param string $k
     * @param string $v
     * @return lpMongoModel
     */
    public static function by($k, $v)
    {
        $primary = static::metaData()["primary"];

        /** @var lpMongoModel $i */
        $i = new static();
        $i->data = static::find([$k => $v]);
        $i->id = isset($i->data[$primary]) ? $i->data[$primary] : null;
        return $i;
    }

    /**
     * 根据主键构造实例
     *
     * @param MongoId $id 主键的值
     * @return lpMongoModel
     */
    public static function byID($id)
    {
        if(!$id)
            return new static();
        return static::by(static::metaData()["primary"], $id);
    }

    /**
     * @return array
     */
    public function data()
    {
        return $this->data;
    }

    // ----- implements ArrayAccess

    public function offsetSet($offset, $value)
    {
        if(is_null($offset))
            $this->data[] = $value;
        else
            $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    // ----- 静态成员部分

    /**
     * @param array $data
     * @return array
     */
    protected static function meta(array $data)
    {
        $default = [
            "db" => lpFactory::get("MongoDB.LightPHP"),
            "primary" => "_id"
        ];

        return array_merge($default, $data);
    }

    /**
     * 示例：
     *
     * class PDOModel extends lpMongoModel
     * {
     *     protected static function metaData($data = null)
     *     {
     *         return parent::meta([
     *             "table" => "user"
     *         ]);
     *     }
     * }
     *
     * 子类可通过 meta() 来读入默认配置，然后只需提供 table 一项必选信息即可，
     * table 指定数据库中的集合名。
     *
     * @return array
     */
    protected static function metaData()
    {
        return self::meta([]);
    }

    /**
     * @return MongoDB
     */
    public static function getDB()
    {
        return static::metaData()["db"];
    }

    // ----- 数据库操作部分

    /** 查询操作符 */
    const QueryEscape = '$';
}