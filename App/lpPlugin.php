<?php

/**
 * 该类提供了简单的插件机制，大概分两部分：
 *
 * * 静态成员：
 *     用于加载和管理插件。
 * * 实例部分：
 *     插件需继承该类来提供元信息。
 *
 * TODO:
 * * 为 initPlugin() 提供缓存机制
 * * 根据元信息优化插件加载和 hook 执行的顺序
 * * 提供控制插件权限的机制
 * * 调研并改进性能瓶颈
 */
class lpPlugin
{
    // ----- 静态成员

    private static $pluginMeta = [];
    private static $hooks = [];

    public static function initAutoload()
    {
        $pluginMeta = &self::$pluginMeta;
        spl_autoload_register(function($className) use(&$pluginMeta) {
            foreach($pluginMeta as $meta)
            {
                foreach($meta["autoload"] as $dir)
                {
                    $file = $meta["dir"] . "/{$dir}/{$className}.php";
                    if(file_exists($file))
                        return require_once($file);
                }
            }
        });
    }

    public static function registerPluginDir($dir, $namespacePrefix = "lpPlugins")
    {
        foreach(new DirectoryIterator($dir) as $fileinfo)
        {
            /** @var $fileinfo DirectoryIterator */
            if(!$fileinfo->isDot() && $fileinfo->isDir())
            {
                $file = "{$fileinfo->getPathname()}/{$fileinfo->getFilename()}.php";
                if(file_exists($file))
                {
                    require_once($file);
                    $name = $fileinfo->getFilename();
                    $className = "\\{$namespacePrefix}\\{$name}\\{$name}";

                    /** @var $instance lpPlugin */
                    $instance = new $className;

                    self::$pluginMeta[$name] = $instance->metaData();
                    self::$pluginMeta[$name]["instance"] = $instance;
                    self::$pluginMeta[$name]["classPrefix"] = "\\{$namespacePrefix}\\{$name}\\";
                }
            }
        }
    }

    public static function initPlugin()
    {
        foreach(self::$pluginMeta as $plugin)
        {

            $plugin["instance"]->init();

            foreach($plugin["hook"] as $hookName => $func)
                self::$hooks[$hookName][] = $func;
        }
    }

    public static function bindRoute()
    {
        foreach(self::$pluginMeta as $plugin)
        {
            foreach($plugin["instance"]->routes() as $k => $v)
            {
                if(is_array($v))
                    list($func, $flags) = $v;
                else
                    list($func, $flags) = [$v, []];
                lpApp::bind($k, $func, $flags);
            }
        }
    }

    public static function hook($hookName, array $param = [])
    {
        foreach(self::$hooks[$hookName] as $hook)
            $param = $hook($param);

        return $param;
    }

    // ----- 需要重写的函数

    protected static function meta(array $data)
    {
        $default = [
            "autoload" => ["handler", "model"],
            "type" => [],
            "requestStatic" => [],
            "requestVersion" => [null => null],
            "requestPlugins" => []
        ];

        return array_merge($default, $data);
    }

    protected function hooks()
    {
        return [];
    }

    protected function routes()
    {
        return [];
    }

    protected function metaData()
    {

    }

    protected function init()
    {

    }

    // ----- 实例成员

    private $dir;

    public function __construct()
    {
        $this->dir = $this->metaData()["dir"];
    }

    public function file($name)
    {
        return "{$this->dir}/{$name}";
    }

    public function className($class)
    {
        return lpPlugin::$pluginMeta[$this->metaData()["name"]] . $class;
    }
}