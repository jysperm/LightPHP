<?php

class lpJSONLocale implements ArrayAccess
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
     * @param string $domain 本地化 JSON 文件的文件名
     */
    public function __construct($localeRoot, $language, $domain = null)
    {
        $this->localeRoot = $localeRoot;
        $this->language = $language;

        $domain = $domain ?: $language;

        $this->data = json_decode(file_get_contents("{$localeRoot}/{$language}/{$domain}.json"), true);
    }

    public function file($file, $locale = null)
    {
        if(!$locale)
            $locale = $this->language;

        return "{$this->localeRoot}/{$locale}/{$file}";
    }

    public function language()
    {
        return $this->language;
    }

    public function get($name, $param = [])
    {
        $keys = explode(".", $name);
        try {
            $key = $keys[0];
            $string = $this->data[$key];

            if(count($keys) > 1)
                foreach($keys as $index => $key)
                    if($index > 0)
                        $string = $string[$key];

            foreach($param as $k => $v)
                $string = str_replace("%{$k}", $v, $string);

            return $string;
        }
        catch (Exception $e) {
            return $name;
        }
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