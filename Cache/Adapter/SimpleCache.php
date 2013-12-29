<?php

namespace LightPHP\Cache\Adapter;

use LightPHP\Cache\AbstractCache;

class SimpleCache extends AbstractCache
{
    private $cache = [];

    public function __construct($server = null, $ttl = 0, array $config = [])
    {
        parent::__construct($server, $ttl, $config);
    }

    public function set($key, $value, $ttl = -1)
    {
        $ttl = $ttl <= 0 ? $this->ttl : $ttl;
        $expired = $ttl ? time() + $ttl : PHP_INT_MAX;
        $this->cache[$key] = [$value, $expired];
    }

    public function get($key)
    {
        list($value, $expired) = $this->cache[$key];

        if(time() > $expired)
        {
            unset($this->cache[$key]);
            return null;
        }
        else
        {
            return $value;
        }
    }

    public function delete($key)
    {
        unset($this->cache[$key]);
    }

    public function exist($key)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($value, $expired) = $this->cache[$key];

        if(time() > $expired)
        {
            unset($this->cache[$key]);
            return false;
        }
        else
        {
            return true;
        }
    }
}
