<?php

require_once("lp-load.php");

class lpSmtpTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        global $lpROOT;
        if($lpROOT == "/home/travis/builds/jybox/LightPHP")
            return;

        $xxoo = new lpSmtp;

        $xxoo->send("jyboxnet@gmail.com", "PHPUnit 测试邮件", "内容");

        $log = $xxoo->getLog();

        $this->assertContains("235 Authentication successful", $log);
        $this->assertContains("queued as", $log);
        $this->assertNotContains("Error", $log);
    }
}