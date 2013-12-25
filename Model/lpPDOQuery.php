<?php

/**
 * Class lpPDOQuery
 */
class lpPDOQuery
{
    /** @var string 主键字段 */
    public $primary = "id";

    /** @var PDO $pdo */
    private $pdo;
    /** @var string $table */
    private $table;

    /** @var string 数据库驱动名称 */
    private $attrDRIVER_NAME = null;

    /**
     * @param PDO $pdo
     * @param string $table
     */
    public function __construct($pdo, $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;

        list($this->attrDRIVER_NAME) = [
            $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)
        ];
    }

    /** 查询操作符 */
    const QueryEscape = '$';

    /**
     * 通过占位符语法构建 SQL
     *
     * 该函数仅负责构建 SQL, 你可能需要通过 drive() 获取 PDO 对象后执行查询。
     *
     * @param string $query SQL, 其中可以包含形如 {1} {2} 的占位符
     * @param array $params 用于填充占位符的数据，该函数会对它们进行转义
     * @return string 填充后的 SQL
     */
    public function query($query, array $params)
    {
        foreach ($params as $index => $value)
            $query = str_replace("{{$index}}", $this->pdo->quote($value), $query);
        return $query;
    }

    /**
     * 查询数据
     *
     * @param array $if 检索条件 [
     *     <字段> => <值>,
     *     '$or' => [<字段> => <值>, ...],
     *     '$lt' => [<字段> => <值>],
     *     "原始 SQL", ...
     * ]
     *
     * ## 操作符列表
     * * or
     * * lt, lte, gt, gte, ne
     * * like, %like%, regexp
     *
     * $or 操作符需提供一个数组，数组中的条件将会被以 OR 连接，如果需要用 OR 查询几个字段，可以使用这样的语法：
     *
     *     ['$or' => [["uid" => 2], ["uid" => 3]]]
     *
     * $lt, $lte, $gt, $gte, $ne 需提供一个具有单一元素的数组。
     *
     * $like 需手动在参数中包含通配符，$like% 自动在参数两端添加通配符，$regexp 是正则表达式匹配。
     *
     * @param array $options 选项 [
     *     "sort" => [<排序字段> => <(bool)是否为正序>, <排序字段> => <(bool)是否为正序>],
     *     "select" => [<要检索的字段>],
     *     "skip" => <(int)跳过条数>,
     *     "limit" => <(int)检索条数>
     *     "count" => <(bool)是否只获取结果数>
     * ]
     *
     * 只有在使用了 limit 后才能使用 skip.
     *
     * @return PDOStatement
     */
    public function select(array $if = [], array $options = [])
    {
        $select = "*";
        $where = $this->buildWhere($if);
        $orderBy = "";
        $sqlLimit = "";

        foreach ($options as $option => $value) {
            switch ($option) {
                case "count":
                    if ($value)
                        $select = "COUNT(*)";
                    break;

                case "sort":
                    foreach ($value as $k => $v) {
                        if (is_int($k)) {
                            $k = $v;
                            $v = true;
                        }

                        $orderBy = $orderBy ? "{$orderBy}, " : " ORDER BY ";
                        $orderBy .= "{$this->quoteIdentifiers($k)} " . ($v ? "ASC" : "DESC");
                    }
                    break;

                case "select":
                    foreach ($value as &$i)
                        $i = $this->quoteIdentifiers($i);
                    $select = implode(", ", $value);
                    break;
            }
        }

        $skip = isset($options["skip"]) ? $options["skip"] : -1;
        $limit = isset($options["limit"]) ? $options["limit"] : -1;

        if ($limit > -1 && $skip > -1)
            $sqlLimit = " LIMIT {$skip}, {$limit}";
        else if ($limit > -1 && !($skip > -1))
            $sqlLimit = " LIMIT {$limit}";

        $sql = "SELECT {$select} FROM {$this->quoteIdentifiers($this->table)} WHERE {$where} {$orderBy} {$sqlLimit}";

        $result = $this->handlingError($sql, [$this->pdo, "query"]);
        $result->setFetchMode(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * 获取符合条件的第一条数据
     *
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return array|null 成功返回数组，失败返回 null
     */
    public function findOne(array $if = [], array $options = [])
    {
        $options = array_merge($options, ["limit" => 1]);
        $data = $this->select($if, $options)->fetch();

        return $data ? : null;
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
    public function selectArray(array $if = [], array $options = [])
    {
        return $this->select($if, $options)->fetchAll();
    }

    /**
     * 获取某个字段的值的数组
     *
     * @param string $field 字段
     * @param array $if 条件
     * @param array $options 选项
     *
     * @return array [
     *     "value1", "value2", "value3"
     * ]
     */
    public function selectValueList($field, array $if = [], array $options = [])
    {
        $rs = $this->select($if, $options)->fetchAll();
        foreach ($rs as &$v)
            $v = $v[$field];
        return $rs;
    }

    /**
     * 获取以主键为键的二维数组
     *
     * @param array $if 条件
     * @param array $options 选项
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

        $rs = $this->select($if, $options)->fetchAll();
        $result = [];
        foreach ($rs as $v)
            $result[$v[$field]] = $v;
        return $result;
    }

    /**
     * 获取符合条件的行数
     *
     * @param array $if 条件
     * @param array $options 选项
     * @return int
     */
    public function count(array $if = [], array $options = [])
    {
        $options = array_merge($options, ["count" => true]);
        return $this->select($if, $options)->fetch()["COUNT(*)"];
    }

    /**
     * 插入数据
     *
     * @param array $data 数据
     * @return int Last Insert ID
     */
    public function insert(array $data)
    {
        $columns = array_keys($data);
        $values = array_values($data);

        array_walk($columns, function (&$v) {
            $v = "`{$v}`";
        });

        array_walk($values, function (&$v) {
            $v = $this->pdo->quote($v);
        });

        $columns = implode(", ", $columns);
        $values = implode(", ", $values);

        $sql = "INSERT INTO {$this->quoteIdentifiers($this->table)} ({$columns}) VALUES ({$values});";

        $this->handlingError($sql, false);
        return $this->pdo->lastInsertId();
    }

    /**
     * 插入多条数据
     *
     * @param array $data 数据
     * @return array Last Insert ID 数组
     */
    public function insertArray(array $data)
    {
        $result = [];
        foreach ($data as $i)
            $result[] = $this->insert($i);
        return $result;
    }

    /**
     * 更新数据
     *
     * @param array $if 条件
     * @param array $data 新数据
     * @return int 被更新的行数
     */
    public function update(array $if, array $data)
    {
        $sqlSet = [];
        foreach ($data as $k => $v) {
            $v = $this->pdo->quote($v);
            $sqlSet[] = "`{$k}` = {$v}";
        }

        $sqlSet = implode(", ", $sqlSet);
        $where = static::buildWhere($if);

        $sql = "UPDATE {$this->quoteIdentifiers($this->table)} SET {$sqlSet} WHERE {$where}";

        return $this->handlingError($sql, [$this->pdo, "exec"]);
    }

    /**
     * 删除数据
     *
     * @param array $if 条件
     * @return int 被删除的行数
     */
    public function delete(array $if)
    {
        $where = $this->buildWhere($if);
        $sql = "DELETE FROM {$this->quoteIdentifiers($this->table)} WHERE {$where}";

        return $this->handlingError($sql, [$this->pdo, "exec"]);
    }

    public function getAttribute($name)
    {
        switch($name)
        {
            case "primary":
                return $this->primary;
        }
    }

    /**
     * 运行一条 SQL 并进行错误处理
     *
     * @param string $sql
     * @param callable $runner
     * @throws Exception
     * @return PDOStatement
     */
    public function handlingError($sql, callable $runner)
    {
        $result = $runner($sql);

        if ($result === false) {
            $info = $this->pdo->errorInfo();

            $message = "SQL Query Error. \n
                        SQL: {$sql}. \n
                        Error Code: {$info[0]}({$info[1]}). \n
                        Error Message: {$info[2]}.\n";

            throw new lpSQLException($message, $info);
        }

        return $result;
    }

    /**
     * @param $name
     * @return string
     */
    protected function quoteIdentifiers($name)
    {
        static $escapes = [
            "mysql" => '`%s`',
            "mssql" => '[%s]',
            "sqlite" => '"%s"',
            "pgsql" => '"%s"'
        ];

        return sprintf($escapes[$this->attrDRIVER_NAME], $name);
    }

    /**
     * 构建 WHERE 语句
     *
     * @param array $if 条件
     * @param bool $isAndOrOr 是否默认以 AND 连接
     * @return string WHERE 语句
     */
    protected function buildWhere(array $if, $isAndOrOr = true)
    {
        $where = [];

        foreach ($if as $k => $v) {
            if (substr($k, 0, 1) == self::QueryEscape) {
                $op = strtolower(substr($k, 1));

                switch ($op) {
                    case "or":
                        $where[] = $this->buildWhere($v, false);
                        break;

                    case "lt":
                    case "lte":
                    case "gt":
                    case "gte":
                    case "ne":
                    case "like":
                    case "regexp":
                        $opMap = [
                            "lt" => "<",
                            "lte" => "<=",
                            "gt" => ">",
                            "gte" => ">=",
                            "ne" => "<>",
                            "like" => "LIKE",
                            "regexp" => "REGEXP"
                        ];

                        $kk = array_keys($v)[0];
                        $vv = array_values($v)[0];

                        $vv = $this->pdo->quote($vv);
                        $where[] = "({$this->quoteIdentifiers($kk)} {$opMap[$op]} {$vv})";
                        break;

                    case "%like%":
                        $kk = array_keys($v)[0];
                        $vv = array_values($v)[0];

                        $vv = $this->pdo->quote("%{$vv}%");
                        $where[] = "({$this->quoteIdentifiers($kk)} LIKE {$vv})";
                        break;
                }
            } else if (is_int($k) && is_string($v)) {
                $where[] = $v;
            } else if (is_array($v)) {
                foreach ($v as $kk => $vv) {
                    $vv = $this->pdo->quote($vv);
                    $where[] = "({$this->quoteIdentifiers($kk)} = {$vv})";
                }
            } else {
                $v = $this->pdo->quote($v);
                $where[] = "({$this->quoteIdentifiers($k)} = {$v})";
            }
        }

        $connector = $isAndOrOr ? " AND " : " OR ";
        $where = implode($connector, $where);

        if ($where)
            return $where;
        return "TRUE";
    }
} 