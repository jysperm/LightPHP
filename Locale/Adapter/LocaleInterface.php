<?php

namespace LightPHP\Locale\Adapter;

interface LocaleInterface
{
    /**
     * @param string $localeRoot
     * @param string $language
     */
    public function init($localeRoot, $language);

    /**
     * @param string $file
     */
    public function load($file);

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name);
}
