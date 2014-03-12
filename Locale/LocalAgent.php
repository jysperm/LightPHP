<?php

namespace LightPHP\Locale;

use ArrayAccess;
use LightPHP\Locale\Adapter\LocaleInterface;
use LightPHP\Locale\Exception\LocaleNotExistException;

class LocalAgent implements ArrayAccess
{
    /** @var LocaleInterface */
    protected $adapter;
    /** @var string */
    protected $localeRoot;
    /** @var string */
    protected $language;

    /** @var string */
    protected $spliter = ".";

    public function __construct(LocaleInterface $adapter, $localeRoot, $language, $spliter = ".")
    {
        $languageDir = "{$localeRoot}/{$language}";
        if (!file_exists($languageDir))
            throw new LocaleNotExistException($languageDir);

        $adapter->init($localeRoot, $language);

        $this->adapter = $adapter;
        $this->localeRoot = $localeRoot;
        $this->language = $language;
        $this->spliter = $spliter;
    }

    public function load($name)
    {
        $filename = "{$this->localeRoot}/{$this->language}/{$name}";
        $this->adapter->load($filename);
    }

    public function translate($name, array $param = [])
    {
        $names = explode($this->spliter, $name);

        $key = array_shift($names);
        $result = $this->adapter->get($key);

        while (count($names)) {
            $key = array_shift($names);
            if (isset($result[$key]))
                $result = $result[$key];
            else
                return $name;
        }

        foreach ($param as $k => $v)
            $result = str_replace("%{{$k}}", $v, $result);

        return $result;
    }

    public function path($filename)
    {
        return "{$this->localeRoot}/{$this->language}/{$filename}";
    }

    public function language()
    {
        return $this->language;
    }

    public function adapter()
    {
        return $this->adapter();
    }

    // --- implements ArrayAccess

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {

    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->adapter->get($offset) !== null;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {

    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->translate($offset);
    }
}
