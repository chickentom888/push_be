<?php

use Phalcon\Cache\Adapter\Redis as RedisAdapter;
use Phalcon\Cli\Dispatcher;
use Phalcon\Storage\SerializerFactory;

/**
 * Set the default namespace for dispatcher
 */
$di->setShared('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('Dcore\Modules\Cli\Tasks');
    return $dispatcher;
});

$di->setShared('cache', function () {
    $config = $this->getConfig();
    $time = 172800;
    $serializerFactory = new SerializerFactory();

    $options = [
        'host' => $config->redis->host,
        'port' => $config->redis->port,
        'persistent' => true,
        'lifetime' => $time,
        'prefix' => $config->redis->prefix
    ];
    if (strlen($config->redis->authorize)) {
        $options['auth'] = $config->redis->authorize;
    }

    return new RedisAdapter($serializerFactory, $options);
});