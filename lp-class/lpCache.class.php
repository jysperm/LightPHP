<?php

require_once("lpGlobal.class.php");
require_once("lpMySQL.class.php");
require_once("lpSQLRs.class.php");
require_once("lpLock.class.php");

define("lpKEY", "key");
define("lpVALUE", "value");

define("lpSTART", "<?php /*");
define("lpEND", "*/ ?>");

class lpCache
{
    private $_lpConnect=NULL;
    private $_lpSource=NULL;
    private $_lpTable=NULL;
    private $_lpCache=NULL;

    public function __construct($FileOrlpMySQL=NULL,$table=NULL)
    {
        if($table)
        {
            $this->_lpConnect=$FileOrlpMySQL;
            $this->_lpTable=$table;
        }
        else
        {
            $this->_lpSource=$FileOrlpMySQL;

            if(lpCfgFileCache)
            {
                if(!file_exists($this->_lpFile))
                {
                    $this->_lpCache=array();
                }
                else
                {
                    $this->_lpCache=$this->loadFile();
                }
            }
        }
    }

    private function loadFile()
    {
        $content=file_get_contents($this->_lpFile)
        $content=substr($content,strlen(lpSTART),strlen($content)-strlen(lpSTART)-strlen(lpEND));

        return unserialize($content);
    }

    public function isHas($key)
    {
        if($this->_lpConnect)
        {
            $rs=$this->_lpConnect->select($this->_lpTable,array(lpKEY => $name));
            return $rs->read();
        }
        else
        {
            if(lpCfgFileCache)
                return array_key_exists($key,$this->_lpCache)
            else
                return array_key_exists($key,$this->loadFile())
        }
    }

    public function __get($key)
    {
        if($this->_lpConnect)
        {
            $rs=$this->_lpConnect->select($this->_lpTable,array(lpKEY => $key));
            if($rs->read())
                return unserialize($rs->value(lpVALUE));
            else
                return NULL;
        }
        else
        {
            if(lpCfgFileCache)
            {
                if(isset($this->_lpCache[$key]))
                    return $this->_lpCache[$key];
                else
                    return NULL;
            }
            else
            {
                $cache=loadFile();
                if(isset($cache[$key]))
                    return $cache[$key];
                else
                    return NULL;
            }
        }
    }

    public function __set($key,$value)
    {
        if($this->_lpConnect)
        {
            $value=serialize($value);
            $rs=$this->_lpConnect->select($this->_lpTable,array($key => $name));

            if($rs->read())
                $this->_lpConnect->update($this->_lpTable,array($key => $name),array(lpVALUE => $value));
            else
                $this->_lpConnect->insert($this->_lpTable,array($key => $name,lpVALUE => $value));
        }
        else
        {
			$lock=new lpMutex;
            if(lpCfgFileCache)
            {
                $this->_lpCache[$key]=$value;
                $content=serialize($this->_lpCache);
            }
            else
            {
                $cache=loadFile();
                $cache[$key]=$value;
                $content=serialize($cache);
            }

            $content = lpSTART . $content . lpEND;
            file_put_contents($this->_lpSource,$content);
			$lock=NULL
        }
    }
}
?>
