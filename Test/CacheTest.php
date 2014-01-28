<?php

namespace LightPHP\Test;

use LightPHP\Cache\Adapter\FileCache;
use LightPHP\Cache\Adapter\SimpleCache;
use LightPHP\Cache\CacheAgent;
use LightPHP\Cache\Adapter\MemCache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     */
    public function testCache($agent)
    {
        $agent->set("test", "value");
        $this->assertEquals("value", $agent->get("test"));

        $agent->delete("test");
        $this->assertEquals(false, $agent->exist("test"));
    }

    public function provider()
    {
        return [
            new CacheAgent(new MemCache),
            new CacheAgent(new FileCache),
            new CacheAgent(new SimpleCache)
        ];
    }
}
 