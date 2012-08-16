<?php

require_once("../lp-config.php");
require_once("../lp-class/lpGlobal.class.php");
require_once("../lp-class/lpSQLRs.class.php");
require_once("../lp-class/lpMySQL.class.php");

//连接到默认数据库
$conn=new lpMySQL();
$conn->open();

//在“执行SQL”的教程中，我们知道了如何向数据库提交SQL，
//那么如何得到查询到的结果集呢？

//lpMySQL的exec函数会返回一个lpSQLRs类型的示例,
//这个类型是LightPHP对MySQL查询结果集的封装.
//例如你可以这样得到一个结果集($rs)：
$rs=$conn->exec("SELECT * FROM `user`");

/*
得到结果集后，首先我们需要用read()函数来读取下一条记录，
注意，结果集一开始的当前记录是空的，所以你得到结果集后需要调用read()函数，
来加载第一条记录.

read()函数会返回是否成功读取，如果返回false,那就说明到记录集的结尾了.
*/

//例如下面的代码会加载第一条记录
$rs->read();

//至于读取字段的值，你可以直接去读它的属性(实际上是魔法函数),
//或者调用value()函数，一般前者方便一点
//例如下面的代码会打印这个用户的用户名和密码
print "用户名：{$rs->uname}，密码：{$rs->value("passwd")}.\n";
/* 执行结果
用户名：jybox，密码：1a91f14ce74e651beba69dc813cefb058bf3ab48.
*/

//有时候你还需要知道结果集中一共有多少条记录，这时你可以用num()函数：
var_dump($rs->num());
/* 执行结果
int(5)
*/

//为了接下来的演示，我们需要把结果集指针重新移动到结果集的开始处，
//这时你可以试用seek()函数，通过它你可以移动结果集指针，
//下面的代码会把结果集指针移动到开始处
$rs->setSeek(0);

//话说其实稍微有点经验的人，就可以想到，
//可以通过类似下面的一个循环，来读取数据集中全部的行：
while($rs->read())
{
    print "用户名：{$rs->uname}，密码：{$rs->passwd}.\n";
}
/* 执行结果
用户名：jybox，密码：1a91f14ce74e651beba69dc813cefb058bf3ab48.
用户名：whtsky，密码：946f4fe5095b1a0749cf2676613acb55cbd74825.
用户名：abreto，密码：a42ce82da177fdc35a79ed3d0c5c8603c0c8447b.
用户名：abort，密码：f4f1dfdaf4a1cf1b1f3f111fd43cf73cd8b21ab7.
用户名：zeroms，密码：64556c314e890f5d041a22c808918b5d997ae22e.
*/

//最后，你可以通过close()函数手动释放被结果集占用的内存，
//当然即使你不手动释放，超出对象作用域时也会自动释放.
$rs->close();

?>