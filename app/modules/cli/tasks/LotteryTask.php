<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;
use Dcore\Services\LotteryService;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\Collection;

class LotteryTask extends Web3Task
{
    /** @var Collection */
    public $lotteryCronCollection;

    public function initialize($param = [])
    {
        parent::initialize($param);
        $this->lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
    }

    /**
     * @throws Exception
     */
    public function processCronAction()
    {
        $this->getStatusPendingTransaction();
        $this->processPendingTransaction();
    }

    protected function getStatusPendingTransaction()
    {
        $listData = $this->lotteryCronCollection->find([
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_ACTIVE,
            'hash' => ['$exists' => true],
            'tx_status' => ContractLibrary::TRANSACTION_STATUS_PENDING
        ]);
        !empty($listData) && $listData = $listData->toArray();
        if (count($listData)) {
            foreach ($listData as $item) {
                $platform = $item['platform'];
                $network = $item['network'];
                $mainCurrency = Adapter::getMainCurrency($platform);
                /** @var BinanceWeb3 $coinInstance */
                $coinInstance = Adapter::getInstance($mainCurrency, $network);
                $hash = $item['hash'];
                $txStatus = $coinInstance->getTransactionStatus($hash);
                if ($txStatus === null) {
                    continue;
                }
                $lotteryCronData = [
                    'tx_status' => $txStatus == 1 ? ContractLibrary::TRANSACTION_STATUS_SUCCESS : ContractLibrary::TRANSACTION_STATUS_FAIL
                ];
                $this->lotteryCronCollection->updateOne(['_id' => $item['_id']], ['$set' => $lotteryCronData]);
            }

        }
    }

    /**
     * @throws Exception
     */
    protected function processPendingTransaction()
    {
        $now = time();
        $listData = $this->lotteryCronCollection->find([
            'cron_time' => ['$lte' => $now],
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING
        ]);
        !empty($listData) && $listData = $listData->toArray();
        if (count($listData)) {
            foreach ($listData as $item) {
                $platform = $item['platform'];
                $network = $item['network'];
                /** @var BinanceWeb3 $coinInstance */
                $lotteryService = new LotteryService($network, $platform);
                if ($item['action'] == ContractLibrary::FUNCTION_START_LOTTERY) {
                    $lotteryService->cronStartLottery($item);
                } else if ($item['action'] == ContractLibrary::FUNCTION_CLOSE_LOTTERY) {
                    $lotteryService->cronCloseLottery($item);
                } else if ($item['action'] == ContractLibrary::FUNCTION_CALCULATE_REWARD) {
                    $lotteryService->cronCalculateReward($item);
                }
            }

        }
    }
}
