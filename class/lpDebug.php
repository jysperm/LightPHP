<?php

class lpDebug
{
    public static function registerErrorHandling()
    {
        error_reporting(0);

        if(lpRunLevel <= lpProduction)
        {
            $func500 = function() {
                while(ob_get_level())
                    ob_end_clean();
                die(header("HTTP/1.1 500 Internal Server Error"));
            };

            set_exception_handler($func500);
            register_shutdown_function($func500);
        }
        else
        {
            set_exception_handler(function(Exception $exception) {
                if(!headers_sent())
                    header("Content-Type: text/plant; charset=UTF-8");

                // 头部
                print sprintf(
                    "\nException `%s`: %s\n",
                    get_class($exception),
                    $exception->getMessage()
                );

                // 运行栈
                print "\n^ Call Stack:\n";
                // 从异常对象获取运行栈
                $trace = $exception->getTrace();
                // 如果是 ePHPException 则去除运行栈的第一项，即 error_handler
                if($exception instanceof lpPHPException)
                    array_shift($trace);

                // 只有在调试模式才会显示参数的值，其他模式下只显示参数类型
                if(lpRunLevel < lpDebug)
                    foreach ($trace as $key => $v)
                        $trace[$key]["args"] = array_map("gettype", $trace[$key]["args"]);

                // 用于打印参数的函数
                $printArgs = function($a) use(&$printArgs)
                {
                    $result = "";
                    foreach($a as $k => $v)
                    {
                        if(is_array($v))
                        {
                            $v = "[" . $printArgs($v) . "]";
                        }
                        else
                        {
                            if(is_string($v) && lpRunLevel >= lpDebug)
                                $v = "`{$v}`";
                            else if(is_object($v))
                                $v = get_class($v);
                            else if(!is_int($k))
                                $v = "`$k` => $v";
                        }

                        $result .= ($result ? ", {$v}" : $v);
                    }
                    return $result;
                };
                
                // 打印运行栈
                foreach ($trace as $k => $v)
                    print sprintf(
                        "#%s %s%s %s(%s)\n",
                        $k,
                        isset($v["file"]) ? $v["file"] : "",
                        isset($v["line"]) ? "({$v["line"]}):" : "",
                        $v["function"],
                        $printArgs(isset($v["args"]) ? $v["args"] : [])
                    );

                print sprintf(
                    "#  {main}\n  thrown in %s on line %s\n\n",
                    $exception->getFile(),
                    $exception->getLine()
                );

                // 如果当前是调试模式，且异常对象是我们构造的 lpPHPException 类型，打印符号表
                if(lpRunLevel >= lpDebug && $exception instanceof lpPHPException)
                {
                    // 用于打印符号表的函数
                    $printVarList = function($a, $tab=0) use(&$printVarList)
                    {
                        $out = "";
                        $tabs = str_repeat("   ", $tab);
                        foreach($a as $k => $v)
                            if(is_array($v))
                                if(!$v)
                                    $out.= "{$tabs}`{$k}` => []\n";
                                else
                                    $out.= "{$tabs}`{$k}` => [\n" . $printVarList($v, $tab+1) . "{$tabs}]\n";
                            else if(is_object($v))
                                $out.= "{$tabs}`{$k}` => " . get_class($v) ."\n";
                            else if(!is_int($k))
                                $out.= "{$tabs}`{$k}` => {$v}\n";
                            else
                                $out.= "{$tabs}`{$k}` => `{$v}`\n";
                        return $out;
                    };

                    print "^ Symbol Table:\n";
                    print $printVarList($exception->getVarList());
                }

                if(lpRunLevel >= lpDebug)
                {
                    print "\n^ Code:\n";

                    // 显示出错附近行的代码
                    $code = file($exception->getFile());
                    $s = max($exception->getLine()-6, 0);
                    $e = min($exception->getLine()+5, count($code));
                    $code = array_slice($code, $s, $e - $s);

                    // 为代码添加行号
                    $line = $s + 1;
                    foreach($code as &$v)
                    {
                        $l = $line++;
                        if(strlen($l) < 4)
                            $l = str_repeat(" ", 4-strlen($l)) . $l;
                        if($exception->getLine() == $l)
                            $v = "{$l}->{$v}";
                        else
                            $v = "{$l}  {$v}";
                    }

                    print implode("", $code);
                }
            });

            register_shutdown_function(function() {
                $error = error_get_last();

                if($error && in_array($error["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR]))
                {
                    $exceptionHandler = set_exception_handler(function() {});
                    restore_exception_handler();
                    if($exceptionHandler !== null)
                        call_user_func($exceptionHandler, new lpPHPFatalException($error['message'], $error['type'], 0, $error['file'], $error['line']));
                    exit;
                }
            });
        }
    }
}