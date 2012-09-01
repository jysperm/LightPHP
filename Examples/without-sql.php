<?php

require_once("../lp-load.php");

//连接到默认数据库
$conn=new lpMySQL();
$conn->open();

//LightPHP还支持无需SQL的MySQL数据库的查、改、增、删.

//查
//可以查询一个表中，一个或多个字段与指定的值相等的记录.
//例如：
$rs=$conn->select("user",array("uname"=>"jybox","passwd"=>"1a91f14ce74e651beba69dc813cefb058bf3ab48"));
//这等价于：
// SELECT * FROM `user` WHERE ( `uname` = 'jybox' ) AND( `passwd` = '1a91f14ce74e651beba69dc813cefb058bf3ab48' )

//这时我们就可以按照“获取结果集”教程中的方法来读取结果了：
while($rs->read())
{
    print "用户名：{$rs->uname}，密码：{$rs->passwd}.\n";
}
/* 执行结果
用户名：jybox，密码：1a91f14ce74e651beba69dc813cefb058bf3ab48.
*/

//你还可以向select()函数传递更多参数，详情请见文档
//例如下面的代码：
$rs=$conn->select("user",NULL,"uid",2,2,false);
//这等价于：
// SELECT * FROM `user` ORDER BY `uid` DESC LIMIT 2,2
while($rs->read())
{
    print "{$rs->uid}.用户名：{$rs->uname}，密码：{$rs->passwd}.\n";
}
/* 执行结果
3.用户名：abreto，密码：a42ce82da177fdc35a79ed3d0c5c8603c0c8447b.
2.用户名：whtsky，密码：946f4fe5095b1a0749cf2676613acb55cbd74825.
*/

//改
//可以用update()函数实现修改记录，它的三个参数分别是表名、查询条件、要修改成的值，例如：
$conn->update("user",array("uname"=>"abort"),array("email"=>"abort@jybox.net"));
//这等价于：
// UPDATE `user` SET `email` = 'abort@jybox.net' WHERE `uname` = 'abort'

//你还可以在第二个或第三个参数中传入有多个参数的数组，来实现更复杂的查询条件、一次修改更多个字段，如：
$conn->update("user",array("uname"=>"abort","uid"=>5),array("email"=>"abort@jybox.net","website"=>"http://sunyboy.cn/"));
//这等价于：
// UPDATE `user` SET `email` = 'abort@jybox.net'  WHERE `uname` = 'abort' AND `uid` = '5'
// UPDATE `user` SET `website` = 'http://sunyboy.cn/'  WHERE `uname` = 'abort' AND `uid` = '5'

//好吧好吧，不要乱改，改回去吧：
$conn->update("user",array("uname"=>"abort"),array("email"=>"m@sunyboy.cn"));

//增
//可以用insert()函数实现新增记录，它的两个参数分别是表名和要插入的值，例如：
$conn->insert("user",array("uname"=>"limit","passwd"=>"6279b0721d64113a71c8c06d39d9b1eda0c67900","email"=>"l@gov.com.im"));
//这等价于：
// INSERT INTO `user` (`uname`,`passwd`,`email`) VALUES ('limit','6279b0721d64113a71c8c06d39d9b1eda0c67900','l@gov.com.im')

//删
//可以用delete()函数实现删除记录，他的两个参数分别是表名和删除条件，例如：
$conn->delete("user",array("uname"=>"limit"));
//这等价于：
// DELETE FROM `user` WHERE `uname` = 'limit'

?>