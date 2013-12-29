<?php

namespace LightPHP\Locale;

class Locale
{
    /**
     * 判断客户端语言
     *
     * * 首先根据 Cookie
     * * 然后根据 HTTP Accept-Language
     * * 最后私用默认语言
     *
     * @param $localeRoot 本地化文件根目录
     * @param $defaultLanguage 默认语言
     * @param string $cookieName 储存语言的Cookie
     * @return string
     */
    public static function checkLanguage($localeRoot, $defaultLanguage, $cookieName = "language")
    {
        $lang = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : "";
        if ($lang && preg_match("/^[_A-Za-z]+$/", $lang) && is_dir("{$localeRoot}/{$lang}"))
            return $_COOKIE[$cookieName];

        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && $_SERVER["HTTP_ACCEPT_LANGUAGE"]) {
            $languages = explode(",", str_replace("-", "_", $_SERVER["HTTP_ACCEPT_LANGUAGE"]));

            foreach ($languages as $i)
                if (preg_match("/^[_A-Za-z]+$/", $i) && is_dir("{$localeRoot}/{$lang}"))
                    return $i;
        }

        return $defaultLanguage;
    }
} 