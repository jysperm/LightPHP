<?php

namespace LightPHP\Cache\Adapter;

use LightPHP\Cache\Exception\NoDataException;

class FileCache implements CacheInterface
{
    /** @var string $path */
    protected $path;
    /** @var string $prefix */
    protected $prefix;

    /**
     * @param string $path "/tmp/lpFileCache"
     * @param string $prefix
     */
    public function __construct($path = null, $prefix = "")
    {
        $path = $path ? : "/tmp/lpFileCache";

        $this->path = $path;
        $this->prefix = $prefix;

        if (!file_exists($path))
            mkdir($path);
    }

    /**
     * @return FileCache
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
        $filename = "{$this->path}/" . md5("{$this->prefix}{$key}");

        $data = serialize([$value, $expired]);

        $file = fopen($filename, "w+");
        flock($file, LOCK_EX);
        fwrite($file, $data);
        fclose($file);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws NoDataException
     */
    public function fetch($key)
    {
        $filename = "{$this->path}/" . md5("{$this->prefix}{$key}");

        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            list($value, $expired) = unserialize($data);

            if ($expired > time())
                return $value;
            else
                unlink($filename);
        }

        throw new NoDataException;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $filename = "{$this->path}/" . md5("{$this->prefix}{$key}");
        unlink($filename);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exist($key)
    {
        $filename = "{$this->path}/" . md5("{$this->prefix}{$key}");
        return file_exists($filename);
    }
}
