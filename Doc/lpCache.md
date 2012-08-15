##lpOptions
该类提供了一种简单的、全局的、键/值对应的储存系统.可以几乎无差别地读写MySQL数据库和INI文件.  
该类支持序列化，可以储存所有可以被序列化的PHP类型.

###依赖
依赖lpMySQL类、lpSQLRs类、lpGlobal类.

###MySQL
建表SQL：

    CREATE TABLE IF NOT EXISTS `lp-cache` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `key` text NOT NULL,
      `value` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    
###INI文件
注意事项

* 如果INI文件过大会带来一定的效率问题.
* 不支持分组，分组将被忽略.
* 请通过`.ini.php`的扩展名命名INI，自动插入的PHP注释会防止文件内容泄漏.

##全局空间
####`define("lpKEY", "key")`
数据库中代表键名的的字段名.

####`define("lpVALUE", "value")`
数据库中代表值的字段名.

##`class lpOptions`
####`private resource $_lpConnect=NULL`
数据库模式时，关联的lpMySQL(数据库连接)对象.

####`private string $_lpFile=NULL`
INI文件模式时文件的路径.

####`private string $_lpTable=NULL`
数据库模式时的表名.

####`private array $_lpCache=NULL`
INI文件模式时文件的读取缓存.

可通过 lp-config.ini.php 中 [lpOptions] 段的 `file_cache` 设置是否启用.

####`function __construct(mixed $FileOrlpMySQL=NULL,string $table=NULL)`
数据库模式时，同时指定两个参数，第一个是lpMySQL(数据库连接)对象，第二个是表名.  
INI文件模式时，只在第一个参数指定文件路径.

####`function string __get($name)`
读取相应键名的值.

INI文件模式时，可通过 lp-config.ini.php 中 [lpOptions] 段的 `file_cache` 设置是否启用缓存.  
启用缓存后会缓存文件内容，而不是每次都重新读取文件.

####`function __set($name,$value)`
设置相应键名的值.
