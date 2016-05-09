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