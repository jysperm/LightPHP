<?php

class lpMySQL
{
    private $configs;
    private $connect=NULL;
    private $isPCconnect;

    public function __construct($host=NULL,$dbname=NULL,$user=NULL,$pwd=NULL,$charset=NULL)
    {
        global $lpCfgMySQL;

        $this->configs=$lpCfgMySQL;

        if($host)
            $this->configs["host"]=$host;
        if($dbname)
            $this->configs["dbname"]=$dbname;
        if($user)
            $this->configs["user"]=$user;
        if($pwd)
            $this->configs["pwd"]=$pwd;
        if($charset)
            $this->configs["charset"]=$charset;
    }

    public function __destruct()
    {
        if(!$this->isPCconnect)
            $this->close();
    }

    public function open($isPC=false)
    {
        if(!$this->ping())
        {
            $this->isPCconnect=$isPC;

            $configs=$this->configs;

            if($isPC)
                $this->connect=mysql_pconnect($configs["host"],$configs["user"],$configs["pwd"]);
            else
                $this->connect=mysql_connect($configs["host"],$configs["user"],$configs["pwd"]);

            if(!$this->connect)
                throw new RuntimeException("连接到数据库失败(无法连接到服务器`{$configs["host"]}`,或密码错误)");

            mysql_query("SET NAMES {$this->escape($configs["charset"])}",$this->connect);

            if(!mysql_select_db($configs["dbname"],$this->connect))
                throw new RuntimeException("打开数据库`{$configs["dbname"]}`失败");
        }

        return $this->ping();
    }

    public function close()
    {
        try
        {
            if($this->connect)
                mysql_close($this->connect);
        }
        catch(Exception $e)
        {
          
        }
    }

    public function ping()
    {
        if($this->connect)
            return mysql_ping($this->connect)?true:false;
        else
            return false;
    }

    public function exec($sql,$more=NULL)
    {
        global $lpCfgDebug,$lpCfgMySQLDebug;

        if(!$this->ping())
            $this->open();

        $args=func_get_args();
        array_shift($args);

        $sql=$this->parseSQL($sql, $args);

        if($lpCfgDebug && $lpCfgMySQLDebug)
            echo "lpMySQL::exec(): {$sql}\n";

        return new lpSQLRs(mysql_query($sql,$this->connect),$this);
    }

    public function select($table,$querys=NULL,$orderBy=NULL,$start=-1,$num=-1,$isASC=true)
    {
        $table=$this->escape($table);
        $orderBy=$this->escape($orderBy);
        $start=(int)$start;
        $num=(int)$num;

        $sql="SELECT * FROM `{$table}` " . $this->buildWhere($querys);

        if($orderBy!="")
            $sql .=" ORDER BY `{$orderBy}` ";

        if(!$isASC)
            $sql .= " DESC ";

        if($num>-1 && $start>-1)
            $sql .= " LIMIT {$start},{$num} ";
        if($num>-1 && !($start>-1))
            $sql .= " LIMIT {$num} ";

        return $this->exec($sql);
    }

    public function update($table,$querys,$values)
    {
        foreach($values as $key => $value)
        {
            $this->exec("UPDATE `%s` SET `%s` = '%t' " . $this->buildWhere($querys),$table,$key,$value);
        }
    }

    public function insert($table,$values)
    {
        $table=$this->escape($table);

        $sqlColumns="(";
        $sqlValues="(";

        foreach($values as $key => $value)
        {
            $key=$this->escape($key);
            $value=$this->escape($value);

            $sqlColumns .= "`{$key}`,";
            $sqlValues .= "'{$value}',";
        }

        if(substr($sqlColumns,-1*strlen(","))==",")
            $sqlColumns=substr($sqlColumns,0,strlen($sqlColumns)-strlen(","));
        $sqlColumns .= ")";

        if(substr($sqlValues,-1*strlen(","))==",")
            $sqlValues=substr($sqlValues,0,strlen($sqlValues)-strlen(","));
        $sqlValues .= ")";

        $sql="INSERT INTO `{$table}` {$sqlColumns} VALUES {$sqlValues}";

        $this->exec($sql);
    }

    public function delete($table,$querys)
    {
        $table=$this->escape($table);

        $sql="DELETE FROM `{$table}` " . $this->buildWhere($querys);
        $this->exec($sql);
    }

    public function testSQL($sql,$more=NULL)
    {
        $args=func_get_args();
        array_shift($args);

        $sql=$this->parseSQL($sql, $args);

        return $sql;
    }

    public function affected()
    {
        return mysql_affected_rows($this->connect);
    }

    public function insertId()
    {
        return mysql_insert_id($this->connect);
    }

    public function getDBs()
    {
        if(!$this->ping())
            $this->open();

        return $this->queryToArray(mysql_list_dbs($this->connect));
    }

    public function lastError()
    {
        if(mysql_errno($this->connect))
            return mysql_error($this->connect);
        else
            return NULL;
    }

    public function getTables($dbname)
    {
        if(!$this->ping())
            $this->open();

        return $this->queryToArray(mysql_list_tables($dbname,$this->connect));
    }

    private function queryToArray($query)
    {
        $result=array();
        while($i = mysql_fetch_row($query))
            $result=array_merge($result,$i);
        return $result;
    }

    private function escape($str)
    {
        if(!$this->ping())
            $this->open();

        return mysql_real_escape_string($str,$this->connect);
    }

    private function buildWhere($querys)
    {
        if($querys)
            $sql = " WHERE ";
        else
            return "";

        foreach($querys as $key => $value)
        {
            $key=$this->escape($key);
            $value=$this->escape($value);

            $sql .= "( `{$key}` = '{$value}' ) AND";
        }

        if(substr($sql,-1 * strlen("AND"))=="AND")
            $sql=substr($sql,0,strlen($sql)-strlen("AND"));

        return $sql;
    }

    private function parseSQL($sql,$args)
    {
        $offset=0;
        foreach($args as $i)
        {
            if(preg_match("/%([iIfFsStT])/",$sql,$result,PREG_OFFSET_CAPTURE,$offset))
            {
                $fStr=$result[1][0];
                $pos=$result[1][1];

                $tPos=$pos-1;
                while($sql[$pos]=="%")
                {
                    $tPos--;
                }

                if(!(($pos-$tPos)%2))
                    continue;

                switch ($fStr)
                {
                    case "i":
                    case "I":
                        $value=(int)$i;
                        if($fStr=="I" && $i!=$value)
                            return NULL;
                        break;
                    case "f":
                    case "F":
                        $value=(float)$i;
                        if($fStr=="F" && $i!=$value)
                            return NULL;
                        break;
                    case "s":
                    case "S":
                        $value=preg_replace("/[^A-Za-z0-9_]/","",$i);
                        $value=$this->escape($value);
                        if($fStr=="S" && $i!=$value)
                            return NULL;
                        break;
                    case "t":
                    case "T":
                        $value=$this->escape($i);
                        if($fStr=="T" && $i!=$value)
                            return NULL;
                        break;
                }

                $sql=substr($sql,0,$pos-1) . $value . substr($sql,$pos+1);

                $offset=$pos + 1;
            }
        }

        $sql=str_replace("%%", "%", $sql);

        return $sql;
    }
}
