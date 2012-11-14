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
            foreach(lpOverload::$_data[$creatFuncName] as $v)
            {
                if(count($v)==count($argInfo))
                {
                    $thisFunc=array_shift($v);
                    if($v===$argList)
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
        $realFuncInfo=lpOverload::$_data[$funcName][0];
        $realFunc=array_shift($realFuncInfo);

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
/*
test1 test
*/

echo lpOverload::debugData();
/*
Array
(
    [test] => Array
    (
        [0] => Array
        (
            [0] => test1
            [1] => string
            [2] => int
        )
        [1] => Array
        (
            [0] => Closure Object
            (
                [parameter] => Array
                (
                    [$x] => <required>
                )
            )
            [1] => int
            [2] => string
        )

    )
)
*/

echo lpOverload::debugFunc("test");
/*
test1(string,int);
Lambda[Closure](int,string);
*/

?>
