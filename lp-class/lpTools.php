<?php

class lpTools
{
    public static function onError($info,$file,$line)
    {
        if(lpCfgDebug)
        {
            $out="错误:{$info} in {$file} line {$line}";
            die($out);
        }
        else
        {
            //TODO:清空缓冲区
            die(lpErrorMsg);
        }
    }

    public static function rxMatch($rx,$str)
    {
        if(preg_match($rx,$str,$r) && isset($r[0]))
            return $r[0];
        else
            return NULL;
    }

    public static function linkTo($libName,$version=NULL,$isMin=true)
    {
    	global $lpUrl;
    	
		if($isMin)
			$isMin=".min";
		else
			$isMin="";
    				
    	switch($libName)
    	{
    		case "bootstrap":
    			if(!$version)
    				$version=lpCfgBootstrapVer;
    				
    			return "<link href='{$lpUrl}lp-style/bootstrap-{$version}/css/bootstrap{$isMin}.css' rel='stylesheet' type='text/css' />";
    		case "bootstrap-responsive":
    			if(!$version)
    				$version=lpCfgBootstrapVer;
    				
    			return "<link href='{$lpUrl}lp-style/bootstrap-{$version}/css/bootstrap-responsive{$isMin}.css' rel='stylesheet' type='text/css' />";
    		case "bootstrap-js":
    			if(!$version)
    				$version=lpCfgBootstrapVer;
    				
    			return "<script type='text/javascript' src='{$lpUrl}lp-style/bootstrap-{$version}/js/bootstrap{$isMin}.js'></script>";
    		case "jquery":
    			if(!$version)
    				$version=lpCfgJQueryVer;
    				
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
