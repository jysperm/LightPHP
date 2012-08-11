<?php
//该文件保存了LightPHP的一些设置

//--Debug

//是否启用Debug模式(将实时显示错误信息)
define("lpCfgDebug",true);
//非Debug模式下出现错误时提示给用户的信息
define("lpErrorMsg","服务器脚本执行错误，请联系管理员");

//--公共

//与北京时间的时差(单位秒)
define("lpCfgTimeToChina",0);

//--lpAuth

//cookie键名前缀
define("lpCfgCookiePrefix","lp_");
//cookie安全码,随机字符串
define("lpCfgSecurityCode","0123456789abcdefghijklmnopqrstuvwxyz");
//登录状态有效期,单位天
define("lpCfgTimeLimit",30);
//获取密码的回调函数
define("lpCfgGetPasswd","lpGetPasswd");
    //用户信息所在表(仅当get_passwd=lpGetPasswd时有效)
    define("lpCfgTable","user");
    //用户名所在字段名(仅当get_passwd=lpGetPasswd时有效)
    define("lpCfgUNameField","uname");
    //密码所在字段名(仅当get_passwd=lpGetPasswd时有效)
    define("lpCfgPasswdField","passwd");
//登录成功后的回调函数
define("lpCfgCallback",NULL);
//数据库形式密码加密算法
define("lpCfgDBHash","lpDBHash");
//Cookie形式密码加密算法
define("lpCfgCookieHash","lpCookieHash");


//--lpCache

//是否缓存文件内容，而不是每次都重新读取文件
define("lpCfgFileCache",true);


//--lpOptions
//各组件的版本信息

define("lpCfgVer","3.0.0");
define("lpCfgBootstrapVer","2.0.4");
define("lpCfgJQueryVer","1.8.0");


//--lpMySQL

//Debug模式将打印所有执行的MySQL语句
define("lpMySQLDebug",false);
//该部分用于指定lpMySQL默认使用的数据库连接信息
$lpCfgMySQL=array(
                   "host" => "localhost",
                   "dbname" => "mydb",
                   "user" => "myuser",
                   "pwd" => "mypassword",
                   "charset" => "utf8"
                 );

?>
