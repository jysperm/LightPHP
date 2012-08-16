<?php

require_once("../lp-config.php");
require_once("../lp-class/lpGlobal.class.php");
require_once("../lp-class/lpSQLRs.class.php");
require_once("../lp-class/lpMySQL.class.php");
require_once("../lp-class/lpLock.class.php");
require_once("../lp-class/lpCache.class.php");

/*
该类提供了一个键/值对应的储存系统，通过序列化，可以储存PHP中任何数据类型，它可以无差别地读写来自MySQL或文件的数据.

它可以用于读取或者保存一些全局的数据，例如设置，再或者缓存.
*/

//使用时你需要先创建一个实例，目标数据可以是文件也可以是数据库：
//为了保护这个数据文件不被下载，你可以使用.php做扩展名
$cache=new lpCache("{$lpROOT}/my-config.data.php");
//或
$conn=new lpMySQL();
$cache=new lpCache($conn,"lp-options");

//你可以这样地保存数据：
$cache->myname="jybox";

//然后像这样读取数据：
var_dump($cache->myname);
/* 执行结果：
string(5) "jybox"
*/


//你还可以存储数组等所有可被序列化的PHP类型
$cache->myarray=array(123,"456",789,"0AB");

var_dump($cache->myarray);

?>
