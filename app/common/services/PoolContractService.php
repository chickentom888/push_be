<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Exception;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class PoolContractService extends BaseContractService
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
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolBuyLogCollection = $this->mongo->selectCollection('pool_buy_log');
        $poolUserLogCollection = $this->mongo->selectCollection('pool_user_log');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }
        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        $abiName = ContractLibrary::POOL . "_" . $pool['contract_version'];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $poolAddress) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("BuyToken: Invalid Event Data Tx: " . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $baseTokenAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $pool['base_token_decimals']))->toFloat();
        $poolTokenAmount = BigDecimal::of($eventDataDecode[2])->exactlyDividedBy(pow(10, $pool['pool_token_decimals']))->toFloat();

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$poolInfo, $pool) {
            if ($res) {
                $poolInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $pool['base_token_decimals']))->toFloat();
                $poolInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $pool['pool_token_decimals']))->toFloat();
                $poolInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $poolInfo['success_at'] = intval($res['successAt']->toString());
                $poolInfo['active_claim_at'] = intval($res['activeClaimAt']->toString());
                $poolInfo['current_status'] = intval($res['currentStatus']->toString());
                $poolInfo['current_round'] = intval($res['currentRound']->toString());
                $poolInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $poolInfo['current_round'] = -1;

                if ($poolInfo['success_at'] > 0) {
                    $poolInfo['message'] = 'Pool reached soft cap';
                }
                if ($poolInfo['total_base_collected'] >= $pool['hard_cap']) {
                    $poolInfo['message'] = 'Pool reached hard cap';
                }
            }
        });
        // </editor-fold>

        $poolInfo['token_sold_percent'] = $poolInfo['total_token_sold'] / $pool['amount'] * 100;
        $userAddress = $transaction['from'];

        // <editor-fold desc = "Pool Buy Log">
        $poolBuyLog = $poolBuyLogCollection->findOne([
            'hash' => $transaction['hash'],
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'pool_address' => $pool['contract_address'],
            'user_address' => $userAddress
        ]);

        if (empty($poolBuyLog)) {
            $dataPoolBuyLog = [
                'network' => $pool['network'],
                'platform' => $pool['platform'],
                'user_address' => $userAddress,
                'pool_address' => $pool['contract_address'],
                'hash' => $transaction['hash'],
                'base_token_amount' => $baseTokenAmount,
                'pool_token_amount' => $poolTokenAmount,
                'round' => $pool['current_round'],
                'created_at' => $transaction['timestamp']
            ];
            $poolBuyLogCollection->insertOne($dataPoolBuyLog);
        }
        // </editor-fold>

        // <editor-fold desc = "Get Buyer Info">
        $buyerInfo = [];
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_BUYER_INFO, $userAddress, function ($err, $res) use (&$buyerInfo, $pool) {
            if ($res) {
                $buyerInfo['base_token_amount'] = BigDecimal::of($res['baseDeposited']->toString())->exactlyDividedBy(pow(10, $pool['base_token_decimals']))->toFloat();
                $buyerInfo['pool_token_amount'] = BigDecimal::of($res['tokenBought']->toString())->exactlyDividedBy(pow(10, $pool['pool_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Pool User">
        $dataPoolUser = [
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'user_address' => $userAddress,
            'pool_address' => $poolAddress
        ];
        $poolUser = $poolUserLogCollection->findOne($dataPoolUser);
        if (!$poolUser) {
            $dataPoolUser['base_token_amount'] = $buyerInfo['base_token_amount'];
            $dataPoolUser['pool_token_amount'] = $buyerInfo['pool_token_amount'];
            $dataPoolUser['contract_type'] = $transaction['contract_type'];

            if (isset($pool['active_vesting']) && $pool['active_vesting'] == ContractLibrary::POOL_ACTIVE_VESTING) {
                if (!isset($pool['list_vesting_period']) || empty($pool['list_vesting_period'])
                    || !isset($pool['list_vesting_percent']) || empty($pool['list_vesting_percent'])
                    || count($pool['list_vesting_period']) != count($pool['list_vesting_percent'])) {
                    throw new Exception("Invalid vesting data. Contract Address" . $pool['contract_address']);
                }
                $dataPoolUser['active_vesting'] = $pool['active_vesting'];
                foreach ($pool['list_vesting_period'] as $key => $vestingTime) {
                    $dataPoolUser['list_vesting'][] = [
                        'vesting_period' => $vestingTime,
                        'vesting_percent' => $pool['list_vesting_percent'][$key],
                        'vesting_number' => $key + 1,
                        'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                    ];
                }
            }
            $dataPoolUser['withdraw_status'] = ContractLibrary::NOT_WITHDRAW;

            $poolUserLogCollection->insertOne($dataPoolUser);
        } else {
            $poolUserLogCollection->updateOne(['_id' => $poolUser['_id']], ['$set' => $buyerInfo]);
        }
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * @throws Exception
     */
    public function processPoolActiveClaim($transaction, $dataDecode)
    {
        $coinInstance = $this->web3;
        $poolCollection = $this->mongo->selectCollection('pool');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }
        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];

        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $poolAddress) {
                    $abiType = ContractLibrary::POOL . "_" . $pool['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Pool ActiveClaim: Invalid Event Data Tx: " . $transaction['hash']);
        }

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$poolInfo, $pool) {
            if ($res) {
                $poolInfo['is_active_claim'] = $res['isActiveClaim'];
                $poolInfo['active_claim_at'] = intval($res['activeClaimAt']->toString());
                $poolInfo['message'] = 'Pool active claim';
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Round Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_ROUND_INFO, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['active_auction_round'] = $res['activeAuctionRound'];
            }
        });
        // </editor-fold>


        // <editor-fold desc = "Get Auction Round Info">
        if ($poolInfo['active_auction_round']) {
            $poolInfo['auction_round'] = $pool['auction_round'];
            $poolContractInstance->call(ContractLibrary::FUNCTION_GET_AUCTION_ROUND_INFO, null, function ($err, $res) use (&$poolInfo, $coinInstance) {
                if ($res) {
                    $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;
                    $totalTokenAmount = ((!empty($res['totalTokenAmount'])) && ($res['totalTokenAmount'] instanceof BigInteger)) ? $res['totalTokenAmount']->toString() : 0;
                    $burnedTokenAmount = ((!empty($res['burnedTokenAmount'])) && ($res['burnedTokenAmount'] instanceof BigInteger)) ? $res['burnedTokenAmount']->toString() : 0;

                    $poolInfo['auction_round']['token_address'] = $coinInstance->toCheckSumAddress($res['tokenAddress']);
                    $poolInfo['auction_round']['start_time'] = intval($res['startTime']->toString());
                    $poolInfo['auction_round']['end_time'] = intval($res['endTime']->toString());
                    $poolInfo['auction_round']['registered_slot'] = intval($registeredSlot);
                    $poolInfo['auction_round']['total_token_amount'] = $totalTokenAmount;
                    $poolInfo['auction_round']['burned_token_amount'] = $burnedTokenAmount;
                    $poolInfo['auction_round']['refund_token_amount'] = 0;
                }
            });
            $poolInfo['auction_round']['total_token_amount'] = BigDecimal::of($poolInfo['auction_round']['total_token_amount'])->exactlyDividedBy(pow(10, $pool['auction_round']['token_decimals']))->toFloat();
            $poolInfo['auction_round']['burned_token_amount'] = BigDecimal::of($poolInfo['auction_round']['burned_token_amount'])->exactlyDividedBy(pow(10, $pool['auction_round']['token_decimals']))->toFloat();
        }
        // </editor-fold>

        $this->burnAuctionToken($poolAddress);
        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    private function burnAuctionToken($poolAddress)
    {
        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');
        $condition = [
            'network' => $this->network,
            'platform' => $this->platform,
            'pool_address' => $poolAddress,
        ];
        $poolWhitelist = $poolWhitelistCollection->find($condition);
        if (empty($poolWhitelist)) {
            return;
        }

        $poolWhitelist = $poolWhitelist->toArray();
        $listUserAddress = array_column($poolWhitelist, 'user_address');
        if (empty($listUserAddress)) {
            return;
        }
        $poolUserAuctionRoundCollection = $this->mongo->selectCollection('pool_user_auction_round');
        $condition['user_address'] = ['$in' => $listUserAddress];
        $poolUserAuctionRoundCollection->updateMany($condition, ['$set' => ['is_burned' => ContractLibrary::ACTIVE]]);
    }

    /**
     * Process Force Fail
     * @throws Exception
     */
    public function processForceFailContractPool($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['force_failed'] = $res['forceFailed'];
                $poolInfo['current_status'] = intval($res['currentStatus']->toString());
                $poolInfo['current_round'] = intval($res['currentRound']->toString());
                $poolInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $poolInfo['current_round'] = -1;
            }
        });
        $poolInfo['message'] = 'Admin force failed';
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * @throws Exception
     */
    public function processPoolContractUpdateTime($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_POOL_MAIN_INFO, null, function ($err, $res) use (&$poolInfo, $pool) {
            if ($res) {
                $poolInfo['start_time'] = intval($res['startTime']->toString());
                $poolInfo['end_time'] = intval($res['endTime']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $finishAt = ((!empty($res['finishAt'])) && ($res['finishAt'] instanceof BigInteger)) ? $res['finishAt']->toString() : 0;
                $poolInfo['zero_round.finish_at'] = intval($finishAt);
            }
        });
        // </editor-fold>

        $poolInfo['message'] = 'Owner update limit per buyer';

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    public function processPoolContractSetWhitelistFlag($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $value = $dataDecode['data_decode'][0];
        $poolInfo['whitelist_only'] = $value;
        $poolInfo['message'] = 'Owner update whitelist flag';

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    public function processPoolContractEditWhitelist($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $listAddress = $dataDecode['data_decode'][0];
        $action = $dataDecode['data_decode'][1];

        if ($action) {
            foreach ($listAddress as $address) {
                $address = $this->web3->toCheckSumAddress($address);
                $dataPoolWhitelist = [
                    'network' => $pool['network'],
                    'platform' => $pool['platform'],
                    'pool_address' => $pool['contract_address'],
                    'user_address' => $address
                ];
                $poolWhitelist = $poolWhitelistCollection->findOne($dataPoolWhitelist);
                if (!$poolWhitelist) {
                    $poolWhitelistCollection->insertOne($dataPoolWhitelist);
                }
            }
        } else {
            foreach ($listAddress as $address) {
                $address = $this->web3->toCheckSumAddress($address);
                $dataPoolWhitelist = [
                    'network' => $pool['network'],
                    'pool_address' => $pool['contract_address'],
                    'platform' => $pool['platform'],
                    'user_address' => $address
                ];
                $poolWhitelistCollection->deleteOne($dataPoolWhitelist);
            }
        }

        // <editor-fold desc = "Init Pool Contract Instance">
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($pool['contract_address']);
        // </editor-fold>

        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_WHITELISTED_USER_LENGTH, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['whitelisted_users_length'] = intval($res[0]->toString());
            }
        });

        $poolInfo['message'] = 'Owner update whitelist user';

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * @throws Exception
     */
    public function processPoolContractRegisterZeroRound($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserZeroRoundCollection = $this->mongo->selectCollection('pool_user_zero_round');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;
                $poolInfo['zero_round.registered_slot'] = intval($registeredSlot);
            }
        });
        // </editor-fold>
        $userAddress = $transaction['from'];

        // <editor-fold desc = "Pool User Zero Round">
        $dataPoolUserZeroRound = [
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'pool_address' => $pool['contract_address'],
            'user_address' => $userAddress
        ];
        $poolUserZeroRound = $poolUserZeroRoundCollection->findOne($dataPoolUserZeroRound);
        if (empty($poolUserZeroRound)) {
            $dataPoolUserZeroRound['created_at'] = $transaction['timestamp'];
            $dataPoolUserZeroRound['withdraw_status'] = ContractLibrary::INACTIVE;
            $poolUserZeroRoundCollection->insertOne($dataPoolUserZeroRound);
        }
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * @throws Exception
     */
    public function processPoolContractRegisterAuctionRound($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserAuctionRoundCollection = $this->mongo->selectCollection('pool_user_auction_round');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_AUCTION_ROUND_INFO, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;
                $poolInfo['auction_round.registered_slot'] = intval($registeredSlot);
            }
        });
        // </editor-fold>

        $userAddress = $transaction['from'];
        //<editor-fold desc = "Get Zero Round Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_AUCTION_USER_ROUND_INFO, $userAddress, function ($err, $res) use (&$pool, &$auctionRoundAmount) {
            if ($res) {
                $auctionRoundAmount = ((!empty($res['auctionAmount'])) && ($res['auctionAmount'] instanceof BigInteger)) ? BigDecimal::of($res['auctionAmount'])->exactlyDividedBy(pow(10, $pool['auction_round']['token_decimals']))->toFloat() : 0;
            }
        });
        //</editor-fold>

        // <editor-fold desc = "Pool User Auction Round">
        $condition = [
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'pool_address' => $pool['contract_address'],
            'user_address' => $userAddress
        ];

        $dataPoolUserAuctionRound = $condition;
        $dataPoolUserAuctionRound['auction_amount'] = $auctionRoundAmount;
        $dataPoolUserAuctionRound['created_at'] = $transaction['timestamp'];
        $dataPoolUserAuctionRound['is_burned'] = ContractLibrary::INACTIVE;
        $dataPoolUserAuctionRound['withdraw_status'] = ContractLibrary::INACTIVE;
        $poolUserAuctionRoundCollection->updateOne($condition, ['$set' => $dataPoolUserAuctionRound], ['upsert' => true]);
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * @throws Exception
     */
    public function processPoolContractUpdateLimitPerBuyer($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_POOL_MAIN_INFO, null, function ($err, $res) use (&$poolInfo, $pool) {
            if ($res) {
                $poolInfo['limit_per_buyer'] = BigDecimal::of($res['limitPerBuyer']->toString())->exactlyDividedBy(pow(10, $pool['base_token_decimals']))->toFloat();
                $poolInfo['max_buyer'] = ceil($pool['hard_cap'] / $poolInfo['limit_per_buyer']);
            }
        });
        // </editor-fold>
        $poolInfo['message'] = 'Owner update limit per buyer';

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * Process User Withdraw Base Token
     * @throws Exception
     */
    public function processPoolContractUserWithdrawBaseToken($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserLogCollection = $this->mongo->selectCollection('pool_user_log');
        $poolUserZeroRoundCollection = $this->mongo->selectCollection('pool_user_zero_round');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $poolAddress) {
                    $abiType = ContractLibrary::POOL . "_" . $pool['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $withdrawAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $pool['base_token_decimals']))->toFloat();

        // <editor-fold desc = "Get Status Info">
        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$poolInfo, $pool) {
            if ($res) {
                $poolInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $pool['base_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Pool User">
        $userAddress = $transaction['from'];
        $poolUser = $poolUserLogCollection->findOne([
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'pool_address' => $poolAddress,
            'user_address' => $userAddress
        ]);
        if ($poolUser) {
            $dataPoolUser = [
                'withdraw_status' => ContractLibrary::ACTIVE,
                'base_token_withdraw_amount' => $withdrawAmount,
                'withdraw_token_type' => 'base_token',
                'withdraw_at' => $transaction['timestamp'],
            ];
            $poolUserLogCollection->updateOne(['_id' => $poolUser['_id']], ['$set' => $dataPoolUser]);
        }
        // </editor-fold>

        // <editor-fold desc = "Pool User Zero Round">
        $poolUserZeroRound = $poolUserZeroRoundCollection->findOne([
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'pool_address' => $poolAddress,
            'user_address' => $userAddress
        ]);
        if ($poolUserZeroRound) {
            $dataPoolUserZeroRound = [
                'withdraw_status' => ContractLibrary::ACTIVE,
                'withdraw_at' => $transaction['timestamp'],
            ];
            $poolUserZeroRoundCollection->updateOne(['_id' => $poolUserZeroRound['_id']], ['$set' => $dataPoolUserZeroRound]);
        }
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * Pool CONTRACT: Process User Withdraw Pool Token
     * @throws Exception
     */
    public function processPoolContractUserWithdrawPoolToken($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserLogCollection = $this->mongo->selectCollection('pool_user_log');

        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolAddress = $pool['contract_address'];
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $poolAddress) {
                    $abiType = ContractLibrary::POOL . "_" . $pool['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Pool withdraw pool token Invalid Event Data transaction hash:" . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $withdrawAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $pool['pool_token_decimals']))->toFloat();
        $numberClaimed = $eventDataDecode[3];

        // <editor-fold desc = "Get Status Info">
        $poolContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$poolInfo, $pool) {
            if ($res) {
                $poolInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $pool['pool_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Pool User">
        $userAddress = $transaction['from'];
        $poolUser = $poolUserLogCollection->findOne([
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'pool_address' => $poolAddress,
            'user_address' => $userAddress
        ]);

        if ($poolUser) {
            if (isset($pool['active_vesting']) && $pool['active_vesting'] == ContractLibrary::POOL_ACTIVE_VESTING) {
                foreach ($poolUser['list_vesting'] as &$vesting) {
                    if ($vesting['vesting_number'] == $numberClaimed) {
                        $vesting['withdraw_status'] = ContractLibrary::WITHDRAWN;
                        $vesting['pool_token_withdraw_amount'] = $withdrawAmount;
                        $vesting['withdraw_token_type'] = 'pool_token';
                        $vesting['withdraw_at'] = $transaction['timestamp'];
                    }
                }
            } else {
                $poolUser['pool_token_withdraw_amount'] = $withdrawAmount;
                $poolUser['withdraw_at'] = $transaction['timestamp'];
            }
            $poolUser['withdraw_status'] = ContractLibrary::WITHDRAWN;
            $poolUser['withdraw_token_type'] = 'pool_token';
            $poolUserLogCollection->updateOne(['_id' => $poolUser['_id']], ['$set' => $poolUser]);
        }
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * Pool CONTRACT: Process User Withdraw Auction Token
     * @throws Exception
     */
    public function processUserWithdrawAuctionToken($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserAuctionRoundCollection = $this->mongo->selectCollection('pool_user_auction_round');


        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }
        $userAddress = $transaction['from'];
        $poolAddress = $pool['contract_address'];
        $poolUserAuctionRound = $poolUserAuctionRoundCollection->findOne([
            'network' => $transaction['network'],
            'platform' => $transaction['platform'],
            'pool_address' => $poolAddress,
            'user_address' => $userAddress,
        ]);

        if ($poolUserAuctionRound) {
            $dataUpdate = [
                'withdraw_status' => ContractLibrary::WITHDRAWN,
                'withdraw_at' => $transaction['timestamp'],
            ];
            $poolUserAuctionRoundCollection->updateOne(['_id' => $poolUserAuctionRound['_id']], ['$set' => $dataUpdate]);
        }

        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);

        // <editor-fold desc = "Init Pool Contract Instance">
        $poolContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $poolAddress) {
                    $abiType = ContractLibrary::POOL . "_" . $pool['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Pool withdraw auction token Invalid Event Data transaction hash:" . $transaction['hash']);
        }

        // <editor-fold desc = "Update Auction Round Info">
        $poolInfo = [];
        if ($pool['active_auction_round']) {
            $poolInfo['auction_round'] = $pool['auction_round'];
            $poolContractInstance->call(ContractLibrary::FUNCTION_GET_AUCTION_ROUND_INFO, null, function ($err, $res) use (&$poolInfo) {
                if ($res) {
                    $refundTokenAmount = ((!empty($res['refundTokenAmount'])) && ($res['refundTokenAmount'] instanceof BigInteger)) ? $res['refundTokenAmount']->toString() : 0;
                    $poolInfo['auction_round']['refund_token_amount'] = $refundTokenAmount;
                }
            });
            $poolInfo['auction_round']['refund_token_amount'] = BigDecimal::of($poolInfo['auction_round']['refund_token_amount'])->exactlyDividedBy(pow(10, $pool['auction_round']['token_decimals']))->toFloat();
        }
        // </editor-fold>

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * Process Owner Withdraw Base Token
     * @throws Exception
     */
    public function processPoolContractOwnerWithdrawBaseToken($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $poolInfo['message'] = 'Owner withdraw base token';
        $poolInfo['owner_withdraw_base_token'] = ContractLibrary::ACTIVE;
        $poolInfo['withdraw_base_token_at'] = $transaction['timestamp'];

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    /**
     * Process Owner Withdraw Pool Token
     * @throws Exception
     */
    public function processPoolContractOwnerWithdrawPoolToken($transaction, $dataDecode)
    {
        $coinInstance = $this->web3;
        $poolCollection = $this->mongo->selectCollection('pool');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        if (!isset($logsData[0]['data'])) {
            throw new Exception("POOL_OWNER_WITHDRAW_POOL_TOKEN_ERROR: Invalid Status log data. Pool Address: " . $pool['contract_address']);
        }

        $value = $coinInstance->convertHex2Dec($logsData[0]['data']);
        $withdrawPoolTokenAmount = BigDecimal::of($value)->exactlyDividedBy(pow(10, $pool['pool_token_decimals']))->toFloat();

        $poolInfo['message'] = 'Owner withdraw pool token';
        $poolInfo['owner_withdraw_pool_token'] = ContractLibrary::ACTIVE;
        $poolInfo['withdraw_pool_token_at'] = $transaction['timestamp'];
        $poolInfo['withdraw_pool_token_amount'] = $withdrawPoolTokenAmount;

        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }

    public function processUpdateVestingInfo($transaction, $dataDecode)
    {
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserLogCollection = $this->mongo->selectCollection('pool_user_log');

        $pool = $poolCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$pool) {
            return;
        }

        $coinInstance = $this->web3;
        // <editor-fold desc = "Re-init Contract Instance By Right Version">
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $pool['contract_version']);
        $poolContract = new Contract($coinInstance->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($pool['contract_address']);
        // </editor-fold>

        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_VESTING_INFO, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['active_vesting'] = $res['activeVesting'];
                if ($poolInfo['active_vesting']) {
                    $vestingPeriod = $res['vestingPeriod'];
                    $vestingPercent = $res['vestingPercent'];
                    foreach ($vestingPeriod as $item) {
                        $poolInfo['list_vesting_period'][] = intval($item->toString());
                    }

                    foreach ($vestingPercent as $item) {
                        $poolInfo['list_vesting_percent'][] = doubleval($item->toString() / 10);
                    }
                }
            }
        });

        if (isset($poolInfo['active_vesting']) && $poolInfo['active_vesting'] == ContractLibrary::POOL_ACTIVE_VESTING) {
            if (!isset($poolInfo['list_vesting_period']) || empty($poolInfo['list_vesting_period'])
                || !isset($poolInfo['list_vesting_percent']) || empty($poolInfo['list_vesting_percent'])
                || count($poolInfo['list_vesting_period']) != count($poolInfo['list_vesting_percent'])) {
                throw new Exception("Invalid vesting data. Contract Address" . $pool['contract_address']);
            }
            $vestingData['active_vesting'] = $poolInfo['active_vesting'];
            foreach ($poolInfo['list_vesting_period'] as $key => $vestingTime) {
                $vestingData['list_vesting'][] = [
                    'vesting_period' => $vestingTime,
                    'vesting_percent' => $poolInfo['list_vesting_percent'][$key],
                    'vesting_number' => $key + 1,
                    'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                ];
            }
            $condition = [
                'pool_address' => $transaction['to'],
                'network' => $transaction['network'],
                'platform' => $transaction['platform']
            ];
            $poolUserLogCollection->updateMany($condition, ['$set' => $vestingData]);
        }


        $this->updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo);
    }
}