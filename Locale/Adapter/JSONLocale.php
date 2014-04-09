<?php

namespace LightPHP\Locale\Adapter;

use LightPHP\Locale\Exception\LocaleNotExistException;

class JSONLocale implements LocaleInterface
{
    /** @var array */
    private $data = [];
    /** @var string */
    private $suffix;

    /**
     * @param string $suffix
     */
    public function __construct($suffix = ".json")
    {
        $this->suffix = $suffix;
    }

    /**
     * @param string $localeRoot
     * @param string $language
     */
    public function init($localeRoot, $language)
    {

    }

    /**
     * @param string $file
     * @throws LocaleNotExistException
     */
    public function load($file)
    {
        $filename = "{$file}{$this->suffix}";
        if (!file_exists($filename))
            throw new LocaleNotExistException($filename);

        $this->data = array_merge($this->data, json_decode(file_get_contents($filename), true));
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->data[$name]))
            return $this->data[$name];
        return null;
    }
}
