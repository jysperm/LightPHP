<?php

namespace LightPHP\Model;

use ArrayAccess;
use LightPHP\Core\Exception\FeatureNotSupportException;
use LightPHP\Model\Adapter\QueryInterface;

class Model implements ArrayAccess
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
     * @return Model
     */
    public static function by($k, $v)
    {
        /** @var Model $i */
        $i = new static();
        $i->primary = static::q()->getAttribute("primary");
        $i->data = static::q()->findOne([$k => $v]);
        $i->id = isset($i->data[$i->primary]) ? $i->data[$i->primary] : null;
        return $i;
    }

    public static function byID($id)
    {
        if (!$id)
            return new static();
        return static::by(static::q()->getAttribute("primary"), $id);
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

    public function findOne(array $if = [], array $options = [])
    {
        return static::q()->findOne($if, $options);
    }

    public function update(array $data)
    {
        return static::q()->update([static::$primary => $this->id], $data);
    }

    public function delete()
    {
        return static::q()->delete([static::$primary => $this->id]);
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

    protected static $dbQueries = [];
    protected static $primary = null;

    private static $table = null;
    private static $driver = null;
    private static $source = null;

    /**
     * @throws FeatureNotSupportException
     * @return QueryInterface
     */
    protected static function q()
    {
        $class = get_called_class();

        if (!isset(self::$dbQueries[$class])) {
            $table = $class::$table;
            $driver = $class::$driver ? : self::DEFAULT_DRIVER;

            if (!class_exists($driver))
                throw new FeatureNotSupportException("drive not support");

            /** @var QueryInterface $queryDriver */
            $queryDriver = new $driver($class::$source, $table);

            self::$dbQueries[$class] = $queryDriver;

            $class::$primary = $queryDriver->getAttribute("primary");
        }

        return self::$dbQueries[$class];
    }
}
