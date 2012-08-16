<?php

require_once("../lp-config.php");
require_once("../lp-class/lpGlobal.class.php");
require_once("../lp-class/lpSQLRs.class.php");
require_once("../lp-class/lpMySQL.class.php");
require_once("../lp-class/lpAuth.class.php");

//我们可以通过这样的方式从GET参数接收用户名和密码，
//并验证用户输入的密码是否正确：
var_dump(lpAuth::login($_GET["U"],$_GET["P"]));
/* 运行结果
bool(true)
*/

//在之后，我们也可以通过不带参数的login()函数判断用户是否登录
//这时是通过Cookie判断的
var_dump(lpAuth::login());
/* 运行结果
bool(true)
*/

//在确定用户已经登录后，可以用getUName()函数获得当前用户名：
var_dump(lpAuth::getUName());
/* 运行结果
jybox
*/

//可以通过logout()函数来退出登录
lpAuth::logout();

//注：配置文件中有大量关于lpAuth的设置，详情参见配置文件的注释.
?>
