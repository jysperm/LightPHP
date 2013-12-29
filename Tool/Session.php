<?php

namespace LightPHP\Tool;

class Session
{
    /**
     *  初始化Session
     */
    public static function initSession()
    {
        if (!isset($_SESSION))
            session_start();
    }

    /**
     *  重置Session
     */
    public static function resetSession()
    {
        session_regenerate_id();
    }

    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function get($name, $remove = false)
    {
        $result = null;
        if (isset($_SESSION[$name])) {
            $result = $_SESSION[$name];
            if ($remove)
                unset($_SESSION[$name]);
        }
        return $result;
    }
} 