<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
*   处理器基类
*/

abstract class lpHandler
{
    /** @var lpPlugin */
    protected $plugin = null;

    public static function invoke($action, $param)
    {
        try {
            ob_start();

            $handler = get_called_class();

            if(!$action)
                $action = "__invoke";

            $reflection = new ReflectionMethod($handler, $action);
            if(!$reflection->isPublic() || $reflection->isStatic())
                throw new lpHandlerException("unknown action", ["handler" => $handler, "operator" => $action]);

            $handler = new $handler;
            return $reflection->invokeArgs($handler, $param);
        }
        catch(lpHandlerException $e)
        {
            static::onException($e->getMessage(), $e->getData());
        }
        catch(ReflectionException $e)
        {
            throw new lpException("action not found");
        }
    }

    protected static function onException($message, $data)
    {
        echo "Exception {$message}\n";
        print_r($data);
    }

    protected function result($data)
    {
        print $data;
    }

    protected function render($template, $values = [])
    {
        if($this->plugin)
            return lpCompiledTemplate::outputFile($this->plugin->file("template/{$template}.php"), $values);
        else
            return lpCompiledTemplate::outputFile(lpApp::$paths["template"] . "/{$template}.php", $values);
    }

    protected function get(array $rules)
    {
        return $this->assertParam($_GET, $rules);
    }

    protected function post(array $rules)
    {
        return $this->assertParam($_POST, $rules);
    }

    protected static function isPost()
    {
        return $_SERVER["REQUEST_METHOD"] == "POST";
    }

    private function assertParam($source, $rules)
    {
        $result = [];
        foreach($rules as $name => $rx)
        {
            if(is_int($name))
                list($name, $rx) = [null, $name];

            $value = isset($source[$name]) ? $source[$name] : "";

            if(!$rx || preg_match($rx, $value))
                $result[]= $value;
            else
                throw new lpHandlerException("missing request data", ["name" => $name, "assert" => $rx]);
        }
        return $result;
    }

    public function __construct($plugin = null)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param string $name
     * @return lpPDOModel
     */
    protected function model($name)
    {
        if($this->plugin)
            return lpFactory::get("p{$name}Model");
        else
            return lpFactory::get("{$name}Model");
    }
}
