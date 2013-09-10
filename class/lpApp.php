<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
*   该类提供应用全局层面的数据储存，工具函数。
*/

class lpApp
{
    public static $url = "";
    public static $matchedUrl = "";
    public static $urlParam = [];

    public static function helloWorld(array $config)
    {
        $c = new lpConfig;

        $c->loadFromArray([
            // 时区
            "TimeZone" => "Asia/Shanghai",
            // 运行级别
            "RunLevel" => lpDefault,
            // 最低支持的 PHP 版本
            "RecommendedPHPVersion" => "5.4.0"
        ]);

        $c->loadFromArray($config);

        // 设置时区
        date_default_timezone_set($c["TimeZone"]);

        // 设置运行模式
        if(!defined("lpRunLevel"))
            define("lpRunLevel", $c["RunLevel"]);

        // 如果PHP版本过低, 显示警告
        if(version_compare(PHP_VERSION, $c["lpRecommendedPHPVersion"]) <= 0)
            trigger_error("Please install the newly version of PHP ({$c["RecommendedPHPVersion"]}+).");
    }

    public static function bye()
    {
        exit(0);
    }

    public static function goUrl($url, $code = 302)
    {
        if($code == 301)
            header('HTTP/1.1 301 Moved Permanently');
        header("Location: {$url}");
    }

    public static function bind($rx, callable $func, array $flags = [])
    {
        if(!self::$url)
        {
            $queryStrLen = 0;
            if(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"])
                $queryStrLen = strlen($_SERVER["QUERY_STRING"]) + 1;

            self::$url = substr($_SERVER["REQUEST_URI"], 0, strlen($_SERVER["REQUEST_URI"]) - $queryStrLen);
            if(substr(self::$url, -1, 1) == "?")
                self::$url = substr(self::$url, 0, strlen(self::$url) - 1);
        }

        foreach($flags as $flag => $value)
        {
            switch($flag)
            {
                case "method":
                    if(!in_array($_SERVER['REQUEST_METHOD'], $value))
                        return;
            }
        }

        $rf = isset($flags["regex.flags"]) ? $flags["regex.flags"] : "";
        $rs = isset($flags["regex.split"]) ? $flags["regex.split"] : "%";

        if(!$rx | preg_match("{$rs}{$rx}{$rs}{$rf}", self::$url, $result))
        {
            self::$matchedUrl = array_shift($result);
            self::$urlParam = $result;

            call_user_func_array($func, $result);

            self::bye();
        }
    }
}
