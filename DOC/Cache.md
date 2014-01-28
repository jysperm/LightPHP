# 缓存

## 文件清单

    LightPHP/
        Adapter/
            CacheInterface.php
            FileCache.php
            MemCache.php
            SimpleCache.php

        Exception/
            NoDataException.php

        CacheAgent.php

## 配适器 (Adapter)

### FileCache
FileCache 提供了一种基于文件的缓存机制，它将会在指定的目录下，以键名的 MD5 值为文件名创建文件，并将值和过期时间序列化后保存到文件中。
FileCache 需要对指定的目录(默认为 `/tmp/lpFileCache`)有读写权限。

### MemCache
MemCache 提供了对 Memcached 的访问，MemCache 依赖 PHP 的 memcached 扩展。

### SimpleCache
SimpleCache 将数据储存在当前 PHP 进程的一个变量中，如果在一次请求期间多次使用某数据，还是可以起到一些作用的。
SimpleCache 提供了最简单的缓存机制，因此它总是可用。

## 示例
CacheAgent 是缓存部分的对外接口，我们总是需要创建一个 CacheAgent 实例。

    // 实例化时还需要传递一个配适器实例
    $cacheAgent = new CacheAgent(new SimpleCache);

    // 缓存数据
    $cacheAgent->set("key", "value");

    // 获取数据
    try {
        print $cacheAgent->get("key");
    }
    catch(NoDataException $e) {
        print "No data";
    }

    // 获取数据，如果不存在即设置缓存
    print $cacheAgent->check("key", function() {
        return "value";
    });

## 更多实例

    // 每个配适器都有不同的参数
    // 例如 FileCache 可以指定储存路径，以及要在 key 前自动添加的前缀
    $cacheAgent = new CacheAgent(new FileCache("/data/cache", "prefix"));

    // 实例化 CacheAgent 时可以指定默认过期时间
    $cacheAgent = new CacheAgent(new MemCache, 3600);

    // CacheAgent 的 adapter() 可以获取其配适器
    // 配适器的 driver() 可以获取其后端驱动
    print $cacheAgent->adapter()->driver()->getOption(Memcached::OPT_POLL_TIMEOUT);

    // CacheAgent 也支持通过数组的形式设置和获取数据
    $cacheAgent["key"] = "value";
    print $cacheAgent["key"];

    // 检查数据是否存在
    print $cacheAgent->exist("key");
    print isset($cacheAgent["key"]);

## 编写配适器
编写自己的配适器需要实现 CacheInterface 接口，以下是一个什么也不做的 FakeCache:

    class FakeCache implements CacheInterface
    {
        public function set($key, $value, $ttl)
        {

        }

        public function get($key)
        {
            throw new NoDataException;
        }

        public function delete($key)
        {

        }

        public function exist($key)
        {
            return false;
        }

        public function driver()
        {
            return null;
        }
    }
