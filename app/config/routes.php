<?php

$router = $di->getRouter();
/*  TODO: DEFINE BACKEND*/
$router->add('/admin', [
    'namespace' => 'Dcore\Modules\Admin\Controllers',
    'module' => 'admin',
    'controller' => 'index',
    'action' => 'index',
]);
$router->add('/api', [
    'namespace' => 'Dcore\Modules\Api\Controllers',
    'module' => 'api',
    'controller' => 'index',
    'action' => 'index',
]);
$router->add('/admin/login', [
    'namespace' => 'Dcore\Modules\Admin\Controllers',
    'module' => 'admin',
    'controller' => 'authorized',
    'action' => 'login',
]);

foreach ($application->getModules() as $key => $module) {
    $namespace = preg_replace('/Module$/', 'Controllers', $module["className"]);
    $router->add('/'.$key.'/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 'index',
        'action' => 'index',
        'params' => 1
    ])->setName($key);
    $router->add('/'.$key.'/:controller/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ]);
    $router->add('/'.$key.'/:controller/:action/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ]);
}
