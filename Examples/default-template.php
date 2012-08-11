<?php
include_once("../lp-load.php");

$tmp=new lpTemplate();
$args["title"]="标题";
$args["isResponsive"]=true;

echo file_get_contents("./README.html");

lpBeginBlock();
?>
    <a href="https://github.com/jybox/JYLib/tree/master/LightPHP">LightPHP</a> by <a href="http://jyprince.me/">精英王子</a>
<?php
$args["footer"]=lpEndBlock();

$tmp->parse(lpDEFAULT,$args);
?>
