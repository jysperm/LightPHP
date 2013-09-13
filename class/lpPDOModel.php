<?php

/**
 * 该类提供了简单的PDO数据源的访问，大概分三个部分：
 *
 * * 实例化部分：每个实例表示相应数据表中的一个条目
 * * 静态成员部分：子类需要重写 metaData() 来向该类提供表名等元信息
 * * 数据库操作部分：无需 SQL 的 CRUD.
 */
abstract class lpPDOModel implements ArrayAccess
{
    // 实例化部分

    protected $data = [];
    protected $id = null;

    /**
     * 根据指定的列来构造实例
     *
     * @param string $k
     * @param mixed $v
     * @return lpPDOModel
     */
    public static function by($k, $v)
    {
        $i = new static();
        $i->data = static::find([$k => $v]);
        $i->id = isset($i->data["id"]) ? $i->data["id"] : null;
        return $i;
    }

    public static function byID($id)
    {
        if(!$id)
            return new static();
        return static::by("id", $id);
    }

    /**
     * 获取数组形式的数据
     *
     * @return array|null
     */
    public function data()
    {
        return $this->data;
    }

    // implements ArrayAccess

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

    // 静态成员部分

    /* 数据类型 */
    const INT = "INT";
    const UINT = "INT UNSIGNED";
    const AI = "AUTO_INCREMENT";
    const VARCHAR = "VARCHAR";
    const TEXT = "TEXT";
    const JSON = "JSON";

    /* 数据修饰 */
    const PRIMARY = "PRIMARY";
    const NOTNULL = "NOT NULL";

    protected static function meta($data)
    {
        $default = [
            "db" => lpFactory::get("PDO.lpPDODB"),
            "table" => null,
            "engine" => "MyISAM",
            "charset" => "utf8"
        ];

        return array_merge($default, $data);
    }

    /**
     * 子类需要重写这个函数来提供元信息
     *
     * @return array
     */
    protected static function metaData()
    {

    }

    /**
     * 获取数据库连接，子类可以重写该函数提供不同的数据库连接
     *
     * @return PDO
     */
    public static function getDB()
    {
        return self::metaData()["db"];
    }

    // 数据库操作部分

    /**
     * 检索数据
     *
     * @param array $if 检索条件 [
     *     <字段> => <值>,
     *     q("OR") => [<字段> => <值>, ...],
     *     q(">") => [<字段> => <值>],
     *     "原始 SQL", ...
     * ]
     *
     * @param array $options 选项 [
     *     "sort" => [<排序字段> => <是否为正序>, <排序字段> => <是否为正序>],
     *     "select" => [<要检索的字段>],
     *     "skip" => <跳过条数>,
     *     "limit" => <检索条数>
     *     "count" => <是否只获取结果数>
     * ]
     *
     * ## q() 操作符列表
     * * OR
     * * >, >=, <, <=
     * * LIKE, %LIKE%
     *
     * @return PDOStatement
     */
    public static function select($if = [], $options = [])
    {
        $table = static::metaData()["table"];

        $select = "*";
        $where = static::buildWhere($if);
        $orderBy = "";
        $sqlLimit = "";

        foreach($options as $option => $value)
        {
            switch($option)
            {
                case "count":
                    if($value)
                        $select = "COUNT(*)";
                    break;
                case "sort":
                    foreach($value as $k => $v)
                    {
                        if(!$orderBy)
                            $orderBy = " ORDER BY ";
                        else
                            $orderBy .= ", ";

                        $orderBy .= "`{$k}` " . ($v ? "ASC" : "DESC");
                    }
                    break;
                case "select":
                    foreach($value as &$i)
                        $i = "`{$i}`";
                    $select = implode(", ", $value);
                    break;
            }
        }

        $skip = isset($options["skip"]) ? $options["skip"] : -1;
        $limit = isset($options["limit"]) ? $options["limit"] : -1;

        if($limit > -1 && $skip > -1)
            $sqlLimit = " LIMIT {$skip}, {$limit}";
        else if($limit > -1 && !($skip > -1))
            $sqlLimit = " LIMIT {$limit}";

        $sql = "SELECT {$select} FROM `{$table}` {$where} {$orderBy} {$sqlLimit}";

        $result = static::getDB()->query($sql);
        $result->setFetchMode(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 获取符合条件的第一条数据
     * @param array $if     条件
     * @param array $options 额外参数
     *
     * @return array|null  成功返回数组, 失败返回null
     */
    public static function find($if = [], $options = [])
    {
        $options = array_merge($options, ["limit" => 1]);
        $data = static::select($if, $options)->fetch();

        return $data ? static::jsonDecode($data) : null;
    }

    /**
     * 获取所有符合条件的记录为二维数组
     * @param array $if     条件
     * @param array $config 额外参数
     *
     * @return array
     */
    public static function selectArray($if = [], $config = [])
    {
        $rs = static::select($if, $config)->fetchAll();
        foreach($rs as &$v)
            $v = static::jsonDecode($v);
        return $rs;
    }

    /**
     * 获取符合条件的行数
     * @param array $if
     * @param array $config
     *
     * @return int
     */
    public static function count($if = [], $config = [])
    {
        $config = array_merge($config, ["count" => true]);
        return static::select($if, $config)->fetch(PDO::FETCH_ASSOC)["COUNT(*)"];
    }

    /**
     * 插入数据
     * @param $data     数据
     *
     * @return string   Last Insert ID
     */
    public static function insert($data)
    {
        $table = static::metaData()["table"];
        $db = static::getDB();

        $data = static::jsonEncode($data);

        $columns = array_keys($data);
        $values = array_values($data);

        array_walk($columns, function (&$v) {
            $v = "`{$v}`";
        });

        array_walk($values, function (&$v) use ($db) {
            $v = $db->quote($v);
        });

        $columns = implode(", ", $columns);
        $values = implode(", ", $values);

        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});";

        $db->query($sql);
        return $db->lastInsertId();
    }

    /**
     * 更新数据
     * @param $if   条件
     * @param $data 新数据
     *
     * @return int  被更新的行数
     */
    public static function update($if, $data)
    {
        $table = static::metaData()["table"];
        $db = static::getDB();

        $data = static::jsonEncode($data);

        $sqlSet = [];
        foreach($data as $k => $v)
        {
            $v = $db->quote($v);
            $sqlSet[] = "`{$k}` = {$v}";
        }

        $sqlSet = implode(", ", $sqlSet);
        $where = static::buildWhere($if);

        $sql = "UPDATE `{$table}` SET {$sqlSet} {$where}";

        return $db->exec($sql);
    }

    /**
     * 删除数据
     * @param $if   条件
     *
     * @return int  被删除的行数
     */
    public static function delete($if)
    {
        $table = static::metaData()["table"];

        $where = static::buildWhere($if);
        $sql = "DELETE FROM `{$table}` {$where}";

        return static::getDB()->exec($sql);
    }

    /**
     *  安装数据表
     */
    public static function install()
    {
        $meta = static::metaData();
        $db = static::getDB();

        $sql = "CREATE TABLE IF NOT EXISTS `{$meta['table']}` (";

        foreach($meta["struct"] as $k => $v)
        {
            switch($v["type"])
            {
                case self::AI:
                    $type = self::INT . " " . self::AI;
                    break;
                case self::JSON:
                    $type = self::TEXT;
                    break;
                case self::VARCHAR:
                    $type = self::VARCHAR . "({$v['length']})";
                    break;
                default:
                    $type = $v["type"];
            }
            if(isset($v[self::NOTNULL]) && $v[self::NOTNULL])
                $type .= " " . self::NOTNULL;

            if(isset($v["default"]))
                $type .= " DEFAULT " . $db->quote($v["default"]);

            $sql .= "`{$k}` {$type},";
        }

        $sql .= " PRIMARY KEY (`{$meta['PRIMARY']}`) ) ENGINE={$meta['engine']} CHARSET={$meta['charset']};";

        $db->exec($sql);
    }

    public static function jsonEncode($data)
    {
        foreach(static::metaData()["struct"] as $k => $v)
            if($v["type"] == self::JSON && array_key_exists($k, $data))
                $data[$k] = json_encode($data[$k]);
        return $data;
    }

    public static function jsonDecode($data)
    {
        foreach(static::metaData()["struct"] as $k => $v)
            if($v["type"] == self::JSON && array_key_exists($k, $data))
                $data[$k] = json_decode($data[$k], true);
        return $data;
    }

    protected static function buildWhere($if)
    {
        $where = "";
        foreach($if as $k => $v)
        {
            $v = static::getDB()->quote($v);

            if(!$where)
                $where = "(`{$k}` = {$v})";
            else
                $where = "{$where} AND (`{$k}` = {$v})";
        }

        if($where)
            $where = "WHERE {$where}";

        return $where;
    }
}