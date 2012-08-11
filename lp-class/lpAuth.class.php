<?php

require_once("lpGlobal.class.php");
require_once("lpMySQL.class.php");
require_once("lpSQLRs.class.php");

define("lpUNAME", "{$lpConfigs['lpAuth']['cookie_prefix']}uname");
define("lpPASSWD", "{$lpConfigs['lpAuth']['cookie_prefix']}passwd");

class lpAuth
{
    public static function auth($user,$passwd,$isRaw=true,$isDB=false,$isCookie=false)
    {
        if($isRaw)
            $passwd=lpAuth::DBHash($user, $passwd);

        if($isRaw || $isDB)
        {
            if(lpAuth::getPasswd($user)==$passwd)
                return true;
            else
                return false;
        }
        else
        {
            if(lpAuth::cookieHash(lpAuth::getPasswd($user))==$passwd)
                return true;
            else
                return false;
        }
    }

    public static function login($user=NULL,$passwd=NULL,$isRaw=true,$isDB=false,$isCookie=false)
    {
        if(!$user || !$passwd)
        {
            $isRaw=false;
            $isCookie=true;
        }

        if(!$user && isset($_COOKIE[lpUNAME]))
            $user=$_COOKIE[lpUNAME];

        if(!$passwd && isset($_COOKIE[lpPASSWD]))
            $passwd=$_COOKIE[lpPASSWD];

        if(!$user || !$passwd)
            return false;

        if(lpAuth::auth($user,$passwd,$isRaw,$isDB,$isCookie))
        {
            if($isRaw)
                $passwd=lpAuth::DBHash($user, $passwd);

            if($isRaw || $isDB)
                $passwd=lpCookieHash($passwd);

            if(lpCfgCallback)
                call_user_func(lpCfgCallback,$user);

            $expire=time()+lpCfgTimeLimit*24*3600;

            setcookie(lpUNAME,$user,$expire,"/");
            setcookie(lpPASSWD,$passwd,$expire,"/");

            return true;
        }
        else
        {
            setcookie(lpPASSWD,NULL,time()-1,"/");
            return false;
        }
    }

    public static function getUName()
    {
        if(isset($_COOKIE[lpUNAME]))
            return $_COOKIE[lpUNAME];
        else
            return NULL;
    }

    public static function logout()
    {
        setcookie(lpUNAME,NULL,time()-1,"/");
        setcookie(lpPASSWD,NULL,time()-1,"/");
    }

    public static function DBHash($user,$passwd)
    {
        return lpCfgDBHash($user,$passwd);
    }

    public static function cookieHash($DBPasswd)
    {
        return lpCfgCookieHash($DBPasswd);
    }

    public static function getPasswd($uname)
    {
        return lpCfgGetPasswd($uname);
    }
}

function lpHash256($data)
{
    return hash("sha256", $data);
}

function lpDBHash($user,$passwd)
{
    return lpHash256(lpHash256($user) . lpHash256($passwd));
}

function lpCookieHash($DBPasswd)
{
    return lpHash256(lpHash256(lpCfgSecurityCode) . $DBPasswd);
}

function lpGetPasswd($uname)
{
    $conn=new lpMySQL();
    $rs=$conn->select(lpCfgTable,array(lpCfgUNameField => $uname));
    if($rs->read())
        return $rs->value(lpCfgPasswdField);
    return NULL;
}

?>
