<?php

require_once("../lp-load.php");

//构建发信组建
$mailer=new lpSmtpMail();

$mailTitle="邮件标题"; 
$mailBody="<b>邮件内容</b>";

//发送邮件
$mailer->send("xxoo@xo.ox",$mailTitle,$mailBody,"HTML");

//打印日志
print $mailer->getLog();

?>
