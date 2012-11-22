<?php

class lpFileLock
{
    private $file=NULL;
    private $fileName;
    private $isLocked=false;

    public function __construct($name,$path=".")
    {
        $this->fileName=$path . "/" . $name . "." . substr(md5($name),6) . ".lock";
    }

    public function lock($type=LOCK_EX,$testOnly=false)
    {
        if(!$this->file)
            $this->file=fopen($this->fileName,"w+");

        if($this->isLocked)
            return false;

        if($testOnly)
        {
            if(flock($this->file,$type | LOCK_NB))
            {
                $this->unLock();
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            while(!flock($this->file,$type));
            $this->isLocked=true;
        }
    }

    public function unLock()
    {
        if($this->isLocked)
        {
            flock($this->file,LOCK_UN);
            fclose($this->file);
            $this->isLocked=false;
            return true;
        }
        else
        {
            return false;
        }
    }

    public function isLock()
    {
        return $this->isLocked;
    }

    public function __destruct()
    {
        $this->unLock();
    }
}

class lpMySQLLock
{
    private $name=NULL;
    private $conn;

    public function __construct($name="lpLock",$conn=NULL)
    {
        $this->name=$name;

        if(!$conn)
            $this->conn=new lpMySQL;
        else
            $this->conn=$conn;
    }

    public function lock($timeout=999,$testOnly=false)
    {
        $timeout=(int)$timeout;

        if($testOnly)
        {
            $rs=$this->conn->exec("SELECT IS_FREE_LOCK('%s',{$timeout} );",$this->name);

            $result=$rs->toArray();
            return (bool)$result[0];
        }
        else
        {
            $rs=$this->conn->exec("SELECT GET_LOCK('%s',{$timeout} );",$this->name);

            $result=$rs->toArray();
            return (bool)$result[0];
        }
    }

    public function unLock()
    {
        $rs=$this->conn->exec("SELECT RELEASE_LOCK('%s');",$this->name);

        $result=$rs->toArray();
        return (bool)$result[0];
    }

    public function isLock()
    {
        $rs=$this->conn->exec("SELECT IS_USED_LOCK('%s');",$this->name);

        $result=$rs->toArray();
        return (bool)$result[0];
    }

    public function __destruct()
    {
        $this->unLock();
    }
}

class lpMutex
{
    private $lock;

    public function __construct()
    {
        $this->lock=new lpFileLock(md5($_SERVER["SCRIPT_FILENAME"]));
        $this->lock->lock();
    }

    public function __destruct()
    {
        $this->lock->unLock();
    }
}
