<?php

require_once("lp-config.php");

function lpLoader($name)
{
    global $lpROOT;

    $path="{$lpROOT}/lp-class/{$name}.php";
    if(file_exists($path))
        require_once($path);
		
	$path="{$lpROOT}/lp-class/link/{$name}.php";
	if(file_exists($path))
        require_once($path);
}

function lpExceptionHandler($no,$str,$file,$line)
{
    throw new ErrorException($str,0,$no,$file,$line);
}

spl_autoload_register("lpLoader");
set_error_handler("lpExceptionHandler");
