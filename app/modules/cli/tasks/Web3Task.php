<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;
use Dcore\Services\LockContractService;
use Dcore\Services\LockSettingContractService;
use Dcore\Services\StakingService;
use Exception;

class Web3Task extends TaskBase
{

    public $platform;

    /**
     * Process Function Type
     * @param $transaction
     * @param $dataDecode
     * @throws Exception
     */
    public function processFunction($transaction, $dataDecode)
    {
        $network = $transaction['network'];
        $platform = $transaction['platform'];
        switch ($transaction['contract_type']) {
            case ContractLibrary::LOCK_SETTING:
                $lockSettingService = LockSettingContractService::getInstance($network, $platform);
                $lockSettingService->processUpdateLockSetting($transaction, $dataDecode);
                break;
            case ContractLibrary::LOCK_CONTRACT:
                $lockService = LockContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_LOCK_TOKEN) {
                    $lockService->processLock($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_EXTEND_LOCK) {
                    $lockService->processExtendLock($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_TRANSFER_LOCK) {
                    $lockService->processTransferLock($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_WITHDRAW_LOCK) {
                    $lockService->processWithdrawLock($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::STAKING:
                $stakingService = StakingService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_STAKING) {
                    $stakingService->processStaking($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_BUY) {
                    $stakingService->processBuy($transaction, $dataDecode);
                } else {
                    $stakingService->processUpdateSetting($transaction, $dataDecode);
                }
                break;
        }
    }

    /**
     * @param $network
     * @param $toAddress
     * @return array
     */
    protected function checkInListenAddress($network, $toAddress)
    {
        $listConfigAddress = ContractLibrary::getListConfigAddressByNetworkAndPlatform($network, $this->platform);
        if (!empty($listConfigAddress)) {
            foreach ($listConfigAddress as $configAddress) {
                if ($configAddress['address'] == $toAddress && $configAddress['is_listen'] == ContractLibrary::ACTIVE) {
                    return [
                        'in_condition' => true,
                        'contract_type' => $configAddress['type']
                    ];
                }
            }
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            'network' => $network,
            'platform' => $this->platform,
            'contract_address' => $toAddress
        ]);
        if (!empty($presale)) {
            if ($presale['project_type'] == ContractLibrary::SALE) {
                return [
                    'in_condition' => true,
                    'contract_type' => ContractLibrary::SALE
                ];
            }
            return [
                'in_condition' => true,
                'contract_type' => ContractLibrary::PRESALE
            ];
        }

        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            'network' => $network,
            'platform' => $this->platform,
            'contract_address' => $toAddress
        ]);
        if (!empty($pool)) {
            return [
                'in_condition' => true,
                'contract_type' => ContractLibrary::POOL
            ];
        }

        return [
            'in_condition' => false,
            'contract_type' => null
        ];
    }
}