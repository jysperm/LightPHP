<?php

require_once("lp-config.php");

function lpLoader($name)
{
    global $lpROOT;
    
    if(class_exists($name,false))
        return;

    $path="{$lpROOT}/lp-class/{$name}.php";
    if(file_exists($path))
        require_once($path);
    
    $path="{$lpROOT}/lp-class/links/{$name}.php";
    if(file_exists($path))
          require_once($path);
}

function lpExceptionHandler($no,$str,$file,$line)
{
    global $lpCfgDebug;
    
    if($lpCfgDebug)
    {
        throw new ErrorException($str,0,$no,$file,$line);
    }
    else
    {
        header("HTTP/1.1 500 Internal Server Error");
        header("Status: 500 Internal Server Error");
        exit();
    }
}

if(!$lpCfgDebug)
    ini_set("display_errors","Off");

spl_autoload_register("lpLoader");
if(defined("lpOFF_Exception") && lpOFF_Exception)
    set_error_handler("lpExceptionHandler");
