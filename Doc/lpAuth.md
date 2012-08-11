##lpAuth
该类提供了登录状态管理的功能.

该类全部为静态函数.

###依赖
依赖lpMySQL类、lpSQLRs类、lpGlobal类.

###LightPHP的密码加密算法
LightPHP的密码形式分为**原始密码**(明文密码)、**数据库密码**、**Cookie密码**

相应的加密函数可在配置文件设置，LightPHP也自带了默认的加密算法.

##全局空间
####`define("lpUNAME", "{$lpConfigs['lpAuth']['cookie_prefix']}uname")`
Cookie中用户名一项的键名.

####`define("lpPASSWD", "{$lpConfigs['lpAuth']['cookie_prefix']}passwd")`
Cookie中密码一项的键名.

####`function string lpHash256(string $data)`
SHA-256散列算法.

####`function string lpDBHash(string $user,string $passwd)`
默认的数据库密码加密算法：

	SHA-256( SHA-256($user) + SHA-256($passwd) 

####`function string lpCookieHash(string $DBPasswd)`
默认的Cookie密码加密算法：

	SHA-256( SHA-256(Cookie安全码) + $DBPasswd ) 

####`function string lpGetPasswd(string $uname)`
默认的密码获取函数，将按照配置文件的设置从MySQL数据库中取得密码.

##`class lpAuth`
####`public static bool function auth(string $user,string $passwd,bool $isRaw=true,bool $isDB=false,bool $isCookie=false)`
验证密码是否正确.

`$isRaw`、`$isDB`、`$isCookie`分别表示原始密码、数据库密码、Cookie密码，通常其中只有一个被设置为`true`.

####`public static bool function login(string $user=NULL,string $passwd=NULL,bool $isRaw=true,bool $isDB=false,bool $isCookie=false)`
进行登录验证，  
不指定用户名和密码，将从Cookie中读取，若登录成功，会将登录信息写入Cookie

`$isRaw`、`$isDB`、`$isCookie`分别表示原始密码、数据库密码、Cookie密码，通常其中只有一个被设置为`true`.

####`public static string function getUName()`
获得当前用户的用户名.

注意！请先用`login()`函数验证是否登录，然后再使用该函数！这里很容易受到攻击！

####`public static function logout()`
退出登录，清除Cookie.

####`public static string function DBHash(string $user,string $passwd)`
由原始密码生成数据库密码，该函数将调用配置文件中设置的回调函数.

####`public static string function cookieHash(string $DBPasswd)`
由数据库密码生成数据库密码，该函数将调用配置文件中设置的回调函数.

####`public static string function getPasswd(string $uname)`
获得对应用户名的原始密码，该函数将调用配置文件中设置的回调函数.
