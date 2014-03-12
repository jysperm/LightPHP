<?php

namespace LightPHP\Locale\Adapter;

use LightPHP\Locale\Exception\LocaleNotExistException;

class ArrayLocale implements LocaleInterface
{
    /** @var array */
    private $data = [];
    /** @var string */
    private $suffix;

    public function __construct($suffix = ".php")
    {
        $this->suffix = $suffix;
    }

    public function init($localeRoot, $language)
    {

    }

    public function load($file)
    {
        $filename = "{$file}{$this->suffix}";
        if (!file_exists($filename))
            throw new LocaleNotExistException($filename);

        $this->data = array_merge($this->data, include($filename));
    }

    public function get($name)
    {
        if (isset($this->data[$name]))
            return $this->data[$name];
        return null;
    }
}
