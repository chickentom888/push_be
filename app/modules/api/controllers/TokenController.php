<?php

namespace Dcore\Modules\Api\Controllers;

use Brick\Math\BigDecimal;
use Dcore\Collections\BaseCollection;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class TokenController extends ApiControllerBase
{

    /** @var BinanceWeb3|EthereumWeb3 */
    public $web3;

    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws ConnectionErrorException
     */
    public function indexAction()
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $order = $dataGet['order'] ?? 'created_at';
        $by = $dataGet['by'] ?? 'desc';
        $sort = $this->sort($order, $by);
        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['sale_type'])) {
            if ($dataGet['sale_type'] == ContractLibrary::SALE_TYPE_IDO || $dataGet['sale_type'] == ContractLibrary::SALE_TYPE_IDOV) {
                $conditions['sale_type'] = ['$in' => [ContractLibrary::SALE_TYPE_IDO, ContractLibrary::SALE_TYPE_IDOV]];
            } elseif ($dataGet['sale_type'] == 'all') {
                $conditions['sale_type'] = ['$in' => [
                    ContractLibrary::SALE_TYPE_IDO,
                    ContractLibrary::SALE_TYPE_IDOV,
                    ContractLibrary::SALE_TYPE_ILO,
                    ContractLibrary::SALE_TYPE_ILOV,
                    ContractLibrary::PROJECT_TYPE_POOL,
                ]];
            } else {
                $conditions['sale_type'] = $dataGet['sale_type'];
            }
        }

        $conditions['status'] = $this->listStatus()[$dataGet['status']] ?? ContractLibrary::ACTIVE;
        if (strlen($dataGet['q'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            $conditions['$or'] = [
                ['name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['$or'][] = ['address' => $coinInstance->toCheckSumAddress($dataGet['q'])];
            }
        }

        $tokenCollection = $this->mongo->selectCollection('tokens');
        $count = $tokenCollection->countDocuments($conditions);
        $listData = $tokenCollection->aggregate([
            ['$match' => $conditions],
            ['$skip' => ($p - 1) * $limit],
            ['$limit' => $limit],
            ['$sort' => $sort]
        ]);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        !empty($listData) && $listData = $listData->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    /**
     * @throws Exception
     */
    public function getInfoAction()
    {
        try {
            $dataPost = $this->postData;
            $userAddress = trim($dataPost['user_address']);
            $contractAddress = trim($dataPost['contract_address']);
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
            $this->web3 = $coinInstance;

            // <editor-fold desc = "Validate Address">
            $validContractAddress = $coinInstance->validAddress($contractAddress);
            if (!$validContractAddress) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid contract address');
            }
            // </editor-fold>

            $contractAddress = $coinInstance->toCheckSumAddress($contractAddress);

            $mainTokenInfo = [];

            $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
            $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;
            $functionName = ContractLibrary::FUNCTION_NAME;
            $functionBalanceOf = ContractLibrary::FUNCTION_BALANCE_OF;
            $functionTotalSupply = ContractLibrary::FUNCTION_TOTAL_SUPPLY;
            $cacheKey = "search_token:{$platform}_{$network}_$contractAddress";

            $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
            $tokenCollection = $this->mongo->selectCollection('tokens');
            $contract = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
            $tokenContract = $contract->at($contractAddress);
            $tokenCache = $this->redis->get($cacheKey);
            if (!$tokenCache) {

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
                        });
                        $tokenContract->call($functionName, null, function ($err, $res) use (&$mainTokenInfo) {
                            if ($res) {
                                $mainTokenInfo['name'] = $res[0];
                            }
                        });
                        $tokenContract->call($functionSymbol, null, function ($err, $res) use (&$mainTokenInfo) {
                            if ($res) {
                                $mainTokenInfo['symbol'] = $res[0];
                            }
                        });
                        $tokenContract->call($functionTotalSupply, null, function ($err, $res) use (&$mainTokenInfo) {
                            if ($res) {
                                $mainTokenInfo['total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $mainTokenInfo['decimals']))->toFloat();
                            }
                        });
                        // </editor-fold>
                    } else {
                        $liquidInfo = $this->getTokenLiquid($liquidInfo);

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

            } else {
                $tokenCache = json_decode($tokenCache, true);
                $mainTokenInfo = $tokenCache;
            }
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
                });
                $userBalance = $userBalance / pow(10, $mainTokenInfo['decimals']);
                $mainTokenInfo['user_balance'] = $userBalance;
            }
            // </editor-fold>

            $mainTokenInfo['avatar'] = "https://tokens.pancakeswap.finance/images/$contractAddress.png";

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $mainTokenInfo, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function detailByAddressAction($tokenAddress)
    {
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if ($coinInstance->validAddress($tokenAddress)) {
            $tokenAddress = $coinInstance->toCheckSumAddress($tokenAddress);
            $dataGet = $this->getData;
            $conditions['address'] = $tokenAddress;
            $conditions['platform'] = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $conditions['network'] = $dataGet['network'] ?? ContractLibrary::MAIN_NETWORK;
            $token = $tokenCollection->findOne($conditions);
            $token['lookup_value'] = 0;
            if ($token['total_lock_value']) {
                $registry = $this->mongo->selectCollection('registry')->findOne();
                $token['lookup_value'] = $this->getAmountMainCurrency($registry, $token['total_lock_value'], $token['platform']);
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $token, 'Success');
        }

        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong!');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function updateImageByAddressAction($address)
    {
        $data = $this->postData;
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $conditions = [];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if ($coinInstance->validAddress($address)) {
            $conditions['address'] = $coinInstance->toCheckSumAddress($address);
            $conditions['platform'] = $data['platform'];
            $conditions['network'] = $data['network'];
            $image = $data['image'];
            $curl = curl_init($image);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($curl);
            $ext = @curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
            if ($ext == 'image/png' || $ext == 'image/svg+xml') {
                $tokenCollection->updateOne($conditions, ['$set' => [
                    'image' => $image,
                ]]);

                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');
            }
        }

        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
    }

    protected function getAmountMainCurrency($registry, $amount, $platform = BinanceWeb3::PLATFORM)
    {
        $mainCurrency = Adapter::getMainCurrency($platform);
        $rate = $registry["{$mainCurrency}_price"] ?? 0;
        if ($rate <= 0) {
            return 0;
        }

        return $amount / $rate;
    }

    protected function listStatus()
    {
        return [
            ContractLibrary::INACTIVE,
            ContractLibrary::ACTIVE,
        ];
    }

    /**
     * Check transaction is liquid or token lock
     * @param $contractAddress
     * @return array
     * @throws Exception
     */
    public function checkTokenLiquid($contractAddress)
    {

        $abiLiquid = ContractLibrary::getAbi(ContractLibrary::DEX_PAIR);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $abiLiquid);
        $contract = $contract->at($contractAddress);

        $token0 = '';
        $functionName = ContractLibrary::FUNCTION_TOKEN0;
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
        $functionName = ContractLibrary::FUNCTION_TOKEN1;
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
        $functionName = ContractLibrary::FUNCTION_KLAST;
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
        $functionBalance = ContractLibrary::FUNCTION_BALANCE_OF;
        $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
        $functionSupply = ContractLibrary::FUNCTION_TOTAL_SUPPLY;
        $functionName = ContractLibrary::FUNCTION_NAME;
        $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;

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

    protected function getListTokenProject($collection, $conditions, $column)
    {
        $project = $this->mongo->selectCollection($collection)
            ->aggregate([
                [
                    '$match' => $conditions
                ],
                [
                    '$project' => [
                        '_id' => 1,
                        $column => 1,
                    ],
                ],
            ]);
        !empty($project) && $project = $project->toArray();

        return Arrays::arrayColumn($project, $column);
    }

    public function getPoolPriceAction()
    {
        try {
            $dataPost = $this->jsonData;
            $fromTokenAddress = trim($dataPost['from_token_address']);
            $toTokenAddress = trim($dataPost['to_token_address']);
            if (!strlen($fromTokenAddress)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid from token');
            }

            if (!strlen($toTokenAddress)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid to token');
            }
            $platform = $dataPost['platform'];
            $network = $dataPost['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;

            $mainCurrency = Adapter::getMainCurrency($platform);
            $coinInstance = Adapter::getInstance($mainCurrency, $network);

            // <editor-fold desc="Validate From & To Token">
            $isValidFromTokenAddress = $coinInstance->validAddress($fromTokenAddress);
            if (!$isValidFromTokenAddress) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid from token');
            }

            $isValidToTokenAddress = $coinInstance->validAddress($toTokenAddress);
            if (!$isValidToTokenAddress) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid to token');
            }

            $fromTokenAddress = $coinInstance->toCheckSumAddress($fromTokenAddress);
            $toTokenAddress = $coinInstance->toCheckSumAddress($toTokenAddress);

            $listMainTokenSwap = $this->getMainTokenSwapByNetwork($network);
            $listMainTokenSwapAddress = array_column($listMainTokenSwap, 'address');
            if (!in_array($fromTokenAddress, $listMainTokenSwapAddress) || !in_array($toTokenAddress, $listMainTokenSwapAddress)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Token not valid');
            }

            $stakingToken = Arrays::searchArrayByValue($listMainTokenSwap, 'staking_token', '1');
            $stakingTokenAddress = $stakingToken['address'];

            $listSwapToken = Arrays::searchArrayByValue($listMainTokenSwap, 'staking_token', '1', true);
            $listSwapTokenAddress = array_column($listSwapToken, 'address');

            if (in_array($toTokenAddress, $listSwapTokenAddress) && $fromTokenAddress != $stakingTokenAddress) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid from token');
            }
            if ($fromTokenAddress == $stakingTokenAddress && !in_array($toTokenAddress, $listSwapTokenAddress)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid to token');
            }
            // </editor-fold>

            $registry = $this->mongo->selectCollection('registry')->findOne();
            $coinRate = $registry['coin_rate'];
            $mainTokenRate = $registry['bnb_rate'];

            $mainTokenInfo = Arrays::searchArrayByValue($listMainTokenSwap, 'main_token', '1');
            if ($fromTokenAddress == $stakingTokenAddress) {
                $tokenPrice = $toTokenAddress == $mainTokenInfo['address'] ? $coinRate / $mainTokenRate : $coinRate;
            } else {
                $tokenPrice = $fromTokenAddress == $mainTokenInfo['address'] ? $mainTokenRate / $coinRate : 1 / $coinRate;
            }


            $fromTokenInfo = [
                'address' => $fromTokenAddress,
                'avatar' => "https://tokens.pancakeswap.finance/images/$fromTokenAddress.png",
            ];
            $toTokenInfo = [
                'address' => $toTokenAddress,
                'avatar' => "https://tokens.pancakeswap.finance/images/$toTokenAddress.png",
            ];

            $dataResponse = [
                'from_token_info' => $fromTokenInfo,
                'to_token_info' => $toTokenInfo,
                'token_price' => $tokenPrice
            ];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function listMainTokenSwapAction()
    {
        try {
            $dataGet = $this->getData;
            $network = $dataGet['network'] ?: ContractLibrary::MAIN_NETWORK;
            $listData = $this->getMainTokenSwapByNetwork($network);
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
        }
    }

    protected function getMainTokenSwapByNetwork($network = ContractLibrary::MAIN_NETWORK)
    {
        if ($network == ContractLibrary::MAIN_NETWORK) {
            $listData = [
                [
                    'address' => '0xbb4CdB9CBd36B01bD1cBaEBF2De08d9173bc095c',
                    'name' => 'BNB',
                    'symbol' => 'BNB',
                    'decimals' => 18,
                    'avatar' => 'https://assets-cdn.trustwallet.com/blockchains/smartchain/assets/0xbb4CdB9CBd36B01bD1cBaEBF2De08d9173bc095c/logo.png',
                    'main_token' => 1,
                    'staking_token' => 0,
                ],
                [
                    'address' => '0x241c9eF7602F031BEF4F14458c3083dcd37B9be5',
                    'name' => 'PushSwap Token',
                    'symbol' => 'Push',
                    'decimals' => 18,
                    'avatar' => 'https://raw.githubusercontent.com/notresponse404/Images/main/push-icon.svg',
                    'main_token' => 0,
                    'staking_token' => 1,
                ],
                [
                    'address' => '0x55d398326f99059fF775485246999027B3197955',
                    'name' => 'BSC-USD',
                    'symbol' => 'USDT',
                    'decimals' => 18,
                    'avatar' => 'https://tokens.pancakeswap.finance/images/0x55d398326f99059fF775485246999027B3197955.png',
                    'main_token' => 0,
                    'staking_token' => 0,
                ],
                [
                    'address' => '0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56',
                    'name' => 'BUSD',
                    'symbol' => 'BUSD',
                    'decimals' => 18,
                    'avatar' => 'https://tokens.pancakeswap.finance/images/0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56.png',
                    'main_token' => 0,
                    'staking_token' => 0,
                ],
            ];
        } else {
            $listData = [
                [
                    'address' => '0xbb4CdB9CBd36B01bD1cBaEBF2De08d9173bc095c',
                    'name' => 'BNB',
                    'symbol' => 'BNB',
                    'decimals' => 18,
                    'avatar' => 'https://assets-cdn.trustwallet.com/blockchains/smartchain/assets/0xbb4CdB9CBd36B01bD1cBaEBF2De08d9173bc095c/logo.png',
                    'main_token' => 1,
                    'staking_token' => 0,
                ],
                [
                    'address' => '0x241c9eF7602F031BEF4F14458c3083dcd37B9be5',
                    'name' => 'PushSwap Token',
                    'symbol' => 'Push',
                    'decimals' => 18,
                    'avatar' => 'https://raw.githubusercontent.com/notresponse404/Images/main/push-icon.svg',
                    'main_token' => 0,
                    'staking_token' => 1,
                ],
                [
                    'address' => '0x55d398326f99059fF775485246999027B3197955',
                    'name' => 'BSC-USD',
                    'symbol' => 'USDT',
                    'decimals' => 18,
                    'avatar' => 'https://tokens.pancakeswap.finance/images/0x55d398326f99059fF775485246999027B3197955.png',
                    'main_token' => 0,
                    'staking_token' => 0,
                ],
                [
                    'address' => '0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56',
                    'name' => 'BUSD',
                    'symbol' => 'BUSD',
                    'decimals' => 18,
                    'avatar' => 'https://tokens.pancakeswap.finance/images/0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56.png',
                    'main_token' => 0,
                    'staking_token' => 0,
                ],
            ];

            /*$listData = [
                [
                    'address' => '0xae13d989daC2f0dEbFf460aC112a837C89BAa7cd',
                    'name' => 'BNB',
                    'symbol' => 'BNB',
                    'decimals' => 18,
                    'avatar' => 'https://assets-cdn.trustwallet.com/blockchains/smartchain/assets/0xbb4CdB9CBd36B01bD1cBaEBF2De08d9173bc095c/logo.png',
                    'main_token' => 1,
                    'staking_token' => 0,
                ],
                [
                    'address' => '0xd545F8AF7f728dCdbbA95a7A0f5855621966230f',
                    'name' => 'LunaSwap Token',
                    'symbol' => 'Push',
                    'decimals' => 18,
                    'avatar' => 'https://raw.githubusercontent.com/notresponse404/Images/main/push-icon.svg',
                    'main_token' => 0,
                    'staking_token' => 1,
                ],
                [
                    'address' => '0x3405dBE19C529E77e1F083C8e04d6CA6a00A3f94',
                    'name' => 'Token1',
                    'symbol' => 'TOKEN1',
                    'decimals' => 18,
                    'avatar' => 'https://tokens.pancakeswap.finance/images/0x55d398326f99059fF775485246999027B3197955.png',
                    'main_token' => 0,
                    'staking_token' => 0,
                ],
            ];*/
        }
        return $listData;
    }
}
