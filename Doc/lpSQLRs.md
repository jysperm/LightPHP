##lpSQLRs
该类表示一个MySQL结果集.

###依赖
依赖lpMySQL类、lpGlobal类.

##`class lpSQLRs`
####`public function __construct(resource $res=NULL)`
`resource $res=NULL` : MySQL结果集标识.

构造完成后，需要通过`read()`函数读取结果集的第一行.

####`public function __destruct()`
析构函数将会调用`close()`.

####`public function close()`
销毁结果集，释放资源.

####`public string function __get(string $name)`
取得当前行某一字段的值.如果字段名和该类的成员变量冲突，请使用`value()`函数.

####`public string function value(string $name)`
取得当前行某一字段的值.

####`public bool function read()`
加载结果集中的下一行.返回是否加载成功，不成功即代表已到达结果集的末尾.

在该类构造完成后，需要调用该函数来加载结果集的第一行.

####`public array function toArray(int $num=-1)`
将结果集的接下来N条记录转换为数组，默认转换全部.

####`public int function num()`
返回结果集中的行数.

####`public int function seek()`
获取当前结果集游标指针的位置.

####`public bool function setSeek(int $s=0)`
移动结果集的游标指针.返回是否执行成功.
当前指针位置可通过`$_lpSeek`来读取.
