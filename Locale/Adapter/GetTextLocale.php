<?php

namespace LightPHP\Locale\Adapter;

use ArrayAccess;

class GetTextLocale implements ArrayAccess
{
    /** @var string  本地化文件根目录 */
    private $localeRoot;
    /** @var string 语言 */
    private $language;
    /** @var array 数据 */
    private $data = [];

    /**
     * 构造一个实例
     *
     * @param string $localeRoot 本地化文件根目录
     * @param string $language 语言
     * @param string $domain 本地化 .mo/.po 文件的文件名
     */
    public function __construct($localeRoot, $language, $domain = null)
    {
        $this->localeRoot = $localeRoot;
        $this->language = $language;

        $domain = $domain ? : $language;

        setlocale(LC_ALL, $language);
        putenv("LC_ALL={$language}");
        bindtextdomain($domain, $localeRoot);
        textdomain($domain);
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

    public function get($name, $param = [])
    {
        $string = gettext($name);
        foreach ($param as $k => $v)
            $string = str_replace("%{$k}", $v, $string);
        return $string;
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
        return $this->get($offset);
    }
}
