<?php

class lpPage
{
    protected $httpCode=200;

    public function _lpInit()
    {
        ob_start();
    }

    public function _Init()
    {
        
    }

    public function get($args)
    {
        echo "没有实现GET方法";
    }
    
    public function post($args)
    {
        echo "没有实现POST方法";
    }
    
    public function _lpFinish()
    {
        global $lpCfgDebug;

        if($this->httpCode!=200)
        {
            $codeStr = array (
                302 => "302 Found",
                400 => "400 Bad Request",
                403 => "403 Forbidden",
                404 => "404 Not Found",
                500 => "500 Internal Server Error"
            );

            header("HTTP/1.1 {$codeStr[$this->httpCode]}");
            header("Status: {$codeStr[$this->httpCode]}");
        }

        if($this->httpCode!=200 || $lpCfgDebug)
            ob_end_flush();
        else
            ob_end_clean();
    }

    public function _Finish()
    {

    }
}

class lpAction extends lpPage
{
    public static function execAct($action,$actName)
    {
        if(method_exists($action,$act))
            $action->$act(lpMVC::$urlArgs);
        else
            echo "不存在对应操作";
    }

    public static function exec($action,$act,$isPostOrGet=true)
    {
        $args=$isPostOrGet?$_POST:$_GET;
        if(isset($args[$act]))
            lpAction::execAct($action,$args[$act]);
        else
            echo "操作为空";
    }
}

class lpMVC
{
    public static $urlArgs;

    public static function bind($rx,$handler)
    {
        if(preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpMVC::$urlArgs))
            lpMVC::procHandler($handler);
    }

    public static function onDefault($handler)
    {
        lpMVC::procHandler($handler);
    }

    private static function procHandler($handler)
    {
        if(strtolower(get_class($handler))==strtolower("Closure"))
            $handler=$handler();
        
        if(is_string($handler))
        {
            echo $handler;
        }
        else
        {
            array_shift(lpMVC::$urlArgs);
            $methodName=strtolower($_SERVER["REQUEST_METHOD"]);
            
            $handler->_lpInit();
            $handler->_Init();
            if(!$handler->$methodName(lpMVC::$urlArgs))
                $handler->procError();
            $handler->_Finish();
            $handler->_lpFinish();
        }
        
        exit(0);
    }
}

?>
