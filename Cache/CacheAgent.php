<?php

namespace LightPHP\Cache;

use ArrayAccess;
use LightPHP\Cache\Adapter\CacheInterface;
use LightPHP\Cache\Exception\NoDataException;

class CacheAgent implements ArrayAccess
{
    /** @var CacheInterface */
    protected $adapter;
    /** @var int $ttl */
    protected $ttl;

    /**
     * @param CacheInterface $adapter
     * @param int $ttl
     */
    public function __construct(CacheInterface $adapter, $ttl = null)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        if ($ttl === null)
            $ttl = $this->ttl;
        $this->adapter->set($key, $value, $ttl);
    }

    /**
     * @param string $key
     * @return mixed
     * @throw NoDataException
     */
    public function fetch($key)
    {
        return $this->adapter->fetch($key);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        try {
            return $this->adapter->fetch($key);
        } catch (NoDataException $e) {
            return $default;
        }
    }

    /**
     * @param string $key
     * @param callable $setter
     * @param int $ttl
     * @return mixed
     */
    public function check($key, callable $setter, $ttl = null)
    {
        try {
            $result = $this->adapter->fetch($key);
        } catch (NoDataException $e) {
            $result = $setter();

            if ($ttl === null)
                $ttl = $this->ttl;

            $this->adapter->set($key, $result, $ttl);

            return $result;
        }

        return $result;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $this->adapter->delete($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exist($key)
    {
        return $this->adapter->exist($key);
    }

    /**
     * @return CacheInterface
     */
    public function adapter()
    {
        return $this->adapter;
    }

    // --- implements ArrayAccess

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->exist($offset);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
