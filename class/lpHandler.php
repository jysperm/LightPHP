<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
*   处理器基类
*/

abstract class lpHandler
{
    protected function result($data)
    {
        print $data;
    }

    protected function get(array $rules)
    {
        return $this->assertParam($_GET, $rules);
    }

    protected function post(array $rules)
    {
        return $this->assertParam($_POST, $rules);
    }

    private function assertParam($source, $rules)
    {
        $result = [];
        foreach($rules as $name => $rx)
        {
            $value = isset($source[$name]) ? $source[$name] : "";

            if(!$rx || preg_match($rx, $value))
                $result[]= $value;
            else
                throw new lpException("missing request data", ["name" => $name, "assert" => $rx]);
        }
        return $result;
    }
}
