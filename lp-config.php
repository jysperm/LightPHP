<?php
//该文件保存了LightPHP的一些设置

//常用
//------------------------

//--Debug

//是否启用Debug模式(将实时显示错误信息)
define("lpCfgDebug",true);

//--LightPHP

//与北京时间的时差(单位秒)
define("lpCfgTimeToChina",0);
//LightPHP相对于域名根目录的位置
define("lpUrl","/");

//各组件的版本信息

define("lpCfgVer","3.0.0");
define("lpCfgBootstrapVer","2.0.4");
define("lpCfgJQueryVer","1.8.0");

//--lpAuth

//cookie安全码,随机字符串
define("lpCfgSecurityCode","0123456789abcdefghijklmnopqrstuvwxyz");
//登录状态有效期,单位天
define("lpCfgTimeLimit",30);
//登录成功后的回调函数
define("lpCfgCallback",NULL);

//--lpMySQL

//Debug模式将打印所有执行的MySQL语句
define("lpCfgMySQLDebug",false);
//该部分用于指定lpMySQL默认使用的数据库连接信息
$lpCfgMySQL=array(
                   "host" => "localhost",
                   "dbname" => "mydb",
                   "user" => "myuser",
                   "pwd" => "mypassword",
                   "charset" => "utf8"
                 );






//高级
//------------------------

//--Debug

//非Debug模式下出现错误时提示给用户的信息
define("lpCfgErrorMsg","服务器脚本执行错误，请联系管理员");

//--LightPHP

//LightPHP在服务器文件系统上的位置
define("lpROOT",dirname(__FILE__));

//--lpAuth

//Cookie的用户字段的名字
define("lpCfgUNAME", "lp_uname");
//Cookie的密码字段的名字
define("lpCfgPASSWD", "lp_passwd");
//获取密码的回调函数
define("lpCfgGetPasswd","lpGetPasswd");
    //用户信息所在表(仅当get_passwd=lpGetPasswd时有效)
    define("lpCfgTable","user");
    //用户名所在字段名(仅当get_passwd=lpGetPasswd时有效)
    define("lpCfgUNameField","uname");
    //密码所在字段名(仅当get_passwd=lpGetPasswd时有效)
    define("lpCfgPasswdField","passwd");
//数据库形式密码加密算法
define("lpCfgDBHash","lpDBHash");
//Cookie形式密码加密算法
define("lpCfgCookieHash","lpCookieHash");

//--lpCache

//是否缓存文件内容，而不是每次都重新读取文件
define("lpCfgFileCache",true);
//数据库模式中表示键的字段名
define("lpCfgKEY", "key");
//数据库模式中表示值的字段名
define("lpCfgVALUE", "value");
//文件模式中文件开头的保护性注释
define("lpCfgSTART", "<?php /*");
//文件模式中文件末尾的保护性注释
define("lpCfgEND", "*/ ?>");

//--lpTemplate

//默认模板文件的位置
define("lpCfgDefault","{$lpROOT}/lp-style/default.template.php");



//以下请勿修改
//------------------------

if(!lpCfgDebug)
    ini_set("display_errors","Off");

?>
