<?php

/**
*   该文件包含 lpDBQuery 的类定义.
*
*   @package LightPHP
*/

/**
*   数据库查询类.
*
*   该类可通过链式调用完成各种数据库查询.
*
*   @type resource class
*/

class lpDBQuery
{
    /** @type lpDBDrive 底层数据库驱动实例 */
    private $conn = null;

    /** @type string 要查询的目标表名 */
    private $table = null;

    /** @type lpDBInquiryDrive 当前的查询条件 */
    private $inquiry = null;

    /**
    *   构造一个查询器.
    *
    *   @param lpDBDrive $conn 底层数据库驱动实例
    */

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->inquiry = $conn->getInquiry();
    }

    /**
    *   开始一个查询.
    *
    *   链式调用.
    *
    *   @param string $table 表名
    *
    *   @return $this
    */

    public function __invoke($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
    *   向数据表插入一行.
    *
    *   终结链式调用.
    *
    *   @param array  $row   要插入的数据 [列名 => 值]
    */

    public function insert($row)
    {
        $this->conn->insert($this->table, $row);
        $this->cleanUp();
    }

    /**
    *   从数据表查询数据.
    *
    *   终结链式调用.
    *
    *   @param array  $config 查询选项 [选项 => 值]
    */

    public function select($config)
    {
        $r = $this->conn->select($this->table, $this->inquiry, $config);
        $this->cleanUp();
        return new lpDBResult($r, $this->conn);
    }

    /**
    *   获取结果集的第一项.
    *
    *   终结链式调用.
    *
    *   @param array  $config 查询选项 [选项 => 值]
    *
    *   @return array|null
    */

    public function top($config)
    {
        $r = $this->conn->select($this->table, $this->inquiry, $config);
        $this->cleanUp();
        $r = new lpDBResult($r, $this->conn);
        if($r->read)
            return $r->toArray();
        else
            return null;
    }

    /**
    *   从数据表修改数据.
    *
    *   终结链式调用.
    *
    *   @param array  $new    新数据 [列名 => 值]
    */

    public function update($new)
    {
        $this->conn->update($this->table, $this->inquiry, $new);
        $this->cleanUp();
    }

    /**
    *   从数据表删除数据.
    *
    *   终结链式调用.
    */

    public function delete()
    {
        $this->conn->delete($this->table, $this->inquiry);
        $this->cleanUp();
    }

    /**
    *   添加查询条件(AND操作).
    *
    *   链式调用.
    *
    *   @param string $key   列名
    *   @param string $value 值
    *   @param enum(Operator) $operator 操作符
    *
    *   @return $this
    */

    public function where($key, $value, $operator=lpDBInquiryDrive::Equal)
    {
        $this->inquiry->andC($key, $value, $operator);
        return $this;
    }

    /**
    *   添加查询条件(AND操作).
    *
    *   链式调用.
    *
    *   @param lpDBInquiryDrive $other 查询条件
    *
    *   @return $this
    */

    public function whereI($other)
    {
        $this->inquiry->andOther($other);
        return $this;
    }

    /**
    *   添加查询条件(OR操作).
    *
    *   链式调用.
    *
    *   @param string $key   列名
    *   @param string $value 值
    *   @param enum(Operator) $operator 操作符
    *
    *   @return $this
    */

    public function whereOr($key, $value, $operator=lpDBInquiryDrive::Equal)
    {
        $this->inquiry->orC($key, $value, $operator);
        return $this;
    }

    /**
    *   添加查询条件(OR操作).
    *
    *   链式调用.
    *
    *   @param lpDBInquiryDrive $other 查询条件
    *
    *   @return $this
    */

    public function whereOrI($other)
    {
        $this->inquiry->orOther($other);
        return $this;
    }

    /**
    *   对查询取反(NOT操作)
    *
    *   链式调用.
    *
    *   @return $this
    */

    public function notC()
    {
        $this->notC();
        return $this;
    }

    /**
    *   清除该实例的状态信息.
    */

    private function cleanUp()
    {
        $this->table = null;
        $this->inquiry = $this->conn->getInquiry();
    }
}
