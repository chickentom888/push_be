<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Collections\Users;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use DCrypto\Networks\PolygonWeb3;
use Exception;
use RedisException;

class IndexController extends ApiControllerBase
{

    public function initialize($param = null)
    {
        parent::initialize($param);
    }

    public function indexAction()
    {

    }

    public function selectedAddressAction()
    {
        try {
            $dataPost = $this->jsonData;

            $ethChainId = [
                "1" => ContractLibrary::MAIN_NETWORK,
                "3" => ContractLibrary::TEST_NETWORK,
                "4" => ContractLibrary::TEST_NETWORK,
                "5" => ContractLibrary::TEST_NETWORK,
                "42" => ContractLibrary::TEST_NETWORK,
            ];

            $bscChainId = [
                "56" => ContractLibrary::MAIN_NETWORK,
                "97" => ContractLibrary::TEST_NETWORK,
            ];

            $polygonChainId = [
                "131" => ContractLibrary::MAIN_NETWORK,
                "80001" => ContractLibrary::TEST_NETWORK,
            ];

            $listChainIdEth = array_keys($ethChainId);
            $listChainIdBsc = array_keys($bscChainId);
            $listChainIdPolygon = array_keys($polygonChainId);
            $chainId = $dataPost['chain_id'];

            if (in_array($chainId, $listChainIdEth)) {
                $platform = EthereumWeb3::PLATFORM;
                $network = $ethChainId[$chainId];
            } else if (in_array($chainId, $listChainIdBsc)) {
                $platform = BinanceWeb3::PLATFORM;
                $network = $bscChainId[$chainId];
            } else if (in_array($chainId, $listChainIdPolygon)) {
                $platform = PolygonWeb3::PLATFORM;
                $network = $polygonChainId[$chainId];
            } else {
                $platform = BinanceWeb3::PLATFORM;
                $network = ContractLibrary::MAIN_NETWORK;
            }

            $dataPost['platform'] = $platform;
            $dataPost['network'] = $network;

            $data = ['status' => 1];
            if (strlen($dataPost['address'])) {
                $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY, ContractLibrary::MAIN_NETWORK);
                $dataPost['address'] = $coinInstance->toCheckSumAddress($dataPost['address']);
                $userConnectCollection = $this->mongo->selectCollection('user_connect');
                $userConnect = $userConnectCollection->findOne([
                    'address' => $dataPost['address']
                ]);
                $inviterId = null;
                $inviterCode = $dataPost['ref'] ?? '';
                if (strlen($inviterCode)) {
                    $inviter = $userConnectCollection->findOne([
                        'code' => $inviterCode
                    ]);
                    if ($inviter) {
                        $inviterId = $inviter['_id'];
                    }
                }

                // <editor-fold desc="Check Signature">
                $listMinutePostfix[] = date('i', time());
                $listMinutePostfix[] = date('i', strtotime('-1 minutes'));
                $prefix = "pushswap_";
                foreach ($listMinutePostfix as $minutePostfix) {
                    $listSignature[] = md5($prefix . $dataPost['address'] . "_" . $minutePostfix);
                }
                $signature = $dataPost['signature'];

                /*if (!in_array($signature, $listSignature)) {
                    return $this->setDataJson(0, null, 'Invalid Signature');
                }*/
                // </editor-fold>


                if (!$userConnect) {
                    $branch = strtolower($dataPost['branch']) == BaseCollection::BRANCH_RIGHT ? BaseCollection::BRANCH_RIGHT : BaseCollection::BRANCH_LEFT;
                    if (empty($branch)) {
                        $branch = !empty($inviter) ? $inviter['branch_for_child'] : BaseCollection::BRANCH_LEFT;
                    }

                    $dataUserConnect = [
                        'branch' => $branch,
                        'branch_for_child' => $branch,
                        'address' => $dataPost['address'],
                        'inviter_id' => $inviterId ?? null,
                        'code' => Helper::randomString(12),
                        'level' => 0,
                        'diagram_date' => time(),
                        'created_at' => time(),
                    ];

                    $parentCode = $dataPost['pcode'] ?? '';
                    if (strlen($parentCode)) {
                        $parent = $userConnectCollection->findOne([
                            'code' => $parentCode
                        ]);
                        if ($parent) {
                            $dataUserConnect['top_id'] = $parent['_id'];
                        }
                    }

                    $userConnectCode = $dataUserConnect['code'];
                    $userConnectId = $userConnectCollection->insertOne($dataUserConnect)->getInsertedId();
                } else {
                    $userConnectCode = $userConnect['code'];
                    $userConnectId = $userConnect['_id'];
                }
                $dataPost['_id'] = strval($userConnectId);
                $dataPost['code'] = $userConnectCode;
                $token = $this->createJWTToken($dataPost);
                $data['data'] = $token;
                $data['user_id'] = strval($userConnectId);
                $data['code'] = $userConnectCode;
                Users::calcTree();
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function listNetworkAction()
    {
        $listNetwork = Adapter::listNetwork();
        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listNetwork, 'Success');
    }

    public function listPlatformAction()
    {
        global $config;
        $env = $config->application['env'];
        if ($env == 'sandbox') {
            $listPlatform = [
                "main" => [
                    [
                        "name" => "Binance Smart Chain",
                        "symbol" => strtoupper(BinanceWeb3::PLATFORM),
                        "chainId" => 56,
                        "icon" => "https://raw.githubusercontent.com/notresponse404/Images/main/bsc.svg",
                    ],
                    /*[
                        "name" => "Ethereum",
                        "symbol" => strtoupper(EthereumWeb3::PLATFORM),
                        "chainId" => 1,
                        "icon" => "https://raw.githubusercontent.com/Antexchange/Ant-Launch/main/eth.svg",
                    ],
                    [
                        "name" => "Polygon (Matic)",
                        "symbol" => strtoupper(PolygonWeb3::PLATFORM),
                        "chainId" => 137,
                        "icon" => "https://raw.githubusercontent.com/Antexchange/Ant-Launch/main/matic.svg",
                    ],
                    [
                        "name" => "Fantom",
                        "symbol" => strtoupper(FantomWeb3::PLATFORM),
                        "chainId" => 250,
                        "icon" => "https://raw.githubusercontent.com/Antexchange/Ant-Launch/main/fantom.svg",
                    ],*/
                ],
                "test" => [
                    [
                        "name" => "Binance Smart Chain Testnet",
                        "symbol" => strtoupper(BinanceWeb3::PLATFORM),
                        "chainId" => 97,
                        "icon" => "https://raw.githubusercontent.com/notresponse404/Images/main/bsc.svg",
                    ],
                    /*[
                        "name" => "Ethereum Ropsten",
                        "symbol" => strtoupper(EthereumWeb3::PLATFORM),
                        "chainId" => 3,
                        "icon" => "https://cryptologos.cc/logos/ethereum-eth-logo.svg?v=014",
                    ],
                    [
                        "name" => "Polygon Mumbai",
                        "symbol" => strtoupper(PolygonWeb3::PLATFORM),
                        "chainId" => 80001,
                        "icon" => "https://raw.githubusercontent.com/Antexchange/Ant-Launch/main/matic.svg",
                    ],*/
                ]

            ];
        } else {
            $listPlatform = [
                "main" => [
                    [
                        "name" => "Binance Smart Chain",
                        "symbol" => strtoupper(BinanceWeb3::PLATFORM),
                        "chainId" => 56,
                        "icon" => "https://raw.githubusercontent.com/notresponse404/Images/main/bsc.svg",
                    ],
                    /*[
                        "name" => "Polygon",
                        "symbol" => strtoupper(PolygonWeb3::PLATFORM),
                        "chainId" => 137,
                        "icon" => "https://raw.githubusercontent.com/Antexchange/Ant-Launch/main/matic.svg",
                    ],*/
                ]
            ];
        }


        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listPlatform, 'Success');
    }

    public function statisticAction()
    {
        $dataGet = $this->getData;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $platform = $dataGet['platform'] == EthereumWeb3::PLATFORM ? EthereumWeb3::PLATFORM : BinanceWeb3::PLATFORM;
        $cacheKey = "statistic:{$platform}_$network";
        $tokenCache = $this->redis->get($cacheKey);
        if ($tokenCache) {
            $response = json_decode($tokenCache, true);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success');
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $poolCollection = $this->mongo->selectCollection('pool');

        $match = [
            'current_status' => ContractLibrary::PRESALE_STATUS_SUCCESS,
            'platform' => $platform,
            'network' => $network,
        ];
        $conditions = [
            [
                '$match' => $match
            ],
            [
                '$group' => [
                    '_id' => null,
                    "total" => ['$sum' => '$usd_raised'],
                    "count" => ['$sum' => 1],
                ],
            ],
            [
                '$project' => [
                    "total" => 1,
                    "count" => 1,
                ],
            ],
        ];

        $presale = $presaleCollection->aggregate($conditions);
        $pool = $poolCollection->aggregate($conditions);

        !empty($presale) && $presale = $presale->toArray();
        !empty($pool) && $pool = $pool->toArray();

        $raised = $presale[0]['total'] + $pool[0]['total'];
        $count = $presale[0]['count'] + $pool[0]['count'];

        $totalTokenLock = $this->getTotalTokenLock($platform, $network);
        $response = [
            'project_created' => $count,
            'raised' => $raised,
            'total_token_locked' => $totalTokenLock['total_token_lock'],
            'total_liquidity_locked' => $totalTokenLock['total_liquid_lock'],
            'count_token' => $totalTokenLock['count_token'],
        ];
        $this->redis->set($cacheKey, json_encode($response), 60 * 10);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success');
    }

    protected function getTotalTokenLock($platform, $network)
    {
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $match = [
            'platform' => $platform,
            'network' => $network,
            'status' => ContractLibrary::ACTIVE,
        ];
        $conditions = [
            [
                '$match' => $match
            ],
            [
                '$group' => [
                    '_id' => null,
                    "total_value_lock" => [
                        '$sum' => '$token_lock_value'
                    ],
                    "total_value_liquid" => [
                        '$sum' => '$liquid_lock_value'
                    ],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "total_value_lock" => 1,
                    "total_value_liquid" => 1
                ],
            ],
        ];

        $totalTokenLock = $tokenCollection->aggregate($conditions);
        $total_token_lock = 0;
        $total_liquid_lock = 0;
        if (!empty($totalTokenLock) && $totalTokenLock = $totalTokenLock->toArray()) {
            $total_token_lock = !empty($totalTokenLock[0]['total_value_lock']) ? $totalTokenLock[0]['total_value_lock'] : 0;
            $total_liquid_lock = !empty($totalTokenLock[0]['total_value_liquid']) ? $totalTokenLock[0]['total_value_liquid'] : 0;
        }
        $countToken = $tokenCollection->countDocuments($match);

        return [
            'total_token_lock' => $total_token_lock,
            'total_liquid_lock' => $total_liquid_lock,
            'count_token' => $countToken,
        ];
    }

    public function mainTokenAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network']) {
                throw new Exception('Invalid params. Missing require param');
            }
            $platform = strtolower(trim($dataGet['platform']));
            $network = strtolower(trim($dataGet['network']));

            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            if (!$registry) {
                throw new Exception('Not found registry');
            }

            $key = "main_token_{$platform}_{$network}";
            $mainToken = $registry[$key];
            if (!$mainToken) {
                throw new Exception('Not found token info');
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $mainToken, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function registryAction()
    {
        $registry = $this->mongo->selectCollection('registry')->findOne();
        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $registry, 'Success');
    }

    /**
     * @throws RedisException
     */
    public function totalValueLockAction()
    {
        $cacheKey = "total_value_lock";
        $totalValueLockData = $this->redis->get($cacheKey);
        if (!strlen($totalValueLockData)) {
            $totalValueLockData = [
                'value' => rand(1000000, 2000000),
                'updated_at' => time()
            ];
        } else {
            $totalValueLockData = json_decode($totalValueLockData, true);
        }
        $diffSecond = time() - $totalValueLockData['updated_at'];
        $diffAmount = $diffSecond * (rand(100, 1500) / 1000);
        $totalValueLockData['value'] += $diffAmount;
        $totalValueLockData['updated_at'] = time();
        $this->redis->set($cacheKey, json_encode($totalValueLockData));
        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $totalValueLockData['value'], 'Success');
    }
}