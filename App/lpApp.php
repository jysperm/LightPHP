<?php

/**
 * 该类提供应用全局层面的数据储存，工具函数。
 */
class lpApp
{
    public static $paths = "";
    private static $atExit = [];

    public static $get = [];
    public static $post = [];
    public static $cookie = [];
    public static $server = [];

    public static function helloWorld(array $config)
    {
        $c = (new lpConfig)->loadFromArray([
            // 时区
            "TimeZone" => "Asia/Shanghai",
            // 运行级别
            "RunLevel" => lpDefault,
            // 最低支持的 PHP 版本
            "RecommendedPHPVersion" => "5.4.0",
            "Paths" => [
                "core" => dirname(__FILE__) . "/../../core",
                "template" => dirname(__FILE__) . "/../../compiled/template"
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
            lpDebug::registerErrorHandling();
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

    public static function registerBuildInShortFunc()
    {
        function c($k = null)
        {
            /** @var lpConfig $config */
            $config = lpFactory::get("lpConfig");
            if ($k)
                return $config->get($k);
            else
                return $config->data();
        }

        function l($k = null)
        {
            $locale = lpFactory::get("lpLocale");
            $param = func_get_args();
            array_shift($param);
            return call_user_func_array([$locale, "get"], [$k, $param]);
        }

        function f($name, $tag = null)
        {
            return lpFactory::get($name, $tag);
        }
    }
}
