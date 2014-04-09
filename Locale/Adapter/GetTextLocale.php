<?php

namespace LightPHP\Locale\Adapter;

class GetTextLocale implements LocaleInterface
{
    /**
     * @param string $localeRoot
     * @param string $language
     */
    public function init($localeRoot, $language)
    {
        setlocale(LC_ALL, $language);
        putenv("LC_ALL={$language}");
        bindtextdomain($language, $localeRoot);
        textdomain($language);
    }

    /**
     * @param string $file
     */
    public function load($file)
    {

    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return gettext($name);
    }
}
