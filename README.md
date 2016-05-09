# swoole-chess

PHP+Swoole+Phalcon+Redis+Html5+Javascript实现的中国象棋在线对弈程序

Demo地址:http://chess.phpby.com

# 本地部署顺序

1.检测PHP版本，PHP版本最高支持php5.6，这是由于Phalcon框架扩展目前只支持到php5.6的限制

2.安装php的Swoole扩展

3.安装php的Phalcon扩展

4.安装Redis-server

5.安装php的Redis扩展

6.开启redis-server和Websocket服务

7.导入mysql文件

8.修改项目的配置文件

9.使用的浏览器访问项目地址，注意，浏览器需要支持Websocket通信,浏览器举例：IE9+,Firefox,Chrome等

# PHP的Swoole扩展安装

Swoole是一个PHP的网络通信框架，由C/C++编写成PHP扩展实现。

安装步骤请访问：https://github.com/swoole/swoole-src

Swoole详细介绍请访问：http://www.swoole.com/

# PHP的Phalcon扩展安装

Phalcon是一个PHP的编码框架，由C/C++编写成PHP扩展实现。

安装步骤请访问：https://docs.phalconphp.com/zh/latest/reference/install.html

详细介绍请访问：https://www.phalconphp.com/

# Redis-server安装、启动和关闭

1.安装redis-server

```
//进入本地源码包文件夹，平常会要使用的源码包有很多，
//定义一个文件夹来放置所有的源码包，用来存储所有的源码包，
//比如redis-server,phpredis等，没有此文件夹的话，可以创建一个
cd /opt/source/
//Redis-server官网下载地址：http://redis.io/download，选择下载最新的stable版本，我下载的是3.0.7
wget http://download.redis.io/releases/redis-3.0.7.tar.gz
//解压下载下来的源码包
tar xzf redis-3.0.7.tar.gz
//进入解压出来的新文件夹
cd redis-3.0.7
//安装
make
//此处已经安装完毕，不需要执行make install，将安装后的文件夹移动到/opt/目录下
mv /opt/source/redis-3.0.7 /opt/
```

2.启动redis-server


```
//进入redis-server安装目录
cd /opt/redis-3.0.7
//修改redis.conf，将daemonize的值设置为yes，这样redis-server在启动时，会以守护进程的方式在后台运行
vim redis.conf
//进入vim界面的操作步骤：
//1.输入/daemonize回车找到daemonize所在行
//2.按下i，进入编译模式，将daemonize的值修改为yes
//3.按下ESC键，退出编辑模式
//4.输入:wq回车，即保存退出了
//5.对于vim不熟悉的朋友可以查下vim的使用方法
//启动redis-server
src/redis-server redis.conf
```

3.关闭redis-server

```
//1.如果你没有修改daemonize的值为yes，或者在启动时没有带redis.conf的参数，那么直接ctrl+c即可关闭redis-server
//2.如果是以守护进程的方式运行的redis-server，则需要杀死redis-server的进程，过程如下：
//查找redis-server进程，可以找到redis-server的进程ID
ps -ef | grep redis-server
//杀死redis-server进程
sudo kill -9 redis-server进程ID
```

# php的redis扩展安装

github项目地址：https://github.com/phpredis/phpredis

详细安装步骤：

```
//各稳定版的phpredis源码包可以到pecl上下载，下载地址：
http://pecl.php.net/package/redis
//我下载的是2.2.7的stable版本,进入源码包文件夹
cd /opt/source/
//下载源码包
wget http://pecl.php.net/get/redis-2.2.7.tgz
//解压下载的redis-2.2.7.tgz
tar xzf redis-2.2.7.tgz 
//修改解压出来的目录名redis-2.2.7为phpredis-2.2.7，以便与redis-server区分 
mv redis-2.2.7 phpredis-2.2.7
//进入phpredis-2.2.7
cd phpredis-2.2.7
//开始php扩展的安装
phpize
./configure --with-php-config=php-config
make && make install
//执行完毕make install之后，成功的话会出现一个路径，请记录下此路径，我的路径是
/opt/php5.6.19/lib/php/extensions/no-debug-zts-20131226/
//修改php.ini，加上phpredis扩展的加载
vim /etc/php.ini
    //进入vim编辑模式
    //1.修改extension\_dir的值，extension\_dir的值是在make install后记录的路径
    extension\_dir = "/opt/php5.6.19/lib/php/extensions/no-debug-zts-20131226/"
    //2.增加一行，加载phpredis扩展
    extension = redis.so
```

# 开启Redis-server和Websocket服务

开启redis-server的方法请查看redis-server的启动和关闭段落

开启Websocket服务,假设现在的项目目录在/www/chess/目录下

```
cd /www/chess/
php cli.php Chesswebsocket
```

# 导入mysql文件

将文件chess.sql导入mysql中

```
mysql -u root -p
create database chess;
use chess;
source chess.sql的完整路径地址;
```

# 需要修改的配置文件

数据库配置文件:app/config/db.php

redis配置文件:app/config/redis.php

js文件中的websocket配置服务器地址配置，需要修改两个文件:

public/js/datingwebsocket.js

public/js/websocket.js

# 使用浏览器访问项目地址

http://localhost/chess/index.php
