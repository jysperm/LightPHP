<?php

namespace LightPHP\Tool;

use ArrayAccess;

/**
 *  该类提供了配置信息管理的功能
 */
class Config implements ArrayAccess
{
    private $data = [];

    public function loadFromPHPFile($file)
    {
        $this->loadFromArray(include($file));
        return $this;
    }

    public function loadFromArray($data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function data()
    {
        return $this->data;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    /* --- ArrayAccess */
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