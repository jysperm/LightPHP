<?php

/**
*   该文件包含 lpApp 的类定义.
*
*   @package LightPHP
*/

/**
*   如果你继承了 lpApp, 那么你可以覆盖这个变量.
*
*   因为 lpApp 的所有成员都是静态成员, 所以使用这个实例和直接用类名没有区别.
*
*   @type lpApp
*/
$lpApp = new lpApp;

/**
*   lpApp 用来管理全局的资源, 如数据库连接等等
*/

class lpApp
{
    static private $dbs = [];

    static public function registerDatabase($db, $id="")
    {
        $this->dbs[$id] = $db;
    }

    static public function getDB($id="")
    {
        return $this->dbs[$id];
    }

    static private $auths = [];

    static public function registerAuthTool($auth, $id="")
    {
        $this->auths[$id] = $auth;
    }

    static public function auth($id="")
    {
        return $this->auths[$id];
    }
}