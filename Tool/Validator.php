<?php

namespace LightPHP\Tool;

const Email = "email";

class Validator
{
    public static function rx($type)
    {
        switch ($type) {
            case Email:
                return '/^[A-Za-z0-9_\-\.\+]+@[A-Za-z0-9_\-\.]+$/';
        }
    }

    public static function test($type, $data)
    {
        return preg_match(self::rx($type), $data);
    }
}