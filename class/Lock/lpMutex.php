<?php

/**
 *   互斥锁类.
 *
 *   构造该类即进入互斥区, 销毁该类即离开互斥区.
 */

class lpMutex
{
    /** @var lpFileLock 锁 */
    private $lock;

    public function __construct()
    {
        $this->lock = new lpFileLock($_SERVER["SCRIPT_FILENAME"]);
        $this->lock->lock();
    }

    public function __destruct()
    {
        $this->lock->unLock();
    }
}