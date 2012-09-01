<?php

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
    	global $lpCfgCallback,$lpCfgUNAME,$lpCfgPASSWD,$lpCfgTimeLimit;
    	
        if(!$user || !$passwd)
        {
            $isRaw=false;
            $isCookie=true;
        }

        if(!$user && isset($_COOKIE[$lpCfgUNAME]))
            $user=$_COOKIE[$lpCfgUNAME];

        if(!$passwd && isset($_COOKIE[$lpCfgPASSWD]))
            $passwd=$_COOKIE[$lpCfgPASSWD];

        if(!$user || !$passwd)
            return false;

        if(lpAuth::auth($user,$passwd,$isRaw,$isDB,$isCookie))
        {
            if($isRaw)
                $passwd=lpAuth::DBHash($user, $passwd);

            if($isRaw || $isDB)
                $passwd=lpCookieHash($passwd);

            if($lpCfgCallback)
                call_user_func($lpCfgCallback,$user);

            $expire=time()+$lpCfgTimeLimit * 24 * 3600;

            setcookie($lpCfgUNAME,$user,$expire,"/");
            setcookie($lpCfgPASSWD,$passwd,$expire,"/");

            return true;
        }
        else
        {
            setcookie($lpCfgPASSWD,NULL,time()-1,"/");
            return false;
        }
    }

    public static function getUName()
    {
        global $lpCfgUNAME;

        if(isset($_COOKIE[$lpCfgUNAME]))
            return $_COOKIE[$lpCfgUNAME];
        else
            return NULL;
    }

    public static function logout()
    {
        global $lpCfgUNAME,$lpCfgPASSWD;

        setcookie($lpCfgUNAME,NULL,time()-1,"/");
        setcookie($lpCfgPASSWD,NULL,time()-1,"/");
    }

    public static function DBHash($user,$passwd)
    {
        global $lpCfgDBHash;

        return call_user_func($lpCfgDBHash,$user,$passwd);
    }

    public static function cookieHash($DBPasswd)
    {
        global $lpCfgCookieHash;

        return call_user_func($lpCfgCookieHash,$DBPasswd);
    }

    public static function getPasswd($uname)
    {
        global $lpCfgGetPasswd;

        return call_user_func($lpCfgGetPasswd,$uname);
    }
}

function lpHash256($data)
{
    return hash("sha256",$data);
}

function lpDBHash($user,$passwd)
{
    return lpHash256(lpHash256($user) . lpHash256($passwd));
}

function lpCookieHash($DBPasswd)
{
    global $lpCfgSecurityCode;

    return lpHash256(lpHash256($lpCfgSecurityCode) . $DBPasswd);
}

function lpGetPasswd($uname)
{
    global $lpCfgUNameField,$lpCfgPasswdField;

    $conn=new lpMySQL;
    $rs=$conn->select($lpCfgTable,array($lpCfgUNameField => $uname));
    if($rs->read())
        return $rs->value($lpCfgPasswdField);
    return NULL;
}

?>
