<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
*   文件锁类.
*/

class lpFileLock
{
    /** @var resource 文件句柄 */
    private $file = null;

    /** @var string 文件名 */
    private $fileName;

    /** @var bool 当前是否加锁 */
    private $isLocked = false;

    /**
    *   构造一个文件锁实例.
    *
    *   @param string $name 锁的名字
    *   @param string $path 锁的路径
    */

    public function __construct($name, $path = ".")
    {
        $this->fileName = "{$path}/lpLock_" . md5($name) . ".lock";
    }

    /**
     * 加锁.
     *
     * @param int $type 锁的类型
     */

    public function lock($type = LOCK_EX)
    {
        if(!$this->file)
            $this->file = fopen($this->fileName, "w+");
        if($this->isLocked)
            return;

        while(!flock($this->file, $type));
        $this->isLocked = true;
    }

    /**
    *   解锁.
    */

    public function unLock()
    {
        if($this->isLocked)
        {
            flock($this->file, LOCK_UN);
            fclose($this->file);
            $this->isLocked=false;
        }
    }

    /**
    *   @return bool 是否处于加锁状态.
    */

    public function isLock()
    {
        return $this->isLocked;
    }

    public function __destruct()
    {
        $this->unLock();
        if(file_exists($this->fileName))
            unlink($this->fileName);
    }
}
