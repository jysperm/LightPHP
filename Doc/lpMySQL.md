##lpMySQL
该类提供了到MySQL数据库的连接；还提供了解析包含占位符的SQL的功能；以及简单的、无需SQL的查、改、增、删功能.

###依赖
依赖lpSQLRs类、lpGlobal类.

##`class lpMySQL`
####`public function __construct(string $host=NULL,string $dbname=NULL,string $user=NULL,string $pwd=NULL,string $charset=NULL)`
默认会采用配置文件中[lpMySQL]段的数据库连接参数，向该函数传递参数会覆盖默认的数据库连接参数.

`string $host=NULL` : 服务器IP或主机名(如`localhost`),还可以指定端口(如`localhost:4567`),也可以使用本地Socket(如`/var/run/mysqld/mysqld.sock`).
`string $dbname=NULL` : 数据库名.
`string $user=NULL` : 数据库用户名.
`string $pwd=NULL` : 数据库密码.
`string $charset=NULL` : 数据库字符集.

####`public function __destruct()`
析构函数将会调用`close()`.

####`public bool function open(bool $isPC=false)`
打开数据库连接.

`bool $isPC=false` : 是否启用持久连接，有关持久连接，请参考MySQL的文档.
该函数将返回是否成功打开数据库.

####`public function close()`
断开与数据库连接.

####`public bool function ping()`
返回到数据库的连接是否可用.
如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public lpSQLRs function exec(string $sql,mixed $more=NULL)`
该函数将解析并执行含有占位符的SQL，然后返回一个lpSQLRs(数据集)对象.

`string $sql` : 格式字符串，可以包含下面8种占位符：

* `%i`或`%I`  -  整数
* `%f`或`%F`  -  浮点数
* `%s`或`%S`  -  仅包含字母、数字、下划线的字符串
* `%t`或`%T`  -  任何字符串，但单引号会被转义

小写字母版本的占位符，会把提供的参数强制转换到相应的类型.
而大写字母版本的占位符，如果发现无法转换，将不会执行SQL而是返回NULL.

如SQL中需要包含百分号(`%`),请使用双百分号(`%%`)进行转义.
如果百分号后没有紧挨着上面八个占位符，也可以不转义.

`mixed $more=NULL` : 可以提供无限个参数，它们一一对应`$sql`中的占位符.
如果参数少于占位符，那么后面剩下的占位符将不会被替换，原样留下，
如果参数多于占位符，那么多余的参数将被忽略.

如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public lpSQLRs function select(string $table,array $querys,string $orderBy=NULL,int $start=-1,int $num=-1,bool $isASC=true)`
该函数将返回一个lpSQLRs(数据集)对象，对指定的表进行简单的查询.

`string $table` ： 要查询的表名
`array $querys` : 查询条件，例如`array("uname"=>"jybox")`.还可以指定多个并列的条件，例如`array("uname" => "jybox","email"=>"m@jybox.net")`
`string $orderBy=NULL` : 按哪个字段进行排序.
`int $start=-1` ： 从第几行开始返回结果集，`-1`表示默认，一般为0,即开始处.
`int $num=-1` : 返回多少行结果，`-1`表示默认，一般为不限制.
`bool $isASC=true` : 是否按正序排序，为`false`时表示倒序排序.

如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public function update(string $table,array $querys,array $values)`
将表`$table`中所有满足`$querys`条件的记录，按照`$values`进行修改.
如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public function insert(string $table,array $values)`
向表`$table`中插入一条值为`$values`的新记录.
如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public function delete(string $table,array $querys)`
从表`$table`中删除所有满足`$querys`条件的记录.

如`$querys`为`NULL`,会删除表中所有记录并发出一个警告.
如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public string function testSQL(string $sql,mixed $more=NULL)`
返回解析后的SQL,而不是执行，可用于调试.

如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public int function affected()`
返回上一条查询影响的行数.

####`public int function insertId()`
返回上一次插入操作产生的ID.

####`public array function getDBs()`
以数组的形式返回所有数据库名.

如果调用该函数时数据库连接没有被打开，将自动调用`open()`函数.

####`public array function getTables(string $dbname)`
以数组的形式返回指定数据库的所有表名.

####`public array function queryToArray(resource $query)`
将结果集资源标识符转换为数组.
