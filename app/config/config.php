<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */

use Phalcon\Config;

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
date_default_timezone_set("Asia/Ho_Chi_Minh");
//ini_set('memory_limit', -1);

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

return new Config([
    'version' => '1.0',
    'database' => [
        'adapter' => 'Mysql',
        'host' => '',
        'username' => '',
        'password' => '',
        'dbname' => '',
        'charset' => '',
    ],

    'mongo' => [
        'host' => $_ENV['MONGO_HOST'],
        'port' => $_ENV['MONGO_PORT'],
        'username' => $_ENV['MONGO_USERNAME'],
        'password' => $_ENV['MONGO_PASSWORD'],
        'dbname' => $_ENV['MONGO_DBNAME'],
        'option' => $_ENV['MONGO_OPTION'],
        'charset' => 'utf8',
    ],

    'application' => [
        'appDir' => APP_PATH . '/',
        'modelsDir' => APP_PATH . '/common/models/',
        'migrationsDir' => APP_PATH . '/migrations/',
        'cacheDir' => BASE_PATH . '/cache/',

        // This allows the baseUri to be understood project paths that are not in the root directory
        // of the webapps.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri' => "/",
        'url' => '',
        'dataDir' => BASE_PATH . '/account_store/',
        'env' => $_ENV['ENV']
    ],

    /**
     * if true, then we print a new line at the end of each CLI execution
     *
     * If we don't print a new line,
     * then the next command prompt will be placed directly on the left of the output, and it is less readable.
     *
     * You can disable this behaviour if the output of your application needs to don't have a new line at end
     */
    'printNewLine' => true,

    "site" => [
        'name' => "PushSwap",
        "coin_key" => "push",
        "coin_ticker" => "Push",
        "coin_name" => "Pushswap Token",
        'link' => $_ENV['BASE_URL'],
        'root' => BASE_PATH . '/public',
        "logo" => "/assets/images/logo.svg?v=3",
        "icon" => "/assets/images/icon.svg?v=3",
        "admin_login_token" => "",
        "twofa" => true,
        "label" => "[PUSHSWAP]",
        "file_version" => 1050
    ],

    'blockchain' => [
        'bsc_rpc_main_net' => $_ENV['BSC_RPC_MAIN_NET'],
        'bsc_rpc_test_net' => $_ENV['BSC_RPC_TEST_NET'],
        'withdraw_address' => $_ENV['WITHDRAW_ADDRESS'],
        'withdraw_private_key' => $_ENV['WITHDRAW_PRIVATE_KEY'],
        'support_liquid_address' => $_ENV['SUPPORT_LIQUID_ADDRESS'],
        'support_liquid_private_key' => $_ENV['SUPPORT_LIQUID_PRIVATE_KEY'],
        'bsc_api_url' => $_ENV['BSC_API_URL'],
        'bsc_api_key' => $_ENV['BSC_API_KEY'],
    ],

    'jwt' => "a8s7d891hdjkahsdjatsdy7asgd",
    'redis' => [
        'host' => $_ENV['REDIS_HOST'],
        'port' => $_ENV['REDIS_PORT'],
        'authorize' => $_ENV['REDIS_AUTHORIZE'],
        'prefix' => $_ENV['REDIS_PREFIX'],
    ],

    "telegram" => [
        "token" => $_ENV['TELEGRAM_TOKEN'],
        "monitor_token" => $_ENV['TELEGRAM_MONITOR_TOKEN'],
        "main_channel" => $_ENV['TELEGRAM_MAIN_CHANNEL'],
        "monitor_channel" => $_ENV['TELEGRAM_MONITOR_CHANNEL'],
    ],
    'swap' => [
        'base_url' => $_ENV['SWAP_API_URL'],
        'fee_receipt' => $_ENV['SWAP_FEE_RECEIPT']
    ]
]);
