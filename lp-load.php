<?php

require_once("lp-config.php");

function lpLoader($name)
{
    global $lpROOT;

    $path="{$lpROOT}/lp-class/{$name}.php";
    if(file_exists($path))
        require_once($path);
}

spl_autoload_register("lpLoader");

?>
