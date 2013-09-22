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
 * * 根据原信息优化插件加载和 hook 执行的顺序
 * * 提供控制插件权限的机制
 * * 调研并改进性能瓶颈
 */
class lpPlugin
{
    const ClassNamePrefix = "p";

    // ----- 静态成员

    private static $pluginMeta = [];
    private static $hooks = [];

    public static function registerPluginDir($dir)
    {
        foreach(new DirectoryIterator($dir) as $fileinfo)
        {
            /** @var $fileinfo DirectoryIterator */
            if(!$fileinfo->isDot() && $fileinfo->isDir())
            {
                $file = "{$fileinfo->getPathname()}/" . self::ClassNamePrefix . "{$fileinfo->getFilename()}.php";
                if(file_exists($file));
                {
                    require_once($file);
                    $name = $fileinfo->getFilename();
                    $pluginClassName = self::ClassNamePrefix . $name;

                    /** @var $pluginClassName lpPlugin */
                    self::$pluginMeta[$name] = $pluginClassName::metaData();

                    $plugin = new $pluginClassName($fileinfo->getPathname());
                    self::$pluginMeta[$name]["instance"] = $plugin;;
                }
            }
        }
    }

    public static function registerPlugin($instance, $dir = null)
    {
        $pluginClassName = get_class($instance);
        $name = substr($pluginClassName, strlen(self::ClassNamePrefix));

        /** @var $pluginClassName lpPlugin */
        self::$pluginMeta[$name] = $pluginClassName::metaData();

        $plugin = new $pluginClassName($dir);
        self::$pluginMeta[$name]["instance"] = $plugin;;
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
            foreach($plugin["route"] as $k => $v)
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
            "type" => [],
            "requestStatic" => [],
            "requestVersion" => [null => null],
            "requestPlugins" => [],
            "route" => [],
            "hook" => []
        ];

        return array_merge($default, $data);
    }

    protected static function metaData()
    {

    }

    protected function init()
    {

    }

    // ----- 实例成员

    protected $dir = null;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    protected function load($name)
    {
        require_once("{$this->dir}/{$name}.php");
    }
}