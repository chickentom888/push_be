<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use Dcore\Services\PoolContractService;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class PoolTask extends TaskBase
{
    protected string $channel = ContractLibrary::RPUB_PRESALE_CHANGE;

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    /**
     * @throws Exception
     * @var BinanceWeb3 $coinInstance
     */
    public function updateStatusAction()
    {
        $listChanged = [];
        $listPool = $this->getListPoolUpdateStatus();
        if (empty($listPool)) {
            return;
        }
        foreach ($listPool as $pool) {
            $network = $pool['network'];
            $platform = $pool['platform'];
            $poolService = PoolContractService::getInstance($network, $platform);
            $poolUpdate = $poolService->updateStatusByABI($pool, ContractLibrary::POOL);

            if ($poolUpdate) {
                $poolUpdate['round_name'] = Helper::getPoolRoundName($pool);
                $poolUpdate['round_define'] = Helper::getPoolRoundDefine($pool);
                $listChanged[(string)$pool['_id']] = $poolUpdate;
            }
        }

        if (count($listChanged)) {
            $this->redis->publish($this->channel, json_encode(array_values($listChanged)));
        }
    }

    private function getListPoolUpdateStatus()
    {
        $conditions = [
            '$or' => [
                ['start_time' => ['$lte' => time()]],
                ['auction_round.start_time' => ['$lte' => time()]],
            ],
            'current_status' => ['$in' => [ContractLibrary::PRESALE_STATUS_PENDING, ContractLibrary::PRESALE_STATUS_ACTIVE]]
        ];
        $poolCollection = $this->mongo->selectCollection('pool');
        $listPool = $poolCollection->find($conditions);
        if (empty($listPool)) {
            return [];
        }
        return $listPool->toArray();
    }
}
