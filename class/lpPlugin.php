<?php

/**
 * 该类提供了简单的插件机制，大概分两部分：
 *
 * * 静态成员：
 *     用于加载和管理插件。
 * * 实例部分：
 *    插件需继承该类来提供元信息。
 */
class lpPlugin
{
    // ----- 静态成员

    public static $dirs = [];

    public static function registerPluginDir($dir)
    {
        self::$dirs[] = $dir;
    }

    public static function initPlugin()
    {

    }

    public static function bindRoute()
    {

    }

    public static function hook($hookName, array $param)
    {

    }

    // ----- 实例成员

    public static function metaData($data = null)
    {

    }
}