<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class LockContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Lock Function
     * @throws Exception
     */
    public function processLock($transaction, $dataDecode)
    {
        $network = $this->network;
        $platform = $this->platform;
        $abi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');

        $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
        $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;
        $functionName = ContractLibrary::FUNCTION_NAME;
        $functionSupply = ContractLibrary::FUNCTION_TOTAL_SUPPLY;

        $dataDecode['data_decode'][0] = $this->web3->toCheckSumAddress($dataDecode['data_decode'][0]);
        $dataDecode['data_decode'][1] = $this->web3->toCheckSumAddress($dataDecode['data_decode'][1]);
        $dataDecode['data_decode'][3] = intval($dataDecode['data_decode'][3]);

        $inputData = $dataDecode['data_decode'];
        $tokenAddress = $inputData[0];
        $withdrawAddress = $inputData[1];
        $amount = $inputData[2];
        $unlockTime = intval($inputData[3]);
        $liquidInfo = $this->checkTokenLiquid($dataDecode);
        $isLiquid = $liquidInfo['is_liquid'];
        $pricePlatformToken = $this->getPricePlatformToken($platform);
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];

        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::LOCK_CONTRACT);
                }
            }
        }
        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Lock Data");
        }

        $eventDataDecode = $eventLogData['data_decode'];
        $lockId = intval($eventDataDecode[0]);

        $lockHistoryData = [
            'lock_id' => $lockId,
            'contract_address' => $tokenAddress,
            'address_lock' => $transaction['from'],
            'address_withdraw' => $withdrawAddress,
            'created_at' => $transaction['timestamp'],
            'unlock_time' => $unlockTime,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW,
            'hash' => $transaction['hash'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform'],
            'base_fee_rate' => $pricePlatformToken
        ];

        // <editor-fold desc="Check Lock History">
        $checkLockHistory = $lockHistoryCollection->findOne([
            'lock_id' => $lockId,
            'contract_address' => $tokenAddress,
            'hash' => $transaction['hash'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform'],
        ]);
        if (!empty($checkLockHistory)) {
            $this->updateTransaction($transaction, $dataDecode);
            return;
        }
        // </editor-fold>

        // <editor-fold desc="Find User Connect">
        $userConnect = $userConnectCollection->findOne([
            'address' => $withdrawAddress
        ]);
        if (!empty($userConnect)) {
            $lockHistoryData['user_connect_id'] = $userConnect['_id'];
        }
        // </editor-fold>

        /**
         * If transaction is liquid lock
         */
        if ($isLiquid) {
            $liquidInfo = $this->getTokenLiquid($liquidInfo);
            $mainTokenInfo = $liquidInfo['main_token'];
            $liquidTokenInfo = $liquidInfo['liquid_token'];
            $mainTokenAddress = $mainTokenInfo['address'];
            $totalSupply = $liquidTokenInfo['total_supply'];

            // <editor-fold desc = "Set Lock History Data">
            $lockHistoryData['token_address'] = $mainTokenInfo['address'];
            $lockHistoryData['type'] = ContractLibrary::LOCK_TYPE_LIQUID;
            $lockHistoryData['amount'] = $amount / pow(10, $liquidTokenInfo['decimals']);
            $lockHistoryData['contract_name'] = $liquidTokenInfo['name'];
            $lockHistoryData['contract_symbol'] = $liquidTokenInfo['symbol'];
            $lockHistoryData['contract_decimals'] = $liquidTokenInfo['decimals'];
            $lockHistoryData['token_fee_amount'] = $eventDataDecode[10] / pow(10, $liquidTokenInfo['decimals']);
            $lockHistoryData['real_token_amount'] = $eventDataDecode[11] / pow(10, $liquidTokenInfo['decimals']);
            $lockHistoryData['liquid_info'] = $liquidInfo;
            // </editor-fold>
        } else {
            // Transaction is token lock
            $mainTokenInfo = [
                'address' => $tokenAddress
            ];
            $mainTokenAddress = $mainTokenInfo['address'];
            $contract = new Contract($this->web3->rpcConnector->getProvider(), $abi);
            $mainTokenContract = $contract->at($mainTokenAddress);

            // <editor-fold desc = "Get Decimal, Name, Ticker Main Token">
            $tokenInfo = $tokenCollection->findOne(['address' => $mainTokenAddress]);
            if ($tokenInfo) {
                $mainTokenInfo['decimals'] = $tokenInfo['decimals'];
                $mainTokenInfo['name'] = $tokenInfo['name'];
                $mainTokenInfo['symbol'] = $tokenInfo['symbol'];
            } else {
                $mainTokenContract->call($functionDecimals, null, function ($err, $res) use (&$mainTokenInfo) {
                    if ($res) {
                        $mainTokenInfo['decimals'] = intval($res[0]->toString());
                    }
                });

                $mainTokenContract->call($functionName, null, function ($err, $res) use (&$mainTokenInfo) {
                    if ($res) {
                        $mainTokenInfo['name'] = $res[0];
                    }
                });

                $mainTokenContract->call($functionSymbol, null, function ($err, $res) use (&$mainTokenInfo) {
                    if ($res) {
                        $mainTokenInfo['symbol'] = $res[0];
                    }
                });
            }
            // </editor-fold>

            // <editor-fold desc = "Get Total Supply Main Token">
            $mainTokenContract->call($functionSupply, null, function ($err, $res) use (&$totalSupply) {
                if ($res) {
                    $totalSupply = $res[0]->toString();
                }
            });
            $totalSupply = BigDecimal::of($totalSupply)->exactlyDividedBy(pow(10, $mainTokenInfo['decimals']))->toFloat();
            $mainTokenInfo['total_supply'] = $totalSupply;
            // </editor-fold>

            // <editor-fold desc = "Set Lock History Data">
            $lockHistoryData['token_address'] = $mainTokenInfo['address'];
            $lockHistoryData['type'] = ContractLibrary::LOCK_TYPE_TOKEN;
            $lockHistoryData['amount'] = $amount / pow(10, $mainTokenInfo['decimals']);
            $lockHistoryData['contract_name'] = $mainTokenInfo['name'];
            $lockHistoryData['contract_symbol'] = $mainTokenInfo['symbol'];
            $lockHistoryData['contract_decimals'] = $mainTokenInfo['decimals'];
            $lockHistoryData['token_fee_amount'] = $eventDataDecode[10] / pow(10, $mainTokenInfo['decimals']);
            $lockHistoryData['real_token_amount'] = $eventDataDecode[11] / pow(10, $mainTokenInfo['decimals']);
            // </editor-fold>
        }
        $lockHistoryData['percent'] = BigDecimal::of($lockHistoryData['amount'])->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->multipliedBy(100)->toFloat();
        $lockHistoryData['need_to_pay_fee'] = $eventDataDecode[7];
        $lockHistoryData['base_fee_amount'] = $eventDataDecode[8] / pow(10, ContractLibrary::DEFAULT_DECIMALS);
        $lockHistoryData['base_fee_usd'] = $lockHistoryData['base_fee_amount'] * $pricePlatformToken;
        $lockHistoryData['token_fee_percent'] = $eventDataDecode[9] * 100 / 1000;

        if (!$lockHistoryData['need_to_pay_fee']) {
            $lockHistoryData['token_fee_amount'] = $lockHistoryData['base_fee_amount'] = $lockHistoryData['base_fee_usd'] = 0;
        }

        // <editor-fold desc = "Save Lock History">
        $lockHistoryCollection->insertOne($lockHistoryData);
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        // <editor-fold desc = "Re-update lock percent">
        $listLockHistory = $lockHistoryCollection->find([
            'contract_address' => $tokenAddress,
            'network' => $network,
            'platform' => $platform,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW
        ]);
        if (!empty($listLockHistory)) {
            $listLockHistory = $listLockHistory->toArray();
            foreach ($listLockHistory as $historyItem) {
                $percent = BigDecimal::of($historyItem['amount'])->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->multipliedBy(100)->toFloat();
                $historyData = [
                    'percent' => $percent
                ];
                $lockHistoryCollection->updateOne(['_id' => $historyItem['_id']], ['$set' => $historyData]);
            }
        }
        // </editor-fold>

        $tokenInfo = $tokenCollection->findOne([
            'address' => $mainTokenAddress,
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        $dataPrice = $this->getPriceTokenData($platform, $network, $mainTokenInfo);
        if (!$tokenInfo) {
            $tokenLockAmount = $isLiquid ? 0 : $lockHistoryData['amount'];
            $tokenLockPercent = $isLiquid ? 0 : $lockHistoryData['percent'];
            $tokenLockValue = $dataPrice['token_price_usd'] * $tokenLockAmount;
            $liquidLockPercent = $isLiquid ? $lockHistoryData['percent'] : 0;
            $liquidLockValue = $isLiquid ? ($dataPrice['sub_token_usd'] / 100 * $liquidLockPercent) : 0;
            $circulatingSupplyAmount = $mainTokenInfo['total_supply'] - $tokenLockAmount;
            $circulatingSupplyPercent = $circulatingSupplyAmount / $mainTokenInfo['total_supply'] * 100;
            $totalLockValue = $tokenLockValue + $liquidLockValue;

            $tokenInsertData = [
                'address' => $mainTokenInfo['address'],
                'name' => $mainTokenInfo['name'],
                'symbol' => $mainTokenInfo['symbol'],
                'decimals' => $mainTokenInfo['decimals'],
                'total_supply_token' => $mainTokenInfo['total_supply'],
                'token_lock_amount' => $tokenLockAmount,
                'token_lock_percent' => $tokenLockPercent,
                'token_lock_value' => $tokenLockValue,
                'liquid_lock_percent' => $liquidLockPercent,
                'liquid_lock_value' => $liquidLockValue,
                'token_price_usd' => $dataPrice['token_price_usd'],
                'token_pool' => $dataPrice['token_pool'],
                'lock_time' => intval($lockHistoryData['created_at']),
                'unlock_time' => intval($lockHistoryData['unlock_time']),
                'total_pool_usd' => $dataPrice['sub_token_usd'],
                'total_supply_usd' => $dataPrice['total_supply_usd'],
                'network' => $network,
                'platform' => $platform,
                'circulating_supply_amount' => $circulatingSupplyAmount,
                'circulating_supply_percent' => $circulatingSupplyPercent,
                'total_lock_value' => $totalLockValue,
                'dex_address_pair' => $dataPrice['dex_address_pair'],
                'status' => ContractLibrary::ACTIVE
            ];

            $tokenInsertData['_id'] = $tokenCollection->insertOne($tokenInsertData)->getInsertedId();
            $this->getCoinGeckoInfo($tokenInsertData);
        } else {
            $tokenUpdateData = [
                'total_supply_token' => $mainTokenInfo['total_supply'],
                'token_pool' => $dataPrice['token_pool'],
                'token_price_usd' => $dataPrice['token_price_usd'],
                'total_pool_usd' => $dataPrice['sub_token_usd'],
                'total_supply_usd' => $dataPrice['total_supply_usd'],
                'dex_address_pair' => $dataPrice['dex_address_pair'],
                'status' => ContractLibrary::ACTIVE
            ];

            if ($isLiquid) {
                $liquidLockPercent = $this->calculateLiquidPercent($tokenInfo);
                $tokenUpdateData['liquid_lock_percent'] = $liquidLockPercent;
                $tokenUpdateData['liquid_lock_value'] = $dataPrice['sub_token_usd'] / 100 * $liquidLockPercent;
                $tokenLockValue = $dataPrice['token_price_usd'] * $tokenInfo['token_lock_amount'];
                $totalLockValue = $tokenLockValue + $tokenUpdateData['liquid_lock_value'];
            } else {
                $tokenUpdateData['token_lock_amount'] = $tokenInfo['token_lock_amount'] + $lockHistoryData['amount'];
                $tokenUpdateData['token_lock_percent'] = $tokenUpdateData['token_lock_amount'] / $tokenUpdateData['total_supply_token'] * 100;
                $tokenUpdateData['token_lock_value'] = $dataPrice['token_price_usd'] * $tokenUpdateData['token_lock_amount'];
                $tokenLockValue = $tokenUpdateData['token_lock_value'];
                $totalLockValue = $tokenLockValue + $tokenInfo['liquid_lock_value'];
            }

            $newestUnlockHistory = $this->getNewestUnlockHistory($tokenInfo['address'], $network, $platform);
            if ($newestUnlockHistory) {
                $tokenUpdateData['lock_time'] = intval($newestUnlockHistory['created_at']);
                $tokenUpdateData['unlock_time'] = intval($newestUnlockHistory['unlock_time']);
            }

            $tokenUpdateData['circulating_supply_amount'] = $tokenUpdateData['total_supply_token'] - $tokenUpdateData['token_lock_amount'];
            $tokenUpdateData['circulating_supply_percent'] = $tokenUpdateData['circulating_supply_amount'] / $tokenUpdateData['total_supply_token'] * 100;
            $tokenUpdateData['total_lock_value'] = $totalLockValue;

            $tokenCollection->updateOne(['_id' => $tokenInfo['_id']], ['$set' => $tokenUpdateData]);
        }

        if ($isLiquid) {
            $subTokenInfo = $liquidInfo['sub_token'];
            $subTokenAddress = $subTokenInfo['address'];
            $totalSupply = $subTokenInfo['total_supply'];
            $tokenLiquid = $tokenCollection->findOne(['address' => $subTokenAddress]);
            if (!$tokenLiquid) {
                $subTokenInsertData = [
                    'address' => $subTokenInfo['address'],
                    'name' => $subTokenInfo['name'],
                    'symbol' => $subTokenInfo['symbol'],
                    'decimals' => $subTokenInfo['decimals'],
                    'total_supply_token' => $subTokenInfo['total_supply'],
                    'network' => $network,
                    'platform' => $platform,
                    'status' => ContractLibrary::INACTIVE
                ];
                $subTokenInsertData['_id'] = $tokenCollection->insertOne($subTokenInsertData)->getInsertedId();
                $this->getCoinGeckoInfo($subTokenInsertData);
            }
        }
        $cacheKey = "lock_token_statistic:{$platform}_$network";
        $this->redis->del($cacheKey);
    }

    /**
     * Process Extend Lock Transaction
     * @param $transaction
     * @param $dataDecode
     */
    public function processExtendLock($transaction, $dataDecode)
    {
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');

        $dataDecode['data_decode'][0] = intval($dataDecode['data_decode'][0]);
        $dataDecode['data_decode'][1] = intval($dataDecode['data_decode'][1]);
        $inputData = $dataDecode['data_decode'];
        $lockId = $inputData[0];
        $unlockTime = intval($inputData[1]);

        $network = $transaction['network'];
        $platform = $transaction['platform'];

        $lockHistory = $lockHistoryCollection->findOne([
            'lock_id' => $lockId,
            'network' => $network,
            'platform' => $platform
        ]);
        if ($lockHistory) {
            $lockHistoryCollection->updateOne(['_id' => $lockHistory['_id']], ['$set' => ['unlock_time' => $unlockTime]]);
            $tokenInfo = $tokenCollection->findOne([
                'address' => $lockHistory['token_address'],
                'network' => $network,
                'platform' => $platform
            ]);
            if ($tokenInfo) {
                $firstUnlockHistory = $this->getNewestUnlockHistory($tokenInfo['address'], $network, $platform);
                if ($firstUnlockHistory) {
                    $tokenCollection->updateOne(['_id' => $tokenInfo['_id']], ['$set' => [
                        'lock_time' => intval($firstUnlockHistory['created_at']),
                        'unlock_time' => intval($firstUnlockHistory['unlock_time']),
                    ]]);
                }
            }
        }

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * Process Transfer Lock To Other
     * @param $transaction
     * @param $dataDecode
     */
    public function processTransferLock($transaction, $dataDecode)
    {
        $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');

        $dataDecode['data_decode'][0] = intval($dataDecode['data_decode'][0]);
        $inputData = $dataDecode['data_decode'];
        $lockId = $inputData[0];
        $receiveAddress = $this->web3->toCheckSumAddress($inputData[1]);
        $network = $transaction['network'];
        $platform = $transaction['platform'];


        $lockHistory = $lockHistoryCollection->findOne([
            'lock_id' => $lockId,
            'network' => $network,
            'platform' => $platform
        ]);
        if ($lockHistory) {
            $lockHistoryData = [
                'address_withdraw' => $receiveAddress,
            ];
            // <editor-fold desc="Find User Connect">
            $userConnect = $userConnectCollection->findOne([
                'address' => $receiveAddress
            ]);
            if (!empty($userConnect)) {
                $lockHistoryData['user_connect_id'] = $userConnect['_id'];
            }
            // </editor-fold>
            $lockHistoryCollection->updateOne(['_id' => $lockHistory['_id']], ['$set' => $lockHistoryData]);
        }

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * Process Withdraw Lock To Other
     * @param $transaction
     * @param $dataDecode
     * @throws Exception
     */
    public function processWithdrawLock($transaction, $dataDecode)
    {
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');

        $dataDecode['data_decode'][0] = intval($dataDecode['data_decode'][0]);
        $inputData = $dataDecode['data_decode'];
        $lockId = $inputData[0];
        $network = $transaction['network'];
        $platform = $transaction['platform'];

        $lockHistory = $lockHistoryCollection->findOne([
            'lock_id' => $lockId,
            'network' => $network,
            'platform' => $platform
        ]);
        if ($lockHistory) {
            $dataLockHistoryUpdate = [
                'withdraw_status' => ContractLibrary::WITHDRAWN,
                'withdraw_at' => $transaction['timestamp'],
                'withdraw_hash' => $transaction['hash']
            ];

            $lockHistoryCollection->updateOne(['_id' => $lockHistory['_id']], ['$set' => $dataLockHistoryUpdate]);
            $tokenInfo = $tokenCollection->findOne([
                'address' => $lockHistory['token_address'],
                'network' => $network,
                'platform' => $platform,
            ]);
            $tokenInfo['total_supply'] = $tokenInfo['total_supply_token'];
            $dataPrice = $this->getPriceTokenData($lockHistory['platform'], $network, $tokenInfo);
            $isLiquid = $lockHistory['type'] == ContractLibrary::LOCK_TYPE_LIQUID;
            $tokenUpdateData = [
                'token_pool' => $dataPrice['token_pool'],
                'token_price_usd' => $dataPrice['token_price_usd'],
                'total_pool_usd' => $dataPrice['sub_token_usd'],
                'total_supply_usd' => $dataPrice['total_supply_usd'],
            ];

            if ($isLiquid) {
                $liquidLockPercent = $this->calculateLiquidPercent($tokenInfo);
                $tokenUpdateData['liquid_lock_percent'] = $liquidLockPercent;
                $tokenUpdateData['liquid_lock_value'] = $dataPrice['sub_token_usd'] / 100 * $liquidLockPercent;
                $totalLockValue = $tokenInfo['token_lock_value'] + $tokenUpdateData['liquid_lock_value'];
            } else {
                $tokenPercentInfo = $this->calculateTokenPercent($tokenInfo);
                $tokenUpdateData['total_supply_token'] = $tokenPercentInfo['total_supply'];
                $tokenUpdateData['token_lock_amount'] = $tokenPercentInfo['token_lock_amount'];
                $tokenUpdateData['token_lock_percent'] = $tokenPercentInfo['token_lock_percent'];
                $tokenUpdateData['circulating_supply_amount'] = $tokenPercentInfo['circulating_supply_amount'];
                $tokenUpdateData['circulating_supply_percent'] = $tokenPercentInfo['circulating_supply_percent'];
                $tokenUpdateData['token_lock_value'] = $dataPrice['token_price_usd'] * $tokenUpdateData['token_lock_amount'];
                $totalLockValue = $tokenUpdateData['token_lock_value'] + $tokenInfo['liquid_lock_value'];
            }

            $tokenUpdateData['total_lock_value'] = $totalLockValue;
            $tokenCollection->updateOne(['_id' => $tokenInfo['_id']], ['$set' => $tokenUpdateData]);
        }

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        // <editor-fold desc = "Del Key Lock Token">
        $cacheKey = "lock_token_statistic:{$platform}_$network";
        $this->redis->del($cacheKey);
        // </editor-fold>
    }
}
