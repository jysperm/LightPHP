<?php

/**
 * 该类提供了 PDO 数据源的访问操作，大概分三个部分：
 *
 * * 实例化部分：
 *    每个实例表示相应数据表中的一个条目，使用该部分功能需要数据表有一个主键(最好为 INT AUTO_INCREMENT)，
 *    实例是允许通过数组操作符来写入数据的，但写入的数据不会被保存到数据库。
 * * 静态成员部分：
 *    定义数据类型和数据修饰的相关常量。
 *    同时 query() 提供了简单的 SQL 占位符语法。
 * * 数据库操作部分：
 *    提供了无需 SQL 的 CRUD, 自动安装数据表等功能。
 *    还支持自动对 JSON 字段进行序列化和反序列化
 *
 * 使用时，对应数据库中的每个数据表，都要创建一个该类的子类，重写 metaData() 函数来为该类提供元信息。
 * 在 MVC 中，该类(的子类)充当 Model 的角色，与数据库相关的计算都可以写在该类的子类。
 */
abstract class lpPDOModel implements ArrayAccess
{
    // ----- 实例化部分

    /** @var array 实例中的数据，可通过 data(), 或数组操作符来访问 */
    protected $data = [];
    /** @var int 实例在数据表中的 ID, 即主键的值 */
    protected $id = null;

    /**
     * 根据指定的字段来构造实例
     *
     * @param string $k 字段名
     * @param string $v 值
     * @return lpPDOModel
     */
    public static function by($k, $v)
    {
        $primary = self::metaData()["primary"];

        /** @var lpPDOModel $i */
        $i = new static();
        $i->data = static::find([$k => $v]);
        $i->id = isset($i->data[$primary]) ? $i->data[$primary] : null;
        return $i;
    }

    /**
     * 根据主键构造实例
     *
     * @param int $id 主键的值
     * @return lpPDOModel
     */
    public static function byID($id)
    {
        if(!$id)
            return new static();
        return static::by("id", $id);
    }

    /**
     * 以数组的形式获取实例的数据
     *
     * 可通过 !$i->data() 来判断实例是否有效(是否有数据)。
     *
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

    /* 数据类型 */

    const INT = "INT";
    const UINT = "INT UNSIGNED";
    const VARCHAR = "VARCHAR";
    const TEXT = "TEXT";

    /* 数据修饰 */

    const NOTNULL = "NOT NULL";
    const DEFALT = "DEFAULT";
    const AI = "AUTO_INCREMENT";

    /**
     * 标记该字段的类型为 JSON
     *
     * lpPDOModel 特有，在读写时 lpPDOModel 会自动对 JSON 进行序列化和反序列化。
     *
     * * 读取部分
     *    find(), selectArray(), selectFieldList(), selectPrimaryArray() 会对 JSON 进行反序列化，
     *    而 select() 不会，因为 select() 的返回值是 PDOStatement.
     * * 写入部分
     *    insert() 和 update() 都会对 JSON 进行序列化。
     */
    const JSON = "JSON";

    /**
     * 该函数可供子类读取默认配置
     * 见 metaData().
     *
     * @param array $data
     * @return array
     */
    protected static function meta(array $data)
    {
        $default = [
            "db" => lpFactory::get("PDO.LightPHP"),
            "primary" => "id",
            "engine" => "MyISAM",
            "charset" => "utf8"
        ];

        return array_merge($default, $data);
    }

    /**
     * 子类需要重写这个函数来提供元信息
     *
     * 示例：
     *
     * class PDOModel extends lpPDOModel
     * {
     *     protected static function metaData($data = null)
     *     {
     *         return parent::meta([
     *             "table" => "user",
     *             "struct" => [
     *                 "id" => [self::INT, self::AI, self::PRIMARY],
     *                 "uname" => [self::VARCHAR => 256],
     *                 "passwd" => [self::TEXT],
     *                 ...
     *              ]
     *         ]);
     *     }
     * }
     *
     * 子类可通过 meta() 来读入默认配置，然后只需提供 table 和 struct 两项必选信息即可，
     * table 指定数据表的表名，struct 用来指定数据表的结构。
     *
     * @return array
     */
    protected static function metaData()
    {
        return self::meta([]);
    }

    /**
     * 获取原生数据库连接对象
     *
     * @return PDO
     */
    public static function getDB()
    {
        return self::metaData()["db"];
    }

    /**
     * 通过占位符语法构建 SQL
     *
     * 该函数仅负责构建 SQL, 你可能需要通过 getDB() 获取 PDO 对象后执行查询。
     *
     * @param string $query SQL, 其中可以包含形如 {1} {2} 的占位符
     * @param array $params 用于填充占位符的数据，该函数会对它们进行转义
     * @return string 填充后的 SQL
     */
    public static function query($query, array $params)
    {
        foreach($params as $index => $value)
            $query = str_replace("{{$index}}", substr(self::getDB()->quote($value), 1, -1), $query);
        return $query;
    }

    // ----- 数据库操作部分

    /** 查询操作符 */
    const QueryEscape = '$';

    /**
     * 检索数据
     *
     * @param array $if 检索条件 [
     *     <字段> => <值>,
     *     '$OR' => [<字段> => <值>, ...],
     *     '$LT' => [<字段> => <值>],
     *     "原始 SQL", ...
     * ]
     *
     * ## 操作符列表
     * * OR
     * * LT, LTE, GT, GTE, NE
     * * LIKE, %LIKE%
     *
     * $OR 操作符需提供一个数组，数组中的条件将会被以 OR 连接。
     * $LT, $LTE, $GT, $GTE, $NE 需提供一个具有单一元素的数组。
     * $LIKE, $%LIKE% 需提供一个字符串。
     *
     * @param array $options 选项 [
     *     "sort" => [<排序字段> => <(bool)是否为正序>, <排序字段> => <(bool)是否为正序>],
     *     "select" => [<要检索的字段>],
     *     "skip" => <(int)跳过条数>,
     *     "limit" => <(int)检索条数>
     *     "count" => <(bool)是否只获取结果数>
     * ]
     *
     * @return PDOStatement
     */
    public static function select(array $if = [], array $options = [])
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
                        $orderBy = $orderBy ? " ORDER BY " : "{$orderBy}, ";
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

        $sql = "SELECT {$select} FROM `{$table}` WHERE {$where} {$orderBy} {$sqlLimit}";

        $result = static::getDB()->query($sql);
        $result->setFetchMode(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * 获取符合条件的第一条数据
     *
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return array|null  成功返回数组，失败返回null
     */
    public static function find(array $if = [], array $options = [])
    {
        $options = array_merge($options, ["limit" => 1]);
        $data = static::select($if, $options)->fetch();

        return $data ? static::jsonDecode($data) : null;
    }

    /**
     * 获取所有符合条件的记录为二维数组
     *
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return array [
     *     ["field1" => "value1", "field2" => "value2"],
     *     ["field1" => "value1", "field2" => "value2"]
     * ]
     */
    public static function selectArray(array $if = [], array $options = [])
    {
        $rs = static::select($if, $options)->fetchAll();
        foreach($rs as &$v)
            $v = static::jsonDecode($v);
        return $rs;
    }

    /**
     * 获取某个字段的值的数组
     *
     * @param $field 字段
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return array [
     *     "value1", "value2", "value3"
     * ]
     */
    public static function selectFieldList($field, array $if = [], array $options = [])
    {
        $rs = static::select($if, $options)->fetchAll();
        foreach($rs as &$v)
            $v = static::jsonDecode($v)[$field];
        return $rs;
    }

    /**
     * 获取以主键为键的二维数组
     *
     * @param string|null $field 作为键的字段，null 表示使用主键
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return array [
     *     "value1" => ["field1" => "value1", "field2" => "value2"],
     *     "value2" => ["field1" => "value1", "field2" => "value2"]
     * ]
     */
    public static function selectPrimaryArray($field, array $if = [], array $options = [])
    {
        if(!$field)
            $field = self::metaData()["primary"];

        $rs = static::select($if, $options)->fetchAll();
        foreach($rs as &$v)
            $v = static::jsonDecode($v)[$field];
        return $rs;
    }

    /**
     * 获取符合条件的行数
     *
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return int
     */
    public static function count(array $if = [], array $options = [])
    {
        $options = array_merge($options, ["count" => true]);
        return static::select($if, $options)->fetch(PDO::FETCH_ASSOC)["COUNT(*)"];
    }

    /**
     * 插入数据
     *
     * @param array $data 数据
     *
     * @return int Last Insert ID
     */
    public static function insert(array $data)
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
     *
     * @param array $if 条件
     * @param array  $data 新数据
     *
     * @return int 被更新的行数
     */
    public static function update(array $if, array $data)
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

        $sql = "UPDATE `{$table}` SET {$sqlSet} WHERE {$where}";

        return $db->exec($sql);
    }

    /**
     * 删除数据
     *
     * @param array $if 条件
     *
     * @return int 被删除的行数
     */
    public static function delete(array $if)
    {
        $table = static::metaData()["table"];

        $where = static::buildWhere($if);
        $sql = "DELETE FROM `{$table}` WHERE {$where}";

        return static::getDB()->exec($sql);
    }

    /**
     * 安装数据表
     *
     * 该函数会使用 IF NOT EXISIS 语法，仅当数据表不存在时才会创建。
     * 目前该函数还功能有限，只支持一部分的数据表结构。
     */
    public static function install()
    {
        $meta = static::metaData();
        $db = static::getDB();

        $sql = "CREATE TABLE IF NOT EXISTS `{$meta['table']}` (";

        foreach($meta["struct"] as $name => $data)
        {
            $type = "";

            foreach($data as $k => $v)
            {
                if(is_int($k))
                {
                    $k = $v;
                    $v = null;
                }

                switch($k)
                {
                    case self::INT:
                        $type = self::INT;
                        break;
                    case self::UINT:
                        $type = self::UINT;
                        break;
                    case self::TEXT:
                        $type = self::TEXT;
                        break;
                    case self::VARCHAR:
                        $type = self::VARCHAR . "({$v})";
                        break;
                }
            }

            $suffix = in_array(self::NOTNULL, $data) ? "NOT NULL " : "NULL ";
            if(in_array(self::AI, $data))
                $suffix .= self::AI;

            if(isset($data[self::DEFALT]))
                $type .= " DEFAULT " . $db->quote($data[self::DEFALT]);

            $sql .= "`{$name}` {$type} {$suffix},";
        }

        $sql .= " PRIMARY KEY (`{$meta["primary"]}`) ) ENGINE={$meta['engine']} CHARSET={$meta['charset']}";

        $db->exec($sql);
    }

    /**
     * 根据元信息对 JSON 字段进行序列化
     *
     * @param array $data
     * @return array
     */
    public static function jsonEncode(array $data)
    {
        foreach(static::metaData()["struct"] as $k => $v)
            if(in_array(self::JSON, $v) && array_key_exists($k, $data))
                $data[$k] = json_encode($data[$k]);
        return $data;
    }

    /**
     * 根据元信息对 JSON 字段进行反序列化
     *
     * @param array $data
     * @return array
     */
    public static function jsonDecode(array $data)
    {
        foreach(static::metaData()["struct"] as $k => $v)
            if(in_array(self::JSON, $v) && array_key_exists($k, $data))
                $data[$k] = json_decode($data[$k], true);
        return $data;
    }

    /**
     * 构建 WHERE 语句
     *
     * @param array $if 条件
     * @param bool $isAndOrOr 是否默认以 AND 连接
     * @return string WHERE 语句
     */
    protected static function buildWhere(array $if, $isAndOrOr = true)
    {
        $where = [];

        foreach($if as $k => $v)
        {
            if(substr($k, 0, 1) == self::QueryEscape)
            {
                $op = strtolower(substr($k, 1));

                switch($op)
                {
                    case "or":
                        $where[] = self::buildWhere($if, false);
                        break;
                    case "lt":
                    case "lte":
                    case "gt":
                    case "gte":
                    case "ne":
                    case "like":
                        $opMap = [
                            "lt" => "<",
                            "lte" => "<=",
                            "gt" => ">",
                            "gte" => ">=",
                            "ne" => "<>",
                            "like" => "LIKE"
                        ];

                        $v = self::getDB()->quote($v);
                        $where[] = "(`{$k}` {$opMap[$op]} {$v})";
                        break;
                    case "%like%":
                        $v = self::getDB()->quote("%{$v}%");
                        $where[] = "(`{$k}` LIKE {$v})";
                        break;

                }
            }
            else if(is_int($k))
            {
                $where[] = $v;
            }
            else
            {
                $where[] = "(`{$k}` = {$v})";
            }
        }

        $connector = $isAndOrOr ? " AND " : " OR ";
        $where = implode($connector, $where);

        return "($where)";
    }
}