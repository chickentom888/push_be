<?php

namespace Dcore\Library;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;

class Log
{
    public static function createLog($message, $type = 'error')
    {
        $date = date('d-m-Y');
        $logsDir = BASE_PATH . "/logs/$date";
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0777, true);
        }
        $logsFile = $logsDir . "/log.txt";
        $adapter = new Stream($logsFile);
        $logger = new Logger('messages', [
                'main' => $adapter,
            ]
        );
        $logger->{$type}($message);
    }
}