<?php
use Phalcon\Mvc\Model\MetaData\Redis as RedisAdapter;

$redis = new RedisAdapter(
    array(
        "prefix"        =>  'chess_',       //前缀
        "lifetime"      =>  86400,          //有效期，单位秒
        "host"          =>  '127.0.0.1',    //服务器地址
        "port"          =>  '6379',         //端口
        "persistent"    =>  false,          //是否长连接
    )
);

return $redis;
