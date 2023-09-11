<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class PresaleContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processBuyToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleBuyLogCollection = $this->mongo->selectCollection('presale_buy_log');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');

        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        $abiName = ContractLibrary::PRESALE . "_" . $presale['contract_version'];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $presaleAddress) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("BuyToken: Invalid Event Data Tx: " . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $baseTokenAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $presale['base_token_decimals']))->toFloat();
        $saleTokenAmount = BigDecimal::of($eventDataDecode[2])->exactlyDividedBy(pow(10, $presale['sale_token_decimals']))->toFloat();

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$presaleInfo, $presale) {
            if ($res) {
                $presaleInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $presale['base_token_decimals']))->toFloat();
                $presaleInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $presale['sale_token_decimals']))->toFloat();
                $presaleInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $presaleInfo['success_at'] = intval($res['successAt']->toString());
                $presaleInfo['current_status'] = intval($res['currentStatus']->toString());
                $presaleInfo['current_round'] = intval($res['currentRound']->toString());
                $presaleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $presaleInfo['current_round'] = -1;

                if ($presaleInfo['success_at'] > 0) {
                    $presaleInfo['message'] = 'Presale reached soft cap';
                }
                if ($presaleInfo['total_base_collected'] >= $presale['hard_cap']) {
                    $presaleInfo['message'] = 'Presale reached hard cap';
                }
            }
        });
        // </editor-fold>

        $presaleInfo['token_sold_percent'] = $presaleInfo['total_token_sold'] / $presale['amount'] * 100;
        $userAddress = $transaction['from'];

        // <editor-fold desc = "Presale Buy Log">
        $presaleBuyLog = $presaleBuyLogCollection->findOne([
            'hash' => $transaction['hash'],
            'network' => $presale['network'],
            'platform' => $presale['platform'],
            'presale_address' => $presale['contract_address'],
            'user_address' => $userAddress
        ]);
        $projectType = isset($presale['project_type']) && strlen($presale['project_type']) ? $presale['project_type'] : ContractLibrary::PROJECT_TYPE_PRESALE;
        if (isset($presale['sale_type']) && strlen($presale['sale_type'])) {
            $saleType = $presale['sale_type'];
        } else {
            if (isset($presale['active_vesting']) && $presale['active_vesting'] == ContractLibrary::PRESALE_ACTIVE_VESTING) {
                $saleType = ContractLibrary::SALE_TYPE_ILO;
            } else {
                $saleType = ContractLibrary::SALE_TYPE_ILOV;
            }
        }
        if (empty($presaleBuyLog)) {
            $dataPresaleBuyLog = [
                'network' => $presale['network'],
                'platform' => $presale['platform'],
                'user_address' => $userAddress,
                'presale_address' => $presale['contract_address'],
                'project_type' => $projectType,
                'sale_type' => $saleType,
                'hash' => $transaction['hash'],
                'base_token_amount' => $baseTokenAmount,
                'sale_token_amount' => $saleTokenAmount,
                'round' => $presaleInfo['current_round'],
                'created_at' => $transaction['timestamp']
            ];
            $presaleBuyLogCollection->insertOne($dataPresaleBuyLog);
        }
        // </editor-fold>

        // <editor-fold desc = "Get Buyer Info">
        $buyerInfo = [];
        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_BUYER_INFO, $userAddress, function ($err, $res) use (&$buyerInfo, $presale) {
            if ($res) {
                $buyerInfo['base_token_amount'] = BigDecimal::of($res['baseDeposited']->toString())->exactlyDividedBy(pow(10, $presale['base_token_decimals']))->toFloat();
                $buyerInfo['sale_token_amount'] = BigDecimal::of($res['tokenBought']->toString())->exactlyDividedBy(pow(10, $presale['sale_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Presale User">
        $dataPresaleUser = [
            'network' => $presale['network'],
            'platform' => $presale['platform'],
            'user_address' => $userAddress,
            'presale_address' => $presaleAddress
        ];
        $presaleUser = $presaleUserLogCollection->findOne($dataPresaleUser);
        if (!$presaleUser) {
            $dataPresaleUser['base_token_amount'] = $buyerInfo['base_token_amount'];
            $dataPresaleUser['sale_token_amount'] = $buyerInfo['sale_token_amount'];
            $dataPresaleUser['contract_type'] = $transaction['contract_type'];
            $dataPresaleUser['project_type'] = $projectType;
            $dataPresaleUser['sale_type'] = $saleType;

            if (isset($presale['active_vesting']) && $presale['active_vesting'] == ContractLibrary::PRESALE_ACTIVE_VESTING) {
                if (!isset($presale['list_vesting_period']) || empty($presale['list_vesting_period'])
                    || !isset($presale['list_vesting_percent']) || empty($presale['list_vesting_percent'])
                    || count($presale['list_vesting_period']) != count($presale['list_vesting_percent'])) {
                    throw new Exception("Invalid vesting data. Contract Address" . $presale['contract_address']);
                }
                $dataPresaleUser['active_vesting'] = $presale['active_vesting'];
                foreach ($presale['list_vesting_period'] as $key => $vestingTime) {
                    $dataPresaleUser['list_vesting'][] = [
                        'vesting_period' => $vestingTime,
                        'vesting_percent' => $presale['list_vesting_percent'][$key],
                        'vesting_number' => $key + 1,
                        'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                    ];
                }
            }
            $dataPresaleUser['withdraw_status'] = ContractLibrary::NOT_WITHDRAW;

            $presaleUserLogCollection->insertOne($dataPresaleUser);
        } else {
            $presaleUserLogCollection->updateOne(['_id' => $presaleUser['_id']], ['$set' => $buyerInfo]);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Add Liquidity
     * @throws Exception
     */
    public function processAddLiquidity($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['lp_generation_complete'] = $res['lpGenerationComplete'];
                $presaleInfo['force_failed'] = $res['forceFailed'];
                $presaleInfo['liquidity_at'] = intval($res['liquidityAt']->toString());
                $presaleInfo['current_status'] = intval($res['currentStatus']->toString());
            }
        });
        // </editor-fold>

        $web3Provider = $this->web3->rpcConnector->getProvider();
        $dexFactoryAbi = ContractLibrary::getAbi(ContractLibrary::DEX_FACTORY);
        $factoryContract = new Contract($web3Provider, $dexFactoryAbi);
        $dexFactoryContract = $factoryContract->at($presale['dex_factory_address']);

        $dexFactoryContract->call(ContractLibrary::FUNCTION_GET_PAIR, $presale['base_token_address'], $presale['sale_token_address'], function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['dex_pair_address'] = $res[0];
            }
        });

        $presaleInfo['message'] = 'Presale was added liquidity failed';
        if ($presaleInfo['current_status'] == ContractLibrary::PRESALE_STATUS_SUCCESS) {
            $presaleInfo['message'] = 'Presale was added liquidity successfully';
            $presaleInfo['base_fee_amount'] = $presale['total_base_collected'] / 100 * $presale['base_fee_percent'];
            $presaleInfo['sale_token_fee_amount'] = $presale['total_token_sold'] / 100 * $presale['token_fee_percent'];
            $presaleInfo['base_token_liquidity_amount'] = ($presale['total_base_collected'] - $presaleInfo['base_fee_amount']) / 100 * $presale['liquidity_percent'];
            $presaleInfo['sale_token_liquidity_amount'] = $presaleInfo['base_token_liquidity_amount'] * $presale['listing_price'];
            $presaleInfo['base_token_owner_amount'] = ($presale['total_base_collected'] - $presaleInfo['base_fee_amount'] - $presaleInfo['base_token_liquidity_amount']);

            $this->processLockLiquid($transaction);
        }

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Thêm mới bản ghi lock histories, cập nhật token nếu
     * transaction có event lock liquid
     *
     * @param $transaction
     * @throws ConnectionErrorException
     * @throws Exception
     */
    private function processLockLiquid($transaction)
    {
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $platform = $transaction['platform'];
        $network = $transaction['network'];

        $lockAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::LOCK_CONTRACT);
        foreach ($logsData as $logItem) {
            if (!isset($logItem['address'])) {
                continue;
            }

            $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
            if ($logItem['address'] != $lockAddress) {
                continue;
            }

            $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::LOCK_CONTRACT);
            if (!isset($eventLogData['data_decode'][0])) {
                throw new Exception("Invalid Lock Data");
            }

            $eventDataDecode = $eventLogData['data_decode'];
            $tokenAmount = $eventDataDecode[4];
            $tokenAddress = $this->web3->toCheckSumAddress($eventDataDecode[1]);
            $liquidInfo = $this->checkTokenLiquid(['data_decode' => [$tokenAddress]]);
            $isLiquid = $liquidInfo['is_liquid'];
            $liquidInfo = $this->getTokenLiquid($liquidInfo);

            $pricePlatformToken = $this->getPricePlatformToken($platform);
            $mainTokenInfo = $liquidInfo['main_token'];
            $liquidTokenInfo = $liquidInfo['liquid_token'];
            $addressLock = $this->web3->toCheckSumAddress($eventDataDecode[2]);
            $addressWithdraw = $this->web3->toCheckSumAddress($eventDataDecode[3]);
            $totalSupply = $liquidTokenInfo['total_supply'];
            $lockHistoryAmount = BigDecimal::of($tokenAmount)->exactlyDividedBy(pow(10, $liquidTokenInfo['decimals']))->toFloat();
            $lockHistoryBaseFeeAmount = BigDecimal::of($eventDataDecode[8])->exactlyDividedBy(pow(10, ContractLibrary::DEFAULT_DECIMALS))->toFloat();
            $lockHistoryTokenFeeAmount = BigDecimal::of($eventDataDecode[10])->exactlyDividedBy(pow(10, $liquidTokenInfo['decimals']))->toFloat();
            $lockHistoryRealTokenAmount = BigDecimal::of($eventDataDecode[11])->exactlyDividedBy(pow(10, $liquidTokenInfo['decimals']))->toFloat();
            $lockHistoryPercent = $lockHistoryAmount / $totalSupply * 100;
            // <editor-fold desc = "Set Lock History Data">
            $lockHistoryData = [
                'lock_id' => intval($eventDataDecode[0]),
                'contract_address' => $tokenAddress,
                'address_lock' => $addressLock,
                'address_withdraw' => $addressWithdraw,
                'timestamp' => intval($eventDataDecode[5]),
                'created_at' => time(),
                'unlock_time' => intval($eventDataDecode[6]),
                'withdraw_status' => ContractLibrary::NOT_WITHDRAW,
                'hash' => $transaction['hash'],
                'network' => $network,
                'platform' => $platform,
                'base_fee_rate' => $pricePlatformToken,
                'token_address' => $mainTokenInfo['address'],
                'type' => ContractLibrary::LOCK_TYPE_LIQUID,
                'amount' => $lockHistoryAmount,
                'contract_name' => $liquidTokenInfo['name'],
                'contract_symbol' => $liquidTokenInfo['symbol'],
                'contract_decimals' => $liquidTokenInfo['decimals'],
                'token_fee_amount' => $lockHistoryTokenFeeAmount,
                'real_token_amount' => $lockHistoryRealTokenAmount,
                'liquid_info' => $liquidInfo,
                'percent' => $lockHistoryPercent,
                'need_to_pay_fee' => $eventDataDecode[7],
                'base_fee_amount' => $lockHistoryBaseFeeAmount,
                'base_fee_usd' => $lockHistoryBaseFeeAmount * $pricePlatformToken,
                'token_fee_percent' => $eventDataDecode[9] * 100 / 1000,
            ];

            if (!$lockHistoryData['need_to_pay_fee']) {
                $lockHistoryData['token_fee_amount'] = $lockHistoryData['base_fee_amount'] = $lockHistoryData['base_fee_usd'] = 0;
            }

            $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');
            $lockHistoryCollection->insertOne($lockHistoryData);
            // </editor-fold>

            // <editor-fold desc = "Re-update lock percent">
            $listLockHistory = $lockHistoryCollection->find([
                    'contract_address' => $lockHistoryData['address_withdraw'],
                    'network' => $network,
                    'platform' => $platform,
                    'withdraw_status' => ContractLibrary::NOT_WITHDRAW]
            );
            if (!empty($listLockHistory)) {
                $listLockHistory = $listLockHistory->toArray();
                foreach ($listLockHistory as $historyItem) {
                    $percent = BigDecimal::of($historyItem['amount'])->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat() * 100;
                    $historyData = [
                        'percent' => $percent
                    ];
                    $lockHistoryCollection->updateOne(['_id' => $historyItem['_id']], ['$set' => $historyData]);
                }
            }
            // </editor-fold>

            $tokenCollection = $this->mongo->selectCollection('tokens');
            $tokenInfo = $tokenCollection->findOne([
                'address' => $mainTokenInfo['address'],
                'network' => $network,
                'platform' => $platform
            ]);
            $dataPrice = $this->getPriceTokenData($platform, $network, $mainTokenInfo);
            if (!$tokenInfo) {
                $tokenLockAmount = $isLiquid ? 0 : $lockHistoryData['amount'];
                $tokenLockPercent = $isLiquid ? 0 : $lockHistoryData['percent'];
                $tokenLockValue = $dataPrice['token_price_usd'] * $tokenLockAmount;
                $liquidLockPercent = $isLiquid ? $lockHistoryData['percent'] : 0;
                $liquidLockValue = $isLiquid ? BigDecimal::of($dataPrice['sub_token_usd'])->exactlyDividedBy(100)->toFloat() * $liquidLockPercent : 0;
                $circulatingSupplyAmount = $mainTokenInfo['total_supply'] - $tokenLockAmount;
                $circulatingSupplyPercent = BigDecimal::of($circulatingSupplyAmount)->dividedBy($mainTokenInfo['total_supply'], ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->multipliedBy(100)->toFloat();

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
                    'lock_time' => intval($lockHistoryData['timestamp']),
                    'unlock_time' => intval($lockHistoryData['unlock_time']),
                    'total_pool_usd' => $dataPrice['sub_token_usd'],
                    'total_supply_usd' => $dataPrice['total_supply_usd'],
                    'network' => $network,
                    'platform' => $platform,
                    'circulating_supply_amount' => $circulatingSupplyAmount,
                    'circulating_supply_percent' => $circulatingSupplyPercent,
                    'total_lock_value' => $tokenLockValue + $liquidLockValue,
                    'dex_address_pair' => $dataPrice['dex_address_pair'],
                    'status' => ContractLibrary::ACTIVE
                ];
                $tokenInsertData['_id'] = $tokenCollection->insertOne($tokenInsertData)->getInsertedId();
                $this->getCoinGeckoInfo($tokenInsertData);
            } else {
                $liquidLockPercent = $this->calculateLiquidPercent($tokenInfo);
                $liquidLockValue = BigDecimal::of($dataPrice['sub_token_usd'])->exactlyDividedBy(100)->toFloat() * $liquidLockPercent;
                $tokenLockValue = $dataPrice['token_price_usd'] * $tokenInfo['token_lock_amount'];

                $tokenUpdateData = [
                    'total_supply_token' => $mainTokenInfo['total_supply'],
                    'token_pool' => $dataPrice['token_pool'],
                    'token_price_usd' => $dataPrice['token_price_usd'],
                    'total_pool_usd' => $dataPrice['sub_token_usd'],
                    'total_supply_usd' => $dataPrice['total_supply_usd'],
                    'dex_address_pair' => $dataPrice['dex_address_pair'],
                    'status' => ContractLibrary::ACTIVE,
                    'liquid_lock_percent' => $liquidLockPercent,
                    'liquid_lock_value' => $liquidLockValue,
                    'total_lock_value' => $tokenLockValue + $liquidLockValue,
                ];

                $newestUnlockHistory = $this->getNewestUnlockHistory($tokenInfo['address'], $network, $platform);
                if ($newestUnlockHistory) {
                    $tokenUpdateData['lock_time'] = intval($newestUnlockHistory['timestamp']);
                    $tokenUpdateData['unlock_time'] = intval($newestUnlockHistory['unlock_time']);
                }

                $tokenCollection->updateOne(['_id' => $tokenInfo['_id']], ['$set' => $tokenUpdateData]);

                $subTokenInfo = $liquidInfo['sub_token'];
                $subTokenAddress = $subTokenInfo['address'];
                $tokenLiquid = $tokenCollection->findOne(['address' => $subTokenAddress]);
                if (!$tokenLiquid) {
                    $subTokenInsertData = [
                        'address' => $subTokenInfo['address'],
                        'name' => $subTokenInfo['name'],
                        'symbol' => $subTokenInfo['symbol'],
                        'decimals' => $subTokenInfo['decimals'],
                        'total_supply_token' => $subTokenInfo['total_supply'],
                        'network' => $transaction['network'],
                        'platform' => $transaction['platform'],
                        'status' => ContractLibrary::INACTIVE
                    ];
                    $subTokenInsertData['_id'] = $tokenCollection->insertOne($subTokenInsertData)->getInsertedId();
                    $this->getCoinGeckoInfo($subTokenInsertData);
                }
            }
        }
    }

    /**
     * Process User Withdraw Sale Token
     * @throws Exception
     */
    public function processUserWithdrawSaleToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');

        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        $abiName = ContractLibrary::PRESALE . "_" . $presale['contract_version'];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $presaleAddress) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Presale-userWithdrawSaleToken: Invalid Event Data tx " . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $withdrawAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $presale['sale_token_decimals']))->toFloat();
        $numberClaimed = $eventDataDecode[3];

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$presaleInfo, $presale) {
            if ($res) {
                $presaleInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $presale['sale_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Presale User">
        $userAddress = $transaction['from'];
        $presaleUser = $presaleUserLogCollection->findOne([
            'network' => $presale['network'],
            'platform' => $presale['platform'],
            'presale_address' => $presaleAddress,
            'user_address' => $userAddress
        ]);
        if ($presaleUser) {
            if (isset($presale['active_vesting']) && $presale['active_vesting'] == ContractLibrary::PRESALE_ACTIVE_VESTING) {
                foreach ($presaleUser['list_vesting'] as &$vesting) {
                    if ($vesting['vesting_number'] == $numberClaimed) {
                        $vesting['withdraw_status'] = ContractLibrary::WITHDRAWN;
                        $vesting['sale_token_withdraw_amount'] = $withdrawAmount;
                        $vesting['withdraw_token_type'] = 'sale_token';
                        $vesting['withdraw_at'] = $transaction['timestamp'];
                    }
                }
            } else {
                $presaleUser['sale_token_withdraw_amount'] = $withdrawAmount;
                $presaleUser['withdraw_at'] = $transaction['timestamp'];
            }
            $presaleUser['withdraw_status'] = ContractLibrary::WITHDRAWN;
            $presaleUser['withdraw_token_type'] = 'sale_token';
            $presaleUserLogCollection->updateOne(['_id' => $presaleUser['_id']], ['$set' => $presaleUser]);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Force Fail
     * @throws Exception
     */
    public function processForceFail($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['force_failed'] = $res['forceFailed'];
                $presaleInfo['current_status'] = intval($res['currentStatus']->toString());
                $presaleInfo['current_round'] = intval($res['currentRound']->toString());
                $presaleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $presaleInfo['current_round'] = -1;
            }
        });
        $presaleInfo['message'] = 'Admin force failed';
        if ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_FORCE_FAIL) {
            $presaleInfo['message'] = 'User force failed';
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process User Withdraw Base Token
     * @throws Exception
     */
    public function processUserWithdrawBaseToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');

        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $presaleAddress) {
                    $abiType = ContractLibrary::PRESALE . "_" . $presale['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Presale: processUserWithdrawBaseToken Invalid Event Data. Tx: " . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $withdrawAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $presale['base_token_decimals']))->toFloat();

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$presaleInfo, $presale) {
            if ($res) {
                $presaleInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $presale['base_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Presale User">
        $userAddress = $transaction['from'];
        $presaleUser = $presaleUserLogCollection->findOne([
            'network' => $presale['network'],
            'platform' => $presale['platform'],
            'presale_address' => $presaleAddress,
            'user_address' => $userAddress
        ]);
        if ($presaleUser) {
            $dataPresaleUser = [
                'withdraw_status' => ContractLibrary::ACTIVE,
                'base_token_withdraw_amount' => $withdrawAmount,
                'withdraw_token_type' => 'base_token',
                'withdraw_at' => $transaction['timestamp']
            ];
            $presaleUserLogCollection->updateOne(['_id' => $presaleUser['_id']], ['$set' => $dataPresaleUser]);
        }
        // </editor-fold>

        // <editor-fold desc = "Presale User Zero Round">
        $presaleUserZeroRound = $presaleUserZeroRoundCollection->findOne([
            'network' => $presale['network'],
            'platform' => $presale['platform'],
            'presale_address' => $presaleAddress,
            'user_address' => $userAddress
        ]);
        if ($presaleUserZeroRound) {
            $dataPresaleUserZeroRound = [
                'withdraw_status' => ContractLibrary::ACTIVE,
                'withdraw_at' => $transaction['timestamp'],
            ];
            $presaleUserZeroRoundCollection->updateOne(['_id' => $presaleUserZeroRound['_id']], ['$set' => $dataPresaleUserZeroRound]);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Owner Withdraw Sale Token
     * @throws Exception
     */
    public function processOwnerWithdrawSaleToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }

        $presaleInfo['message'] = 'Owner withdraw sale token';
        $presaleInfo['owner_withdraw_sale_token'] = ContractLibrary::ACTIVE;
        $presaleInfo['withdraw_sale_token_at'] = $transaction['timestamp'];

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Update Limit Per Buyer
     * @throws Exception
     */
    public function processUpdateLimitPerBuyer($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $functionGetPresaleMainInfo = ContractLibrary::FUNCTION_GET_PRESALE_MAIN_INFO;
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $presaleContractInstance->call($functionGetPresaleMainInfo, null, function ($err, $res) use (&$presaleInfo, $presale) {
            if ($res) {
                $presaleInfo['limit_per_buyer'] = BigDecimal::of($res['limitPerBuyer']->toString())->exactlyDividedBy(pow(10, $presale['base_token_decimals']))->toFloat();
                $presaleInfo['max_buyer'] = ceil($presale['hard_cap'] / $presaleInfo['limit_per_buyer']);
            }
        });
        // </editor-fold>
        $presaleInfo['message'] = 'Owner update limit per buyer';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Update Time
     * @throws Exception
     */
    public function processUpdateTime($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_PRESALE_MAIN_INFO, null, function ($err, $res) use (&$presaleInfo, $presale) {
            if ($res) {
                $presaleInfo['start_time'] = intval($res['startTime']->toString());
                $presaleInfo['end_time'] = intval($res['endTime']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $finishAt = ((!empty($res['finishAt'])) && ($res['finishAt'] instanceof BigInteger)) ? $res['finishAt']->toString() : 0;
                $presaleInfo['zero_round.finish_at'] = intval($finishAt);
            }
        });
        // </editor-fold>

        $presaleInfo['message'] = 'Owner update limit per buyer';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Set Whitelist Flag
     * @throws Exception
     */
    public function processSetWhitelistFlag($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }

        $value = $dataDecode['data_decode'][0];
        $presaleInfo['whitelist_only'] = $value;
        $presaleInfo['message'] = 'Owner update whitelist flag';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Edit Whitelist
     * @throws Exception
     */
    public function processEditWhitelist($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleWhitelistCollection = $this->mongo->selectCollection('presale_whitelist');

        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }

        $listAddress = $dataDecode['data_decode'][0];
        $action = $dataDecode['data_decode'][1];

        if ($action) {
            foreach ($listAddress as $address) {
                $address = $this->web3->toCheckSumAddress($address);
                $dataPresaleWhitelist = [
                    'network' => $presale['network'],
                    'platform' => $presale['platform'],
                    'presale_address' => $presale['contract_address'],
                    'user_address' => $address
                ];
                $presaleWhitelist = $presaleWhitelistCollection->findOne($dataPresaleWhitelist);
                if (!$presaleWhitelist) {
                    $presaleWhitelistCollection->insertOne($dataPresaleWhitelist);
                }
            }
        } else {
            foreach ($listAddress as $address) {
                $address = $this->web3->toCheckSumAddress($address);
                $dataPresaleWhitelist = [
                    'network' => $presale['network'],
                    'presale_address' => $presale['contract_address'],
                    'platform' => $presale['platform'],
                    'user_address' => $address
                ];
                $presaleWhitelistCollection->deleteOne($dataPresaleWhitelist);
            }
        }

        $presaleInfo['message'] = 'Owner update whitelist user';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * Process Register Zero Round
     * @throws Exception
     */
    public function processRegisterZeroRound($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');

        $functionGetZeroRoundInfo = ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO;
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $presaleContractInstance->call($functionGetZeroRoundInfo, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;
                $presaleInfo['zero_round.registered_slot'] = intval($registeredSlot);
            }
        });
        // </editor-fold>
        $userAddress = $transaction['from'];

        // <editor-fold desc = "Presale User Zero Round">
        $dataPresaleUserZeroRound = [
            'network' => $presale['network'],
            'platform' => $presale['platform'],
            'presale_address' => $presale['contract_address'],
            'user_address' => $userAddress
        ];
        $presaleUserZeroRound = $presaleUserZeroRoundCollection->findOne($dataPresaleUserZeroRound);
        if (empty($presaleUserZeroRound)) {
            $dataPresaleUserZeroRound['created_at'] = $transaction['timestamp'];
            $dataPresaleUserZeroRound['withdraw_status'] = ContractLibrary::INACTIVE;
            $presaleUserZeroRoundCollection->insertOne($dataPresaleUserZeroRound);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }
}
