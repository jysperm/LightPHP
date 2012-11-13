<?php

class lpOverload
{
    private static $_data=array();

    public static function bind($creatFuncName,$argList,$realFuncName)
    {
        array_unshift($argList,$realFuncName);

        if(!isset(lpOverload::$_data[$creatFuncName]))
            lpOverload::$_data[$creatFuncName]=array();
        lpOverload::$_data[$creatFuncName][]=$argList;

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
    }
    public static function debugData()
    {
        return print_r(lpOverload::$_data,true);
    }

    public static function _functionOverload($funcName,$argList)
    {
        $realFuncInfo=lpOverload::$_data[$funcName][0];
        $realFuncName=array_shift($realFuncInfo);
        call_user_func_array($realFuncName,$argList);
    }
}

function test1()
{
    echo "test1";
}

lpOverload::bind("test",array("int","string"),"test1");

test();

echo lpOverload::debugData();

?>