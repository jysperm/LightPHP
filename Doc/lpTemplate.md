##lpTemplate
该类提供了一个简易的模板引擎.

###依赖
依赖lpGlobal类.

##全局空间
####`define("lpDEFAULT","{$lpROOT}/lp-style/default.template.php");`
LightPHP默认模板的路径.

####`function lpBeginBlock()`
开始一个缓冲块.

####`function string lpEndBlock()`
结束一个缓冲块，返回这个缓冲块中的内容.

##`class lpTemplate`
####`public function __construct()`
构造函数将开始一个缓冲块.

####`public function parse(string $filename,array $lpVars_=array())`
按照指定的模板进行解析.`$lpVars`是传递给模板的参数，可以在模板文件中当做普通变量使用.  
所有可序列化的对象都可以传递.

请自行防止`$filename`被注入.

####`public function __destruct()`
如果你没有通过`parse()`函数进行过解析，将发出一个警告.

##模板文件
模板文件是一个正常的PHP文件，你可以书写任何PHP代码，
被传递进来的参数可以直接当做变量来读取，同时还有一个内部变量`$lpContents`，
它储存着正文，即没有包含在任何缓冲块中的数据.

你也还可以在模板文件中再继续嵌套模板，以及`lpBeginBlock()`和`lpEndBlock()`.

你还可以在模板文件的开头加入`if(!isset($lpInTemplate)) die();`这样，就只有模板引擎能够调用你的模板文件了，`$lpInTemplate`也是一个内部变量.它的值总是true.
