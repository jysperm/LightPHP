<?php

/**
*   该文件包含 lpHander 的类定义.
*
*   @package LightPHP
*/

abstract class lpHander
{
    public function __construct()
    {
        ob_start();
    }

    public function __destruct()
    {
        ob_end_flush();
    }
}

class lpPage extends lpHander
{

}

class lpAction extends lpHander
{

}