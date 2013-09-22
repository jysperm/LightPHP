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

    /**
     * 查询数据
     *
     * @param array $if 检索条件(Mongo 语法)
     * @param array $select 要检索的字段(传递给 Mongo)
     * @return MongoCursor
     */
    public static function select(array $if = [], array $select = [])
    {
        $c = static::getDB()->selectCollection(static::metaData()["table"]);
        return $c->find($if, $select);
    }

    /**
     * 获取符合条件的第一条数据
     *
     * @param array $if 条件(Mongo 语法)
     * @param array $select 要检索的字段(传递给 Mongo)
     *
     * @return array|null  成功返回数组，失败返回null
     */
    public static function find(array $if = [], array $select = [])
    {
        $c = static::getDB()->selectCollection(static::metaData()["table"]);
        return $c->findOne($if, $select);
    }

    /**
     * 获取所有符合条件的记录为二维数组
     *
     * @param array $if 条件(Mongo 语法)
     * @param array $select 要检索的字段(传递给 Mongo)
     *
     * @return array [
     *     ["field1" => "value1", "field2" => "value2"],
     *     ["field1" => "value1", "field2" => "value2"]
     * ]
     */
    public static function selectArray(array $if = [], array $select = [])
    {
        $cur = static::select($if, $select);
        $result = [];
        foreach($cur as $row)
            $result[] = $row;
        return $result;
    }

    /**
     * 获取某个字段的值的数组
     *
     * @param $field 字段
     * @param array $if 条件(Mongo 语法)
     * @param array $select 要检索的字段(传递给 Mongo)
     *
     * @return array [
     *     "value1", "value2", "value3"
     * ]
     */
    public static function selectValueList($field, array $if = [], array $select = [])
    {
        $cur = static::select($if, $select);
        $result = [];
        foreach($cur as $row)
            $result[] = $row[$field];
        return $result;
    }

    /**
     * 获取以主键为键的二维数组
     *
     * @param string|null $field 作为键的字段，null 表示使用主键
     * @param array $if 条件(Mongo 语法)
     * @param array $select 要检索的字段(传递给 Mongo)
     *
     * @return array [
     *     "value1" => ["field1" => "value1", "field2" => "value2"],
     *     "value2" => ["field1" => "value1", "field2" => "value2"]
     * ]
     */
    public static function selectPrimaryArray($field, array $if = [], array $select = [])
    {
        if(!$field)
            $field = static::metaData()["primary"];

        $cur = static::select($if, $select);
        $result = [];
        foreach($cur as $row)
            $result[$row[$field]] = $row;
        return $result;
    }

    /**
     * 插入数据
     *
     * @param array $data 数据
     */
    public static function insert(array $data)
    {
        $c = static::getDB()->selectCollection(static::metaData()["table"]);
        $c->insert($data);
    }

    /**
     * 插入多条数据
     *
     * @param array $data 数据
     */
    public static function insertArray(array $data)
    {
        foreach($data as $i)
            static::insert($i);
    }

    /**
     * 更新数据
     *
     * @param array $if 条件(Mongo 语法)
     * @param array $data 新数据
     */
    public static function update(array $if, array $data)
    {
        $c = static::getDB()->selectCollection(static::metaData()["table"]);
        $c->update($if, $data, ["upsert" => true, "multiple" => true]);
    }

    /**
     * 删除数据
     *
     * @param array $if 条件(Mongo 语法)
     */
    public static function delete(array $if)
    {
        $c = static::getDB()->selectCollection(static::metaData()["table"]);
        $c->remove($if);
    }
}