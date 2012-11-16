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
        if(method_exists($action,$actName))
            $action->$actName(lpMVC::$urlArgs);
        else
            echo "不存在对应操作";
    }

    public static function exec($action,$act,$isPostOrGet=true)
    {
        $args=$isPostOrGet?$_POST:$_GET;
        if(isset($args[$act]))
        {
            $action->_lpInit();
            $action->_Init();
            lpAction::execAct($action,$args[$act]);
            $page->_Finish();
            $page->_lpFinish();
        }
        else
            echo "操作为空";
    }
}

class lpMVC
{
    public static $urlArgs;

    public static bindPage($rx,$page)
    {
        if(preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpMVC::$urlArgs))
        {
            array_shift(lpMVC::$urlArgs);
            $methodName=strtolower($_SERVER["REQUEST_METHOD"]);
            $page->_lpInit();
            $page->_Init();
            $page->$methodName(lpMVC::$urlArgs);
            $page->_Finish();
            $page->_lpFinish();
            exit(0);
        }
    }
    
    public static bindLambda($rx,$lambda)
    {
        if(preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpMVC::$urlArgs))
        {
            array_shift(lpMVC::$urlArgs);
            
            $lambda(lpMVC::$urlArgs);
            
            exit(0);
        }
    }
    
    public static bindFile($rx,$file)
    {
        if(preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpMVC::$urlArgs))
        {
            array_shift(lpMVC::$urlArgs);
            
            require($file);
            
            exit(0);
        }
    }
    
    public static bindAction($rx,$action,$act,$isPostOrGet=true)
    {
        if(preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpMVC::$urlArgs))
        {
            array_shift(lpMVC::$urlArgs);
            
            lpMVC::exec($action,$act,$isPostOrGet);
            
            exit(0);
        }
    }
}

?>
