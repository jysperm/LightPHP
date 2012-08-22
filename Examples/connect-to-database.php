<?php

require_once("../lp-config.php");
require_once("../lp-class/lpTools.php");
require_once("../lp-class/lpSQLRs.php");
require_once("../lp-class/lpMySQL.php");

//连接到默认数据库
//默认数据库可以在配置文件中指定
$conn=new lpMySQL();

//上面的代码只是产生了一个连接对象.
//你需要像下面这样打开连接
$conn->open();

//你还可以向lpMySQL的构造函数传递更多参数，像下面这样
//具体请参见文档
$conn=new lpMySQL("localhost","mydb","myuser","mypasswd","utf8");
$conn->open();

//你可以通过lpMySQL的ping()函数来检查到数据库的连接是否可用
var_dump($conn->ping());
/* 执行结果：
bool(true)
*/

//你可以通过lpMySQL的close()函数来关闭到数据库的连接
$conn->close();
//让我们再看看ping()函数的返回值
var_dump($conn->ping());
/* 执行结果：
bool(false)
*/

//lpMySQL还提供了持久连接的选项
//有关该功能请参考 http://www.php.net/manual/zh/function.mysql-pconnect.php
$conn=new lpMySQL();
$conn->open(true);

//确实连接到服务器上了么？不相信么？
//我们可以打印一下服务器上的数据库列表
print_r($conn->getDBs());
/* 执行结果：
Array
(
    [0] => information_schema
    [1] => mydb
)
*/

?>
