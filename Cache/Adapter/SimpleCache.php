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
        $key = isset($this->config["prefix"]) ? "{$this->config["prefix"]}{$key}" : $key;
        $ttl = $ttl <= 0 ? $this->ttl : $ttl;
        $expired = $ttl ? time() + $ttl : PHP_INT_MAX;
        $this->cache[$key] = [$value, $expired];
    }

    public function get($key)
    {
        $key = isset($this->config["prefix"]) ? "{$this->config["prefix"]}{$key}" : $key;
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
        $key = isset($this->config["prefix"]) ? "{$this->config["prefix"]}{$key}" : $key;
        unset($this->cache[$key]);
    }

    public function exist($key)
    {
        $key = isset($this->config["prefix"]) ? "{$this->config["prefix"]}{$key}" : $key;
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
