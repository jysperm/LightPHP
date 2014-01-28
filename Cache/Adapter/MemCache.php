<?php

namespace LightPHP\Cache\Adapter;

use LightPHP\Cache\Exception\NoDataException;
use LightPHP\Core\Exception;
use Memcached;

class MemCache implements CacheInterface
{
    /** @var Memcached $memcache */
    protected $memcached;
    /** @var string $prefix */
    protected $prefix;

    /**
     * @param array $servers ["127.0.0.1" => 11211]
     * @param string $prefix
     */
    public function __construct($servers = null, $prefix = "")
    {
        $servers = $servers ? : ["127.0.0.1" => 11211];

        $this->memcached = new Memcached;
        $this->prefix = $prefix;

        foreach ($servers as $host => $port)
            $this->memcached->addserver($host, $port);
    }

    /**
     * @return Memcached
     */
    public function driver()
    {
        return $this->memcached;
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
        $this->memcached->set("{$this->prefix}{$key}", $value, $ttl);
    }

    /**
     * @param string $key
     * @throws Exception
     * @throws NoDataException
     * @return mixed
     */
    public function get($key)
    {
        $result = $this->memcached->get("{$this->prefix}{$key}");
        $status = $this->memcached->getResultCode();

        if($status == Memcached::RES_SUCCESS)
            return $result;
        else if($status == Memcached::RES_NOTFOUND)
            throw new NoDataException;
        else
            throw new Exception("memcached get failed", ["code" => $status]);
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $this->memcached->delete("{$this->prefix}{$key}");
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
