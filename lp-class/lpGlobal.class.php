<?php

$lpE="lpExpr";

if(!isset($lpROOT))
    $lpROOT="{$lpE(dirname(__FILE__))}/..";

require_once("{$lpROOT}/lp-config.php");

if(!lpCfgDebug)
{
    ini_set("display_errors","Off");
}

function lpExpr($value)
{
    return $value;
}

define("lpErrorMsg","服务器脚本执行错误，错误信息已记入日志，请联系管理员");

class lpGlobal
{
    public static function onError($info,$file,$line)
    {
        $out="错误:{$info} in {$file} line {$line}";

        if(lpCfgErrorLog)
            lpGlobal::writeToLog($out);

        if(lpCfgDebug)
            die($out);
        else
            die(lpErrorMsg);
    }

    public static function onWarning($info,$file,$line)
    {
        $out="警告:{$info} in {$file} line {$line}";

        if($lpConfigs["LightPHP"]["warning_log"])
        {
            $this::writeToLog($out);
        }

        if(lpCfgDebug && lpCfgShowWarning)
        {
            echo $out;
        }
    }

    public static function rxMatch($rx,$str)
    {
        if(preg_match($rx,$str,$r) && isset($r[0]))
            return $r[0];
        else
            return NULL;
    }

    public static function httpCode($code)
    {
        $codeStr = array (
          400 => "400 Bad Request",
        );
        header("HTTP/1.1 {$codeStr[$code]}");
        header("Status: {$codeStr[$code]}");
    }

    public static function gotoURL($url)
    {
        header("Location: $url");
        exit(0);
    }

    private static function writeToLog($info)
    {
        global $lpROOT;
        $file="{$lpROOT}/lp-errorlog.txt.php";

        if(file_exists($file))
            $info .= "<?php /* \n";
        $f=fopen($file, "a");
        flock($f,LOCK_EX);
        fwrite($f,"{$info}\n");
        fclose($f);
    }
}

?>
