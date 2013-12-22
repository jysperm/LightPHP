## LightPHP API
这里只介绍了 LightPHP 大致的结构，关于具体类和函数的使用方法，请参见源代码中的 PHPDoc 注释。

### App
该分组是 LightPHP 的核心组成部分。

* [较稳定] lpApp 提供全局层面的资源管理，还负责进行路由
* [稳定] lpFactory 对象构造器
* [较稳定] lpHandler 处理器基类
* lpRouter 更高级的路由功能

### [开发中] Cache
该分组提供了基于各种数据源的缓存功能，它们具有相似的接口。

* lpAPCCache
* lpFileCache
* lpMemCache

### [较稳定] Exception
该分组包含了各种异常类型。

* lpException
* lpHandlerException
* lpPHPException
* lpPHPFatalException
* lpSQLException

### [较稳定] Locale
该分组提供了基于各种数据源的国际化功能，它们具有相似的接口。

* lpArrayLocale
* lpGetTextLocale
* lpJSONLocale

### [开发中] Lock
该分组提供了各种底层实现的锁，它们具有相似的接口。

* lpFileLock
* lpMutex
* lpMySQLLock

### [开发中] Mailer
该分组提供了基于各种底层的邮件发送器，它们具有相似的接口。

* lpMandrillMailer
* lpPHPMailer
* lpSmtpMailer

### Model
该分组提供了基于各种数据库的，基于 PHP 数组的数据库读写功能，它们具有相似的接口。

* [较稳定] lpMongoModel
* [稳定] lpPDOModel

### [不稳定] pluggable

* lpPluggableHandler
* lpPlugin

### [较稳定] Template
该分组提供了几种模版引擎。

* lpCompiledTemplate
* lpPHPTemplate

### Tool
该分组提供了一些与 LightPHP 核心联系不太紧密的工具。

* [较稳定] lpConfig 配置信息管理
* [开发中] lpDebug 调试和错误处理
* [开发中] lpSession
* [较稳定] lpValider