<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
*   该类提供应用全局层面的数据储存，工具函数。
*/

class lpApp
{
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
}
