<?php

namespace LightPHP\Locale\Adapter;

interface LocaleInterface
{
    public function load($file);
    public function get($name);
} 