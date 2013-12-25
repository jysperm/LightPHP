<?php

interface iModelQuery
{
    public function __construct($pdo, $table);
    public function query($query, array $params);

    public function select(array $if = [], array $options = []);
    public function findOne(array $if = [], array $options = []);
    public function selectArray(array $if = [], array $options = []);
    public function selectValueList($field, array $if = [], array $options = []);
    public function selectPrimaryArray(array $if = [], array $options = [], $field = null);
    public function count(array $if = [], array $options = []);


    public function insert(array $data);
    public function insertArray(array $data);
    public function update(array $if, array $data);
    public function delete(array $if);

    public function getAttribute($name);
}

class lpModel implements ArrayAccess
{
    const DEFAULT_DRIVER = "lpPDOQuery";

    // 实例化部分

    protected $id = null;
    protected $data = [];

    /**
     * 根据指定的列来构造实例
     *
     * @param string $k
     * @param mixed $v
     * @return lpModel
     */
    public static function by($k, $v)
    {
        $primary = static::q()->getAttribute("primary");

        /** @var lpModel $i */
        $i = new static();
        $i->data = static::q()->findOne([$k => $v]);
        $i->id = isset($i->data[$primary]) ? $i->data[$primary] : null;
        return $i;
    }

    public static function byID($id)
    {
        if (!$id)
            return new static();
        return static::by("id", $id);
    }

    /**
     * 获取数据形式的数据
     *
     * @return array|null
     */
    public function data()
    {
        return $this->data;
    }

    public function id()
    {
        return $this->id;
    }

    public function update(array $data)
    {
        $primary = static::q()->getAttribute("primary");

        return static::q()->update([$primary => $this->id], $data);
    }

    public function delete()
    {
        $primary = static::q()->getAttribute("primary");

        return static::q()->delete([$primary => $this->id]);
    }

    // implements ArrayAccess

    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
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

    // 静态成员部分

    public static $dbQuerys = [];

    protected static $table = null;
    protected static $driver = null;

    /**
     * @return iModelQuery
     * @throws Exception
     */
    protected static function q()
    {
        $class = get_called_class();

        if(!isset(self::$dbQuerys[$class]))
        {
            $table = $class::$table;
            $driver = $class::$driver ? : self::DEFAULT_DRIVER;

            if(!class_exists($driver))
                throw new lpException("drive not support");

            self::$dbQuerys[$class] = new $driver(lpFactory::get("lpDBDriver", $driver), $table);
        }

        return self::$dbQuerys[$class];
    }
} 