# swoole-chess
PHP+Swoole+Phalcon+Redis+Html5+Javascript实现的中国象棋在线对弈程序
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
`//进入本地源码包文件夹，平常会要使用的源码包有很多，定义一个文件夹来放置所有的源码包，用来存储所有的源码包，比如redis-server,phpredis等，没有此文件夹的话，可以创建一个
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
`