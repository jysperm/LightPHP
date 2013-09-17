<?php

class lpFactoryTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        lpFactory::register("Test", function() {
            return "test";
        });

        $this->assertEquals("test", lpFactory::get("Test"));

        lpFactory::register("User", function($tag) {
            return "user{$tag}";
        });

        $this->assertEquals("user42", lpFactory::get("User", 42));
    }
}
