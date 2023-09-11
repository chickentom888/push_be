<?php

namespace Dcore\Modules\Cli\Tasks;

use Exception;
use Httpful\Exception\ConnectionErrorException;
use RedisException;

class CronTask extends TaskBase
{

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    /**
     * @throws RedisException
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function minuteAction()
    {
        $stakingTask = new StakingTask();
        $transactionTask = new TransactionTask();
        $registryTask = new RegistryTask();
        $tokenTask = new TokenTask();
        $transactionTask->initialize();
        $transactionTask->minuteAction();
        $stakingTask->minuteAction();
        $registryTask->updateRateAction();
        $tokenTask->minuteAction();
    }
}