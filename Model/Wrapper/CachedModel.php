<?php

namespace LightPHP\Model\Wrapper;

class CachedModel extends Model
{
    const DEFAULT_CACHE_DRIVER = "lpCache";

    protected static $cacheDriver = null;

    public function findOne(array $if = [], array $options = [])
    {
        if(!$options)
        {
            if(!static::$primary)
                static::q();
            $primary = static::$primary;

            if(array_keys($if) == [$primary])
            {
                $class = get_called_class();
                $table = $class::$table;

                $cacheKey = "[lgCachedModel]{$table}({$primary}:{$if[$primary]})";

                return self::getCache()->check($cacheKey, function() use($if) {
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

        if(!static::$primary)
            static::q();
        $primary = static::$primary;

        $cacheKey = "[lgCachedModel]{$table}({$primary}:{$id})";
        self::getCache()->delete($cacheKey);
    }

    /**
     * @return lpCache
     */
    protected static function getCache()
    {
        return lpFactory::get(self::$cacheDriver ?: self::DEFAULT_CACHE_DRIVER);
    }
} 