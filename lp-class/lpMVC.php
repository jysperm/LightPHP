<?php

class lpPage
{
    public $httpCode=200;

    public function gotoUrl($url)
    {
        header("Location: {$url}");

        exit(0);
    }
    
    public function _lpInit()
    {
        ob_start();
    }
    
    public function _lpFinish()
    {
        if($this->httpCode!=200)
        {
            $codeStr = array (
                400 => "400 Bad Request",
                403 => "403 Forbidden",
                404 => "404 Not Found",
                500 => "Internal Server Error"
            );

            header("HTTP/1.1 {$codeStr[$code]}");
            header("Status: {$codeStr[$code]}");
        }

        ob_end_flush();
    }
    
    public function get($args)
    {
        echo "没有实现GET方法";
    }
    
    public function post($args)
    {
        echo "没有实现POST方法";
    }
}

class lpMVC
{
    public static function bind($rx,$handler)
    {
        if(preg_match($rx,rawurldecode($_SERVER["REQUEST_URI"]),$args))
            lpMVC::procHandler($handler,$args);
    }

    public static function onDefault($handler)
    {
        lpMVC::procHandler($handler);
    }

    private static function procHandler($handler,$args=array())
    {
        if(strtolower(get_class($handler))==strtolower("Closure"))
            $handler=$handler();
        
        if(is_string($handler))
        {
            echo $handler;
        }
        else
        {
            array_shift($args);
            $methodName=strtolower($_SERVER["REQUEST_METHOD"]);
            
            $handler->_lpInit();
            $handler->$methodName($args);
            $handler->_lpFinish();
        }
        
        exit(0);
    }
}

?>
