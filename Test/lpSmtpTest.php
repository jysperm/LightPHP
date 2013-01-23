<?php

require_once("lp-load.php");

class lpSmtpTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $xxoo = new lpSmtp;

        $xxoo->send("jybox@jybox.net", "PHPUnit 测试邮件", "内容");

        $log = $xxoo->getLog();

        $this->assertContains("235 Authentication successful", $log);
        $this->assertContains("250 Ok: queued as ", $log);
        $this->assertNotContains("Error", $log);
    }
}