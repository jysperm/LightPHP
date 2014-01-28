<?php

namespace LightPHP\Cache\Adapter;

use LightPHP\Cache\Exception\NoDataException;

class MemCache implements CacheInterface
{
    /** @var \Memcache $memcache */
    protected $memcache;
    /** @var string $prefix */
    protected $prefix;

    /**
     * @param array $servers ["127.0.0.1" => 11211]
     * @param string $prefix
     */
    public function __construct($servers = null, $prefix = "")
    {
        $servers = $servers ? : ["127.0.0.1" => 11211];

        $this->memcache = new \Memcache;
        $this->prefix = $prefix;

        foreach ($servers as $host => $port)
            $this->memcache->addserver($host, $port);
    }

    /**
     * @return \Memcache
     */
    public function driver()
    {
        return $this->memcache;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl)
    {
        if ($ttl === null)
            $ttl = 0;
        $this->memcache->set("{$this->prefix}{$key}", $value, null, $ttl);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws NoDataException
     */
    public function get($key)
    {
        $flag = false;
        $result = $this->memcache->get("{$this->prefix}{$key}", $flag);
        if (!is_bool($flag) || !$flag)
            return $result;
        else
            throw new NoDataException;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $this->memcache->delete("{$this->prefix}{$key}");
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exist($key)
    {
        try {
            $this->get($key);
            return true;
        }
        catch(NoDataException $e)
        {
            return false;
        }
    }
}
