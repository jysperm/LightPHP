<?php

namespace LightPHP\View;

use ArrayAccess;

class PHPTemplate implements ArrayAccess
{
    protected $filename;
    protected $values = [];

    /* ArrayAccess */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
            $this->values[] = $value;
        else
            $this->values[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->values[$offset]) ? $this->values[$offset] : null;
    }

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function setValue($k, $v)
    {
        $this->values[$k] = $v;
    }

    public function setValues($arr)
    {
        foreach ($arr as $k => $v)
            $this->setValue($k, $v);
    }

    public function output()
    {
        include($this->filename);
    }

    public function getOutput()
    {
        ob_start();
        $this->output();
        return ob_get_clean();
    }

    public static function outputFile($file, $values = [])
    {
        $tmp = new static($file);
        if ($values)
            $tmp->setValues($values);
        $tmp->output();
    }

    public static function getOutputFile($file, $values = [])
    {
        $tmp = new static($file);
        if ($values)
            $tmp->setValues($values);
        return $tmp->getOutput();
    }
}