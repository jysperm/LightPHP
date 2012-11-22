<?php

class lpCache
{
    private $_lpConnect=NULL;
    private $_lpSource=NULL;
    private $_lpTable=NULL;
    private $_lpCache=NULL;

    public function __construct($FileOrlpMySQL=NULL,$table=NULL)
    {
        global $lpCfgFileCache;

        if($table)
        {
            $this->_lpConnect=$FileOrlpMySQL;
            $this->_lpTable=$table;
        }
        else
        {
            $this->_lpSource=$FileOrlpMySQL;

            if($lpCfgFileCache)
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

    public function isHas($key)
    {
        global $lpCfgKEY,$lpCfgFileCache;

        if($this->_lpConnect)
        {
            $rs=$this->_lpConnect->select($this->_lpTable,array($lpCfgKEY => $name));
            return $rs->read();
        }
        else
        {
            if($lpCfgFileCache)
                return array_key_exists($key,$this->_lpCache);
            else
                return array_key_exists($key,$this->loadFile());
        }
    }
    
    public function deleteKey($key)
    {
        global $lpCfgKEY,$lpCfgFileCache;

        if(!$this->isHas($key))
            return false;
            
        if($this->_lpConnect)
        {
            $rs=$this->_lpConnect->delete($this->_lpTable,array($lpCfgKEY => $name));
        }
        else
        {
            $name=md5(dirname($this->_lpSource) . "/" . basename($this->_lpSource));
            $lock=new lpFileLock($name);
            $lock->lock();
            
            if($lpCfgFileCache)
            {
                unset($this->_lpCache[$key]);
                $this->writeFile($this->_lpCache);
            }
            else
            {
                $cache=$this->loadFile();
                unset($cache[$key]);
                $this->writeFile($cache);
            }
            
            $lock->unLock();
        }
        
        return true;
    }

    public function __get($key)
    {
        global $lpCfgKEY,$lpCfgVALUE,$lpCfgFileCache;

        if($this->_lpConnect)
        {
            $rs=$this->_lpConnect->select($this->_lpTable,array($lpCfgKEY => $key));
            if($rs->read())
                return unserialize($rs->value($lpCfgVALUE));
            else
                return NULL;
        }
        else
        {
            if($lpCfgFileCache)
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
        global $lpCfgKEY,$lpCfgVALUE,$lpCfgFileCache;

        if($this->_lpConnect)
        {
            $value=serialize($value);
            $rs=$this->_lpConnect->select($this->_lpTable,array($lpCfgKEY => $name));

            if($rs->read())
                $this->_lpConnect->update($this->_lpTable,array($lpCfgKEY => $name),array($lpCfgVALUE => $value));
            else
                $this->_lpConnect->insert($this->_lpTable,array($lpCfgKEY => $name,$lpCfgVALUE => $value));
        }
        else
        {
            $name=md5(dirname($this->_lpSource) . "/" . basename($this->_lpSource));
            $lock=new lpFileLock($name);
            $lock->lock();
            
            if($lpCfgFileCache)
            {
                $this->_lpCache[$key]=$value;
                $this->writeFile($this->_lpCache);
            }
            else
            {
                $cache=$this->loadFile();
                $cache[$key]=$value;
                $this->writeFile($cache);
            }
            
            $lock->unLock();
        }
    }
    
    private function loadFile()
    {
        global $lpCfgSTART,$lpCfgEND;

        $content=file_get_contents($this->_lpFile);
        $content=substr($content,strlen($lpCfgSTART),strlen($content)-strlen($lpCfgSTART)-strlen($lpCfgEND));

        return unserialize($content);
    }
    
    private function writeFile($content)
    {
        global $lpCfgSTART,$lpCfgEND;

        $content = $lpCfgSTART . serialize($content) . $lpCfgEND;
        file_put_contents($this->_lpSource,$content);
    }
}
