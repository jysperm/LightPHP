<?php

namespace LightPHP\Core;

use ReflectionMethod;
use ReflectionException;
use LightPHP\Core\Exception\HandlerException;
use LightPHP\Core\Exception;

abstract class Handler
{
    const GET = "GET";
    const POST = "POST";
    const OPTIONS = "OPTIONS";
    const HEAD = "HEAD";
    const PATCH = "PATCH";
    const PUT = "PUT";
    const DELETE = "DELETE";

    const COOKIE = "COOKIE";
    const SERVER = "SERVER";

    public static function invoke($action = "__invoke", $param = [])
    {
        try {
            ob_start();

            $handlerName = get_called_class();

            $reflection = new ReflectionMethod($handlerName, $action);
            if (!$reflection->isPublic() || $reflection->isStatic() || $reflection->isFinal())
                throw new HandlerException("unknown action", ["handler" => $handlerName, "operator" => $action]);

            $handler = new $handlerName;

            return $reflection->invokeArgs($handler, $param);
        } catch (HandlerException $e) {
            static::onException($e->getMessage(), $e->getData());
        } catch (ReflectionException $e) {
            throw new Exception("action not found");
        }
    }

    public static function invokeREST($actionName = "Index", $method = self::GET, $param = [])
    {
        return self::invoke("{$method}{$actionName}", $param);
    }

    public static function method()
    {
        return Application::$server["REQUEST_METHOD"];
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
        var_dump($values);
    }

    protected function get(array $rules)
    {
        return self::assertParam($_GET, $rules);
    }

    protected function post(array $rules)
    {
        return self::assertParam($_POST, $rules);
    }

    protected function param($source, $rules)
    {
        $map = [
            self::GET => Application::$get,
            self::POST => Application::$post,
            self::COOKIE => Application::$cookie,
            self::SERVER => Application::$server
        ];

        return self::assertParam($map[$source], $rules);
    }

    private static function assertParam($source, $rules)
    {
        $result = [];
        foreach ($rules as $name => $rule) {
            if (is_int($name))
                list($name, $rule) = [null, $name];

            $value = isset($source[$name]) ? $source[$name] : "";

            if (is_callable($rule)) {
                if (!$rule($value))
                    throw new HandlerException("invalid request data", ["name" => $name, "assert" => "[callback]"]);
            } else if ($rule) {
                if (!preg_match($rule, $value))
                    throw new HandlerException("invalid request data", ["name" => $name, "assert" => $rule]);
            }

            $result[] = $value;
        }
        return $result;
    }

    /**
     * @param string $name
     * @return \LightPHP\Model\Model
     */
    protected function model($name)
    {
        return Factory::get("{$name}Model");
    }
}
