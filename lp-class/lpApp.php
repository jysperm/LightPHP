<?php

/**
*   该文件包含 lpApp 的类定义.
*
*   @package LightPHP
*/

/**
*   lpApp 用来管理全局的资源, 如数据库连接等等
*/

trait lpAppResourceManager
{
    static private $dbs = [];

    static public function registerDatabase($db, $id="")
    {
        self::$dbs[$id] = $db;
    }

    static public function getDB($id="")
    {
        return self::$dbs[$id];
    }

    static private $auths = [];

    static public function registerAuthTool($auth, $id="")
    {
        self::$auths[$id] = $auth;
    }

    static public function auth($id="")
    {
        return self::$auths[$id];
    }

    /**
     *  注册类自动加载器
     *
     *  欲加载的类名会先经过`预转换表`的处理, 再传入加载器函数.
     *  目前该函数只可被调用一次.
     *
     *  @param  callback $autoload  加载器函数
     *  @param  array    $map       预转换表
     */

    static public function registerAutoload($autoload, $map)
    {
        spl_autoload_register(function($name) use($autoload, $map)
        {
            if(in_array($name, array_keys($map)))
                $name = $map[$name];

            $autoload($name);
        });
    }

    static private $paths = [];

    static public function registerDefaultPath(array $paths)
    {
        self::$paths = $paths;
    }

    static public function defaultPaths()
    {
        return self::$paths;
    }
}

trait lpAppRoute
{
    /**
    *   默认处理器.
    *
    *   这里指定的默认的处理器, 通常是应用首页.
    */
    static public $defaultHandlerName = null;

    /*
     *  处理器类名前缀.
     */
    static public $handlerPerfix = "";

    /**
    *   默认路由分发器.
    *
    *   该函数实现:
    *   将URL以斜杠划分成若干部分, 以第一个部分为类名创建处理器实例, 调用它的第二个部分指定的函数名,
    *   并将剩余的部分作为参数.
    *
    *   例如请求URL是 /user/show/jybox
    *   那么将会创建user类的一个实例, 以jybox为参数, 调用它的show函数.
    *
    *   你需要通过这样的方式在你的应用中启用该分发器:
    *   
    *       $lpApp->bindLambda(null, $lpApp::$defaultFilter);
    */

    static public $defaultFilter = null;

    static public function bindLambda($rx, $lambda)
    {
        if(!$rx | preg_match("%{$rx}%u", rawurldecode($_SERVER["REQUEST_URI"])))
        {
            $lambda();

            exit(0);
        }
    }

    static public function goUrl($url)
    {
        header("Location: {$url}");
    }
}

class lpApp
{
    use lpAppResourceManager, lpAppRoute;

    static public function helloWorld()
    {
        return new lpApp;
    }
}

lpAPP::$defaultFilter = function()
{
    $queryStr = isset($_SERVER["QUERY_STRING"])?$_SERVER["QUERY_STRING"]:"";
    if($queryStr)
        $queryStr = "?{$queryStr}";
    $url = substr($_SERVER["REQUEST_URI"], 0, strlen($_SERVER["REQUEST_URI"]) - strlen($queryStr));

    $args = array_filter(explode("/", $url));

    if(count($args) > 0)
    {
        $hander = lpApp::$handlerPerfix . array_values($args)[0];
        $hander = new $hander;
        array_shift($args);
    }
    else
    {
        $hander = new lpApp::$defaultHandlerName;
    }

    if(!is_subclass_of($hander, "lpHandler"))
        trigger_error("is not a subclass of lpHander");

    if(count($args) > 0)
    {
        $funcName = str_replace("-", "_", $args[0]);
        array_shift($args);
        call_user_func_array([$hander, $funcName], $args);
    }
    else
    {
        $hander();
    }
};

/**
 *   如果你继承了 lpApp, 那么你可以覆盖这个变量.
 *
 *   因为 lpApp 的所有成员都是静态成员, 所以使用这个实例和直接用类名没有区别.
 *
 *   @type lpApp
 */
// $lpApp = new lpApp;
