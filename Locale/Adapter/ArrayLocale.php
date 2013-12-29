<?php

namespace LightPHP\Locale\Adapter;

/**
 * 该类提供了简易的国际化功能.
 */
class ArrayLocale implements ArrayAccess
{
    /** @var string  本地化文件根目录 */
    private $localeRoot;
    /** @var string 语言 */
    private $language;
    /** @var array 数据 */
    private $data = [];
    private $exitsData = [];

    /**
     * 构造一个实例
     *
     * @param string $localeRoot 本地化文件根目录
     * @param string $language 语言
     */
    public function __construct($localeRoot, $language)
    {
        $this->localeRoot = $localeRoot;
        $this->language = $language;
    }

    public function load($files, $ext = ".php")
    {
        if (!is_array($files))
            $files = [$files];

        foreach ($files as $file) {
            $filename = "{$file}{$ext}";

            if (in_array($filename, $this->exitsData))
                return $this->data;
            else
                $this->exitsData[] = $filename;

            $this->data = array_merge($this->data, include("{$this->localeRoot}/{$this->language}/{$filename}"));
        }

        return $this->data;
    }

    public function file($file, $locale = null)
    {
        if (!$locale)
            $locale = $this->language;

        return "{$this->localeRoot}/{$locale}/{$file}";
    }

    public function language()
    {
        return $this->language;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function data()
    {
        return $this->data;
    }

    /* ArrayAccess */
    public function offsetSet($offset, $value)
    {

    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {

    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
