#示例代码
##概述
这里的代码不是供你运行起来玩的，而是供你阅读、copy的.  
甚至这里很多代码根本无法运行.

请仔细阅读其中的注释，所有说明性注释都在被注释的代码之前.  
同时请阅读文档(`Doc`目录)中的函数参考.

最后建议您按照该目录的顺序来阅读示例，这会对您理解代码很有帮助.

##数据库封装
###[db.sql](./db.sql)
该套示例中用到的数据库，直接导入MySQL即可.

###[connect-to-database](./connect-to-database.php)
使用lpMySQL连接到数据库.

###[execute-sql](./execute-sql.php)
执行SQL查询.

###[get-resultset](./get-resultset.php)
获取结果集.

###[without-sql](./without-sql.php)
无需SQL的数据库查、改、增、删.

##工具
###[options](./options.php)
简单键值对储存系统.

###[auth](./auth.php)
登录状态管理组件.

##模板引擎和MVC
###[template](./template.php)
使用模板引擎.

###[template.template.php](./template.template.php)
编写模板文件.

###[default-template](./default-template.php)
以及`README.html`  
使用默认模板

###[mvc](./mvc.php)
MVC路由器.
