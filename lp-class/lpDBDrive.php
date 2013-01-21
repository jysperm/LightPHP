<?php

/**
*   该文件包含 lpDBDrive 的类定义.
*
*   @package LightPHP
*/

/**
*   数据库驱动基类.
*
*   抽象了数据库的底层操作, lpDBQuery 和 lpDBResult 使用该类进行底层操作.
*   通过继承该类可以让LightPHP支持更多的数据库.
*
*   @type abstract resource class
*/

class lpDBDrive
{
    /**
    *   连接到数据库.
    *
    *   @param array $config 连接选项 [选项 => 值]
    */

    public function __construct($config=null)
    {

    }

    /**
    *   向数据表插入一行.
    *
    *   @param string $table 表名
    *   @param array  $row   要插入的数据 [列名 => 值]
    */

    public function insert($table, $row)
    {

    }

    /**
    *   从数据表查询数据.
    *
    *   @param string $table  表名
    *   @param array  $if     查询的条件 [列名 => 值] 或 [列名 => [操作符 => 值]]
    *   @param array  $config 要插入的数据 [选项 => 值]
    */

    public function insert($table, $if, $config)
    {

    }

    /**
    *   从数据表修改数据.
    *
    *   @param string $table  表名
    *   @param array  $if     修改的条件 [列名 => 值] 或 [列名 => [操作符 => 值]]
    *   @param array  $new    新数据 [列名 => 值]
    */

    public function insert($table, $if, $new)
    {
    	
    }

    /**
    *   从数据表删除数据.
    *
    *   @param string $table  表名
    *   @param array  $if     删除的条件 [列名 => 值] 或 [列名 => [操作符 => 值]]
    */

    public function insert($table, $if)
    {
    	
    }
}