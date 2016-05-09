<?php
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Session\Adapter\Files as Session;

// 定义应用目录路径
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__)));

try {
    // Register an autoloader
    $loader = new Loader();
    $loader->registerDirs(array(
        APPLICATION_PATH . '/app/controllers',
        APPLICATION_PATH . '/app/models',
        APPLICATION_PATH . '/app/lib',
    ))->register();

    // Create a DI
    $di = new FactoryDefault();

    // Setup the database service
    $di->set('db', function () {
        require __DIR__.'/app/config/db.php';
        return $db;
    });

    $di->set('common', function(){
        $common = new common();
        return $common;
    });

    $di->setShared('session', function () {
        $session = new Session();
        $session->start();
        return $session;
    });

    // Setup the view component
    $di->set('view', function () {
        $view = new View();
        $view->setViewsDir('app/views/');
        //使用模板引擎
        //所有后缀为phtml的视图文件都会经过模板引擎Volt的处理
        $view->registerEngines(
        array(
            ".phtml" => 'Phalcon\Mvc\View\Engine\Volt'
            )
        );
        return $view;
    });

    // Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set('url', function () {
        $url = new UrlProvider();
        $url->setBaseUri('/');
        return $url;
    });

    // 注册路由
    $di->set(
        'router',
        function () {
            require __DIR__.'/app/config/routes.php';
            return $router;
        }
    );

    // Handle the request
    $application = new Application($di);

    echo $application->handle()->getContent();

} catch (\Exception $e) {
     echo "Exception: ", $e->getMessage();
}
