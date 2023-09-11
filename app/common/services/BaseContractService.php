<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use MongoDB\Database;
use Phalcon\Di;
use phpseclib\Math\BigInteger;
use Redis;
use Web3\Contract;

class BaseContractService
{
    public $web3;
    /** @var Database */
    public $mongo;
    public $redis;
    public string $network;
    public string $platform;
    public string $mainCurrency;
    protected string $presaleChannel = ContractLibrary::RPUB_PRESALE_CHANGE;
    protected string $lotteryChannel = ContractLibrary::RPUB_LOTTERY_CHANGE;

    private static $instances = [];

    /**
     * @throws Exception
     */
    public function __construct($network, $platform)
    {
        /** @var Database $mongo */
        $this->mongo = DI::getDefault()->get('mongo');

        /** @var Redis $redis */
        $this->redis = DI::getDefault()->getShared('redis');

        $this->network = $network;
        $this->platform = $platform;
        $this->mainCurrency = Adapter::getMainCurrency($platform);

        $this->web3 = Adapter::getInstance($this->mainCurrency, $network);
    }

    /**
     * This is the static method that controls the access to the singleton instance.
     * @param $network
     * @param $platform
     * @return mixed|static
     * @throws Exception
     */
    public static function getInstance($network, $platform)
    {
        $cls = static::class;
        $key = $cls . '-' . $network . '-' . $platform;
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static($network, $platform);
        }

        return self::$instances[$key];
    }

    /**
     * Update Transaction And Presale After Process
     * @param $transaction
     * @param $dataDecode
     * @param $presale
     * @param $presaleInfo
     */
    protected function updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        // <editor-fold desc = "Update Presale">
        $presaleCollection->updateOne(['_id' => $presale['_id']], ['$set' => $presaleInfo]);
        $presale = array_merge($presale, $presaleInfo);
        $this->redis->publish($this->presaleChannel, json_encode([$presale]));
        // </editor-fold>
    }

    /**
     * Update Transaction And Presale After Process
     * @param $transaction
     * @param $dataDecode
     * @param $pool
     * @param $poolInfo
     */
    protected function updateTransactionAndPool($transaction, $dataDecode, $pool, $poolInfo)
    {
        $poolCollection = $this->mongo->selectCollection('pool');

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        // <editor-fold desc = "Update Presale">
        $poolCollection->updateOne(['_id' => $pool['_id']], ['$set' => $poolInfo]);
        $pool = array_merge($pool, $poolInfo);
        $pool['round_name'] = Helper::getPoolRoundName($pool);
        $pool['round_define'] = Helper::getPoolRoundDefine($pool);
        $this->redis->publish($this->presaleChannel, json_encode([$pool]));
        // </editor-fold>
    }

    /**
     * Update Transaction And Presale After Process
     * @param $transaction
     * @param $dataDecode
     * @param $lottery
     * @param $lotteryInfo
     * @throws Exception
     */
    protected function updateTransactionAndLottery($transaction, $dataDecode, $lottery, $lotteryInfo)
    {
        $lotteryCollection = $this->mongo->selectCollection('lottery');

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        // <editor-fold desc = "Update Presale">
        $lotteryCollection->updateOne(['_id' => $lottery['_id']], ['$set' => $lotteryInfo]);
        $lottery = array_merge($lottery, $lotteryInfo);
        $this->redis->publish($this->lotteryChannel, json_encode([$lottery]));
        // </editor-fold>
    }

    /**
     * Check transaction is liquid or token lock
     * @param $dataDecode
     * @return array
     * @throws Exception
     */
    public function checkTokenLiquid($dataDecode)
    {
        $inputData = $dataDecode['data_decode'];
        $tokenAddress = $inputData[0];

        $abi = ContractLibrary::getAbi(ContractLibrary::DEX_PAIR);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $abi);
        $contract = $contract->at($tokenAddress);

        $token0 = '';
        $functionName = 'token0';
        $contract->call($functionName, null, function ($err, $res) use (&$token0) {
            if ($res) {
                $token0 = $res[0];
            }
        });
        $token0IsAddress = $this->web3->validAddress($token0);
        if ($token0IsAddress) {
            $token0 = $this->web3->toCheckSumAddress($token0);
        }

        $token1 = '';
        $functionName = 'token1';
        $contract->call($functionName, null, function ($err, $res) use (&$token1) {
            if ($res) {
                $token1 = $res[0];
            }
        });

        $token1IsAddress = $this->web3->validAddress($token0);
        if ($token1IsAddress) {
            $token1 = $this->web3->toCheckSumAddress($token1);
        }

        $kLast = '';
        $functionName = 'kLast';
        $contract->call($functionName, null, function ($err, $res) use (&$kLast) {
            if ($res) {
                $kLast = $res[0]->toString();
            }
        });

        $isLiquid = ($token0IsAddress && $token1IsAddress && strlen($kLast));
        $data = [
            'is_liquid' => $isLiquid,
            'token0' => $token0,
            'token1' => $token1,
            'kLast' => $kLast,
        ];
        if ($isLiquid) {
            $data['liquid_address'] = $tokenAddress;
        }

        return $data;

    }

    /**
     * Get Liquid Info
     * @param $liquidInfo
     * @return mixed
     * @throws Exception
     */
    public function getTokenLiquid($liquidInfo)
    {
        $collection = $this->mongo->selectCollection('tokens');

        $liquidAddress = $liquidInfo['liquid_address'];
        $abi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $abi);
        $functionBalance = 'balanceOf';
        $functionDecimals = 'decimals';
        $functionSupply = 'totalSupply';
        $functionName = 'name';
        $functionSymbol = 'symbol';

        // <editor-fold desc = "Get Balance Token 0">
        $token0 = $liquidInfo['token0'];
        $contract0 = $contract->at($token0);
        $balance0 = null;
        $contract0->call($functionBalance, $liquidAddress, function ($err, $res) use (&$balance0) {
            if ($res) {
                $balance0 = $res[0]->toString();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Decimal, Name, Ticker Token 0">
        $token0Info = $collection->findOne(['address' => $token0]);
        if ($token0Info) {
            $token0Decimals = $token0Info['decimals'];
            $token0Name = $token0Info['name'];
            $token0Symbol = $token0Info['symbol'];
        } else {

            $contract0->call($functionDecimals, null, function ($err, $res) use (&$token0Decimals) {
                if ($res) {
                    $token0Decimals = intval($res[0]->toString());
                }
            });

            $contract0->call($functionName, null, function ($err, $res) use (&$token0Name) {
                if ($res) {
                    $token0Name = $res[0];
                }
            });

            $contract0->call($functionSymbol, null, function ($err, $res) use (&$token0Symbol) {
                if ($res) {
                    $token0Symbol = $res[0];
                }
            });
        }
        $balance0 = $balance0 / pow(10, $token0Decimals);
        // </editor-fold>

        // <editor-fold desc = "Get Total Supply Token 0">
        $token0Supply = null;
        $contract0->call($functionSupply, null, function ($err, $res) use (&$token0Supply) {
            if ($res) {
                $token0Supply = $res[0]->toString();
            }
        });
        $token0Supply = $token0Supply / pow(10, $token0Decimals);
        // </editor-fold>

        // <editor-fold desc = "Get Balance Token 1">
        $token1 = $liquidInfo['token1'];
        $contract1 = $contract->at($token1);
        $balance1 = null;
        $contract1->call($functionBalance, $liquidAddress, function ($err, $res) use (&$balance1) {
            if ($res) {
                $balance1 = $res[0]->toString();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Decimal, Name, Ticker Token 1">
        $token1Info = $collection->findOne(['address' => $token1]);
        if ($token1Info) {
            $token1Decimals = $token1Info['decimals'];
            $token1Name = $token1Info['name'];
            $token1Symbol = $token1Info['symbol'];
        } else {
            $contract1->call($functionDecimals, null, function ($err, $res) use (&$token1Decimals) {
                if ($res) {
                    $token1Decimals = intval($res[0]->toString());
                }
            });
            $contract1->call($functionName, null, function ($err, $res) use (&$token1Name) {
                if ($res) {
                    $token1Name = $res[0];
                }
            });
            $contract1->call($functionSymbol, null, function ($err, $res) use (&$token1Symbol) {
                if ($res) {
                    $token1Symbol = $res[0];
                }
            });
        }
        $balance1 = $balance1 / pow(10, $token1Decimals);
        // </editor-fold>

        // <editor-fold desc = "Get Total Supply Token 1">
        $token1Supply = null;
        $contract1->call($functionSupply, null, function ($err, $res) use (&$token1Supply) {
            if ($res) {
                $token1Supply = $res[0]->toString();
            }
        });
        $token1Supply = $token1Supply / pow(10, $token1Decimals);
        // </editor-fold>

        // <editor-fold desc = "Get Decimal, Name, Ticker Token Liquid">
        $contractLiquid = $contract->at($liquidAddress);
        $contractLiquid->call($functionDecimals, null, function ($err, $res) use (&$tokenLiquidDecimals) {
            if ($res) {
                $tokenLiquidDecimals = intval($res[0]->toString());
            }
        });
        $contractLiquid->call($functionName, null, function ($err, $res) use (&$tokenLiquidName) {
            if ($res) {
                $tokenLiquidName = $res[0];
            }
        });
        $contractLiquid->call($functionSymbol, null, function ($err, $res) use (&$tokenLiquidSymbol) {
            if ($res) {
                $tokenLiquidSymbol = $res[0];
            }
        });
        $contractLiquid->call($functionSupply, null, function ($err, $res) use (&$tokenLiquidTotalSupply) {
            if ($res) {
                $tokenLiquidTotalSupply = $res[0]->toString();
            }
        });
        $tokenLiquidTotalSupply = BigDecimal::of((new BigInteger($tokenLiquidTotalSupply))->toString())->exactlyDividedBy(pow(10, $tokenLiquidDecimals))->toFloat();
        $liquidInfo['liquid_token'] = [
            'address' => $liquidAddress,
            'name' => $tokenLiquidName,
            'decimals' => $tokenLiquidDecimals,
            'symbol' => $tokenLiquidSymbol,
            'total_supply' => $tokenLiquidTotalSupply,
        ];
        // </editor-fold>

        // <editor-fold desc = "Find out main token in pool">
        /**
         * Greater balance is the main token in pool
         */
        if ($balance0 > $balance1) {

            $liquidInfo['main_token'] = [
                'address' => $token0,
                'decimals' => $token0Decimals,
                'balance' => $balance0,
                'total_supply' => $token0Supply,
                'name' => $token0Name,
                'symbol' => $token0Symbol,
            ];

            $liquidInfo['sub_token'] = [
                'address' => $token1,
                'decimals' => $token1Decimals,
                'balance' => $balance1,
                'total_supply' => $token1Supply,
                'name' => $token1Name,
                'symbol' => $token1Symbol,
            ];

        } else {

            $liquidInfo['main_token'] = [
                'address' => $token1,
                'decimals' => $token1Decimals,
                'balance' => $balance1,
                'total_supply' => $token1Supply,
                'name' => $token1Name,
                'symbol' => $token1Symbol,
            ];

            $liquidInfo['sub_token'] = [
                'address' => $token0,
                'decimals' => $token0Decimals,
                'balance' => $balance0,
                'total_supply' => $token0Supply,
                'name' => $token0Name,
                'symbol' => $token0Symbol,
            ];
        }
        // </editor-fold>

        return $liquidInfo;

    }

    /**
     * Get Price Main Token
     * @return float|mixed
     */
    protected function getPricePlatformToken($platform)
    {
        $mainCurrency = Adapter::getMainCurrency($platform);
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $price = 0;
        if ($registry) {
            $key = "{$mainCurrency}_rate";
            $price = $registry[$key];
        }
        if ($price <= 0) {
            $price = ContractLibrary::getPriceByPlatform($platform);
        }
        return $price;

    }

    /**
     * Get Price Data
     * By Get Platform Token And Main Token In Pool
     * @param $platform
     * @param $network
     * @param $mainTokenInfo
     * @return float[]|int[]
     * @throws Exception
     */
    protected function getPriceTokenData($platform, $network, $mainTokenInfo)
    {
        $dexFactoryAbi = ContractLibrary::getAbi(ContractLibrary::DEX_FACTORY);
        $dexFactoryAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::DEX_FACTORY);
        $dexWrapTokenAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::DEX_WRAP_TOKEN);

        $mainTokenAddress = $mainTokenInfo['address'];
        $web3Provider = $this->web3->rpcConnector->getProvider();
        $factoryContract = new Contract($web3Provider, $dexFactoryAbi);
        $dexFactoryContract = $factoryContract->at($dexFactoryAddress);
        $functionGetPair = 'getPair';
        $addressPair = '';
        $dexFactoryContract->call($functionGetPair, $mainTokenAddress, $dexWrapTokenAddress, function ($err, $res) use (&$addressPair) {
            if ($res) {
                $addressPair = $res[0];
            }
        });

        if (strlen($addressPair)) {
            $addressPair = $this->web3->toCheckSumAddress($addressPair);
            $addressPair == ContractLibrary::ADDRESS_ZERO && $addressPair = '';
        }

        $tokenPriceUsd = $mainTokenBalance = $wrapTokenBalance = $wrapTokenUsd = 0;

        if (strlen($addressPair)) {
            $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
            $tokenContract = new Contract($web3Provider, $tokenAbi);
            $mainTokenContract = $tokenContract->at($mainTokenAddress);
            $functionBalance = 'balanceOf';

            $mainTokenContract->call($functionBalance, $addressPair, function ($err, $res) use (&$mainTokenBalance) {
                if ($res) {
                    $mainTokenBalance = $res[0]->toString();
                }
            });
            $mainTokenBalance = $mainTokenBalance / pow(10, $mainTokenInfo['decimals']);

            $wrapTokenContract = $tokenContract->at($dexWrapTokenAddress);
            $wrapTokenContract->call($functionBalance, $addressPair, function ($err, $res) use (&$wrapTokenBalance) {
                if ($res) {
                    $wrapTokenBalance = $res[0]->toString();
                }
            });
            $wrapTokenBalance = $wrapTokenBalance / pow(10, ContractLibrary::DEFAULT_DECIMALS);

            $pricePlatformToken = $this->getPricePlatformToken($platform);
            $wrapTokenUsd = $wrapTokenBalance * $pricePlatformToken;
            $tokenPriceUsd = $mainTokenBalance > 0 ? $wrapTokenUsd / $mainTokenBalance : 0;
        }

        return [
            'token_pool' => $mainTokenBalance,
            'token_price_usd' => $tokenPriceUsd,
            'total_supply_usd' => $tokenPriceUsd * $mainTokenInfo['total_supply'],
            'sub_token_balance' => $wrapTokenBalance,
            'sub_token_usd' => $wrapTokenUsd,
            'dex_address_pair' => $addressPair
        ];
    }

    /**
     * @throws ConnectionErrorException
     */
    protected function getCoinGeckoInfo($tokenInfo)
    {
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $baseUrl = "https://api.coingecko.com/api/v3/coins/";
        if ($tokenInfo['network'] != ContractLibrary::MAIN_NETWORK || !$tokenInfo['address']) {
            return;
        }
        $platformId = 'binance-smart-chain';
        if ($tokenInfo['platform'] == BinanceWeb3::PLATFORM) {
            $platformId = 'binance-smart-chain';
        }
        $baseUrl .= $platformId . "/contract/" . $tokenInfo['address'];
        $response = Request::get($baseUrl)->expectsJson()->send();
        if ($response->hasBody()) {
            $tokenData = Arrays::arrayFrom($response->body);
            $imageUrl = Helper::getUrl($tokenData['image']['large'] ?? '');
            $coinGeckoRank = $tokenData['coingecko_rank'] ?? '';
            $updateData = [
                'image' => $imageUrl,
                'coingecko_rank' => $coinGeckoRank
            ];
            $tokenCollection->updateOne(['_id' => $tokenInfo['_id']], ['$set' => $updateData]);
        }
    }

    /**
     * Update And Calculate Liquid Percent
     * @param $tokenInfo
     * @return float|int
     * @throws Exception
     */
    protected function calculateLiquidPercent($tokenInfo)
    {
        $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $tokenAbi);
        $functionSupply = 'totalSupply';
        $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');
        $listData = $lockHistoryCollection->find([
            'token_address' => $tokenInfo['address'],
            'network' => $tokenInfo['network'],
            'platform' => $tokenInfo['platform'],
            'type' => ContractLibrary::LOCK_TYPE_LIQUID,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW
        ]);
        $liquidPercent = 0;
        $totalLiquidSupply = 0;
        $totalLiquidLock = 0;

        if (!empty($listData)) {
            $listData = $listData->toArray();
            $listData = Arrays::groupArray($listData, 'contract_address');

            if (count($listData)) {
                foreach ($listData as $contractAddress => $listHistory) {

                    // <editor-fold desc = "Get Total Supply Main Token">
                    $contractInstance = $contract->at($contractAddress);
                    $contractInstance->call($functionSupply, null, function ($err, $res) use (&$totalSupply) {
                        if ($res) {
                            $totalSupply = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, ContractLibrary::DEFAULT_DECIMALS))->toFloat();
                        }
                    });
                    $totalLiquidSupply += $totalSupply;
                    // </editor-fold>

                    foreach ($listHistory as $historyItem) {
                        $dataUpdate = [
                            'percent' => BigDecimal::of($historyItem['amount'])->multipliedBy(100)->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat()
                        ];
                        $lockHistoryCollection->updateOne(['_id' => $historyItem['_id']], ['$set' => $dataUpdate]);
                        $totalLiquidLock += $historyItem['amount'];
                    }
                }

                if (BigDecimal::of($totalLiquidSupply)->isGreaterThan(0)) {
                    $liquidPercent = BigDecimal::of($totalLiquidLock)->multipliedBy(100)->dividedBy($totalLiquidSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat();
                }
            }
        }

        return $liquidPercent;
    }

    protected function getNewestUnlockHistory($tokenAddress, $network, $platform)
    {
        $listLockHistoryCollection = $this->mongo->selectCollection('lock_histories');
        return $listLockHistoryCollection->findOne([
            'token_address' => $tokenAddress,
            'network' => $network,
            'platform' => $platform,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW,
            'unlock_time' => [
                '$gte' => time()
            ]
        ], ['sort' => ['unlock_time' => 1]]);
    }

    /**
     * Calculate Token Percent
     * @param $tokenInfo
     * @return array
     * @throws Exception
     */
    protected function calculateTokenPercent($tokenInfo)
    {
        /*$dataAggregate = $this->mongo->selectCollection('lock_histories')->aggregate([
            [
                '$match' => [
                    'token_address' => $tokenInfo['address'],
                    'type' => ContractLibrary::LOCK_TYPE_TOKEN,
                    'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                ],
            ],
            [
                '$group' => [
                    '_id' => null,
                    'amount' => ['$sum' => '$amount']
                ]
            ],
        ]);
        $data = $dataAggregate->toArray();*/

        $listData = $this->mongo->selectCollection('lock_histories')->find([
            'token_address' => $tokenInfo['address'],
            'network' => $tokenInfo['network'],
            'platform' => $tokenInfo['platform'],
            'type' => ContractLibrary::LOCK_TYPE_TOKEN,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW
        ]);

        $tokenLockAmount = 0;
        $contractAddress = $tokenInfo['address'];
        $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $tokenAbi);
        $functionSupply = 'totalSupply';
        $contractInstance = $contract->at($contractAddress);
        $contractInstance->call($functionSupply, null, function ($err, $res) use (&$totalSupply) {
            if ($res) {
                $totalSupply = $res[0]->toString();
            }
        });
        $totalSupply = BigDecimal::of($totalSupply)->exactlyDividedBy(pow(10, $tokenInfo['decimals']))->toFloat();

        if (!empty($listData)) {
            $collection = $this->mongo->selectCollection('lock_histories');
            $listData = $listData->toArray();
            foreach ($listData as $item) {
                $dataUpdate = [
                    'percent' => BigDecimal::of($item['amount'])->multipliedBy(100)->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat()
                ];
                $collection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
                $tokenLockAmount += $item['amount'];
            }
        }

        $circulatingSupplyAmount = BigDecimal::of($totalSupply - $tokenLockAmount)->toFloat();
        $circulatingSupplyPercent = BigDecimal::of($circulatingSupplyAmount)->multipliedBy(100)->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat();
        $totalLockPercent = $tokenLockAmount <= 0 ? 0 : BigDecimal::of($tokenLockAmount)->multipliedBy(100)->dividedBy($totalSupply, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat();

        return [
            'token_lock_amount' => $tokenLockAmount,
            'total_supply' => $totalSupply,
            'token_lock_percent' => $totalLockPercent,
            'circulating_supply_amount' => $circulatingSupplyAmount,
            'circulating_supply_percent' => $circulatingSupplyPercent,
            'token_lock_value' => $tokenLockAmount * $tokenInfo['token_price_usd']
        ];
    }

    /**
     * @param $data
     * @param $abi
     * @return array
     * @throws Exception
     */
    public function updateStatusByABI($data, $abi)
    {
        $coinInstance = $this->web3;
        $initAbi = ContractLibrary::getAbi($abi, $data['contract_version']);
        $contract = new Contract($coinInstance->rpcConnector->getProvider(), $initAbi);
        $contractInstance = $contract->at($data['contract_address']);

        // <editor-fold desc = "Get Status and Round Info">
        $contractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use ($abi, &$updateData) {
            if ($res) {
                $updateData['current_status'] = intval($res['currentStatus']->toString());
                $updateData['success_at'] = intval($res['successAt']->toString());
                $updateData['current_round'] = intval($res['currentRound']->toString());
                $updateData['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $updateData['current_round'] = ContractLibrary::AWAITING_START;
            } elseif ($err) {
                $message = "Error when run task update $abi Status" . PHP_EOL;
                $message .= "Message: " . $err->getMessage() . "." . PHP_EOL;

                Helper::sendTelegramMsgMonitor($message);
            }
        });
        // </editor-fold>

        if (!$updateData) {
            throw new Exception("{$abi}_UPDATE_STATUS_ERROR: Invalid Status Info Data. Contract Address: " . $data['contract_address']);
        }

        if ($abi == ContractLibrary::POOL) {
            $collection = $this->mongo->selectCollection('pool');
        } else {
            $collection = $this->mongo->selectCollection('presale');
        }
        $collection->updateOne(['_id' => $data['_id']], ['$set' => $updateData]);

        return array_merge($data, $updateData);
    }

    public function createRegistryLog($logType, $network, $platform, $oldValue, $value, $createdAt, $checkDifferent = true)
    {

        //<editor-fold desc="Kiểm tra có check sự khác biệt ko ? Nếu có bật thì so sánh dữ liệu cũ và mới khác nhau mới insert log">
        if ($checkDifferent) {
            if (empty(array_diff($oldValue, $value)) && empty(array_diff($value, $oldValue))) {
                return;
            }
        }
        //</editor-fold>

        $registryLogCollection = $this->mongo->selectCollection('registry_log');
        $logInsert = [
            "type" => $logType,
            "network" => $network,
            "platform" => $platform,
            "old_value" => $oldValue,
            "value" => $value,
            "created_at" => intval($createdAt),
        ];
        $registryLogCollection->insertOne($logInsert);
    }

    public function updateTxHash($transaction, $collection, $type)
    {
        try {
            $coinInstance = $this->web3;
            $transactionReceiptData = $coinInstance->getTransactionReceipt($transaction['hash']);
            $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
            $eventLogData = [];
            foreach ($logsData as $logItem) {
                if (isset($logItem['address'])) {
                    $logItem['address'] = $coinInstance->toCheckSumAddress($logItem['address']);
                    if ($logItem['address'] == $transaction['to']) {
                        $eventLogData = $coinInstance->decodeEventInputData($logItem, $type);
                    }
                }
            }

            if (!isset($eventLogData['data_decode'][0])) {
                throw new Exception("Invalid Event Data");
            }

            $eventDataDecode = $eventLogData['data_decode'];
            $contractAddress = $coinInstance->toCheckSumAddress($eventDataDecode[1]);
            $updateInfo['hash'] = $transaction['hash'];

            $collection->updateOne(['contract_address' => $contractAddress], ['$set' => $updateInfo]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateTransaction($transaction, $dataDecode)
    {
        $transactionCollection = $this->mongo->selectCollection('transaction');
        // <editor-fold desc = "Update Transaction">
        $transactionData = [
            'is_process' => ContractLibrary::PROCESSED,
            'process_at' => time(),
            'function' => $dataDecode['name'],
            'data_decode' => $dataDecode,
            'data_input' => $dataDecode['data_decode'] ?? null,
        ];
        $transactionCollection->updateOne(['_id' => $transaction['_id']], ['$set' => $transactionData]);
        // </editor-fold>
    }
}
