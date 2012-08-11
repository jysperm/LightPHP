<?php
/*
 用到的类：lpMySQL
*/
require_once("../lp-class/lpMySQL.class.php");

//连接到默认数据库
$conn=new lpMySQL();
$conn->open();

/*
LightPHP提供了解析含有占位符的SQL的功能，更详细的信息请参见文档.
%i %f %s %t 分别表示 整数、浮点数、只包含大小写字母数字下划线的字符串、任意字符串.

通过占位符，可以明确参数的类型、减少字符串拼接，减少被SQL注入的风险.
而且无论如何，LightPHP都会对参数中的特殊字符(例如单引号)进行转义.

执行SQL主要通过lpMySQL类的exec()函数完成.
*/

//执行一个查询，没有参数
$conn->exec("SELECT * FROM `user`");
//想看看结果？ NO！ 如何读取结果不在这个教程的范围之内！

$uname="jybox";
$conn->exec("SELECT * FROM `user` WHERE `uname`='%s'",$uname);
//这等价于：
// SELECT * FROM `user` WHERE `uname`='jybox'

//如果你手工拼接字符串的话：
$conn->exec("SELECT * FROM `user` WHERE `uname`='{$uname}'");
/*
这样很容易受到SQL注入，例如攻击者可以在uname一项输入: 

     ' OR `uname`='abreto
     
SQL就会变成下面这样，查询到其他用户的信息

    SELECT * FROM `user` WHERE `uname`='' OR `uname`='abreto'

而使用LightPHP则不会，因为占位符%s指定了参数是一个“只包含大小写字母数字下划线的字符串”,
所以最后提交到数据库的字符串会是这样的(所有非法字符都被去掉了)：

    SELECT * FROM `user` WHERE `uname`='ORunameabreto'

如果你使用%S(大写S)做占位符，则LightPHP不会送出任何查询，而是返回一个错误
即使你使用%t占位符表示这是一个任意的字符串，Light也会对单引号进行转义，更详细的信息请参见文档.
*/

//你还可以指定更多的占位符(无限制)：
$conn->exec("UPDATE `%s` SET `email`='%t' WHERE `uname`='%s'","user","m@jybox.net",$uname);
//这等价于
// UPDATE `user` SET `email`='m@jybox.net' WHERE `uname`='jybox'

//如果你的SQL中本身就包含百分号，你可以通过两个百分号来进行转义，例如下面这样：
//详情请参见文档
$conn->exec("SELECT * FROM `user` WHERE `uname` LIKE ='%%%s%%'",$uname);
//这等价于
// SELECT * FROM `user` WHERE `uname` LIKE ='%jybox%'


//我凭什么说它就会按我说的那样解析？
//所以为了方便大家调试，lpMySQL还提供了一个testSQL()函数，它接收和exec()函数一样的参数，
//但它不执行SQL，而是返回解析后的SQL字符串.例如：
var_dump($conn->testSQL("SELECT * FROM `user` WHERE `uname` LIKE ='%%%s%%'",$uname));
/* 执行结果：
string(50) "SELECT * FROM `user` WHERE `uname` LIKE ='%jybox%'"
*/

?>