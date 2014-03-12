<?php

namespace LightPHP\Locale\Adapter;

interface LocaleInterface
{
    public function init($localeRoot, $language);
    public function load($file);
    public function get($name);
}
