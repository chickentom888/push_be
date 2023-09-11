<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Collections\BaseCollection;
use Dcore\Collections\Users;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class StakingService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processStaking($transaction, $dataDecode)
    {
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userPackageHistoryCollection = $this->mongo->selectCollection('user_package_history');
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $userAddress = $transaction['from'];
        $contractAddress = $transaction['to'];

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $settingKey = "staking_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $stakingToken = $settingInfo['staking_token'];
        $stakingTokenDecimals = $stakingToken['token_decimals'];
        $coinRate = $registry['coin_rate'];
        $createdAt = $transaction['timestamp'];

        // <editor-fold desc = "Decode Event Data">
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $abiName = ContractLibrary::STAKING;
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }
        // </editor-fold>

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Staking Data");
        }
        $eventDataDecode = $eventLogData['data_decode'];

        // <editor-fold desc = "Process Staking Event Data">
        $tokenAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $stakingTokenDecimals))->toFloat();
        $contractId = intval($eventDataDecode[2]);
        $paymentToken = $this->web3->toCheckSumAddress($eventDataDecode[3]);
        // </editor-fold>
        $usdAmount = $tokenAmount * $coinRate;

        $isUpdateTree = false;
        // <editor-fold desc="Process User Connect">
        $userConnect = $userConnectCollection->findOne([
            'address' => $userAddress
        ]);
        if (!$userConnect) {
            $dataUserConnect = [
                'address' => $userAddress,
                'branch_for_child' => BaseCollection::BRANCH_LEFT,
                'diagram_date' => time(),
                'code' => Helper::randomString(12),
                'inviter_id' => null,
                'parent_id' => null,
                'created_at' => $createdAt,
            ];
            $userConnect['_id'] = $userConnectCollection->insertOne($dataUserConnect)->getInsertedId();
            $isUpdateTree = true;
        }
        // </editor-fold>

        // <editor-fold desc="Process User Package">
        $dataUserPackage = [
            'user_address' => $userAddress,
            'network' => $network,
            'platform' => $platform,
            'status' => BaseCollection::STATUS_ACTIVE,
            'expired_at' => [
                '$gt' => time()
            ]
        ];
        $userPackage = $userPackageCollection->findOne($dataUserPackage);
        if (empty($userPackage)) {
            $interestMaxDay = 9 * 30;
            $principalMaxDay = 90;
            $fundInterestMaxTimes = 10;
            $fundInterestStartAfterTime = 3;
            $dataUserPackage['code'] = Helper::randomString(12);
            $dataUserPackage['status'] = BaseCollection::STATUS_ACTIVE;
            $dataUserPackage['user_connect_id'] = $userConnect['_id'];
            $dataUserPackage['created_at'] = $createdAt;
            $dataUserPackage['token_amount'] = $tokenAmount;
            $dataUserPackage['contract_id'] = $contractId;
            $dataUserPackage['expired_at'] = strtotime("+$interestMaxDay days", $createdAt);

            $dataUserPackage['fund_interest_at'] = strtotime("+$fundInterestStartAfterTime months", $createdAt);
            $dataUserPackage['fund_interest_max_times'] = $fundInterestMaxTimes;
            $dataUserPackage['fund_interest_paid_times'] = 0;
            $dataUserPackage['last_fund_interest_at'] = null;
            $dataUserPackage['next_fund_interest_at'] = strtotime("+1 days", $dataUserPackage['fund_interest_at']);
            $dataUserPackage['total_fund_interest_amount'] = 0;
            $dataUserPackage['fund_interest_amount_pending'] = 0;
            $dataUserPackage['fund_interest_amount_paid'] = 0;

            $dataUserPackage['principal_max_day'] = $principalMaxDay;
            $dataUserPackage['principal_paid_day'] = 0;
            $dataUserPackage['last_principal_at'] = null;
            $dataUserPackage['next_principal_at'] = strtotime("+1 days", $dataUserPackage['expired_at']);
            $dataUserPackage['principal_amount_paid'] = 0;

            $dataUserPackage['interest_max_day'] = $interestMaxDay;
            $dataUserPackage['interest_paid_day'] = 0;
            $dataUserPackage['last_interest_at'] = null;
            $dataUserPackage['next_interest_at'] = strtotime("+1 days", $createdAt);
            $dataUserPackage['interest_amount_paid'] = 0;

            $dataUserPackage['interest_percent_month'] = Helper::getStakingInterestPercent($tokenAmount);
            $dataUserPackage['interest_percent_day'] = $dataUserPackage['interest_percent_month'] / 30;
            $dataUserPackage['payment_token_address'] = $paymentToken;
            $dataUserPackage['payment_token_symbol'] = $stakingToken['token_symbol'];
            $dataUserPackage['token_change'] = [
                $createdAt => $tokenAmount
            ];
            $userPackageId = $userPackageCollection->insertOne($dataUserPackage)->getInsertedId();
        } else {
            $userPackageId = $userPackage['_id'];
            $match = [
                'platform' => $platform,
                'network' => $network,
                'user_address' => $userAddress,
                'contract_id' => ['$gte' => $userPackage['contract_id']]
            ];
            $conditions = [
                [
                    '$match' => $match
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "token_amount" => [
                            '$sum' => '$token_amount'
                        ]
                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "token_amount" => 1
                    ],
                ],
            ];
            $summaryData = $userPackageHistoryCollection->aggregate($conditions);
            !empty($summaryData) && $summaryData = $summaryData->toArray();
            $totalTokenAmount = $summaryData[0]['token_amount'];
            $totalTokenAmount += $tokenAmount;
            $dataUserPackageUpdate['token_change'] = $userPackage['token_change'];
            $dataUserPackageUpdate['token_change'][$createdAt] = $totalTokenAmount;
            $dataUserPackageUpdate['token_amount'] = $totalTokenAmount;
            $dataUserPackageUpdate['interest_percent_month'] = Helper::getStakingInterestPercent($dataUserPackageUpdate['token_amount']);
            $dataUserPackageUpdate['interest_percent_day'] = $dataUserPackage['interest_percent_month'] / 30;
            $userPackageCollection->updateOne(['_id' => $userPackageId], ['$set' => $dataUserPackageUpdate]);
        }
        // </editor-fold>

        // <editor-fold desc="Process User Package History">
        $datUserPackageHistory = [
            'user_address' => $userAddress,
            'user_connect_id' => $userConnect['_id'],
            'hash' => $transaction['hash'],
            'network' => $network,
            'platform' => $platform,
            'contract_id' => $contractId,
            'contract_address' => $contractAddress
        ];
        $userPackageHistory = $userPackageHistoryCollection->findOne($datUserPackageHistory);
        if (!$userPackageHistory) {
            $datUserPackageHistory += [
                'code' => Helper::randomString(12),
                'user_package_id' => $userPackageId,
                'token_amount' => $tokenAmount,
                'usd_amount' => $usdAmount,
                'coin_rate' => $coinRate,
                'payment_token_amount' => $tokenAmount,
                'payment_token_address' => $paymentToken,
                'payment_token_symbol' => $stakingToken['token_symbol'],
                'payment_token_type' => ContractLibrary::PAYMENT_TYPE_STAKING_TOKEN,
                'created_at' => $createdAt,
                'is_staking_token' => BaseCollection::STATUS_ACTIVE,
                'is_direct_bonus' => BaseCollection::STATUS_INACTIVE,
                'is_team_bonus' => BaseCollection::STATUS_INACTIVE,
            ];
            $userPackageHistoryCollection->insertOne($datUserPackageHistory);
        }
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        if ($isUpdateTree) {
            Users::calcTree();
        }
    }

    /**
     * @throws Exception
     */
    public function processBuy($transaction, $dataDecode)
    {
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userPackageHistoryCollection = $this->mongo->selectCollection('user_package_history');
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $userAddress = $transaction['from'];
        $contractAddress = $transaction['to'];

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $settingKey = "staking_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $stakingToken = $settingInfo['staking_token'];
        $stakingTokenDecimals = $stakingToken['token_decimals'];
        $swapToken = $settingInfo['swap_token'];
        $swapTokenDecimals = $swapToken['token_decimals'];
        $createdAt = $transaction['timestamp'];

        // <editor-fold desc = "Decode Event Data">
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $abiName = ContractLibrary::STAKING;
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }
        // </editor-fold>

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Staking Data");
        }
        $eventDataDecode = $eventLogData['data_decode'];

        // <editor-fold desc = "Process Staking Event Data">
        $swapTokenAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $swapTokenDecimals))->toFloat();
        $swapTokenBalance = BigDecimal::of($eventDataDecode[2])->exactlyDividedBy(pow(10, $swapTokenDecimals))->toFloat();
        $stakingTokenBalance = BigDecimal::of($eventDataDecode[3])->exactlyDividedBy(pow(10, $stakingTokenDecimals))->toFloat();
        $contractId = intval($eventDataDecode[4]);
        $paymentToken = $this->web3->toCheckSumAddress($eventDataDecode[5]);
        // </editor-fold>

        $coinRate = $swapTokenBalance / $stakingTokenBalance;
        $stakingTokenAmount = $swapTokenAmount / $coinRate;

        $isUpdateTree = false;
        // <editor-fold desc="Process User Connect">
        $userConnect = $userConnectCollection->findOne([
            'address' => $userAddress
        ]);
        if (!$userConnect) {
            $dataUserConnect = [
                'address' => $userAddress,
                'branch_for_child' => BaseCollection::BRANCH_LEFT,
                'diagram_date' => time(),
                'code' => Helper::randomString(12),
                'level' => 0,
                'inviter_id' => null,
                'parent_id' => null,
                'created_at' => $createdAt,
            ];
            $userConnect['_id'] = $userConnectCollection->insertOne($dataUserConnect)->getInsertedId();
            $isUpdateTree = true;
        }
        // </editor-fold>

        // <editor-fold desc="Process User Package">
        $dataUserPackage = [
            'user_address' => $userAddress,
            'network' => $network,
            'platform' => $platform,
            'status' => BaseCollection::STATUS_ACTIVE,
            'expired_at' => [
                '$gt' => time()
            ]
        ];
        $userPackage = $userPackageCollection->findOne($dataUserPackage);
        if (empty($userPackage)) {
            $interestMaxDay = 9 * 30;
            $principalMaxDay = 90;
            $fundInterestMaxTimes = 10;
            $fundInterestStartAfterTime = 3;
            $dataUserPackage['code'] = Helper::randomString(12);
            $dataUserPackage['status'] = BaseCollection::STATUS_ACTIVE;
            $dataUserPackage['user_connect_id'] = $userConnect['_id'];
            $dataUserPackage['created_at'] = $createdAt;
            $dataUserPackage['token_amount'] = $stakingTokenAmount;
            $dataUserPackage['contract_id'] = $contractId;
            $dataUserPackage['expired_at'] = strtotime("+$interestMaxDay days", $createdAt);

            $dataUserPackage['fund_interest_at'] = strtotime("+$fundInterestStartAfterTime months", $createdAt);
            $dataUserPackage['fund_interest_max_times'] = $fundInterestMaxTimes;
            $dataUserPackage['fund_interest_paid_times'] = 0;
            $dataUserPackage['last_fund_interest_at'] = null;
            $dataUserPackage['next_fund_interest_at'] = strtotime("+1 days", $dataUserPackage['fund_interest_at']);
            $dataUserPackage['total_fund_interest_amount'] = 0;
            $dataUserPackage['fund_interest_amount_pending'] = 0;
            $dataUserPackage['fund_interest_amount_paid'] = 0;

            $dataUserPackage['principal_max_day'] = $principalMaxDay;
            $dataUserPackage['principal_paid_day'] = 0;
            $dataUserPackage['last_principal_at'] = null;
            $dataUserPackage['next_principal_at'] = strtotime("+1 days", $dataUserPackage['expired_at']);
            $dataUserPackage['principal_amount_paid'] = 0;

            $dataUserPackage['interest_max_day'] = $interestMaxDay;
            $dataUserPackage['interest_paid_day'] = 0;
            $dataUserPackage['last_interest_at'] = null;
            $dataUserPackage['next_interest_at'] = strtotime("+1 days", $createdAt);
            $dataUserPackage['interest_amount_paid'] = 0;

            $dataUserPackage['interest_percent_month'] = Helper::getStakingInterestPercent($stakingTokenAmount);
            $dataUserPackage['interest_percent_day'] = $dataUserPackage['interest_percent_month'] / 30;
            $dataUserPackage['payment_token_address'] = $paymentToken;
            $dataUserPackage['payment_token_symbol'] = $swapToken['token_symbol'];
            $dataUserPackage['token_change'] = [
                $createdAt => $stakingTokenAmount
            ];
            $userPackageId = $userPackageCollection->insertOne($dataUserPackage)->getInsertedId();
        } else {
            $userPackageId = $userPackage['_id'];
            $match = [
                'platform' => $platform,
                'network' => $network,
                'user_address' => $userAddress,
                'contract_id' => ['$gte' => $userPackage['contract_id']]
            ];
            $conditions = [
                [
                    '$match' => $match
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "token_amount" => [
                            '$sum' => '$token_amount'
                        ]
                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "token_amount" => 1
                    ],
                ],
            ];
            $summaryData = $userPackageHistoryCollection->aggregate($conditions);
            !empty($summaryData) && $summaryData = $summaryData->toArray();
            $totalTokenAmount = $summaryData[0]['token_amount'];
            $totalTokenAmount += $stakingTokenAmount;
            $dataUserPackageUpdate['token_change'] = $userPackage['token_change'];
            $dataUserPackageUpdate['token_change'][$createdAt] = $totalTokenAmount;
            $dataUserPackageUpdate['token_amount'] = $totalTokenAmount;
            $dataUserPackageUpdate['interest_percent_month'] = Helper::getStakingInterestPercent($dataUserPackageUpdate['token_amount']);
            $dataUserPackageUpdate['interest_percent_day'] = $dataUserPackage['interest_percent_month'] / 30;
            $userPackageCollection->updateOne(['_id' => $userPackageId], ['$set' => $dataUserPackageUpdate]);
        }
        // </editor-fold>

        // <editor-fold desc="Process User Package History">
        $datUserPackageHistory = [
            'user_address' => $userAddress,
            'user_connect_id' => $userConnect['_id'],
            'hash' => $transaction['hash'],
            'network' => $network,
            'platform' => $platform,
            'contract_id' => $contractId,
            'contract_address' => $contractAddress
        ];
        $userPackageHistory = $userPackageHistoryCollection->findOne($datUserPackageHistory);
        if (!$userPackageHistory) {
            $datUserPackageHistory += [
                'code' => Helper::randomString(12),
                'user_package_id' => $userPackageId,
                'token_amount' => $stakingTokenAmount,
                'usd_amount' => $swapTokenAmount,
                'coin_rate' => $coinRate,
                'swap_token_balance' => $swapTokenBalance,
                'staking_token_balance' => $stakingTokenBalance,
                'payment_token_amount' => $swapTokenAmount,
                'payment_token_address' => $paymentToken,
                'payment_token_symbol' => $swapToken['token_symbol'],
                'payment_token_type' => ContractLibrary::PAYMENT_TYPE_SWAP_TOKEN,
                'support_liquid_status' => BaseCollection::STATUS_INACTIVE,
                'created_at' => $createdAt,
                'is_staking_token' => BaseCollection::STATUS_INACTIVE,
                'is_direct_bonus' => BaseCollection::STATUS_INACTIVE,
                'is_team_bonus' => BaseCollection::STATUS_INACTIVE,
            ];
            $userPackageHistoryCollection->insertOne($datUserPackageHistory);
        }
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        if ($isUpdateTree) {
            Users::calcTree();
        }
    }

    /**
     * @throws Exception
     */
    public function updateStakingSetting($stakingAddress)
    {
        $network = $this->network;
        $platform = $this->platform;
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = $this->web3;
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contractToken = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
        $abiStaking = ContractLibrary::getAbi(ContractLibrary::STAKING);
        $contractLottery = new Contract($coinInstance->rpcConnector->getProvider(), $abiStaking);
        $contractStakingInstance = $contractLottery->at($stakingAddress);

        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);

        $settingInfo = [
            'setting_address' => $stakingAddress
        ];

        // <editor-fold desc = "Get Staking Token">
        $functionStakingToken = ContractLibrary::FUNCTION_STAKING_TOKEN;
        $contractStakingInstance->call($functionStakingToken, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['staking_token']['token_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Swap Token">
        $functionSwapToken = ContractLibrary::FUNCTION_SWAP_TOKEN;
        $contractStakingInstance->call($functionSwapToken, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['swap_token']['token_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Staking Token Info">
        if (isset($settingInfo['staking_token']['token_address'])) {
            if (strlen($settingInfo['staking_token']['token_address']) && $settingInfo['staking_token']['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $stakingTokenInstance = $contractToken->at($settingInfo['staking_token']['token_address']);
                $stakingTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $decimals = intval($res[0]->toString());
                        $settingInfo['staking_token']['token_decimals'] = $decimals;
                    }
                });

                $stakingTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['staking_token']['token_name'] = $res[0];
                    }
                });
                $stakingTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['staking_token']['token_symbol'] = $res[0];
                    }
                });
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Staking Token Info">
        if (isset($settingInfo['swap_token']['token_address'])) {
            if (strlen($settingInfo['swap_token']['token_address']) && $settingInfo['swap_token']['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $swapTokenInstance = $contractToken->at($settingInfo['swap_token']['token_address']);
                $swapTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $decimals = intval($res[0]->toString());
                        $settingInfo['swap_token']['token_decimals'] = $decimals;
                    }
                });

                $swapTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['swap_token']['token_name'] = $res[0];
                    }
                });
                $swapTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['swap_token']['token_symbol'] = $res[0];
                    }
                });
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Operator Address">
        $functionOperatorAddress = ContractLibrary::FUNCTION_OPERATOR_ADDRESS;
        $contractStakingInstance->call($functionOperatorAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['operator_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Dex Pair Address">
        $functionDexPairAddress = ContractLibrary::FUNCTION_DEX_PAIR_ADDRESS;
        $contractStakingInstance->call($functionDexPairAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['dex_pair_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Fee Staking Address">
        $functionFeeStakingAddress = ContractLibrary::FUNCTION_FEE_STAKING_ADDRESS;
        $contractStakingInstance->call($functionFeeStakingAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['fee_staking_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Fee Swap Address">
        $functionFeeSwapAddress = ContractLibrary::FUNCTION_FEE_SWAP_ADDRESS;
        $contractStakingInstance->call($functionFeeSwapAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['fee_swap_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey = "staking_setting_{$platform}_$network";
        $dataUpdate = [
            "{$settingKey}" => $settingInfo,
        ];
        $dataUpdate['dex_pair']['address'] = $settingInfo['dex_pair_address'];

        if (count($settingInfo)) {
            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();
            if ($registry) {
                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $dataUpdate['dex_pair']['address'] = $settingInfo['dex_pair_address'];
                $collection->insertOne($dataUpdate);
            }
        }

        return $settingInfo;
    }

    /**
     * Process Update Setting
     * @throws Exception
     */
    public function processUpdateSetting($transaction, $dataDecode)
    {
        $stakingSettingAddress = $transaction['to'];
        $this->updateStakingSetting($stakingSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

}