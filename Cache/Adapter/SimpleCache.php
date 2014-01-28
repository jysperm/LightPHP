<?php

namespace LightPHP\Cache\Adapter;

use LightPHP\Cache\Exception\NoDataException;

class SimpleCache implements CacheInterface
{
    /** @var array $data */
    private static $data = [];
    /** @var string $prefix */
    protected $prefix;

    /**
     * @param string $prefix
     */
    public function __construct($prefix = "")
    {
        $this->prefix = $prefix;
    }

    /**
     * @return SimpleCache
     */
    public function driver()
    {
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl)
    {
        $expired = ($ttl === null) ? PHP_INT_MAX : time() + $ttl;
        self::$data["{$this->prefix}{$key}"] = [$value, $expired];
    }

    /**
     * @param string $key
     * @return mixed
     * @throws NoDataException
     */
    public function fetch($key)
    {
        $key = "{$this->prefix}{$key}";

        if (isset(self::$data[$key])) {
            list($value, $expired) = self::$data[$key];

            if ($expired > time())
                return $value;
            else
                unset(self::$data[$key]);
        }

        throw new NoDataException;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        unset(self::$data["{$this->prefix}{$key}"]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exist($key)
    {
        $key = "{$this->prefix}{$key}";

        if (isset(self::$data[$key])) {
            list($value, $expired) = self::$data[$key];

            if ($expired > time())
                return true;
            else
                unset(self::$data[$key]);
        }

        return false;
    }
}
