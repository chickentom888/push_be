<?php

namespace Dcore\Modules\Cli\Tasks;

use DCrypto\Networks\BinanceWeb3;
use MongoDB\Database;
use Phalcon\Cli\Task;
use Redis;

/**
 * Class TaskBase
 * @package Dcore\Modules\Cli\Tasks
 * @property Redis redis
 * @property Database mongo
 */
class TaskBase extends Task
{
    public $start_time;
    public $notify;
    public $msg;
    public $monitorLabel;
    /** @var BinanceWeb3 */
    public $web3;

    public function initialize($param = [])
    {
        $this->start_time = microtime(true);
        $this->notify = false;
        global $config;
        $this->monitorLabel = $config->site->label . PHP_EOL;

    }

    public function afterExecuteRoute()
    {
        $exe_time_s = round(microtime(true) - $this->start_time);
        $exe_time_m = round($exe_time_s / 60);
        $now = date('d/m/Y H:i:s');
        $msg = PHP_EOL . "Time: $exe_time_s seconds ~ $exe_time_m minutes - $now" . PHP_EOL;
        if ($this->msg) {
            $msg = PHP_EOL . PHP_EOL . $this->msg . PHP_EOL . $msg;
        }
        echo $msg;
    }

    public function showDebug()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
    }

    public function check_args($args, $count)
    {
        if (count($args) != $count) {
            echo 'Invalid Args' . PHP_EOL;
            die();
        }
    }

    public function check_file($file)
    {
        if (!file_exists($file)) {
            echo 'Invalid File' . PHP_EOL;
            die();
        }
    }


    public function getInput($prompt = "Input: ")
    {
        echo $prompt;
        system('stty -echo');
        $text = trim(fgets(STDIN));
        system('stty echo');
        echo PHP_EOL;
        return $text;
    }

}
