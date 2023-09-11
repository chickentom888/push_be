<?php

namespace Dcore\Modules\Cli\Tasks;


use Phalcon\Cli\Task;

class MainTask extends Task
{
    public function mainAction()
    {
        echo "Congratulations! You are now flying with Phalcon CLI!";
    }
}
