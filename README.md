## LightPHP
A Light PHP Library

LightPHP 致力于用 20% 的代码实现 80% 的功能，至于剩下 20% 的功能，LightPHP 就不管了，这意味着 LightPHP 非常轻量级。

LightPHP 是由若干组件组成的，它们彼此联系并不紧密，你可以单独地使用它们，因此 LightPHP 是一个库而非框架。
LightPHP 在全局空间内的所有标识符均以 `lp` 开头，因此并不影响你与其他框架一同使用。

LightPHP 在设计上充分利用了 PHP 最为好用的两个特征：数组和匿名函数。
因此 LightPHP 选择由抛掉了大量历史包袱的 PHP 5.4 进行编写。

### 功能

* 接管错误处理(可选)
* 对象构造器
* 配置文件和 Session 管理，用户登录状态管理
* MVC 分层模型，无需 SQL 的数据库读写(支持 MySQL 和 Mongo)
* 插件机制
* 缓存(APC, MemCache)
* HTML 模版解析
* 国际化，邮件，锁

### 运行环境
[![Build Status](https://travis-ci.org/jybox/LightPHP.png?branch=master)](https://travis-ci.org/jybox/LightPHP)

* PHP 5.4 / 5.5
* Linux
* PHP-FPM / Apache2

所需 PHP 扩展(来自 PECL)：

* mongo (被 lpMongoModel 依赖)
* apc (被 lpAPCCache 依赖)
* apcu (被 PHP 5.5 下的 lpAPCCache 依赖)
* memcache (被 lpMemCache 依赖)
* pdo (被 lpPDOModel 依赖)
* curl (被 lpMandrillMailer 依赖)

所需环境(均为可选)：

* mongod
* mysql
* memcache

### 开发进度

* 预计于 2013.10.8 发布 v6.0.

### 授权

* 核心代码以 GPLv3 授权
* 测试用例和示例不保留版权

欢迎协助改进代码或提交 Issue.

### 协作者

* 精英王子(<http://jyprince.me>)