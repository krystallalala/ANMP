# ANMP
docker部署开发环境（Alpine + Nginx + MySQL + PHP-FPM）
#### 关于使用
##### 1. 拷贝并命名配置文件 （Mac系统），启动：
```shell
$ cd anmp                                               # 进入项目目录
$ cp env.sample .env                                    # 复制环境变量文件
$ cp docker-compose.sample.yml docker-compose.yml       # 复制 docker-compose 配置文件。启动服务（Nginx、PHP7、MySQL8、Redis）
$ docker-compose up                                     # 启动
```
##### 2.容器内使用composer命令
使用composer命令的其中一种方式是：进入容器（PHP7容器为例），再执行`composer`命令。
```shell
# 在Ubuntu系统中，使用 bash
# 在轻量Linux系统-alpine中，使用 /bin/sh

docker exec -it php /bin/sh
cd /www/localhost
composer update
```
##### 3.服务器启动和构建命令
如果需要管理服务容器，请在命令后面加上服务容器的名称，如：
```shell
$ docker-compose up                         # 创建并启动所有容器
$ docker-compose up -d                      # 创建并以后台运行方式启动所有容器
$ docker-compose up nginx php mysql         # 创建并启动nginx、php、mysql的多个容器
$ docker-compose up -d nginx php mysql      # 创建并以后台运行方式启动nginx、php、mysql、容器


$ docker-compose start php      # 启动服务
$ docker-compose stop php       # 停止服务  
$ docker-compose restart php    # 重启服务
$ docker-compose build php      # 构建或重新构建服务


$ docker-compose rm php         # 删除并停止php容器
$ docker-compose down           # 停止并删除容器、网络、图像和挂载卷



$ docker ps             # 查看所有运行中的容器
$ docker ps -a          # 所有容器


$ docker exec -it nginx /bin/sh     # 进入nginx容器
$ docker exec -it php /bin/sh       # 进入php容器
$ docker exec -it mysql /bin/bash   # 进入mysql容器
$ docker exec -it redis /bin/sh     # 进入redis容器
```
##### 4.关于日志Log
Log文件生成的位置 以 conf文件下的个log配置的值为准。
###### 4.1 Nginx日志
Nginx日志是我们用得最多的日志，在环境变量文件中会将对应日志路径映射到Nginx容器的`/var/log/nginx`目录，所以在Nginx配置文件中需要输出log内容的位置，配置到`/var/log/nginx`目录，如：
```conf
# 在./services/nginx/conf.d/localhost.conf中：

error_log   /var/log/nginx.nginx.localhost.error.log warn;
```
###### 4.2 PHP-FPM日志
大部分情况下，PHP-FPM的日志都会输出到Nginx的日志中，所以不需要额外的配置。
另外建议直接在PHP中打开错误日志(通常在入口文件index.php中)：
```php
error_reporting(E_ALL);
ini_set('error_reporting', 'on');
ini_set('display_errors', 'on');
```
###### 4.3 MySQL日志
因为MySQL容器中的MySQL是使用`mysql`用户启动的，它无法自行在`/var/log`下增加日志文件，所以我们把MySQL日志放在与data一样的目录中，即项目的mysql目录下，对应容器中的`/var/lib/mysql/`目录。
```cnf
slow-query-log-file     = /var/lib/mysql/mysql.slow.log
log-error               = /var/lib/mysql/mysql.error.log
```
##### 5. 如何连接MySQL和Redis服务器
###### 5.1 在PHP代码中
```php
// 连接MySQL
$dbh = new PDO('mysql:host=mysql;dbname=mysql', 'root', '123456');

// 连接Redis
$redis = new Redis();
$redis->connect('redis', 6379);
```
> 容器与容器之间是用`expose`端口联通的，而且是在同一个 `networks`下，所以连接中的`host`参数直接用容器名称，`port`参数使用容器内部的端口。

###### 5.2 在主机中使用命令行 或者 数据库管理工具 连接
首先，主机要连接mysql和redis，要求容器必须先经过`docker-compose.yml`中的`ports`配置将端口映射到主机
然后连接如下：
```shell
# 进入mysql容器
$ docker exec -it mysql /bin/bash 
# 连接
$ mysql -h127.0.0.1 -uroot -p123456 -P3306


# 进入redis容器
$ docker exec -it redis /bin/sh 
# 连接
$ redis-cli -h127.0.0.1
```
这里的`host`参数不能用localhost，是因为**它默认是通过sock文件与mysql通信，而容器和主机文件系统已经隔离**，所以需要通过TCP方式连接，所以需要指定IP。

---
##### 附录：shell命令
###### 重启Nginx
```shell
$ docker exec -it nginx nginx -s reload
```
这里两个`nginx`，第一个是容器名，第二个是容器中的`nginx`程序。
###### 安装PHP扩展
> 除PHP内置扩展外，在`env.sample`文件中我们近默认安装少量扩展: `pdo_mysql,mysqli,mbstring,gd,curl,opcache,redis`。

如果需要安装更多扩展：
STEP 1：打开环境变量文件（`.env`）修改如下的PHP配置（可用的扩展请查看同文件的注释块说明）。
```
PHP_EXTENSIONS=pdo_mysql,mysqli,mbstring,gd,curl,opcache,redis
# PHP 要安装的扩展列表，英文逗号隔开
```
STEP 2：重新build PHP 镜像。
```
docker-compose build php
```



#### 参考来源
https://github.com/shunhua/dnmp