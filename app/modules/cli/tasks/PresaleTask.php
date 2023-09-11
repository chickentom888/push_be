<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Services\PresaleContractService;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use RedisException;

class PresaleTask extends TaskBase
{
    protected string $channel = ContractLibrary::RPUB_PRESALE_CHANGE;

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    public function minuteAction(){
        $this->updateStatusAction();
        $this->updateUsdRaisedForPresaleAction();
    }

    /**
     * @throws Exception
     * @var BinanceWeb3 $coinInstance
     */
    public function updateStatusAction()
    {
        $listPresaleChanged = [];
        $listPresale = $this->getListPresaleUpdateStatus();
        if (empty($listPresale)) {
            return;
        }
        foreach ($listPresale as $presale) {
            $network = $presale['network'];
            $platform = $presale['platform'];

            $presaleService = PresaleContractService::getInstance($network, $platform);
            $presaleUpdate = $presaleService->updateStatusByABI($presale, ContractLibrary::PRESALE);

            if ($presaleUpdate) {
                $listPresaleChanged[(string)$presaleUpdate['_id']] = $presaleUpdate;
            }
        }

        if (count($listPresaleChanged)) {
            $this->redis->publish($this->channel, json_encode(array_values($listPresaleChanged)));
        }
    }

    private function getListPresaleUpdateStatus()
    {
        $conditions = [
            'start_time' => ['$lte' => time()],
            'current_status' => ['$in' => [ContractLibrary::PRESALE_STATUS_PENDING, ContractLibrary::PRESALE_STATUS_ACTIVE]],
            'project_type' => ContractLibrary::PRESALE
        ];
        $presaleCollection = $this->mongo->selectCollection('presale');
        $listPresale = $presaleCollection->find($conditions);
        if (!empty($listPresale)) {
            return $listPresale->toArray();
        }
        return [];
    }

    /**
     * @throws RedisException
     */
    public function updateUsdRaisedForPresaleAction()
    {
        $listPresaleChanged = [];
        $listPoolChanged = [];
        $listPlatform = [];
        $listPoolPlatform = [];
        $presaleCollection = $this->mongo->selectCollection('presale');
        $poolCollection = $this->mongo->selectCollection('pool');
        $conditions = [
            'end_time' => ['$lte' => time()],
            'current_status' => ContractLibrary::PRESALE_STATUS_SUCCESS,
            'network' => ContractLibrary::MAIN_NETWORK,
            '$or' => [
                ['usd_raised' => ['$exists' => 0]],
                ['usd_raised' => ['$lte' => 0]],
            ],
        ];
        $listPresale = $presaleCollection->find($conditions);
        $listPoolSuccess = $poolCollection->find($conditions);
        !empty($listPresale) && $listPresale = $listPresale->toArray();
        !empty($listPoolSuccess) && $listPoolSuccess = $listPoolSuccess->toArray();
        if (count($listPresale) || count($listPoolSuccess)) {
            $presaleBaseToken = $this->mongo->selectCollection('presale_setting_address')->find([
                'type' => ContractLibrary::BASE_TOKEN,
                'current_price' => ['$exists' => 1],
                'network' => ContractLibrary::MAIN_NETWORK,
            ]);
            $saleBaseToken = $this->mongo->selectCollection('sale_setting_address')->find([
                'type' => ContractLibrary::BASE_TOKEN,
                'current_price' => ['$exists' => 1],
                'network' => ContractLibrary::MAIN_NETWORK,
            ]);
            $poolBaseToken = $this->mongo->selectCollection('pool_setting_address')->find([
                'type' => ContractLibrary::BASE_TOKEN,
                'current_price' => ['$exists' => 1],
                'network' => ContractLibrary::MAIN_NETWORK,
            ]);
            !empty($presaleBaseToken) && $presaleBaseToken = $presaleBaseToken->toArray();
            !empty($saleBaseToken) && $saleBaseToken = $saleBaseToken->toArray();
            !empty($poolBaseToken) && $poolBaseToken = $poolBaseToken->toArray();
            $presaleBaseToken = Arrays::reAssignKey($presaleBaseToken, 'token_address');
            $saleBaseToken = Arrays::reAssignKey($saleBaseToken, 'token_address');
            $poolBaseToken = Arrays::reAssignKey($poolBaseToken, 'token_address');

            foreach ($listPresale as $presale) {
                $presaleInfo = [];
                $baseToken = $presaleBaseToken;
                if ($presale['project_type'] == ContractLibrary::PROJECT_TYPE_SALE) {
                    $baseToken = $saleBaseToken;
                }
                if (!empty($baseToken[$presale['base_token_address']]) && $baseToken[$presale['base_token_address']]['platform'] = $presale['platform']) {
                    $presaleInfo['usd_raised'] = $presale['total_base_collected'];
                    if ($presale['base_token_symbol'] != 'USDT') {
                        $presaleInfo['usd_raised'] *= $baseToken[$presale['base_token_address']]['current_price'];
                    }
                    $presale['usd_raised'] = $presaleInfo['usd_raised'];
                    $presaleCollection->updateOne(['_id' => $presale['_id']], ['$set' => $presaleInfo]);
                    $listPresaleChanged[(string)$presale['_id']] = $presale;
                    $listPlatform[] = $presale['platform'];
                }
            }

            foreach ($listPoolSuccess as $pool) {
                $poolInfo = [];
                if (!empty($poolBaseToken[$pool['base_token_address']]) && $poolBaseToken[$pool['base_token_address']]['platform'] = $pool['platform']) {
                    $poolInfo['usd_raised'] = $pool['total_base_collected'];
                    if ($pool['base_token_symbol'] != 'USDT') {
                        $poolInfo['usd_raised'] *= $poolBaseToken[$pool['base_token_address']]['current_price'];
                    }
                    $pool['usd_raised'] = $poolInfo['usd_raised'];
                    $poolCollection->updateOne(['_id' => $pool['_id']], ['$set' => $poolInfo]);
                    $listPoolChanged[(string)$pool['_id']] = $pool;
                    $listPoolPlatform[] = $pool['platform'];
                }
            }
        }

        if (count($listPresaleChanged)) {
            $listPlatform = array_values(array_unique($listPlatform));
            foreach ($listPlatform as $platform) {
                $cacheKeyStatistic = "presale_statistic:{$platform}_main";
                $this->redis->del($cacheKeyStatistic);
            }
            $this->redis->publish($this->channel, json_encode(array_values($listPresaleChanged)));
        }

        if (count($listPoolChanged)) {
            $listPoolPlatform = array_values(array_unique($listPoolPlatform));
            foreach ($listPoolPlatform as $platform) {
                $cacheKeyStatistic = "pool_statistic:{$platform}_main";
                $this->redis->del($cacheKeyStatistic);
            }
            $this->redis->publish($this->channel, json_encode(array_values($listPoolChanged)));
        }
    }
}
