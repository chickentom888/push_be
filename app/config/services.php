<?php

use Dcore\Library\Helper;
use MongoDB\Client;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'charset' => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    return new $class($params);
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Configure the Volt service for rendering .volt templates
 */
$di->setShared('voltShared', function ($view) {
    $config = $this->getConfig();

    $volt = new VoltEngine($view, $this);
    $volt->setOptions([
        'path' => function ($templatePath) use ($config) {
            $basePath = $config->application->appDir;
            if ($basePath && substr($basePath, 0, 2) == '..') {
                $basePath = dirname(__DIR__);
            }

            $basePath = realpath($basePath);
            $templatePath = trim(substr($templatePath, strlen($basePath)), '\\/');


            $filename = basename(str_replace(['\\', '/'], '_', $templatePath), '.volt') . '.php';

            $cacheDir = $config->application->cacheDir;
            if ($cacheDir && substr($cacheDir, 0, 2) == '..') {
                $cacheDir = __DIR__ . DIRECTORY_SEPARATOR . $cacheDir;
            }

            $cacheDir = realpath($cacheDir);

            if (!$cacheDir) {
                $cacheDir = sys_get_temp_dir();
            }

            if (!is_dir($cacheDir . DIRECTORY_SEPARATOR . 'volt')) {
                @mkdir($cacheDir . DIRECTORY_SEPARATOR . 'volt', 0755, true);
            }

            return $cacheDir . DIRECTORY_SEPARATOR . 'volt' . DIRECTORY_SEPARATOR . $filename;
        },
        'stat' => true,
        'always' => true,
        'separator' => '_',
    ]);
    $compiler = $volt->getCompiler();
    $compiler->addFunction('in_array', 'in_array');
    $compiler->addFunction('number_format', 'number_format');
    $compiler->addFunction('str_replace', 'str_replace');
    $compiler->addFunction('time', 'time');
    $compiler->addFunction('date', 'date');
    $compiler->addFunction('print_r', 'print_r');
    $compiler->addFunction('_', function ($resolvedArgs) {
        return '$t->_(' . $resolvedArgs . ')';
    });
    return $volt;
});

$di->setShared('helper', function () {
    return new Helper();
});

$di->setShared('redis', function () {
    $config = $this->getConfig();
    $redis = new Redis();
    $redis->connect($config->redis->host, $config->redis->port);
    if (strlen($config->redis->authorize)) {
        $redis->auth($config->redis->authorize);
    }
    $redis->setOption(Redis::OPT_PREFIX, $config->redis->prefix);
    return $redis;
});

//MongoDB Database
$di->set('mongo', function () {
    $config = $this->getConfig();
    $port = $config->mongo->port;

    if (!strlen($config->mongo->username) || !strlen($config->mongo->password)) {
        $dsn = 'mongodb://' . $config->mongo->host . ":$port";
    } else {
        $dsn = "mongodb://" . $config->mongo->username . ":" . $config->mongo->password . "@" . $config->mongo->host . ":$port";
    }
//    $dsn .= "/" . $config->mongo->dbname;

    if (strlen($config->mongo->option)) {
        $dsn .= "?" . $config->mongo->option;
    }
    $options = ["typeMap" => ['root' => 'array', 'document' => 'array', 'array' => 'array']];
    $mongo = new Client($dsn, [], $options);
    return $mongo->selectDatabase($config->mongo->dbname);
}, true);

$di->setShared('throttler', function () use ($di) {
    return new Baka\PhalconThrottler\RedisThrottler($di->get('redis'), [
        'bucket_size' => 1,
        'refill_time' => 1, // 10m
        'refill_amount' => 1
    ]);
});

$di->setShared('throttler_legacy', function () use ($di) {
    return new Baka\PhalconThrottler\RedisThrottler($di->get('redis'), [
        'bucket_size' => 1,
        'refill_time' => 10, // 10m
        'refill_amount' => 1
    ]);
});