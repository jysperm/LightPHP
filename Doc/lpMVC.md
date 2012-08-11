##lpMVC
该类提供了一个简易的MVC控制组件.

###依赖
依赖lpGlobal类.

##`class lpMVC`
####`public static function bind(string $rx,mixed $handler)`
用正则表达式`$rx`尝试匹配URL.如果匹配成功，正则表达式中的参数会被记录，然后：  

* 如果`$handler`是一个字符串，输出该字符串.
* 如果`$handler`是一个处理程序类，调用它与请求方式同名的函数(小写形式，如`get()`、`post()`).
* 如果`$handler`是一个回调函数，执行它，然后重新判断它是字符串还是处理程序类.


####`public static function onDefault(mixed $handler)`
执行`$handler`,用于设置默认处理程序.

##处理程序类
处理程序类一般需要有两个成员函数：`get()`、`post()`,  
当然，只有`get()`也可以.

它们接收一个参数：匹配的正则表达式的参数列表，数组形式.

##URL重写
LightPHP默认的`.htaccess`如下，存储在`LightPHP.htaccess`中，使用时你需要把它重命名为`.htaccess`

	<IfModule mod_rewrite.c>
	    RewriteEngine On
	    RewriteBase /
	    RewriteCond %{REQUEST_FILENAME} !-f
	    RewriteCond %{REQUEST_FILENAME} !-d
	    RewriteRule . /lp-main.php [L]
	</IfModule>

它会首先判断是否存在与URL对应的文件，如果存在，即使用该文件，如果不存在，将请求重写到`lp-main.php`.  
你可以在`lp-main.php`中使用lpMVC进行控制.

