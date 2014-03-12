<?php

namespace LightPHP\Locale\Wrapper;

use DirectoryIterator;
use LightPHP\Locale\Adapter\LocaleInterface;
use LightPHP\Locale\Exception\LocaleNotExistException;
use LightPHP\Locale\LocalAgent;

class AutoLocalAgent extends LocalAgent
{
    public function __construct(LocaleInterface $adapter, $localeRoot, array $availableLanguage = [], $acceptLanguage = "", $spliter = ".")
    {
        $language = null;

        if (!$availableLanguage) {
            $availableLanguage = [];

            foreach (new DirectoryIterator($localeRoot) as $file)
                if (!$file->isDot() && $file->isDir())
                    $availableLanguage [] = str_replace(strtolower($file->getFilename()), "-", "_");
        }

        if (!$acceptLanguage)
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguage, $parsedLanguages);

        if (count($parsedLanguages[1])) {
            $languages = array_combine($parsedLanguages[1], $parsedLanguages[4]);

            foreach ($languages as $lang => $val)
                if ($val === '')
                    $languages[$lang] = 1;

            arsort($languages, SORT_NUMERIC);

            foreach ($languages as $lang => $val) {
                $lang = str_replace(strtolower($lang), "-", "_");

                if (in_array($lang, $availableLanguage)) {
                    $language = $lang;
                    break;
                }

                if (preg_match('/^\w+/', $lang, $shortLang)) {
                    $shortLang = $shortLang[0];

                    if (in_array($lang, $availableLanguage)) {
                        $language = $lang;
                        break;
                    }
                }
            }

            if ($availableLanguage)
                $language = $availableLanguage[0];
            else
                throw new LocaleNotExistException("not available language");
        }

        parent::__construct($adapter, $localeRoot, $language, $spliter);
    }
}
