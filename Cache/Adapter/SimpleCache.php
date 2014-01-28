<?php

namespace LightPHP\Cache\Adapter;

use LightPHP\Cache\Exception\NoDataException;

class SimpleCache implements CacheInterface
{
    /** @var array $data */
    private $data = [];
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
        $this->data["{$this->prefix}{$key}"] = [$value, $expired];
    }

    /**
     * @param string $key
     * @return mixed
     * @throws NoDataException
     */
    public function get($key)
    {
        $key = "{$this->prefix}{$key}";

        if(isset($this->data[$key]))
        {
            list($value, $expired) = $this->data[$key];

            if ($expired > time())
                return $value;
            else
                unset($this->data[$key]);
        }

        throw new NoDataException;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        unset($this->data["{$this->prefix}{$key}"]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exist($key)
    {
        $key = "{$this->prefix}{$key}";

        if(isset($this->data[$key]))
        {
            list($value, $expired) = $this->data[$key];

            if ($expired > time())
                return true;
            else
                unset($this->data[$key]);
        }

        return false;
    }
}
