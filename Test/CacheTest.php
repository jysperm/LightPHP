<?php

namespace LightPHP\Test;

use LightPHP\Cache\Adapter\FileCache;
use LightPHP\Cache\Adapter\MemCache;
use LightPHP\Cache\Adapter\SimpleCache;
use LightPHP\Cache\CacheAgent;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     */
    public function testSet($agent)
    {
        $agent->set("key", "value");
    }

    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     * @depends      testSet
     */
    public function testGetData($agent)
    {
        $this->assertEquals("value", $agent->fetch("key"));
        $this->assertEquals("value", $agent["key"]);

        $this->assertEquals("value", $agent->get("key"));
        $this->assertEquals("default value", $agent->get("key_not_exist", "default value"));
    }

    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     * @depends      testSet
     * @expectedException LightPHP\Cache\Exception\NoDataException
     */
    public function testFetchException($agent)
    {
        $agent->fetch("key_not_exist");
    }

    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     * @depends      testSet
     */
    public function testExist($agent)
    {
        $this->assertEquals(true, $agent->exist("key"));
        $this->assertEquals(false, $agent->exist("key_not_exist"));

        $this->assertEquals(false, isset($agent["key_not_exist"]));
    }

    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     */
    public function testCheck($agent)
    {
        $closure = function () {
            throw new \Exception;
        };

        $this->assertEquals("value", $agent->check("key", $closure));

        $this->assertEquals("new value", $agent->check("new_key", function () {
            return "new value";
        }));
    }

    /**
     * @param CacheAgent $agent
     *
     * @dataProvider provider
     * @depends      testCheck
     */
    public function testDelete($agent)
    {
        $agent->delete("new_key");

        $this->assertEquals(false, $agent->exist("new_key"));
        $this->assertEquals(true, $agent->exist("key"));
    }

    public function provider()
    {
        return [
            [new CacheAgent(new MemCache(null, "prefix"))],
            [new CacheAgent(new FileCache)],
            [new CacheAgent(new SimpleCache)]
        ];
    }
}
