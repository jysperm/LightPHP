<?php

/**
*   该文件包含 lpAuth 的类定义.
*
*   @package LightPHP
*/

abstract class lpAuthDrive
{
    public $cbSucceed;

    public function __construct()
    {
        $this->cbSucceed = function($user)
        {

        };
    }
    
    public function auth($user, $passwd)
    {

    }

    public function login($user, $passwd)
    {

    }

    static public function logout()
    {

    }

    static public function getUName()
    {

    }
}
