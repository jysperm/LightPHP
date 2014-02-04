<?php

namespace LightPHP\Locale\Adapter;

use LightPHP\Locale\Exception\LocaleNotExistException;

class ArrayLocale implements LocaleInterface
{
    /** @var array 本地化数据 */
    private $data = [];

    public function load($file)
    {
        $filename = "{$file}.php";
        if(!file_exists($filename))
            throw new LocaleNotExistException($filename);

        $this->data = array_merge($this->data, include($filename));
    }

    public function get($name)
    {
        if(isset($this->data[$name]))
            return $this->data[$name];
        return null;
    }
}
