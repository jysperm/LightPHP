<?php

require_once("../lp-config.php");
require_once("../lp-class/lpTools.php");
require_once("../lp-class/lpSQLRs.php");
require_once("../lp-class/lpMySQL.php");
require_once("../lp-class/lpLock.php");

//lpLock其实分成三个类：lpFileLock、lpMySQLLock、lpMutex.

//例如你可以在浏览器中打开两个该页面，你会发现同时只会有一个页面获得锁
$lock=new lpFileLock("mylock");
$lock->lock();

//你也可以使用使用MySQL锁
$conn=new lpMySQL;
$lock=new lpMySQLLock("mylock",$conn);
$lock->lock(10);

//你还可以使用互斥区，或者叫页面锁
$lock=new lpMutex;

sleep(5);//被互斥区保护的代码

//互斥区需要通过销毁互斥区对象来离开
$lock=NULL;

?>
