<?php

use Phalcon\Loader;

$loader = new Loader();
/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'Dcore\Models' => APP_PATH . '/common/models/',
    'Dcore\Services' => APP_PATH . '/common/services/',
    'Dcore\Library' => APP_PATH . '/library/',
    'Dcore\ControllerBase' => APP_PATH . '/common/controllers',
    'Dcore\Collections' => APP_PATH . '/common/collections',
    'DCrypto' => APP_PATH . '/library/DCrypto/',
]);

/**
 * Register module classes
 */
$loader->registerClasses([
    'Dcore\Modules\Api\Module' => APP_PATH . '/modules/api/Module.php',
    'Dcore\Modules\Frontend\Module' => APP_PATH . '/modules/frontend/Module.php',
    'Dcore\Modules\Admin\Module' => APP_PATH . '/modules/admin/Module.php',
    'Dcore\Modules\Cli\Module' => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();
