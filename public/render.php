<?php

use function OpenApi\scan;

error_reporting(0);
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
require(__DIR__ . "/../vendor/autoload.php");
if (!file_exists(__DIR__ . "/swagger.json") || $_GET['refresh']) {
    $openapi = scan(__DIR__ . '/../app');
    $d = $openapi->toJson();
    file_put_contents(__DIR__ . "/swagger.json", $d);
}

header("Content-Type: application/json");

echo file_get_contents(__DIR__ . "/swagger.json");
