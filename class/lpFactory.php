<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
 * 一个对象生成器，模拟工厂模式。
 */

class lpFactory
{
    /**
     * @var array 对象数组
     */
    static private $data = [];
    /**
     * @var array 对象构造器数组
     */
    static private $creator = [];

    /**
     * 注册一个对象构造器
     *
     * @param $name         类名
     * @param $creator      构造器
     */
    public static function register($name, $creator)
    {
        self::$creator[$name] = $creator;
    }

    /**
     * 取出或构造一个新对象
     *
     * @param string $name     类名
     * @param string $tag 额外信息
     *
     * @return mixed    对象
     */
    public static function get($name, $tag = null)
    {
        if(!isset(self::$data[$name][$tag]))
        {
            $creator = self::$creator[$name];

            self::$data[$name][$tag] = $creator($tag);
        }

        return self::$data[$name][$tag];
    }

    /**
     * 强制修改一个对象的值
     * 慎用
     *
     * @param string $name
     * @param mixed $value
     * @param string $tag
     */
    public static function modify($name, $value, $tag = null)
    {
        self::$data[$name][$tag] = $value;
    }
}