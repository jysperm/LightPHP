<?php

namespace LightPHP\Cache\Driver;

use LightPHP\Cache\AbstractCache;

class FileCache extends AbstractCache
{
    public function __construct($server = null, $ttl = 0, array $config = [])
    {
        $server = $server ?: "/tmp/lpFileCache";

        if(!file_exists($server))
            mkdir($server);

        parent::__construct($server, $ttl, $config);
    }

    public function set($key, $value, $ttl = -1)
    {
        $filename = "{$this->server}/" . md5($key);
        $ttl = $ttl <= 0 ? $this->ttl : $ttl;
        $expired = $ttl ? time() + $ttl : PHP_INT_MAX;
        $data = serialize([$value, $expired]);

        $file = fopen($filename, "w+");
        flock($file, LOCK_EX);
        fwrite($file, $data);
        fclose($file);
    }

    public function get($key)
    {
        $filename = "{$this->server}/" . md5($key);
        $data = file_get_contents($filename);
        list($value, $expired) = unserialize($data);

        if(time() > $expired)
        {
            unlink($filename);
            return null;
        }
        else
        {
            return $value;
        }
    }

    public function delete($key)
    {
        $filename = "{$this->server}/" . md5($key);
        unlink($filename);
    }

    public function exist($key)
    {
        $filename = "{$this->server}/" . md5($key);
        return file_exists($filename);
    }
}
