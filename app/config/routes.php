<?php
use Phalcon\Mvc\Router;

$router = new Router();

$router->add(
    "/play/chess/([0-9]{1,3})/?",
    array(
        'controller'    =>  'play',
        'action'        =>  'chess',
        'id'            =>  1
    )
);

$router->add(
    '/play/create/(local|other)/?',
    array(
        'controller'    =>  'play',
        'action'        =>  'create',
        'type'          =>  1
    )
);

$router->add(
    '/play/localChess/?',
    array(
        'controller'    =>  'play',
        'action'        =>  'localChess',
    )
);

return $router;
