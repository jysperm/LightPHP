<?php

namespace LightPHP\Model\Wrapper;

use LightPHP\Cache\AbstractCache;
use LightPHP\Model\Model;

class CachedModel extends Model
{
    /** @var AbstractCache */
    public static $cache = null;

    protected static $cacheDriver = null;

    public function findOne(array $if = [], array $options = [])
    {
        if (!$options) {
            if (!static::$primary)
                static::q();
            $primary = static::$primary;

            if (array_keys($if) == [$primary]) {
                $class = get_called_class();
                $table = $class::$table;

                $cacheKey = "[lgCachedModel]{$table}({$primary}:{$if[$primary]})";

                return self::$cache->check($cacheKey, function () use ($if) {
                    return parent::findOne($if);
                });
            }
        }

        return parent::findOne($if, $options);
    }

    public function update(array $data)
    {
        self::refreshCache($this->id);
        return parent::update($data);
    }

    public function delete()
    {
        self::refreshCache($this->id);
        return parent::delete();
    }

    public static function refreshCache($id)
    {
        $class = get_called_class();
        $table = $class::$table;

        if (!static::$primary)
            static::q();
        $primary = static::$primary;

        $cacheKey = "[lgCachedModel]{$table}({$primary}:{$id})";
        self::$cache->delete($cacheKey);
    }
} 