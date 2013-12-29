<?php

namespace LightPHP\Cache\Driver;

use LightPHP\Cache\AbstractCache;
use LightPHP\Cache\Exception\MethodNotSupportException;

class MemCache extends AbstractCache
{
    /** @var \Memcache $memcache */
    private $memcache = null;

    public function __construct($server = null, $ttl = 0, array $config = [])
    {
        $server = $server ?: ["127.0.0.1" => 11211];

        $this->memcache = new \Memcache;

        foreach($server as $host => $port)
            $this->memcache->addserver($host, $port);

        parent::__construct($server, $ttl, $config);
    }

    public function set($key, $value, $ttl = -1)
    {
        $this->memcache->set($key, $value, null, $ttl <= 0 ? $this->ttl : $ttl);
    }

    public function get($key)
    {
        $flag = false;
        $result = $this->memcache->get($key, $flag);
        if (!is_bool($flag) || !$flag)
            return $result;
        return null;
    }

    public function check($key, $setter, $ttl = -1)
    {
        $flag = false;
        $result = $this->memcache->get($key, $flag);
        if ($flag)
            return $result;

        $value = $setter();
        $this->memcache->set($key, $value, null, $ttl <= 0 ? $this->ttl : $ttl);
        return $value;
    }

    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    public function exist($key)
    {
        throw new MethodNotSupportException("memcache not support exist method");
    }
}
