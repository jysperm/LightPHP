<?php

abstract class lpPage
{
    protected $httpCode=200;

    public function _lpInit()
    {
        ob_start();
    }

    public function _Init()
    {
        
    }

    public function get()
    {
        
    }
    
    public function post()
    {
        
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

abstract class lpAction extends lpPage
{
    public static function execAct($action,$actName)
    {
        if(method_exists($action,$actName))
            $action->$actName();
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
    
    public static function gotoUrl($url)
    {
        header("Location: {$url}");
    }

    public static function bindPage($rx,$page)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            $methodName=strtolower($_SERVER["REQUEST_METHOD"]);
            $page->_lpInit();
            $page->_Init();
            $page->$methodName();
            $page->_Finish();
            $page->_lpFinish();
            exit();
        }
    }
    
    public static function bindLambda($rx,$lambda)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            $lambda();
            
            exit(0);
        }
    }
    
    public static function bindPHPFile($rx,$file)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            require($file);
            
            exit();
        }
    }
    
    public static function bindHTMLFile($rx,$file)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            echo file_get_contents($file);
            
            exit();
        }
    }
    
    public static function bindAction($rx,$action,$act,$isPostOrGet=true)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            lpAction::exec($action,$act,$isPostOrGet);
            
            exit();
        }
    }

    public static function bindTemplate($rx,$template)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);
            
            $template->output();
            
            exit();
        }
    }

    public static function bindTemplateFile($rx,$file)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            array_shift(lpRoute::$urlArgs);


            lpTemplate::outputFile($file);

            exit();
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
            header("Content-Type: text/plant; charset=UTF-8");
            echo $text;

            exit();
        }
    }

    public static function bindHTML($rx,$html)
    {
        if(!$rx | preg_match("%{$rx}%u",rawurldecode($_SERVER["REQUEST_URI"]),lpRoute::$urlArgs))
        {
            header("Content-Type: text/html; charset=UTF-8");
            echo $html;

            exit();
        }
    }
    
    public static function bindDir($alias,$dir)
    {
        if(substr($alias,strlen($alias)-2,1)!="/")
            $alias .= "/";
              
        if(substr($_SERVER["REQUEST_URI"],0,strlen($alias))==$alias)
        {
            if(substr($dir,strlen($dir)-2,1)=="/")
              $dir=substr($dir,0,strlen($dir)-1);
            
            $path=substr($_SERVER["REQUEST_URI"],strlen($alias)-1);
            $path=lpTools::rxMatch('/^[^\?]\?/');
            
            if(file_exists("{$dir}/{$path}"))
            {
                echo file_get_contents("{$dir}/{$path}");
            }
            else
            {
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
            }
            
            exit();
        }
    }
}
