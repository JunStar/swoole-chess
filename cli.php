<?php
use Phalcon\Di\FactoryDefault\Cli as CliDI,
    Phalcon\Cli\Console as ConsoleApp;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

define('VERSION', '1.0.0');

// 使用CLI工厂类作为默认的服务容器
$di = new CliDI();



// 定义应用目录路径
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__)));

/**
 * 注册类自动加载器
 */
$loader = new \Phalcon\Loader();
$loader->registerDirs(
    array(
        APPLICATION_PATH . '/app/cli',
        APPLICATION_PATH . '/app/models',
        APPLICATION_PATH . '/app/lib',
    )
);
$loader->register();

// 加载配置文件（如果存在）
if (is_readable(APPLICATION_PATH . '/config/config.php')) {
    $config = include APPLICATION_PATH . '/config/config.php';
    $di->set('config', $config);
}

$di->set('common', function(){
    $common = new common();
    return $common;
});

// Setup the database service
$di->set('db', function () {
    return new DbAdapter(array(
        "host"     => "localhost",
        "username" => "root",
        "password" => "123456",
        "dbname"   => "chess",
        "charset" => "utf8"
    ));
});

$di->set('PlayIdFdUserIdRel', function(){
    $PlayIdFdUserIdRel = new PlayIdFdUserIdRel();
    return $PlayIdFdUserIdRel;
});

// 创建console应用
$console = new ConsoleApp();
$console->setDI($di);

/**
 * 处理console应用参数
 */
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

// 定义全局的参数， 设定当前任务及动作
define('CURRENT_TASK',   (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    // 处理参数
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}
