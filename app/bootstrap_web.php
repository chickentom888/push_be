<?php

use Dcore\Library\Log;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

/*ini_set('display_errors', 1);
error_reporting(E_ALL);*/

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();
    require APP_PATH . '/../vendor/autoload.php';

    /**
     * Include general services
     */
    require APP_PATH . '/config/services.php';

    /**
     * Include web environment specific services
     */
    require APP_PATH . '/config/services_web.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new Application($di);

    /**
     * Register application modules
     */
    $application->registerModules([
        'frontend' => ['className' => 'Dcore\Modules\Frontend\Module'],
        'admin' => ['className' => 'Dcore\Modules\Admin\Module'],
        'api' => ['className' => 'Dcore\Modules\Api\Module'],
    ]);

    /**
     * Include routes
     */
    require APP_PATH . '/config/routes.php';
    $request = new Phalcon\Http\Request();
    echo $application->handle($request->getURI())->getContent();

} catch (Exception $e) {
    /*echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';*/
    Log::createLog($e->getMessage());
}
