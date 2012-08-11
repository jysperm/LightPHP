##LightPHP 概述
LightPHP(光进程)是一个迷你而强大的PHP类库，致力于协助开发者快速地构建网站.

它包含了：

* 登录状态管理(lpAuth)
* 数据库封装(lpMySQL、lpSQLRs)
* 模板引擎(lpTemplate)
* MVC辅助设计工具(lpMVC)
* 其他组件(lpGlobal、lpOptions)

###文件扩展名
LightPHP使用了一些自有的扩展名来区分文件类型：

* .class.php  -  类定义文件，其中的类名与文件名同名
* .template.php  -  前端模板
* .handler.class.php  -  页面后端处理器
* .txt.php  -  本质是一个纯文本文件

###命名
LightPHP顶级的文件和目录名均以小写的`lp`开头.  
所有存在于全局命名空间中的变量、类、函数同样均以小写的`lp`开头.

###[文件列表](./文件列表.md)
介绍每个文件的用途.

##各组件概述
###[lpGlobal](./lpGlobal.md)
该类为整个LightPHP提供了一个基础的运行环境.

###[lpMySQL](./lpMySQL.md)
该类提供了到MySQL数据库的连接；还提供了解析包含占位符的SQL的功能；以及简单的、无需SQL的查、改、增、删功能.

###[lpSQLRs](./lpSQLRs.md)
该类表示一个MySQL结果集.

###[lpOptions](./lpOptions.md)
该类提供了一种简单的、全局的、键/值对应的储存系统.可以几乎无差别地读写MySQL数据库和INI文件.

###[lpAuth](./lpAuth.md)
该类提供了登录状态管理的功能.

###[lpTemplate](./lpTemplate.md)
该类提供了一个简易的模板引擎.

###[lpMVC](./lpMVC.md)
该类提供了一个简易的MVC控制组件.
