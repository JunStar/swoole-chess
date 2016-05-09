<?php
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

$db = new DbAdapter(
    array(
        "host"     => "localhost",
        "username" => "root",
        "password" => "123456",
        "dbname"   => "chess",
        "charset"  => "utf8"
    )
);

return $db;
