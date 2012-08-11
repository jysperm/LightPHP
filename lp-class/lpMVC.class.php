<?php

require_once("lpGlobal.class.php");

class lpMVC
{
    public static function bind($rx,$handler)
    {
        if(preg_match($rx,rawurldecode($_SERVER["REQUEST_URI"]),$args))
        {
            lpMVC::procHandler($handler,$args);
        }
    }

    public static function onDefault($handler)
    {
        lpMVC::procHandler($handler);
    }

    private static function procHandler($handler,$args=array())
    {
        global $lpE;
        
        array_shift($args);
        if(strtolower(get_class($handler))==strtolower("Closure"))
        {
            $out=$handler();
            if(is_string($out))
                echo $out;
            else
            {
                $funcName=strtolower($_SERVER["REQUEST_METHOD"]);
                if(!method_exists($out,$funcName))
                    lpGlobal::onError("lpMVC::procHandler():{$lpE(get_class($out))}没有{$funcName}方法",__FILE__,__LINE__);
                $out->$funcName($args);
            }
        }
        else
        {
            if(is_string($args))
                echo $args;
            $funcName=strtolower($_SERVER["REQUEST_METHOD"]);
            if(!method_exists($handler,$funcName))
                lpGlobal::onError("lpMVC::procHandler():{$lpE(get_class($handler))}没有{$funcName}方法",__FILE__,__LINE__);
            $handler->$funcName($args);
        }
        exit(0);
    }
}

?>
