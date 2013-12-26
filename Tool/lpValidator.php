<?php

const lpEmail = "lpEmail";

class lpValidator
{
    public static function rx($type)
    {
        switch ($type) {
            case lpEmail:
                return '/^[A-Za-z0-9_\-\.\+]+@[A-Za-z0-9_\-\.]+$/';
        }
    }

    public static function test($type, $data)
    {
        return preg_match(self::rx($type), $data);
    }
}