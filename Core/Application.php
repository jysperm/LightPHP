<?php

namespace LightPHP\Core;

use LightPHP\Tool\Config;
use LightPHP\Tool\Debug;

/**
 * 该类提供应用全局层面的数据储存，工具函数。
 */
class Application
{
    public static $paths = "";
    private static $atExit = [];

    public static $get = [];
    public static $post = [];
    public static $cookie = [];
    public static $server = [];

    public static function helloWorld(array $config)
    {
        $c = (new Config)->loadFromArray([
            // 时区
            "TimeZone" => "Asia/Shanghai",
            // 运行级别
            "RunLevel" => lpDefault,
            "Paths" => [
                "core" => __DIR__ . "/../../core",
                "template" => __DIR__ . "/../../compiled/template"
            ]
        ])->loadFromArray($config);

        // 设置运行模式
        if (!defined("lpRunLevel"))
            define("lpRunLevel", $c["RunLevel"]);

        // 如果PHP版本过低, 显示警告
        if (version_compare(PHP_VERSION, $c["lpRecommendedPHPVersion"]) <= 0)
            trigger_error("Please install the newly version of PHP ({$c["RecommendedPHPVersion"]}+).");

        // 设置时区
        date_default_timezone_set($c["TimeZone"]);

        self::$paths = $c["Paths"];

        if (lpWrapSuperGlobals)
            list(self::$get, self::$post, self::$cookie, self::$server) = [
                $_GET, $_POST, $_COOKIE, $_SERVER
            ];

        if (lpErrorHandling)
            Debug::registerErrorHandling();
    }

    public static function bye()
    {
        foreach (self::$atExit as $func)
            $func();
    }

    public static function registerAtExit(callable $func)
    {
        self::$atExit[] = $func;
    }
}
