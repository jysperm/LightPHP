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

class lpTrachAuth extends lpAuthDrive
{
    static public $cbHash = function($data)
    {
        return hash("sha256", $data);
    }

    static public $cbDBHash = function($user, $passwd)
    {
        return self::$cbHash(self::$cbHash($user) . self::$cbHash($passwd));
    }

    static public $cbGetPasswd = function($uname, $conn=null)
    {
        global $lpCfg;
        $cfg = $lpCfg["lpTrackAuth"]["GetPasswd"]["Default"];

        if(!$conn)
            $conn = $lpApp::getDB();
        $q = new lpDBQuery($conn);

        return $q($cfg["table"])->where([$cfg["user"] => $uname])->top()[$cfg["passwd"]];
    }

    public static function auth($user, $passwd)
    {
        global $lpCfg;

        if(isset($passwd["raw"]))
            $passwd = ["db" => slef::$cbDBHash($user, $passwd["raw"])];

        if(isset($passwd["db"]))
        {
            if(slef::$cbGetPasswd($user) == $passwd["db"])
                return true;
            else
                return false;
        }

        if(isset($paaswd["token"]))
        {
            $cfg = $lpCfg["lpTrackAuth"]["Default"];

            $q = new lpDBQuery($lpApp::getDB());

            $r = $q($cfg["table"])->where([$cfg["user"] => $user, $cfg["token"] => $passwd["token"]])->top();

            if($r)
                return true;
            else
                return false;
        }

        return false;
    }

    static private function creatToken($user)
    {
        global $lpCfg;
        $cfg = $lpCfg["lpTrackAuth"]["Default"];

        $q = new lpDBQuery($lpApp::getDB());

        $token = self::$cbHash($user . mt_rand());

        $q($cfg["table"])->insert([$cfg["user"] => $user, $cfg["token"] => $token, $cfg["lastactivitytime"] => time()]);

        return $token;
    }

    static public function login($user=null, $passwd=null)
    {
        global $lpCfg;
        $cookieName = $lpCfg["lpTrackAuth"]["CookieName"];

        if(!$user || !$passwd)
        {
            if(!$user && isset($_COOKIE[$cookieName["user"]]))
                $user = $_COOKIE[$cookieName["user"]];

            if(!$passwd && isset($_COOKIE[$cookieName["passwd"]))
                $passwd = $_COOKIE[$cookieName["passwd"]];

            if(!$user || !$passwd)
                return false;

            $passwd = ["token" => $passwd];
        }

        if(lpAuth::auth($user, $passwd))
        {
            if(isset($passwd["raw"]))
                $passwd = ["db" => self::$cbHash($user, $passwd["raw"])];

            if(isset($passwd["db"]))
                $passwd = ["token" => self::creatToken($user)];

            self::$cbSucceed();

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

    public static function getUName()
    {
        global $lpCfg;
        $userName = $lpCfg["lpTrackAuth"]["CookieName"]["user"];

        if(isset($_COOKIE[$userName]))
            return $_COOKIE[$userName];
        else
            return null;
    }

    public static function logout()
    {
        global $lpCfg;
        $cookieName = $lpCfg["lpTrackAuth"]["CookieName"];

        setcookie($cookieName["user"], null, time()-1, "/");
        setcookie($cookieName["passwd"], null, time()-1, "/");
    }
}

