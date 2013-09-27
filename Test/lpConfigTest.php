<?php

class lpConfigTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $cfg = new lpConfig;

        $tmpFile = tempnam("/tmp", "lp");
        $data = <<< PHP
<?php
return [
    "key1" => "value1",
    "key2" => "value2"
];

PHP;
        file_put_contents($tmpFile, $data);

        $cfg->loadFromPHPFile($tmpFile);
        $cfg->loadFromArray([
            "key2" => null,
            "key3" => "value3"
        ]);

        $this->assertEquals(null, $cfg->get("key2"));
        $this->assertEquals("value3", $cfg["key3"]);

        $cfg["key2"] = "value2";

        $this->assertEquals([
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3"
        ], $cfg->data());

        unlink($tmpFile);
    }
}
