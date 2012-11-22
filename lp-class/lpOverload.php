<?php

class lpOverload
{
    private static $_data=array();

    public static function bind($creatFuncName,$argList,$realFunc)
    {
        $argInfo=$argList;
        array_unshift($argInfo,$realFunc);

        
        if(isset(lpOverload::$_data[$creatFuncName]))
        {
            foreach(lpOverload::$_data[$creatFuncName] as $oldV)
            {
                $notDefArgs=$oldV;
                array_shift($notDefArgs);
                $notDefArgsOld=lpOverload::removeDefaultArgs($notDefArgs);
                $notDefArgsNew=$notDefArgsOld=lpOverload::removeDefaultArgs($argList);

                if(count($notDefArgsOld)==count($notDefArgsNew))
                {
                    $thisFunc=array_shift($oldV);
                    if($notDefArgsOld===$notDefArgsNew)
                    {
                        if($thisFunc==$realFunc)
                            echo "警告：重复插入了两个相同的重载版本\n";
                        else
                            echo "错误：插入了两个具有相同函数签名的重载版本\n";
                        return false;
                    }
                }
            }
        }
        else
        {
            lpOverload::$_data[$creatFuncName]=array();
        }

        lpOverload::$_data[$creatFuncName][]=$argInfo;

        if(!function_exists($creatFuncName))
        {
            eval("
                function {$creatFuncName}()
                {
                    \$argList=func_get_args();
                    lpOverload::_functionOverload('{$creatFuncName}',\$argList);
                }
                ");
        }

        return true;
    }

    private static removeDefaultArgs($args)
    {
        $i=0;
        foreach($argse as $k => $v)
        {
            $i++;
            if(!is_int($k))
                break;
        }
        $t=array_chunk($argse,$i);
        return $t[0];
    }

    private static checkType($type,$value)
    {
        if(in_array($type,array("int","float","string","array","resource","bool")))
        {
            return call_user_func("is_{$type}",$value);
        }
        else
        {
            switch($type)
            {
                case ".intval":
                    return is_int($value) || is_float($value) || is_numeric($value);
                case ".lambda":
                    return get_class($value)=="Closure";
                case ".nullval":
                    return is_null($value);
                case ".notnull":
                    return !is_null($value);
                case ".any":
                    return true;
                default:
                    if(substr($type,strlen($type)-2,1)=="+")
                        return is_subclass_of($value,$type);
                    return get_class($value)==$type;
            }
        }
    }

    private static selectFunc($name,$argList)
    {
        if(isset(lpOverload::$_data[$name]))
        {
            $allowFunc=array();

            foreach(lpOverload::$_data[$name] as $thisV)
            {
                $args=$thisV;
                $realFunc=array_shift($args);
                $notDefArgNum=count(lpOverload::removeDefaultArgs($args));

                if(count($argList) > $notDefArgNum && count($argList) < count($args))
                {
                    $isOk=true;
                    for($i=0;$i<count($notDefArgNum);$i++)
                    {
                        if(!lpOverload::checkType($notDefArgNum[$i],$argList[$i]))
                        {
                            $isOk=false;
                            break;
                        }
                    }
                    if($isOk)
                        $allowFunc[]=$realFunc;
                }
            }

            if(count($allowFunc)==0)
            {
                echo "错误：没有匹配的函数\n";
            }
            else if(count($allowFunc)==1)
            {
                return $allowFunc[0];
            }
            else
            {
                for($i=0;$i<count($notDefArgNum);$i++)
                {
                    if(!lpOverload::checkType($notDefArgNum[$i],$argList[$i]))
                    {
                        $isOk=false;
                        break;
                    }
                }
            }
        }
        else
        {
            echo "错误：没有绑定任何函数\n";
        }
    }

    public static function debugData()
    {
        return print_r(lpOverload::$_data,true);
    }

    public static function debugFunc($name)
    {
        $out="";
        foreach(lpOverload::$_data[$name] as $v)
        {
            $realFunc=array_shift($v);
            if(!is_string($realFunc) && get_class($realFunc)=="Closure")
                $realFunc="Lambda[Closure]";

            $argStr="";
            foreach($v as $k => $value)
            {
                if(!is_int($k))
                    $value="{$k}={$value}";
                $argStr.="{$value},";
            }

            if(count($v))
                $argStr=substr($argStr,0,strlen($argStr)-1);

            $out.= "{$realFunc}({$argStr});\n";
        }
        return $out;
    }

    public static function _functionOverload($funcName,$argList)
    {
        $realFunc=lpOverload::selectFunc($funcName,$argList)

        if(is_string($realFunc))
        {
            call_user_func_array($realFunc,$argList);
        }
        elseif(get_class($realFunc)=="Closure")
        {
            foreach($argList as &$v)
            {
                $v="'{$v}'";
            }
            $strArgList=join(",",$argList);
            eval("\$realFunc({$strArgList});");
        }
        else
        {
            echo "错误：函数不可执行\n";
        }
    }
}

function test1($x)
{
    echo "test1 ".$x;
}

lpOverload::bind("test",array("string","int"),"test1");

lpOverload::bind("test",array("int","string"),function($x){
    echo "lambda ".$x;
});

test("test\n",0);

echo lpOverload::debugData();

echo lpOverload::debugFunc("test");
