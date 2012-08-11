<?php
/*
 用到的类：lpOptions
*/
require_once("../lp-class/lpOptions.class.php");

/*
lpOptions类提供了一种简单的、全局的、键/值对应的储存系统.可以几乎无差别地读写MySQL数据库和INI文件.

它可以用于读取或者保存一些全局的数据，例如设置，再或者缓存.
*/
//使用时你需要先创建一个实例，目标数据可以是文件也可以是数据库：
$op=new lpOptions("{$lpROOT}/my-config.ini.php");
//或
//$conn=new lpMySQL();
//$op=new lpOptions($conn,"lp-options");

//你可以这样地保存数据：
$op->myname="jybox";

//然后像这样读取数据：
var_dump($op->myname);
/* 执行结果：
string(5) "jybox"
*/


//你还可以存储数组等所有可被序列化的PHP类型
$op->myarray=array(123,"456",789,"0AB");

var_dump($op->myarray);

//lpOptions类还提供了若干选项，请参见配置文件中的注释.
?>
