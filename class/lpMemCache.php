<?php

class lpMemCache implements ArrayAccess
{
    private $config = [
        "ttl" => 0,
        "server" => [
            "127.0.0.1" => 11211
        ]
    ];

    /** @var Memcache */
    private $memcache = null;

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);

        $this->memcache = new Memcache;

        foreach($this->config["server"] as $host => $port)
            $this->memcache->addserver($host, $port);
    }

    public function set($k, $v, $ttl = -1)
    {
        $this->memcache->set($k, $v, null, $ttl >= 0 ? $ttl : $this->config["ttl"]);
    }

    public function get($k)
    {
        $f = false;
        $r = $this->memcache->get($k, $f);
        if(!is_bool($f) || !$f)
            return $r;
        return null;
    }

    public function check($k, $seter, $ttl = -1)
    {
        $f = false;
        $r = $this->memcache->get($k, $f);
        if($f)
            return $r;

        $v = $seter();
        $this->memcache->set($k, $v, null, $ttl >= 0 ? $ttl : $this->config["ttl"]);
        return $v;
    }

    public function delete($k)
    {
        return $this->memcache->delete($k);
    }

    /* ArrayAccess */
    public function offsetSet($offset, $value)
    {
        $this->memcache->set($offset, $value, null, $this->config["ttl"]);
    }

    public function offsetExists($offset)
    {
        return apc_exists($offset);
    }

    public function offsetUnset($offset)
    {
        return apc_delete($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}