<?php

namespace LightPHP\Core;

/**
 * Class lpRouter
 *
 * 该类包含路由相关功能。
 * 该类全部为静态成员，因为同一时刻，程序只在唯一的一条路由路径上。
 */
class Router
{
    /**
     * @var string 被访问路径
     * 如 `/user/show/jysperm`, 不含 Query String.
     */
    public static $url = null;

    /**
     * @var string 被路由匹配到的 URL
     */
    public static $matchedUrl = null;

    /**
     * @var array 路由中被匹配的参数
     * 如，路由为 `/(\d+)/(\w)`, URL 为 `/123/a`,
     * $urlParam 即为 ["123", "a"]
     */
    public static $urlParam = [];

    /**
     * @var array 已注册的路由
     *
     * [
     *     [<callback $func>, <array $flags>],
     *     ...
     * ]
     */
    private static $routes = [];

    /**
     * 发起重定向
     *
     * @param string $url
     * @param int $code
     */
    public static function goUrl($url, $code = 302)
    {
        if ($code == 301)
            header('HTTP/1.1 301 Moved Permanently');
        header("Location: {$url}");
    }

    /**
     * 解析用于路由匹配的 URL
     *
     * @param string $uri
     * @param string $query
     * @return string
     */
    public static function parseURL($uri = null, $query = null)
    {
        $uri = $uri ? : $_SERVER["REQUEST_URI"];
        $query = $query ? : $_SERVER["QUERY_STRING"];

        $queryStrLen = 0;
        if ($query)
            $queryStrLen = strlen($_SERVER["QUERY_STRING"]) + 1;

        $result = substr($uri, 0, strlen($uri) - $queryStrLen);
        if (substr($result, -1, 1) == "?")
            $result = substr($result, 0, strlen($result) - 1);

        return $result;
    }

    /**
     * 添加一个路由
     *
     * @param string $rx 用于匹配的正则表达式
     * @param callable $func 处理器
     * @param array $flags
     */
    public static function bind($rx, callable $func, array $flags = [])
    {
        self::$routes[$rx] = [$func, $flags];
    }

    /**
     * 执行匹配到的路由，然后退出程序
     */
    public static function exec(callable $default = null)
    {
        self::$url = self::$url ? : self::parseURL();

        $func = self::match();

        if($func)
            call_user_func_array($func, self::$urlParam);
        else if($default)
            $default();

        Application::bye();
    }

    /**
     * 进行路由匹配
     *
     * @return callable
     */
    public static function match()
    {
        foreach (self::$routes as $rx => $route) {
            list($func, $flags) = $route;

            foreach ($flags as $flag => $value) {
                switch ($flag) {
                    case "method":
                        if (!in_array(Application::$server["REQUEST_METHOD"], $value))
                            continue 3;
                }
            }

            $rf = isset($flags["regex.flags"]) ? $flags["regex.flags"] : "";
            $rs = isset($flags["regex.split"]) ? $flags["regex.split"] : "|";

            if (!$rx | preg_match("{$rs}{$rx}{$rs}{$rf}", self::$url, $result)) {
                self::$matchedUrl = array_shift($result);
                self::$urlParam = $result;

                call_user_func_array($func, $result);

                return $rx;
            }
        }

        return null;
    }
}
