<?php

class lpSQLRs
{
    private $_lpRes=NULL;
    private $_lpRow=NULL;
    private $_lpSeek=0;

    public function __construct($res=NULL)
    {
        $this->_lpRes=$res;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        @mysql_free_result($this->_rsID);
        $this->_lpRes=NULL;
        $this->_lpRow=NULL;
    }

    public function __get($name)
    {
        return $this->value($name);
    }

    public function value($name)
    {
        return $this->_lpRow[$name];
    }

    public function read()
    {
        $r=mysql_fetch_assoc($this->_lpRes);
        if($r)
        {
            $this->_lpRow=$r;
            $this->_lpSeek++;
            return true;
        }
        else
        {
            return false;
        }
    }

    public function toArray($num=-1)
    {
        $result=array();
        while($this->read() && $num--!=0)
        {
            $result[]=$this->_lpRow;
        }
        return $result;
    }
    
    public function rawArray()
    {
        return $this->_lpRow;
    }

    public function num()
    {
        return mysql_num_rows($this->_lpRes);
    }

    public function seek()
    {
        return $this->_lpSeek;
    }

    public function setSeek($s=0)
    {
        $this->_lpSeek=$s;
        return mysql_data_seek($this->_lpRes,$s);
    }
}
?>
