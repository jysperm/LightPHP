<?php

/**
*   该文件包含 lpMySQLDBDrive 的类定义.
*
*   @package LightPHP
*/

/**
*   MySQL数据库驱动.
*
*   该类继承了 lpDBDrive, 实现了访问MySQL数据库的驱动.
*
*	继承的函数请参见基类的注释.
*
*   @type resource class
*/

class lpMySQLDBDrive extends lpDBDrive
{
	/** @type resource 到MySQL的连接. */
	private $connect=null;
    /** @type array 连接选项, 参见 connect() . */
    private $config=null;

	/**
	*	
	*	该类会从 /lp-config.php 中的 `Default.lpMySQLDrive` 段读取默认连接选项, 可用的选项请参见 /lp-config.php .
	*/

    public function connect($config=null)
    {
    	global $lpCfg;

    	if($config)
    		$config = array_merge($lpCfg["Default.lpMySQLDrive"], $config);

        $this->config = $config;

    	$this->connect=mysql_connect($config["host"], $config["user"], $config["pwd"]);

    	if(!$this->connect)
            throw new RuntimeException("连接到数据库失败(无法连接到服务器`{$config['host']}`,或密码错误)");

        mysql_query("SET NAMES {$this->escape($config['charset'])}", $this->connect);

        if(!mysql_select_db($config["dbname"], $this->connect))
            throw new RuntimeException("打开数据库`{$config['dbname']}`失败");
    }

    public function close()
    {
    	mysql_close($this->connect);
    }

    public function insert($table, $row)
    {
        $table = $this->escape($table);

        $sqlColumns = null;

        foreach($row as $key => $value)
        {
            if(!$sqlColumns)
            {
                $sqlColumns = "";
                $sqlValues = "";
            }
            else
            {
                $sqlColumns .= ",";
                $sqlValues .= ",";
            }

            $key = $this->escape($key);
            $value = $this->escape($value);

            $sqlColumns .= "`{$key}`";
            $sqlValues .= "'{$value}'";
        }

        $sql = "INSERT INTO `{$table}` ({$sqlColumns}) VALUES ({$sqlValues});";

        $this->command($sql);
    }

    /**
    *   从数据表查询数据.
    *
    *   @param string $table  表名
    *   @param array  $if     查询的条件 [列名 => 值] 或 [列名 => [操作符 => 值]]
    *   @param array  $config 要插入的数据 [选项 => 值]
    */

    public function select($table, $if, $config)
    {
        
    }

    /**
    *   从数据表修改数据.
    *
    *   @param string $table  表名
    *   @param array  $if     修改的条件 [列名 => 值] 或 [列名 => [操作符 => 值]]
    *   @param array  $new    新数据 [列名 => 值]
    */

    abstract public function update($table, $if, $new);

    /**
    *   从数据表删除数据.
    *
    *   @param string $table  表名
    *   @param array  $if     删除的条件 [列名 => 值] 或 [列名 => [操作符 => 值]]
    */

    abstract public function delete($table, $if);

    public function tableList()
    {
        $query = mysql_list_tables($this->config["dbname"], $this->connect);
        $result=[];
        while($i = mysql_fetch_row($query))
            $result = array_merge($result, $i);
        return $result;
    }

    /**
    *   执行数据库原生的操作.
    *
    *	这将是数据库相关的, 不推荐.
    *
    *   @param string $name  操作名
    *   @param string $args  参数
    *
    *   @return array
    */

    abstract public function operator($name, $args);

    /**
    *   执行带占位符的SQL指令.
    *
    *   该函数支持占位符语法, 占位符为 `%s` , 不区分大小写.
    *   如需在SQL中使用百分号, 请写两个百分号.
    */

    public function commandArgs($command, $more=null)
    {
        $args=func_get_args();
        array_shift($args);

        $sql=$this->parseSQL($sql, $args);

        return new mysql_query($sql,$this->connect);
    }

    public function command($command)
    {
        return new mysql_query($command,$this->connect);
    }

    /**
    *	转义要添加到SQL中的参数.
    *
    *	@param string $str  要转义的参数
    *
    *	@return string
    */

    private function escape($str)
    {
        return mysql_real_escape_string($str,$this->connect);
    }

    /**
    *	解析含有占位符的SQL, 将参数嵌入SQL.
    *
    *	@param string $sql  含有占位符的SQL
    *	@param array  $args 参数列表
    *
    *	@return string 解析后的SQL.
    */

    private function parseSQL($sql, $args)
    {
        $offset = 0;
        foreach($args as $i)
        {
            if(preg_match("/%([Ss])/", $sql, $result, PREG_OFFSET_CAPTURE, $offset))
            {
                $fStr = $result[1][0];
                $pos = $result[1][1];

                $tPos = $pos - 1;
                while($sql[$pos] == "%")
                {
                    $tPos--;
                }

                if(!(($pos - $tPos) % 2))
                    continue;

                $value=$this->escape($i);

                $sql = substr($sql, 0, $pos - 1) . $value . substr($sql, $pos + 1);

                $offset = $pos + 1;
            }
        }

        return str_replace("%%", "%", $sql);
    }
}

/**
*   MySQL数据库查询驱动.
*
*   该类继承了 lpDBInquiryDrive, 实现了访问查询数据库的功能.
*
*   继承的函数请参见基类的注释.
*
*   @type value class
*/

class lpDBMySQLInquiryDrive extends lpDBInquiryDrive
{
    /** @type string 当前条件的SQL WHERE表示. */
    private $where="";

    public function and($key, $value, $operator=$this::Equal)
    {
        $key = $this->escape($key);
        $value = $this->escape($value);

        if(!$this->where)
            $this->where = "(`{$key}` {$operator} '{$value}')";
        else
            $this->where = "({$this->where} AND (`{$key}` {$operator} '{$value}'))";
    }

    public function andOther($other)
    {
        if(!$this->where)
            $this->where = $other->where;
        else
            $this->where = "({$this->where} AND {$other->where})";
    }
    
    public function or($key, $value, $operator=$this::Equal)
    {
        $key = $this->escape($key);
        $value = $this->escape($value);

        if(!$this->where)
            $this->where = "(`{$key}` {$operator} '{$value}')";
        else
            $this->where = "({$this->where} OR (`{$key}` {$operator} '{$value}'))";
    }

    public function orOther($other)
    {
        if(!$this->where)
            $this->where = $other->where;
        else
            $this->where = "({$this->where} OR {$other->where})";
    }

    public function not()
    {
        if($this->where)
            $this->where = "( NOT {$this->where})";
    }

    /**
    *   根据已有的条件构建SQL WHERE子句.
    *
    *   @return string
    */

    public function buildWhere()
    {
        if($this->where)
            return " WHERE {$this->where}";
        else
            return "";
    }

    /**
    *   转义要添加到SQL中的参数.
    *
    *   @param string $str 要转义的参数
    *
    *   @return string
    */

    private function escape($str)
    {
        return mysql_real_escape_string($str);
    }
}