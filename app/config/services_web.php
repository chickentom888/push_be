<?php


use Phalcon\Cache\Adapter\Redis as RedisAdapter;
use Phalcon\Escaper;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\Router;
use Phalcon\Security;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Manager;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Url;

/**
 * Registering a router
 */
$di->setShared('router', function () {
    $router = new Router();

    $router->setDefaultModule('frontend');

    return $router;
});

/**
 * The URL component is used to generate all kinds of URLs in the application
 */
$di->setShared('url', function () {

    $config = $this->getConfig();

    $url = new Url();

    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Set the default namespace for dispatcher
 */
$di->setShared('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('Dcore\Modules\Frontend\Controllers');
    return $dispatcher;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
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

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    /*ini_set('session.gc_maxlifetime', 4*3600);
    session_set_cookie_params(4*3600);*/

    $session = new Manager();
    $files = new Stream();
    $session->setAdapter($files)->start();
    return $session;
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->setShared('flash', function () use ($di) {
    $escaper = new Escaper();
    $session = $di->getShared('session');

    $cssClasses = [
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ];
    $flash = new FlashSession($escaper, $session);
    $flash->setCssClasses($cssClasses);
    return $flash;
});


$di->set('crypt', function () {
    $crypt = new Phalcon\Crypt();
    $crypt->setKey('-#1+%&/k5l6&olr$');
    return $crypt;
});

$di->setShared('cookies', function () {
    $cookies = new Cookies();
    $cookies->useEncryption(true);
    return $cookies;
});

$di->setShared('security', function () {
    $security = new Security();
    $security->setWorkFactor(12);
    return $security;
});