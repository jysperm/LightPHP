<?php

namespace LightPHP\Cache;

use ArrayAccess;

/**
 * Class AbstractCache
 * @package LightPHP\Cache
 *
 * 该类是所有缓存驱动的基类
 */
abstract class AbstractCache implements ArrayAccess
{
    /** @var int $ttl 默认 TTL */
    protected $ttl;
    /** @var mixed 缓存服务器信息 */
    protected $server;
    /** @var array $config 配置信息 */
    protected $config;

    /**
     * @param mixed $server
     * @param int $ttl
     * @param array $config
     */
    public function __construct($server = null, $ttl = 0, array $config = [])
    {
        $this->server = $server;
        $this->ttl = $ttl;
        $this->config = $config;
    }

    /**
     * 缓存一项逐句
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl = -1)
    {

    }

    /**
     * 获取一项数据
     *
     * 如果不存在该项缓存，将返回 null.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(/** @noinspection PhpUnusedParameterInspection */ $key)
    {
        return null;
    }

    /**
     * 尝试获取一项数据
     *
     * 若该项不存在将调用 $setter 获取数据并缓存。
     *
     * @param string $key
     * @param callable $setter
     * @param int $ttl
     * @return mixed
     */
    public function check($key, callable $setter, $ttl = -1)
    {
        $result = $this->get($key);

        if(!$result)
        {
            $result = $setter;
            $this->set($key, $setter, $ttl);
        }

        return $result;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {

    }

    /**
     * @param $key
     * @return bool
     */
    public function exist($key)
    {
        return false;
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
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
} 