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

    private static $routes = [];
    private static $atExit = [];

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

        // 设置运行模式
        if(!defined("lpRunLevel"))
            define("lpRunLevel", $c["RunLevel"]);

        // 如果PHP版本过低, 显示警告
        if(version_compare(PHP_VERSION, $c["lpRecommendedPHPVersion"]) <= 0)
            trigger_error("Please install the newly version of PHP ({$c["RecommendedPHPVersion"]}+).");

        // 设置时区
        date_default_timezone_set($c["TimeZone"]);

        if(!defined("lpDisableErrorHandling") || !lpDisableErrorHandling)
            lpDebug::registerErrorHandling();
    }

    public static function registerAtExit(callable $func)
    {
        self::$atExit[] = $func;
    }

    public static function registerShortFunc()
    {
        function c($k = null)
        {
            /** @var lpConfig $config */
            $config = lpFactory::get("lpConfig");
            if($k)
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

    /**
     * 判断客户端语言
     *
     * * 首先根据 Cookie
     * * 然后根据 HTTP Accept-Language
     * * 最后私用默认语言
     *
     * @param $localeRoot           本地化文件根目录
     * @param $defaultLanguage      默认语言
     * @param string $cookieName    储存语言的Cookie
     * @return string
     */
    static public function checkLanguage($localeRoot, $defaultLanguage, $cookieName="language")
    {
        $lang = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : "";
        if($lang && preg_match("/^[_A-Za-z]+$/", $lang) && is_dir("{$localeRoot}/{$lang}"))
            return $_COOKIE[$cookieName];

        if($_SERVER["HTTP_ACCEPT_LANGUAGE"])
        {
            $languages = explode(",", str_replace("-", "_", $_SERVER["HTTP_ACCEPT_LANGUAGE"]));

            foreach($languages as $i)
                if(preg_match("/^[_A-Za-z]+$/", $i) && is_dir("{$localeRoot}/{$lang}"))
                    return $i;
        }

        return $defaultLanguage;
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
        self::$routes[$rx] = [$func, $flags];
    }

    public static function exec()
    {
        $queryStrLen = 0;
        if(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"])
            $queryStrLen = strlen($_SERVER["QUERY_STRING"]) + 1;

        self::$url = substr($_SERVER["REQUEST_URI"], 0, strlen($_SERVER["REQUEST_URI"]) - $queryStrLen);
        if(substr(self::$url, -1, 1) == "?")
            self::$url = substr(self::$url, 0, strlen(self::$url) - 1);

        foreach(self::$routes as $rx => $route)
        {
            list($func, $flags) = $route;

            foreach($flags as $flag => $value)
            {
                switch($flag)
                {
                    case "method":
                        if(!in_array($_SERVER["REQUEST_METHOD"], $value))
                            continue 3;
                }
            }

            $rf = isset($flags["regex.flags"]) ? $flags["regex.flags"] : "";
            $rs = isset($flags["regex.split"]) ? $flags["regex.split"] : "%";

            if(!$rx | preg_match("{$rs}{$rx}{$rs}{$rf}", self::$url, $result))
            {
                self::$matchedUrl = array_shift($result);
                self::$urlParam = $result;

                call_user_func_array($func, $result);

                return $rx;
            }
        }

        foreach(self::$atExit as $func)
            $func();
    }
}
