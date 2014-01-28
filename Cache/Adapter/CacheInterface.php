<?php

namespace LightPHP\Cache\Adapter;

interface CacheInterface
{
    public function set($key, $value, $ttl);
    public function get($key);
    public function delete($key);
    public function exist($key);
    public function driver();
}
