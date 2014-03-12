<?php

namespace LightPHP\Locale\Adapter;

class GetTextLocale implements LocaleInterface
{
    public function init($localeRoot, $language)
    {
        setlocale(LC_ALL, $language);
        putenv("LC_ALL={$language}");
        bindtextdomain($language, $localeRoot);
        textdomain($language);
    }

    public function load($file)
    {

    }

    public function get($name)
    {
        return gettext($name);
    }
}
