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
            $action->$actName(lpRoute::$urlArgs);
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
            $action->_Finish();
            $action->_lpFinish();
        }
        else
            echo "操作为空";
    }
}

class lpRoute
{
    public static $urlArgs;
    
    public static function quit($str)
    {
        echo $str;
        exit();
    }

    public static function bindPage($rx,$page)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            $methodName=strtolower($_SERVER["REQUEST_METHOD"]);
            $page->_lpInit();
            $page->_Init();
            $page->$methodName(lpRoute::$urlArgs);
            $page->_Finish();
            $page->_lpFinish();
            exit(0);
        }
    }
    
    public static function bindLambda($rx,$lambda)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            $lambda(lpRoute::$urlArgs);
            
            exit(0);
        }
    }
    
    public static function bindFile($rx,$file)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            require($file);
            
            exit(0);
        }
    }
    
    public static function bindAction($rx,$action,$act,$isPostOrGet=true)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            lpAction::exec($action,$act,$isPostOrGet);
            
            exit(0);
        }
    }

    public static function bindAction($rx,$template)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            $template->output();
            
            exit(0);
        }
    }

    public static function bindTemplateFromFile($rx,$file)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);


            lpTemplate::outputFile($file)

            exit(0);
        }
    }

    public static function bindPageFromFile($rx,$file,$pagename)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);

            require($file);

            eval("\$page = new {$pagename};");
            lpRoute::bindPage(NULL,$page);
        }
    }

    public static function bindActionFromFile($rx,$file,$actname,$act,$isPostOrGet=true)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);

            require($file);

            eval("\$action = new {$actname};");
            lpRoute::bindAction(NULL,$action,$act,$isPostOrGet);
        }
    }

    public static function bindText($rx,$text)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);

            header("Content-Type: text/plant; charset=UTF-8")
            echo $text;

            exit(0);
        }
    }

    public static function bindHTML($rx,$html)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);

            header("Content-Type: text/html; charset=UTF-8")
            echo $html;

            exit(0);
        }
    }
}
