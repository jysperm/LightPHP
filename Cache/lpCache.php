<?php

class lpCache implements ArrayAccess
{
    /** @var int $ttl */
    protected $ttl;
    protected $server;
    /** @var array $config */
    protected $config;

    public function __construct($server = null, $ttl = 0, array $config = [])
    {
        $this->server = $server;
        $this->ttl = $ttl;
        $this->config = $config;
    }

    public function set($key, $value, $ttl = -1)
    {

    }

    public function get($key)
    {
        return null;
    }

    public function check($key, $setter, $ttl = -1)
    {
        $result = $this->get($key);

        if(!$result)
        {
            $result = $setter;
            $this->set($key, $setter, $ttl);
        }

        return $result;
    }

    public function delete($key)
    {

    }

    public function exist($key)
    {
        return false;
    }

    // --- implements ArrayAccess

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->exist($offset);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
} 