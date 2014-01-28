<?php

namespace LightPHP\Cache\Adapter;

interface CacheInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     */
    public function delete($key);

    /**
     * @param string $key
     * @return bool
     */
    public function exist($key);

    /**
     * @return mixed
     */
    public function driver();
}
