<?php

namespace Dcore\Modules\Api\Controllers;

use AndrewBreksa\RSMQ\RSMQClient;
use Brick\Math\BigDecimal;
use Dcore\Collections\BaseCollection;
use Dcore\ControllerBase\ControllerBase;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use Dcore\Library\Swap;
use Dcore\Services\LockContractService;
use Dcore\Services\LotteryService;
use Dcore\Services\StakingService;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use Exception;
use Httpful\Request;
use phpseclib\Math\BigInteger;
use Predis\Client;
use RedisException;
use Web3\Contract;

class TestController extends ControllerBase
{
    public function indexAction()
    {
        $a = "125, 375, 750, 1250, 2500, 5000";
        $a = explode(",", $a);
        $a = array_map('intval', $a);
        var_dump($a);
        die;
        print_r("Ver: 1.2") . PHP_EOL;
        die;
    }

    public function getInfoAction()
    {
        try {
            $dataPost = $this->postData;
            $userAddress = $dataPost['user_address'];
            $contractAddress = $dataPost['contract_address'];
            $platform = $dataPost['platform'];
            $network = $dataPost['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $type = $dataPost['type'] == ContractLibrary::LOCK_TYPE_TOKEN ? ContractLibrary::LOCK_TYPE_TOKEN : ContractLibrary::LOCK_TYPE_LIQUID;
            $tokenKey = Adapter::listMainCurrency()[$platform];
            if (!$tokenKey) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid platform');
            }

            // <editor-fold desc = "Check Address">
            if (!$contractAddress) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Contract address not found');
            }
            // </editor-fold>

            $coinInstance = Adapter::getInstance($tokenKey, $network);


            // <editor-fold desc = "Validate Address">

            $validContractAddress = $coinInstance->validAddress($contractAddress);
            if (!$validContractAddress) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid contract address');
            }
            // </editor-fold>

            $contractAddress = $coinInstance->toCheckSumAddress($contractAddress);

            $mainTokenInfo = [];

            $functionDecimals = 'decimals';
            $functionSymbol = 'symbol';
            $functionName = 'name';
            $functionBalanceOf = 'balanceOf';
            $functionTotalSupply = 'totalSupply';
            $cacheKey = "Search_token:{$platform}_{$network}_$contractAddress";

            $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
            $tokenCollection = $this->mongo->selectCollection('tokens');
            $contract = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
            $tokenContract = $contract->at($contractAddress);
            $tokenCache = $this->redis->get($cacheKey);
//            if (!$tokenCache) {
            $tokenInfo = $tokenCollection->findOne(['address' => $contractAddress]);
            $mainTokenInfo['chainId'] = $coinInstance->chainId ?? '';

            if ($tokenInfo) {

                $mainTokenInfo['decimals'] = $tokenInfo['decimals'];
                $mainTokenInfo['name'] = $tokenInfo['name'];
                $mainTokenInfo['symbol'] = $tokenInfo['symbol'];
                $mainTokenInfo['liquid_lock_percent'] = $tokenInfo['liquid_lock_percent'] ?? null;
                $mainTokenInfo['liquid_lock_value'] = $tokenInfo['liquid_lock_value'] ?? null;
                $mainTokenInfo['total_lock_value'] = $tokenInfo['total_lock_value'] ?? null;
                $mainTokenInfo['lock_time'] = $tokenInfo['lock_time'] ?? null;
                $mainTokenInfo['unlock_time'] = $tokenInfo['unlock_time'] ?? null;

            } else {

                $liquidInfo = $this->checkTokenLiquid($contractAddress);

                if (!$liquidInfo['is_liquid']) {
                    // <editor-fold desc = "Get Decimal, Name, Ticker Main Token">
                    $tokenContract->call($functionDecimals, null, function ($err, $res) use (&$mainTokenInfo) {
                        if ($res) {
                            $mainTokenInfo['decimals'] = intval($res[0]->toString());
                        }
                        if ($err) {
                            Helper::debug($err->getMessage());
                        }
                    });
                    $tokenContract->call($functionName, null, function ($err, $res) use (&$mainTokenInfo) {
                        if ($res) {
                            $mainTokenInfo['name'] = $res[0];
                        }
                        if ($err) {
                            Helper::debug($err->getMessage());
                        }
                    });
                    $tokenContract->call($functionSymbol, null, function ($err, $res) use (&$mainTokenInfo) {
                        if ($res) {
                            $mainTokenInfo['symbol'] = $res[0];
                        }
                        if ($err) {
                            Helper::debug($err->getMessage());
                        }
                    });
                    $tokenContract->call($functionTotalSupply, null, function ($err, $res) use (&$mainTokenInfo) {
                        if ($res) {
                            $mainTokenInfo['total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $mainTokenInfo['decimals']))->toFloat();
                        }
                        if ($err) {
                            Helper::debug($err->getMessage());
                        }
                    });
                    // </editor-fold>
                    Helper::debug('$mainTokenInfo: ', $mainTokenInfo);
                } else {
                    $liquidInfo = $this->getTokenLiquid($liquidInfo);
                    Helper::debug('$liquidInfo: ', $liquidInfo);
                    $mainTokenInfo = $liquidInfo['main_token'];
                    $subTokenInfo = $liquidInfo['sub_token'];
                    $liquidTokenInfo = $liquidInfo['liquid_token'];

                    $mainTokenInfo['main_token'] = $mainTokenInfo;
                    $mainTokenInfo['sub_token'] = $subTokenInfo;
                    $mainTokenInfo['liquid_token'] = $liquidTokenInfo;
                    $mainTokenInfo['name'] = $liquidTokenInfo['name'];
                    if ($type == ContractLibrary::LOCK_TYPE_LIQUID) {
                        $mainTokenInfo['name'] = $mainTokenInfo['symbol'] . "/" . $subTokenInfo['symbol'];
                    }
                    $mainTokenInfo['symbol'] = $liquidTokenInfo['symbol'];
                    $mainTokenInfo['decimals'] = $liquidTokenInfo['decimals'];
                }
            }
            $tokenDataCache = $mainTokenInfo;
            $this->redis->set($cacheKey, json_encode($tokenDataCache), 60);

//            } else {
//                $tokenCache = json_decode($tokenCache, true);
//                $mainTokenInfo = $tokenCache;
//            }
            $mainTokenInfo['contract_address'] = $contractAddress;
            $mainTokenInfo['platform'] = $platform;
            $mainTokenInfo['network'] = $network;
            $mainTokenInfo['user_balance'] = 0;

            // <editor-fold desc = "Get User Balance Token">
            if ($userAddress) {
                $tokenContract->call($functionBalanceOf, $userAddress, function ($err, $res) use (&$userBalance) {
                    if (isset($res[0])) {
                        $userBalance = $res[0]->toString();
                    }
                    if ($err) {
                        Helper::debug($err->getMessage());
                    }
                });
                $userBalance = $userBalance / pow(10, $mainTokenInfo['decimals']);
                $mainTokenInfo['user_balance'] = $userBalance;
            }


            // </editor-fold>

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $mainTokenInfo, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function checkTokenLiquid($contractAddress)
    {

        $abiDexPair = ContractLibrary::getAbi(ContractLibrary::DEX_PAIR);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $abiDexPair);
        $contract = $contract->at($contractAddress);

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
            $data['liquid_address'] = $contractAddress;
        }

        return $data;
    }

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
            if (isset($res[0])) {
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
            $token0Image = $token0Info['image'];
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

            $token0Image = $this->getCoinGeckoInfo($liquidInfo);
        }
        $balance0 = $balance0 / pow(10, $token0Decimals);
        // </editor-fold>

        // <editor-fold desc = "Get Total Supply Token 0">
        $contract0->call($functionSupply, null, function ($err, $res) use (&$token0Supply) {
            if ($res) {
                $token0Supply = $res[0]->toString();
            }
        });
        [$token0Supply] = (new BigInteger($token0Supply))->divide((new BigInteger(pow(10, $token0Decimals))));
        $token0Supply = (doubleval($token0Supply->toString()));
        // </editor-fold>

        // <editor-fold desc = "Get Balance Token 1">
        $token1 = $liquidInfo['token1'];
        $contract1 = $contract->at($token1);
        $balance1 = null;
        $contract1->call($functionBalance, $liquidAddress, function ($err, $res) use (&$balance1) {
            if (isset($res[0])) {
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
            $token1Image = $token1Info['image'];
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
            $token1Image = $this->getCoinGeckoInfo($liquidInfo);
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
        [$token1Supply] = (new BigInteger($token1Supply))->divide((new BigInteger(pow(10, $token1Decimals))));
        $token1Supply = (doubleval($token1Supply->toString()));
        // </editor-fold>

        // <editor-fold desc = "Get Decimal, Name, Ticker Token Liquid">
        $contractLiquid = $contract->at($liquidAddress);
        $contractLiquid->call($functionDecimals, null, function ($err, $res) use (&$tokenLiquidDecimals) {
            if ($res) {
                $tokenLiquidDecimals = $res[0]->toString();
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
        [$tokenLiquidTotalSupply] = (new BigInteger($tokenLiquidTotalSupply))->divide((new BigInteger(pow(10, $tokenLiquidDecimals))));
        $tokenLiquidTotalSupply = (doubleval($tokenLiquidTotalSupply->toString()));
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
                'image' => $token0Image,
            ];

            $liquidInfo['sub_token'] = [
                'address' => $token1,
                'decimals' => $token1Decimals,
                'balance' => $balance1,
                'total_supply' => $token1Supply,
                'name' => $token1Name,
                'symbol' => $token1Symbol,
                'image' => $token1Image,
            ];

        } else {

            $liquidInfo['main_token'] = [
                'address' => $token1,
                'decimals' => $token1Decimals,
                'balance' => $balance1,
                'total_supply' => $token1Supply,
                'name' => $token1Name,
                'symbol' => $token1Symbol,
                'image' => $token1Image,
            ];

            $liquidInfo['sub_token'] = [
                'address' => $token0,
                'decimals' => $token0Decimals,
                'balance' => $balance0,
                'total_supply' => $token0Supply,
                'name' => $token0Name,
                'symbol' => $token0Symbol,
                'image' => $token0Image,
            ];
        }
        // </editor-fold>

        return $liquidInfo;
    }

    protected function getCoinGeckoInfo($token)
    {
        $baseUrl = "https://api.coingecko.com/api/v3/coins/";
        $platformId = 'binance-smart-chain';
        if ($token['platform'] == EthereumWeb3::PLATFORM) {
            $platformId = 'ethereum';
        }
        $baseUrl .= $platformId . "/contract/" . $token['address'];
        $response = Request::get($baseUrl)->expectsJson()->send();
        if ($response->hasBody()) {
            $tokenData = Arrays::arrayFrom($response->body);
            return Helper::getUrl($tokenData['image']['large'] ?? '');
        }
    }

    public function checkRedisAction()
    {
        try {
            return $this->redis->ping('connected');
        } catch (RedisException $e) {
            echo $e->getMessage();
        }
    }

    public function getLotteryAction()
    {
        $this->showDebug();
        $platform = BinanceWeb3::PLATFORM;
        $tokenKey = Adapter::listMainCurrency()[$platform];
        if (!$tokenKey) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid platform');
        }
        $network = ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance($tokenKey, $network);

        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY, 1);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $abiLottery);
        $contractAddress = '0x5aF6D33DE2ccEC94efb1bDF8f92Bd58085432d2c';
        $contractInstance = $contract->at($contractAddress);

        $lotteryInfo = [];
        $functionName = 'viewLottery';
        $lotteryId = 650;
        $contractInstance->call($functionName, $lotteryId, function ($err, $res) use (&$lotteryInfo) {
            if ($res) {
                Helper::debug($res);
            }
        });
    }

    public function startLotteryAction()
    {
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);


        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);

        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lotteryService = new LotteryService($network, $platform);
        $lotteryService->processStartLottery($transaction, $dataDecode);
        echo "Success";
    }

    public function buyTicketsAction()
    {
        $this->showDebug();
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);


        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);

        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lotteryService = new LotteryService($network, $platform);
        $lotteryService->processBuyTickets($transaction, $dataDecode);
        echo "Success";
    }

    public function closeLotteryAction()
    {
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);


        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);

        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lotteryService = new LotteryService($network, $platform);
        $lotteryService->processCloseLottery($transaction, $dataDecode);
        echo "Success";
    }

    public function calculateRewardAction()
    {
        $this->showDebug();
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);


        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);

        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lotteryService = new LotteryService($network, $platform);
        $lotteryService->processCalculateReward($transaction, $dataDecode);
        echo "Success";
    }

    public function claimTicketsAction()
    {
        $this->showDebug();
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);


        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);

        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lotteryService = new LotteryService($network, $platform);
        $lotteryService->processClaimTickets($transaction, $dataDecode);
        echo "Success";
    }

    public function calculateBracketAction()
    {
        $finalNumber = 1234567;
        $userNumber = 1234768;
        Helper::debug(Helper::calculateBracket($finalNumber, $userNumber));
    }

    public function injectFundsAction()
    {
        $this->showDebug();
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);


        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);

        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lotteryService = new LotteryService($network, $platform);
        $lotteryService->processInjectFunds($transaction, $dataDecode);
        echo "Success";
    }

    public function getSettingAction()
    {
        $type = ContractLibrary::LOTTERY;
        $listSaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleFactory)) {
            foreach ($listSaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $saleFactoryAddress = $item['address'];

                $lotteryService = LotteryService::getInstance($network, $platform);
                $lotteryService->updateLotterySetting($saleFactoryAddress);
            }
        }
    }

    public function testEndTimeAction()
    {
        $decimals = BigDecimal::of(10)->power(18);
        $now = date('d/m/Y H:i:s');
        $endTime = strtotime(date('m/d/Y H:00', strtotime("+9 hours")));
//        $endTime = 1664264954;
//        $endTime = strtotime(date('m/d/Y H:i', strtotime("+2 minutes", $endTime)));
        Helper::debug($now, $endTime);
    }

    public function cronCloseLotteryAction()
    {
        $this->showDebug();
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $lotteryCron = $lotteryCronCollection->findOne([
            'action' => ContractLibrary::FUNCTION_CLOSE_LOTTERY,
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING
        ]);
        $network = $lotteryCron['network'];
        $platform = $lotteryCron['platform'];
        $lotteryService = new LotteryService($network, $platform);
        $hash = $lotteryService->cronCloseLottery($lotteryCron);
        echo $hash . "<br/>";
        echo "Success";
    }

    public function cronCalculateRewardAction()
    {
        $this->showDebug();
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $lotteryCron = $lotteryCronCollection->findOne([
            'action' => ContractLibrary::FUNCTION_CALCULATE_REWARD,
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING
        ]);
        $network = $lotteryCron['network'];
        $platform = $lotteryCron['platform'];
        $lotteryService = new LotteryService($network, $platform);
        $hash = $lotteryService->cronCalculateReward($lotteryCron);
        echo $hash . "<br/>";
        echo "Success";
    }

    public function cronStartLotteryAction()
    {
        $this->showDebug();
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $lotteryCron = $lotteryCronCollection->findOne([
            'action' => ContractLibrary::FUNCTION_START_LOTTERY,
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING
        ]);
        $network = $lotteryCron['network'];
        $platform = $lotteryCron['platform'];
        $lotteryService = new LotteryService($network, $platform);
        $hash = $lotteryService->cronStartLottery($lotteryCron);
        echo $hash . "<br/>";
        echo "Success";
    }

    public function chickenAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $platform = 'bsc';
        $network = 'test';

        $registry = $registryCollection->findOne();
        $settingKey = "lottery_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $paymentToken = $settingInfo['payment_token'];
        $rateUsd = $paymentToken['token_price'];
        $decimals = BigDecimal::of(10)->power($paymentToken['token_decimals']);
//        $priceTicket = BigDecimal::of(2)->multipliedBy($decimals)->getIntegralPart();

        $priceTicket = BigDecimal::of(5 / $rateUsd)->multipliedBy($decimals)->getIntegralPart();
//        $priceTicket = BigDecimal::of(6)->dividedBy($rateUsd)->getIntegralPart();
        Helper::debug($priceTicket);
    }

    /**
     * @throws Exception
     */
    public function stakingAction()
    {
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);

        $transactionCollection = $this->mongo->selectCollection('transaction');
        $listData = $transactionCollection->find([
            'is_process' => ['$ne' => 1]
        ])->toArray();
        /*$transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);*/
        foreach ($listData as $transaction) {
            if ($transaction) {
                $abiFileName = $transaction['contract_type'];
                $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
                $stakingService = new StakingService($network, $platform);
                $stakingService->processStaking($transaction, $dataDecode);
                echo "Success";
            } else {
                echo "No Data";
            }
        }

    }

    public function buyAction()
    {
        $network = ContractLibrary::TEST_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);

        $transactionCollection = $this->mongo->selectCollection('transaction');
        $transaction = $transactionCollection->findOne([
            'is_process' => ['$ne' => 1]
        ]);
        if ($transaction) {
            $abiFileName = $transaction['contract_type'];
            $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
            $stakingService = new StakingService($network, $platform);
            $stakingService->processBuy($transaction, $dataDecode);
            echo "Success";
        } else {
            echo "No Data";
        }

    }

    /**
     * @throws Exception
     */
    public function swapAction()
    {
        $this->showDebug();
        try {
            $dataGet = $this->getData;
            $sellAmount = $dataGet['sell_amount'] ?? 1;
            $buyAmount = $dataGet['buy_amount'] ?? 1;
            $inputType = 'sell';
            if (isset($sellAmount) && $sellAmount > 0) {
                $inputType = 'sell';
                unset($buyAmount);
                $sellDecimals = "000000000000000000";
                $sellAmount = $sellAmount . $sellDecimals;
            }
            if (isset($buyAmount) && $buyAmount > 0) {
                $inputType = 'buy';
                unset($sellAmount);
                $buyDecimals = "00000000";
                $buyAmount = $buyAmount . $buyDecimals;
            }
            $swap = new Swap();

            $data = [
//                'sellToken' => '0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56',
//                'sellToken' => 'bnb',
                'sellToken' => '0x92da433da84d58dfe2aade1943349e491cbd6820',
                'buyToken' => '0x8c282ea9eacd1b95d44a3a18dcdd1d0472868998',
                'sellAmount' => $sellAmount ?? null,
                'buyAmount' => $buyAmount ?? null,
            ];
            $dataQuote = $swap->getQuote($data);
            $totalOutput = 0;
            $totalAdjustedOutput = 0;
            $listOrders = $dataQuote['orders'];
            if (count($listOrders)) {
                foreach ($listOrders as $order) {
                    $fillData = $order['fill'];
                    $totalOutput = BigDecimal::of($totalOutput)->plus(BigDecimal::of($fillData['output']));
                    $totalAdjustedOutput = BigDecimal::of($totalAdjustedOutput)->plus(BigDecimal::of($fillData['adjustedOutput']));
                }
            }
            $dataResponse['input_amount'] = $inputType == 'sell' ? $sellAmount : $buyAmount;
            $dataResponse['output'] = strval($totalOutput);
            $dataResponse['adjusted_output'] = strval($totalAdjustedOutput);
            $dataResponse['to'] = $dataQuote['to'];
            $dataResponse['allowanceTarget'] = $dataQuote['allowanceTarget'];
            $dataResponse['data'] = $dataQuote['data'];
            Helper::debug($dataQuote, $dataResponse);

        } catch (Exception $exception) {
            $dataResponse = ['error' => 'Error'];
        }


    }

    public function queueAction()
    {
        $this->showDebug();
        global $config;
        $predis = new Client(
            [
                'host' => $config->redis->host,
                'port' => $config->redis->port,
                'password' => $config->redis->authorize
            ]
        );
        $prefix = $config->redis->prefix . 'rsmq';
        $rsmq = new RSMQClient($predis, $prefix);

//        $rs = $rsmq->createQueue('myqueue_01', 60);
//        $queues = $rsmq->listQueues();
//        var_dump($queues);
//
//        $id = $rsmq->sendMessage('myqueue_01', 'a message');
//        echo "Message Sent. ID: ", $id;

        $message = $rsmq->receiveMessage('myqueue_01');
        echo "Message ID: ", $message->getId();
        echo "Message: ", $message->getMessage();


        die;
    }

    public function tokenAction()
    {
        $this->showDebug();
        $network = ContractLibrary::MAIN_NETWORK;
        $platform = BinanceWeb3::PLATFORM;
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);
        $lockService = LockContractService::getInstance($network, $platform);

        $collection = $this->mongo->selectCollection('transaction');
        $transaction = $collection->findOne(['hash' => '0x61ddea27945b8431b004f416b62e933c0143875981dae80817f0889f10137f9f']);
        $abiFileName = $transaction['contract_type'];
        $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
        $lockService->processLockBak($transaction, $dataDecode);
    }
}
