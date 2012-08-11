##lpOptions
该类为整个LightPHP提供了一个基础的运行环境.

###依赖
无

##全局空间
####`$lpE="lpExpr"`
储存着`lpExpr()`函数的名字，用于在字符串中嵌入函数调用等复杂表达式.

####`string $lpROOT`
LightPHP的路径.

####`array $lpConfigs`
储存有 lp-config.ini.php 中所有信息的数组.

####`mixed function lpExpr(mixed $value)`
原样返回参数，与`$lpE`配合使用

####`define(lpErrorMsg,"服务器脚本执行错误，错误信息已记入日志，请联系管理员")`
关闭调试时的出错提示文字.

##`class lpGlobal`
####`public static function onError(string $info,mixed $file,mixed $line)`
`string $info` : 错误信息.  
`mixed $file` : 出错文件的路径，一般以`__FILE__`填充.  
`mixed $line` : 出错的行号，一般以`__LINE__`填充.

根据 lp-config.ini.php 中 [LightPHP] 段的 `error_log` 的设置，就将错误信息写入到位于LightPHP根目录的 lp-errorlog.txt.php 中.  
再根据 `debug` 的设置，决定是否直接输出错误信息.

####`public static function onWarning(string $info,mixed $file,mixed $line)`
同`onError()`,警告会显示提示而不是中断脚本执行.

只有在 lp-config.ini.php 中 [LightPHP] 段的 `debug` 和 `show_warning` 同时开启时才会显示警告在.  
只有`warning_log`开启才会将警告记入日志.

####`public static string function rxMatch(string $rx,string $str)`
用正则表达式`$rx`在`$str`中匹配，返回匹配的结果，如果无法完成匹配，返回NULL.

####`public static function httpCode(int $code)`
修改当前的HTTP状态码

####`public static function gotoURL(string $url)`
跳转到制定URL

