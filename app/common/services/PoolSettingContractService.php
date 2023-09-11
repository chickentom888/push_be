<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class PoolSettingContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processUpdatePoolSettingByTransaction($transaction, $dataDecode)
    {
        $poolSettingAddress = $transaction['to'];
        $settingInfo = $this->updatePoolSetting($poolSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        if ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_BASE_TOKEN) {
            $this->processUpdatePoolBaseToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_WHITELIST_TOKEN) {
            $this->processUpdatePoolWhitelistToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_ZERO_ROUND) {
            $this->processPoolZeroRoundToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_AUCTION_ROUND) {
            $this->processPoolAuctionRoundToken($settingInfo);
        }
    }

    public function processUpdatePoolBaseToken($settingInfo)
    {
        $poolSettingAddressCollection = $this->mongo->selectCollection('pool_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $listBaseToken = $settingInfo['base_token']['list_address'] ?? [];
        $listBaseTokenAddress = array_column($listBaseToken, 'token_address');
        $poolSettingAddressCollection->deleteMany([
            'network' => $network,
            'platform' => $platform,
            'token_address' => ['$nin' => $listBaseTokenAddress],
            'type' => ContractLibrary::BASE_TOKEN,
        ]);
        foreach ($listBaseToken as $item) {
            if ($item['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $checkExists = $poolSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $item['token_address'],
                    'type' => ContractLibrary::BASE_TOKEN,
                ]);
                if (!$checkExists) {
                    $item['network'] = $network;
                    $item['platform'] = $platform;
                    $item['type'] = ContractLibrary::BASE_TOKEN;
                    $poolSettingAddressCollection->insertOne($item);
                }
            }
        }
    }

    // Pool
    public function processUpdatePoolWhitelistToken($settingInfo)
    {
        $poolSettingAddressCollection = $this->mongo->selectCollection('pool_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $listWhitelistToken = $settingInfo['whitelist_token']['list_address'] ?? [];
        $listWhitelistTokenAddress = array_column($listWhitelistToken, 'token_address');
        $poolSettingAddressCollection->deleteMany([
            'network' => $network,
            'platform' => $platform,
            'token_address' => ['$nin' => $listWhitelistTokenAddress],
            'type' => ContractLibrary::WHITELIST_TOKEN,
        ]);
        foreach ($listWhitelistToken as $item) {
            if ($item['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $checkExists = $poolSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $item['token_address'],
                    'type' => ContractLibrary::WHITELIST_TOKEN,
                ]);
                if (!$checkExists) {
                    $item['network'] = $network;
                    $item['platform'] = $platform;
                    $item['type'] = ContractLibrary::WHITELIST_TOKEN;
                    $poolSettingAddressCollection->insertOne($item);
                }
            }
        }
    }

    /**
     * Process Update Zero Round Token In DB
     * @param $settingInfo
     */
    public function processPoolZeroRoundToken($settingInfo)
    {
        $poolSettingAddressCollection = $this->mongo->selectCollection('pool_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $zeroRound = $settingInfo['zero_round'] ?? [];
        if (!empty($zeroRound)) {
            $tokenAddress = $zeroRound['token_address'];
            if (!strlen($tokenAddress) || $tokenAddress == ContractLibrary::ADDRESS_ZERO) {
                $poolSettingAddressCollection->deleteMany([
                    'network' => $network,
                    'platform' => $platform,
                    'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                ]);
            } else {
                $checkExists = $poolSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $tokenAddress,
                    'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                ]);
                if (!$checkExists) {
                    $poolSettingAddressCollection->deleteMany([
                        'network' => $network,
                        'platform' => $platform,
                        'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                    ]);

                    $zeroRoundToken = [
                        'token_address' => $tokenAddress,
                        'token_name' => $zeroRound['token_name'],
                        'token_symbol' => $zeroRound['token_symbol'],
                        'token_decimals' => $zeroRound['token_decimals'],
                        'token_amount' => $zeroRound['token_amount'],
                        'network' => $network,
                        'platform' => $platform,
                        'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                    ];

                    $poolSettingAddressCollection->insertOne($zeroRoundToken);
                }
            }
        }
    }

    /**
     * Process Update Zero Round Token In DB
     * @param $settingInfo
     */
    public function processPoolAuctionRoundToken($settingInfo)
    {
        $poolSettingAddressCollection = $this->mongo->selectCollection('pool_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $auctionRound = $settingInfo['auction_round'] ?? [];
        if (!empty($auctionRound)) {
            $tokenAddress = $auctionRound['token_address'];
            if (!strlen($tokenAddress) || $tokenAddress == ContractLibrary::ADDRESS_ZERO) {
                $poolSettingAddressCollection->deleteMany([
                    'network' => $network,
                    'platform' => $platform,
                    'type' => ContractLibrary::AUCTION_ROUND_TOKEN,
                ]);
            } else {
                $checkExists = $poolSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $tokenAddress,
                    'type' => ContractLibrary::AUCTION_ROUND_TOKEN,
                ]);
                if (!$checkExists) {
                    $poolSettingAddressCollection->deleteMany([
                        'network' => $network,
                        'platform' => $platform,
                        'type' => ContractLibrary::AUCTION_ROUND_TOKEN,
                    ]);

                    $auctionRoundToken = [
                        'token_address' => $tokenAddress,
                        'token_name' => $auctionRound['token_name'],
                        'token_symbol' => $auctionRound['token_symbol'],
                        'token_decimals' => $auctionRound['token_decimals'],
                        'network' => $network,
                        'platform' => $platform,
                        'type' => ContractLibrary::AUCTION_ROUND_TOKEN,
                    ];

                    $poolSettingAddressCollection->insertOne($auctionRoundToken);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function updatePoolSetting($poolSettingAddress)
    {
        $network = $this->network;
        $platform = $this->platform;

        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = $this->web3;
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $abiPoolSetting = ContractLibrary::getAbi(ContractLibrary::POOL_SETTING);
        $contractPoolSetting = new Contract($coinInstance->rpcConnector->getProvider(), $abiPoolSetting);
        $contractToken = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
        $contractPoolSettingInstance = $contractPoolSetting->at($poolSettingAddress);

        $settingInfo = [];

        // <editor-fold desc = "Get Setting Info">
        $functionGetSettingInfo = ContractLibrary::FUNCTION_GET_SETTING_INFO;
        $contractPoolSettingInstance->call($functionGetSettingInfo, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['setting']['first_round_length'] = intval($res['firstRoundLength']->toString());
                $settingInfo['setting']['max_pool_length'] = intval($res['maxPoolLength']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Setting Address">
        $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_SETTING_ADDRESS, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['setting']['admin_address'] = $coinInstance->toCheckSumAddress($res['adminAddress']);
                $settingInfo['setting']['wrap_token_address'] = $coinInstance->toCheckSumAddress($res['wrapTokenAddress']);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_ZERO_ROUND, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['zero_round']['token_address'] = $coinInstance->toCheckSumAddress($res['tokenAddress']);
                $settingInfo['zero_round']['token_amount'] = $res['tokenAmount']->toString();
                $settingInfo['zero_round']['percent'] = doubleval($res['percent']->toString() / 10);
                $settingInfo['zero_round']['finish_before_first_round'] = intval($res['finishBeforeFirstRound']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Token Decimals">
        if (isset($settingInfo['zero_round']['token_address'])) {
            if (strlen($settingInfo['zero_round']['token_address']) && $settingInfo['zero_round']['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $zeroRoundTokenInstance = $contractToken->at($settingInfo['zero_round']['token_address']);
                $zeroRoundTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $decimals = intval($res[0]->toString());
                        $settingInfo['zero_round']['token_decimals'] = $decimals;
                        $settingInfo['zero_round']['token_amount'] = (BigDecimal::of($settingInfo['zero_round']['token_amount']))->exactlyDividedBy(pow(10, $decimals))->toFloat();
                    }
                });

                $zeroRoundTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['zero_round']['token_name'] = $res[0];
                    }
                });
                $zeroRoundTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['zero_round']['token_symbol'] = $res[0];
                    }
                });
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Whitelist Token">
        $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_WHITE_LIST_TOKEN_LENGTH, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['whitelist_token']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['whitelist_token']['list_address'] = [];

        if (isset($settingInfo['whitelist_token']['address_number'])) {
            if ($settingInfo['whitelist_token']['address_number'] > 0) {
                for ($i = 0; $i < $settingInfo['whitelist_token']['address_number']; $i++) {
                    $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_WHITE_LIST_TOKEN_AT_INDEX, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
                        if ($res) {
                            $settingInfo['whitelist_token']['list_address'][] = [
                                'token_address' => $coinInstance->toCheckSumAddress($res[0]),
                                'token_amount' => $res[1]->toString()
                            ];
                        }
                    });
                }

                if (count($settingInfo['whitelist_token']['list_address'])) {
                    foreach ($settingInfo['whitelist_token']['list_address'] as &$whitelistTokenAddressItem) {
                        $whitelistTokenInstance = $contractToken->at($whitelistTokenAddressItem['token_address']);

                        $whitelistTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$whitelistTokenAddressItem) {
                            if ($res) {
                                $whitelistTokenAddressItem['token_name'] = $res[0];
                            }
                        });

                        $whitelistTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$whitelistTokenAddressItem) {
                            if ($res) {
                                $whitelistTokenAddressItem['token_symbol'] = $res[0];
                            }
                        });

                        $whitelistTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$whitelistTokenAddressItem) {
                            if ($res) {
                                $decimals = intval($res[0]->toString());
                                $whitelistTokenAddressItem['token_decimals'] = $decimals;
                                $whitelistTokenAddressItem['token_amount'] = (BigDecimal::of($whitelistTokenAddressItem['token_amount']))->exactlyDividedBy(pow(10, $decimals))->toFloat();
                            }
                        });
                    }
                }
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Base Token">
        $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_LIST_BASE_TOKEN_LENGTH, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['base_token']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['base_token']['list_address'] = [];
        if (isset($settingInfo['base_token']['address_number'])) {
            if ($settingInfo['base_token']['address_number'] > 0) {
                for ($i = 0; $i < $settingInfo['base_token']['address_number']; $i++) {
                    $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_BASE_TOKEN_AT_INDEX, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
                        if ($res) {
                            $settingInfo['base_token']['list_address'][] = [
                                'token_address' => $coinInstance->toCheckSumAddress($res[0])
                            ];
                        }
                    });
                }

                if (count($settingInfo['base_token']['list_address'])) {
                    foreach ($settingInfo['base_token']['list_address'] as &$baseTokenAddressItem) {
                        $baseTokenInstance = $contractToken->at($baseTokenAddressItem['token_address']);
                        $baseTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$baseTokenAddressItem) {
                            if ($res) {
                                $baseTokenAddressItem['token_name'] = $res[0];
                            }
                        });

                        $baseTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$baseTokenAddressItem) {
                            if ($res) {
                                $baseTokenAddressItem['token_symbol'] = $res[0];
                            }
                        });

                        $baseTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$baseTokenAddressItem) {
                            if ($res) {
                                $baseTokenAddressItem['token_decimals'] = intval($res[0]->toString());
                            }
                        });
                    }
                }
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Auction Round Info">
        $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_AUCTION_ROUND, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['auction_round']['token_address'] = $coinInstance->toCheckSumAddress($res['tokenAddress']);
                $settingInfo['auction_round']['finish_before_first_round'] = intval($res['finishBeforeFirstRound']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Auction Round Token Decimals">
        if (isset($settingInfo['auction_round']['token_address'])) {
            if (strlen($settingInfo['auction_round']['token_address']) && $settingInfo['auction_round']['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $auctionRoundTokenInstance = $contractToken->at($settingInfo['auction_round']['token_address']);
                $auctionRoundTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $decimals = intval($res[0]->toString());
                        $settingInfo['auction_round']['token_decimals'] = $decimals;
                    }
                });

                $auctionRoundTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['auction_round']['token_name'] = $res[0];
                    }
                });
                $auctionRoundTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['auction_round']['token_symbol'] = $res[0];
                    }
                });
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Creators">
        $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_LIST_CREATOR_ADDRESS_LENGTH, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['creator_address']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['creator_address']['list_address'] = [];

        if (isset($settingInfo['creator_address']['address_number'])) {
            if ($settingInfo['creator_address']['address_number'] > 0) {
                for ($i = 0; $i < $settingInfo['creator_address']['address_number']; $i++) {
                    $contractPoolSettingInstance->call(ContractLibrary::FUNCTION_POOL_GET_CREATOR_ADDRESS_AT_INDEX, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
                        if ($res) {
                            $settingInfo['creator_address']['list_address'][] = $coinInstance->toCheckSumAddress($res[0]);
                        }
                    });
                }
            }
        }
        // </editor-fold>

        $settingInfo['pool_setting_address'] = $poolSettingAddress;
        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $dataUpdate = [
            "pool_setting_{$platform}_$network" => $settingInfo
        ];

        if (count($settingInfo)) {
            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();
            if ($registry) {
                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $collection->insertOne($dataUpdate);
            }
        }

        return $settingInfo;
    }
}
