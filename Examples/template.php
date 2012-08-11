<?php
/*
 用到的类：lpTemplate
*/
require_once("../lp-class/lpTemplate.class.php");

/*
lpTemplate是一个灰常简单的模板引擎，它提供的功能很少，但却也很实用.
本例使用的模板文件是当前目录下的 template.template.php
*/

//启用模板：
$tmp=new lpTemplate();

//下面展示了三种不同的方式来给稍后要用户到的参数赋值：

//设置标题：
$title="这里是标题";

//段落1：
lpBeginBlock();

echo "这里是段落1\n";

$p1=lpEndBlock();

//段落2：
lpBeginBlock();

?>
这里是段落2
<?php

$p2=lpEndBlock();

echo "这里是正文\n";

//将参数传递给模板，并进行解析
$tmp->parse("./template.template.php",array("title"=>$title,"p1"=>$p1,"p2"=>$p2));
/* 运行结果
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>这里是标题</title>
  </head>
  <body>
    <h1>这里是标题</h1>
    <p>
      <b>段落1</b>
      这里是段落1
    </p>
    <p>
      <b>段落2</b>
      这里是段落2
    </p>
    <p>
      这里是正文
    </p>
  </body>
</html>
*/

//模板和lpBeginBlock()/lpEndBlock()均可嵌套、互相嵌套地来使用.

?>