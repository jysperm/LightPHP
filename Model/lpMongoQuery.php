<?php

/**
 * Class lpMongoQuery
 */
class lpMongoQuery implements iModelQuery
{
    /** @var string $primary 主键字段 */
    public $primary = "_id";

    /** @var MongoCollection $mongo */
    private $mongo;
    /** @var string $table */
    private $table;

    /**
     * @param MongoDB $mongo
     * @param string $table
     */
    public function __construct($mongo, $table)
    {
        $this->mongo = $mongo->selectCollection($table);
        $this->table = $table;
    }

    /**
     * 查询数据
     *
     * @param array $if 检索条件，Mongo 语法
     * @param array $options 选项 [
     *     "sort" => [<排序字段> => <(bool)是否为正序>, <排序字段> => <(bool)是否为正序>],
     *     "select" => [<要检索的字段>],
     *     "skip" => <(int)跳过条数>,
     *     "limit" => <(int)检索条数>
     * ]
     *
     * @return MongoCursor
     */
    public function select(array $if = [], array $options = [])
    {
        $fields = isset($options["select"]) ? $options["select"] : [];

        $cursor = $this->mongo->find($if, $fields);

        return $this->applyOptions($cursor, $options);
    }

    /**
     * 获取符合条件的第一条数据
     *
     * @param array $if 条件，Mongo 语法
     * @param array $options 选项 [
     *     "select" => [<要检索的字段>]
     * ]
     *
     * @return array|null 成功返回数组，失败返回 null
     */
    public function findOne(array $if = [], array $options = [])
    {
        $fields = isset($options["select"]) ? $options["select"] : [];

        return $this->mongo->findOne($if, $fields);
    }

    /**
     * 获取所有符合条件的记录为二维数组
     *
     * @param array $if 条件，Mongo 语法
     * @param array $options 选项，同 select()
     *
     * @return array [
     *     ["field1" => "value1", "field2" => "value2"],
     *     ["field1" => "value1", "field2" => "value2"]
     * ]
     */
    public function selectArray(array $if = [], array $options = [])
    {
        return iterator_to_array($this->select($if, $options));
    }

    /**
     * 获取某个字段的值的数组
     *
     * @param string $field 字段
     * @param array $if 条件，Mongo 语法
     * @param array $options 选项，同 select()
     *
     * @return array [
     *     "value1", "value2", "value3"
     * ]
     */
    public function selectValueList($field, array $if = [], array $options = [])
    {
        $rs = iterator_to_array($this->select($if, $options));
        foreach ($rs as &$v)
            $v = $v[$field];
        return $rs;
    }

    /**
     * 获取以主键为键的二维数组
     *
     * @param array $if 条件，Mongo 语法
     * @param array $options 选项，同 select()
     * @param string|null $field 作为键的字段，null 表示使用主键
     *
     * @return array [
     *     "value1" => ["field1" => "value1", "field2" => "value2"],
     *     "value2" => ["field1" => "value1", "field2" => "value2"]
     * ]
     */
    public function selectPrimaryArray(array $if = [], array $options = [], $field = null)
    {
        $field = $field ? : $this->primary;

        $rs = iterator_to_array($this->select($if, $options));
        $result = [];
        foreach ($rs as $v)
            $result[$v[$field]] = $v;
        return $result;
    }

    /**
     * 获取符合条件的行数
     *
     * @param array $if 条件，Mongo 语法
     * @param array $options 选项，同 select()
     * @return int
     */
    public function count(array $if = [], array $options = [])
    {
        $fields = isset($options["select"]) ? $options["select"] : [];

        $cursor = $this->mongo->find($if, $fields);

        return $this->applyOptions($cursor, $options)->count();
    }

    /**
     * 插入数据
     *
     * @param array $data 数据
     * @param array $options Mongo 语法
     * @return array|null 来自 Mongo
     */
    public function insert(array $data, $options = [])
    {
        return $this->mongo->insert($data, $options);
    }

    /**
     * 插入多条数据
     *
     * @param array[array] $data 数据
     * @param array $options Mongo 语法
     * @return array[array|null] 来自 Mongo
     */
    public function insertArray(array $data, array $options = [])
    {
        $result = [];
        foreach ($data as $i)
            $result[] = $this->insert($i);
        return $result;
    }

    /**
     * 更新数据
     * 仅更新列出的字段
     *
     * @param array $if 条件，Mongo 语法
     * @param array $data 新数据
     * @param array $options Mongo 语法
     *
     * @return array|bool 来自 Mongo
     */
    public function update(array $if, array $data, array $options = [])
    {
        return $this->mongo->update($if, ['$set' => $data], $options);
    }

    /**
     * 替换数据
     *
     * @param array $if 条件，Mongo 语法
     * @param array $data 新数据
     * @param array $options Mongo 语法
     *
     * @return array|bool 来自 Mongo
     */
    public function replace(array $if, array $data, array $options = [])
    {
        return $this->mongo->update($if, $data, $options);
    }

    /**
     * 删除数据
     *
     * @param array $if 条件，Mongo 语法
     * @param array $options Mongo 语法
     * @return array|bool 来自 Mongo
     */
    public function delete(array $if, array $options = [])
    {
        return $this->mongo->remove($if, $options);
    }

    public function getAttribute($name)
    {
        switch($name)
        {
            case "primary":
                return $this->primary;
            default:
                return null;
        }
    }


    /**
     * @return MongoCollection
     */
    public function driver()
    {
        return $this->mongo;
    }

    /**
     * @param MongoCursor $cursor
     * @param array $options
     * @return MongoCursor
     */
    private function applyOptions($cursor, array $options)
    {
        if(isset($options["sort"]))
        {
            foreach($options["sort"] as &$v)
                $v = $v ? 1 : -1;

            $cursor->sort($options["sort"]);
        }

        if(isset($options["skip"]))
            $cursor->skip($options["skip"]);

        if(isset($options["limit"]))
            $cursor->limit($options["limit"]);

        return $cursor;
    }
}