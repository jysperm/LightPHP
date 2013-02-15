<?php

/**
*   该文件包含 lpTrachAuth 的类定义.
*
*   @package LightPHP
*/

/*
*   跟踪验证.
*
*   该类提供更为高级的验证功能, 该类将会跟踪记录每一次会话, 你可以单独控制这些会话.
*/

class lpTrackAuth extends lpAuthDrive
{
    public $cbHash;
    public $cbDBHash;
    public $cbGetPasswd;

    public function __call($name, $args)
    {
        $vars = get_object_vars($this);
        if(isset($vars[$name]))
        {
            $func = $this->$name;
            return call_user_func_array($func, $args);
        }
    }

    public function __construct()
    {
        $this->cbHash = function($data)
        {
            return hash("sha256", $data);
        };

        $this->cbDBHash = function($user, $passwd)
        {
            return $this->cbHash($this->cbHash($user) . $this->cbHash($passwd));
        };

        $this->cbGetPasswd = function($uname, $conn=null)
        {
            global $lpCfg, $lpApp;
            $cfg = $lpCfg["lpTrackAuth"]["GetPasswd"]["Default"];

            if(!$conn)
                $conn = $lpApp::getDB();
            $q = new lpDBQuery($conn);

            return $q($cfg["table"])->where([$cfg["user"] => $uname])->top()[$cfg["passwd"]];
        };
    }

    public function auth($user, $passwd)
    {
        global $lpCfg, $lpApp;

        if(array_key_exists("raw", $passwd))
            $passwd = ["db" => $this->cbDBHash($user, $passwd["raw"])];

        if(array_key_exists("db", $passwd))
        {
            if($this->cbGetPasswd($user) == $passwd["db"])
                return true;
            else
                return false;
        }

        if(array_key_exists("token", $passwd))
        {
            $cfg = $lpCfg["lpTrackAuth"]["Default"];

            $q = new lpDBQuery($lpApp::getDB());

            $r = $q($cfg["table"])->where([$cfg["user"] => $user, $cfg["token"] => $passwd["token"]])->top();

            if($r)
                return true;
        }

        return false;
    }

    public function creatToken($user)
    {
        global $lpCfg, $lpApp;
        $cfg = $lpCfg["lpTrackAuth"]["Default"];

        $q = new lpDBQuery($lpApp::getDB());

        $token = $this->cbHash($user . mt_rand());

        $q($cfg["table"])->insert([$cfg["user"] => $user, $cfg["token"] => $token, $cfg["lastactivitytime"] => time()]);

        return $token;
    }

    public function login($user=null, $passwd=null)
    {
        global $lpCfg;
        $cookieName = $lpCfg["lpTrackAuth"]["CookieName"];

        if(!$user || !$passwd)
        {
            if(!$user && isset($_COOKIE[$cookieName["user"]]))
                $user = $_COOKIE[$cookieName["user"]];

            if(!$passwd && isset($_COOKIE[$cookieName["passwd"]]))
                $passwd = $_COOKIE[$cookieName["passwd"]];

            if(!$user || !$passwd)
                return false;

            $passwd = ["token" => $passwd];
        }

        if($this->auth($user, $passwd))
        {
            if(isset($passwd["raw"]))
                $passwd = ["db" => $this->cbHash($user, $passwd["raw"])];

            if(isset($passwd["db"]))
                $passwd = ["token" => self::creatToken($user)];

            $this->cbSucceed();

            $expire = time() + $lpCfg["lpTrackAuth"]["Limit"];

            setcookie($cookieName["user"], $user, $expire, "/");
            setcookie($cookieName["passwd"], $passwd["token"], $expire, "/");

            return true;
        }
        else
        {
            setcookie($cookieName["passwd"], null, time()-1, "/");
            return false;
        }
    }

    static public function getUName()
    {
        global $lpCfg;
        $userName = $lpCfg["lpTrackAuth"]["CookieName"]["user"];

        if(isset($_COOKIE[$userName]))
            return $_COOKIE[$userName];
        else
            return null;
    }

    static public function logout()
    {
        global $lpCfg;
        $cookieName = $lpCfg["lpTrackAuth"]["CookieName"];

        setcookie($cookieName["user"], null, time()-1, "/");
        setcookie($cookieName["passwd"], null, time()-1, "/");
    }
}