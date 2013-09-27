<?php

namespace LightPHP\Tool;
defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
 *  该类提供了配置信息管理的功能
 */

class lpConfig implements ArrayAccess
{
    private $data = [];

    public function loadFromPHPFile($file)
    {
        $this->loadFromArray(include($file));
    }

    public function loadFromArray($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function data()
    {
        return $this->data;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    /* ArrayAccess */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}