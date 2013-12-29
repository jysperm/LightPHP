<?php

namespace LightPHP\Tool\Lock;

/**
 *   MySQL锁类.
 *
 * @type resource class
 */
class MySQLLock
{
    /** @var string 锁的名字 */
    private $name = null;

    /** @var PDO 数据库连接 */
    private $db;

    /**
     *   构造一个MySQL锁实例.
     *
     * @param string $name 锁的名字
     * @param PDO $db
     */
    public function __construct($name, $db = null)
    {
        $this->name = $name;
        $this->db = $db ? : lpFactory::get("PDO.LightPHP");
    }

    /**
     *   加锁.
     *
     * @param int $timeout 等待超时的时间
     */
    public function lock($timeout = 30)
    {
        $timeout = (int)$timeout;

        $this->db->exec(lpPDOModel::query("SELECT GET_LOCK('{1}', {2})", [$this->name, $timeout]));
    }

    /**
     *   解锁.
     */
    public function unLock()
    {
        $this->db->exec(lpPDOModel::query("SELECT RELEASE_LOCK('{1}')", [$this->name]));
    }

    /**
     * @return bool 是否处于加锁状态.
     */
    public function isLock()
    {
        $this->db->exec(lpPDOModel::query("SELECT IS_USED_LOCK('{1}')", [$this->name]));
    }

    public function __destruct()
    {
        $this->unLock();
    }
}