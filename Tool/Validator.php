<?php

namespace LightPHP\Tool;

class Validator
{
    const Email = "email";

    public static function rx($type)
    {
        switch ($type) {
            case self::Email:
                return '/^[A-Za-z0-9_\-\.\+]+@[A-Za-z0-9_\-\.]+$/';
        }
    }

    public static function test($type, $data)
    {
        return preg_match(self::rx($type), $data);
    }
}