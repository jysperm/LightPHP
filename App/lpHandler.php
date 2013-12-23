<?php

/**
 *   处理器基类
 */
abstract class lpHandler
{
    public static function invoke($action, $param = [], $propertys = [])
    {
        try {
            ob_start();

            $handler = get_called_class();

            if (!$action)
                $action = "__invoke";

            $reflection = new ReflectionMethod($handler, $action);
            if (!$reflection->isPublic() || $reflection->isStatic())
                throw new lpHandlerException("unknown action", ["handler" => $handler, "operator" => $action]);

            $handler = new $handler;

            foreach ($propertys as $property => $value)
                $handler->$property = $value;

            return $reflection->invokeArgs($handler, $param);
        } catch (lpHandlerException $e) {
            static::onException($e->getMessage(), $e->getData());
        } catch (ReflectionException $e) {
            throw new lpException("action not found");
        }
    }

    public function __construct()
    {

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
        foreach ($rules as $name => $condition) {
            if (is_int($name))
                list($name, $condition) = [null, $name];

            $value = isset($source[$name]) ? $source[$name] : "";

            if (is_callable($condition)) {
                if (!$condition($value))
                    throw new lpHandlerException("invalid request data", ["name" => $name, "assert" => "[callback]"]);
            } else if ($condition) {
                if (!preg_match($condition, $value))
                    throw new lpHandlerException("invalid request data", ["name" => $name, "assert" => $condition]);
            }

            $result[] = $value;
        }
        return $result;
    }

    /**
     * @param string $name
     * @return lpPDOModel
     */
    protected function model($name)
    {
        return lpFactory::get("{$name}Model");
    }
}
