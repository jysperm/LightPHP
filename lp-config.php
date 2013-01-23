<?php

/**
*   该文件包含了LightPHP的全部可修改的配置信息
*
*   该文件会被 /lp-load.php 通过 require() 包含, 通常你不需要手动包含该文件.
*   在你的应用中，你可以自行覆盖下面这些配置信息.
*
*   该文件只会给变量赋值, 因此你可以在希望重置配置信息时, 手动重新包含该文件.
*   但有的配置可能不会随着重新加载而改变, 因为它们已经被其他组件读取并使用了.
*
*	只所以使用变量来储存这些配置, 是方便在使用LightPHP的程序中方便地覆盖这些配置, 而不必修改LightPHP的文件.
*
*   @package LightPHP
*/


// --------------- 经常修改的选项 ---------------


/**
*   时区
*
*   该选项用于设置默认时区, 该选项会被 /lp-load.php 读取并注册到PHP.
*
*   经检查, PHP支持的时区列表中没有北京, 因此选用上海作为北京时间.
*   http://cn2.php.net/manual/zh/timezones.asia.php
*
*   @type string
*/
$lpCfg["TimeZone.LightPHP"] = "Asia/Shanghai";

/**  
*   lpSmtp 类的默认发信服务器. 
*
*   @type string
*   @see lpSmtp
*/
$lpCfg["Host.Default.lpSmtp"] = "smtp.exmail.qq.com";

/**  @type string   lpSmtp 类的默认发信地址. */
$lpCfg["Address.Default.lpSmtp"] = "public@jybox.net";

/**  @type string   lpSmtp 类的默认发信用户名. */
$lpCfg["UName.Default.lpSmtp"] = "public@jybox.net";

/**  @type string   lpSmtp 类的默认发信密码. */
$lpCfg["Passwd.Default.lpSmtp"] = "passwd123123";

/**
*	lpMySQLDrive 类的默认连接选项
*
*	* host 服务器IP或主机名(如`localhost`),还可以指定端口(如`localhost:4567`),也可以使用本地Socket(如`/var/run/mysqld/mysqld.sock`)
*	* dbname 数据库名
*	* user 数据库用户名
*	* passwd 数据库密码
*	* charset 数据库字符集
*
*	@type array
*/
$lpCfg["Default.lpMySQLDBDrive"] = [
	"host" => "localhost",
	"dbname" => "mydb",
	"user" => "myuser",
	"passwd" => "mypasswd",
	"charset" => "utf8"
];

/**
*   关闭PHP版本号过低时显示的警告.
*
*   无论是为了安全、效率、享受新的特征, 你都应该将PHP更新到较新的版本.
*   http://cn2.php.net/downloads.php
*   http://cn2.php.net/manual/zh/install.php
*   当然如果在服务器上你没有更新软件的权限, 当我没说.
*
*   @see $lpCfg["RecommendedPHPVersion.LightPHP"]
*   @type bool
*/
$lpCfg["PHPVersion.TrunOff.Warning"] = false;






// --------------- 高级选项(请慎重修改) ---------------


/**
*   LightPHP推荐的PHP最低版本
*
*   LightPHP可能用到该版本的新特征, 或者做了依赖于该版本的假设.
*
*   @type string
*/
$lpCfg["RecommendedPHPVersion.LightPHP"] = "5.4.0";
