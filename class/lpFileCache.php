<?php

class lpFileCache implements ArrayAccess
{
    private $config = [
        "ttl" => 0,
        "path" => "/tmp/lpFileCache"
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function set($k, $v, $ttl = -1)
    {
        if(!file_exists($this->config["path"]))
            mkdir($this->config["path"]);

        $filename = "{$this->config["path"]}/" . md5($k) . "." . strval($ttl >= 0 ? time() + $ttl : PHP_INT_MAX) . ".cache";

        file_put_contents($filename, serialize($v));
    }

    public function get($k)
    {

    }

    public function check($k, $seter, $ttl = -1)
    {

    }

    public function delete($k)
    {

    }

    /* ArrayAccess */
    public function offsetSet($offset, $value)
    {
        apc_store($offset, $value, $this->config["ttl"]);
    }

    public function offsetExists($offset)
    {
        
    }

    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}