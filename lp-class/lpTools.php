<?php

class lpTools
{
    public static function rxMatch($rx,$str)
    {
        if(preg_match($rx,$str,$r) && isset($r[0]))
            return $r[0];
        else
            return NULL;
    }
    
    public static function gravatarUrl($email)
    {
        global $lpCfgGravatarUrl;

        return $lpCfgGravatarUrl . md5(strtolower(trim($email)));
    }
    
    public static function niceTime($time)
    {
        global $lpCfgTimeToChina;

        $timeDiff=time()+$lpCfgTimeToChina-$time;
        if($timeDiff < 60)
            return $timeDiff . "秒前";
        elseif($timeDiff < 3600)
            return round($timeDiff/60) . "分前";
        elseif($timeDiff < 3600*24)
            return round($timeDiff/(3600)) . "小时前";
        elseif($timeDiff < 3600*24*7)
            return round($timeDiff/(3600*24)) . "天前";
        elseif($timeDiff > (strtotime(gmdate("Y",time()))+3600*11))
            return gmdate("m",$time) . "月" . gmdate("d",$time) . "日";
        else
            return gmdate("Y.m.d",$time);
    }

    public static function linkTo($libName,$version=NULL,$isMin=true)
    {
        global $lpUrl,$lpCfgBootstrapVer,$lpCfgJQueryVer;
        
        if($isMin)
            $isMin=".min";
        else
            $isMin="";
                    
        switch($libName)
        {
            case "bootstrap":
                if(!$version)
                    $version=$lpCfgBootstrapVer;
                    
                return "<link href='{$lpUrl}lp-style/bootstrap-{$version}/css/bootstrap{$isMin}.css' rel='stylesheet' type='text/css' />";
            case "bootstrap-responsive":
                if(!$version)
                    $version=$lpCfgBootstrapVer;
                    
                return "<link href='{$lpUrl}lp-style/bootstrap-{$version}/css/bootstrap-responsive{$isMin}.css' rel='stylesheet' type='text/css' />";
            case "bootstrap-js":
                if(!$version)
                    $version=$lpCfgBootstrapVer;
                    
                return "<script type='text/javascript' src='{$lpUrl}lp-style/bootstrap-{$version}/js/bootstrap{$isMin}.js'></script>";
            case "jquery":
                if(!$version)
                    $version=$lpCfgJQueryVer;
                    
                return "<script type='text/javascript' src='{$lpUrl}lp-style/jquery/jquery-{$version}{$isMin}.js'></script>";
            case "lp-css":
                return "<link href='{$lpUrl}lp-style/LightPHP.css' rel='stylesheet' type='text/css' />";
        }
    }
    
    public static function getIP()
    {
        if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        else
            return $_SERVER["REMOTE_ADDR"];
    }
}

?>
