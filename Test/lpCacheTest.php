<?php

require_once(dirname(__FILE__) . "/../LightPHP.php");

class lpCacheTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $funcRunTest = function($cache) {
            /** @var lpAPCCache $cache */

            $cache["key1"] = "value1";
            $this->assertEquals("value1", $cache->get("key1"));

            $data = str_repeat(md5(rand()), rand(10, 100));
            $cache->set("key2", $data);
            $this->assertEquals($data, $cache["key2"]);

            $flag = 0;
            $seter = function() use(&$flag) {
                $flag++;
                return "value";
            };

            $cache->delete("key2");
            $this->assertEquals(null, $cache["key2"]);
            $this->assertEquals(null, $cache["key3"]);

            $cache->check("key2", $seter);
            $this->assertEquals(1, $flag);
            $this->assertEquals("value", $cache->get("key2"));
            $this->assertEquals(1, $flag);
        };

        $funcRunTest(new lpMemCache);
        if(version_compare(PHP_VERSION, "5.4.0") >= 0 and version_compare(PHP_VERSION, "5.5.0") < 0)
            $funcRunTest(new lpAPCCache);
    }
}
