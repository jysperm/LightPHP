<?php
//该文件保存了LightPHP的一些设置和选项

//常用
//------------------------

//--Debug

//是否启用Debug模式(开启则将实时显示错误信息)
$lpCfgDebug=true;

//--LightPHP

//与北京时间的时差(单位/秒)
$lpCfgTimeToChina=0;
//LightPHP相对于域名根目录的位置
$lpUrl="/";

//各组件的版本信息

$lpCfgVer="3.1.0";
$lpCfgBootstrapVer="2.1.1";
$lpCfgJQueryVer="1.8.0";

//--lpAuth

//cookie安全码,随机字符串
$lpCfgSecurityCode="0123456789abcdefghijklmnopqrstuvwxyz";
//登录状态有效期(单位/天)
$lpCfgTimeLimit=30;
//登录成功后的回调函数
$lpCfgCallback=NULL;

//--lpMySQL

//Debug模式将打印所有执行的MySQL语句
$lpCfgMySQLDebug=false;
//该部分用于指定lpMySQL默认使用的数据库连接信息
$lpCfgMySQL=array(
                   "host" => "localhost",
                   "dbname" => "mydb",
                   "user" => "myuser",
                   "pwd" => "mypasswd",
                   "charset" => "utf8"
                 );

//--lpSmtpMail

//默认SMTP服务器
$lpCfgMailHost="smtp.exmail.qq.com";
//默认邮箱地址
$lpCfgMailAddress="public@jybox.net";
//默认邮箱用户名
$lpCfgMailUName="public@jybox.net";
//默认邮箱密码
$lpCfgMailPasswd="passwd123123";





//高级
//------------------------

//--LightPHP

//LightPHP在服务器文件系统上的位置
$lpROOT=dirname(__FILE__);

//--lpAuth

//Cookie的用户字段的名字
$lpCfgUNAME="lp_uname";
//Cookie的密码字段的名字
$lpCfgPASSWD="lp_passwd";
//获取密码的回调函数
$lpCfgGetPasswd="lpGetPasswd";
    //用户信息所在表(仅当$lpCfgGetPasswd=lpGetPasswd时有效)
    $lpCfgTable="user";
    //用户名所在字段名(仅当$lpCfgGetPasswd=lpGetPasswd时有效)
    $lpCfgUNameField="uname";
    //密码所在字段名(仅当$lpCfgGetPasswd=lpGetPasswd时有效)
    $lpCfgPasswdField="passwd";
//数据库形式密码加密算法
$lpCfgDBHash="lpDBHash";
//Cookie形式密码加密算法
$lpCfgCookieHash="lpCookieHash";

//--lpCache

//是否缓存文件内容，而不是每次都重新读取文件
$lpCfgFileCache=true;
//数据库模式中表示键的字段名
$lpCfgKEY="key";
//数据库模式中表示值的字段名
$lpCfgVALUE="value";
//文件模式中文件开头的保护性注释
$lpCfgSTART="<?php /*";
//文件模式中文件末尾的保护性注释
$lpCfgEND="*/ ?>";

//--lpTemplate

//默认模板文件的位置
$lpCfgDefault="{$lpROOT}/lp-style/default.template.php";

//--lpTools
$lpCfgGravatarUrl="http://www.gravatar.com/avatar/";





//以下请勿修改
//------------------------

if(!$lpCfgDebug)
    ini_set("display_errors","Off");

?>
