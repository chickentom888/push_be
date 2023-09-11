<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class PresaleSettingContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Update Presale Setting
     * @throws Exception
     */
    public function processUpdatePresaleSettingByTransaction($transaction, $dataDecode)
    {
        $presaleSettingAddress = $transaction['to'];
        $settingInfo = $this->updatePresaleSetting($presaleSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        if ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_BASE_TOKEN) {
            $this->processUpdatePresaleBaseToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_WHITELIST_TOKEN) {
            $this->processUpdatePresaleWhitelistToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_ZERO_ROUND) {
            $this->processPresaleZeroRoundToken($settingInfo);
        }
    }

    /**
     * Process Update Base Token In DB
     * @param $settingInfo
     */
    public function processUpdatePresaleBaseToken($settingInfo)
    {
        $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $listBaseToken = $settingInfo['base_token']['list_address'] ?? [];
        $listBaseTokenAddress = array_column($listBaseToken, 'token_address');
        $presaleSettingAddressCollection->deleteMany([
            'network' => $network,
            'platform' => $platform,
            'token_address' => ['$nin' => $listBaseTokenAddress],
            'type' => ContractLibrary::BASE_TOKEN,
        ]);
        foreach ($listBaseToken as $item) {
            if ($item['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $checkExists = $presaleSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $item['token_address'],
                    'type' => ContractLibrary::BASE_TOKEN,
                ]);
                if (!$checkExists) {
                    $item['network'] = $network;
                    $item['platform'] = $platform;
                    $item['type'] = ContractLibrary::BASE_TOKEN;
                    $presaleSettingAddressCollection->insertOne($item);
                }
            }
        }
    }

    /**
     * Process Update Whitelist Token In DB
     * @param $settingInfo
     */
    public function processUpdatePresaleWhitelistToken($settingInfo)
    {
        $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $listWhitelistToken = $settingInfo['whitelist_token']['list_address'] ?? [];
        $listWhitelistTokenAddress = array_column($listWhitelistToken, 'token_address');
        $presaleSettingAddressCollection->deleteMany([
            'network' => $network,
            'platform' => $platform,
            'token_address' => ['$nin' => $listWhitelistTokenAddress],
            'type' => ContractLibrary::WHITELIST_TOKEN,
        ]);
        foreach ($listWhitelistToken as $item) {
            if ($item['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $checkExists = $presaleSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $item['token_address'],
                    'type' => ContractLibrary::WHITELIST_TOKEN,
                ]);
                if (!$checkExists) {
                    $item['network'] = $network;
                    $item['platform'] = $platform;
                    $item['type'] = ContractLibrary::WHITELIST_TOKEN;
                    $presaleSettingAddressCollection->insertOne($item);
                }
            }
        }
    }

    /**
     * Process Update Zero Round Token In DB
     * @param $settingInfo
     */
    public function processPresaleZeroRoundToken($settingInfo)
    {
        $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $zeroRound = $settingInfo['zero_round'] ?? [];
        if (!empty($zeroRound)) {
            $tokenAddress = $zeroRound['token_address'];
            if (!strlen($tokenAddress) || $tokenAddress == ContractLibrary::ADDRESS_ZERO) {
                $presaleSettingAddressCollection->deleteMany([
                    'network' => $network,
                    'platform' => $platform,
                    'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                ]);
            } else {
                $checkExists = $presaleSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $tokenAddress,
                    'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                ]);
                if (!$checkExists) {
                    $presaleSettingAddressCollection->deleteMany([
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

                    $presaleSettingAddressCollection->insertOne($zeroRoundToken);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function updatePresaleSetting($presaleSettingAddress)
    {
        $network = $this->network;
        $platform = $this->platform;

        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = $this->web3;
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $abiPresaleSetting = ContractLibrary::getAbi(ContractLibrary::PRESALE_SETTING);
        $contractPresaleSetting = new Contract($coinInstance->rpcConnector->getProvider(), $abiPresaleSetting);
        $contractToken = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);

        $contractPresaleSettingInstance = $contractPresaleSetting->at($presaleSettingAddress);

        $settingInfo = [];

        // <editor-fold desc = "Get Setting Info">
        $functionGetSettingInfo = ContractLibrary::FUNCTION_GET_SETTING_INFO;
        $contractPresaleSettingInstance->call($functionGetSettingInfo, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['setting']['base_fee_percent'] = doubleval($res['baseFeePercent']->toString() / 10);
                $settingInfo['setting']['token_fee_percent'] = doubleval($res['tokenFeePercent']->toString() / 10);
                $settingInfo['setting']['creation_fee'] = doubleval($res['creationFee']->toString() / pow(10, $coinInstance->decimals));
                $settingInfo['setting']['first_round_length'] = intval($res['firstRoundLength']->toString());
                $settingInfo['setting']['max_presale_length'] = intval($res['maxPresaleLength']->toString());
                $settingInfo['setting']['min_liquidity_percent'] = doubleval($res['minLiquidityPercent']->toString() / 10);
                $settingInfo['setting']['min_lock_period'] = intval($res['minLockPeriod']->toString());
                $settingInfo['setting']['max_success_to_liquidity'] = intval($res['maxSuccessToLiquidity']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Setting Address">
        $functionGetSettingAddress = 'getSettingAddress';
        $contractPresaleSettingInstance->call($functionGetSettingAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['setting']['base_fee_address'] = $coinInstance->toCheckSumAddress($res['baseFeeAddress']);
                $settingInfo['setting']['token_fee_address'] = $coinInstance->toCheckSumAddress($res['tokenFeeAddress']);
                $settingInfo['setting']['admin_address'] = $coinInstance->toCheckSumAddress($res['adminAddress']);
                $settingInfo['setting']['wrap_token_address'] = $coinInstance->toCheckSumAddress($res['wrapTokenAddress']);
                $settingInfo['setting']['dex_locker_address'] = $coinInstance->toCheckSumAddress($res['dexLockerAddress']);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $functionGetZeroRound = 'getZeroRound';
        $contractPresaleSettingInstance->call($functionGetZeroRound, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
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
        $functionWhitelistTokenLength = 'whitelistTokenLength';
        $contractPresaleSettingInstance->call($functionWhitelistTokenLength, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['whitelist_token']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['whitelist_token']['list_address'] = [];

        if (isset($settingInfo['whitelist_token']['address_number'])) {
            if ($settingInfo['whitelist_token']['address_number'] > 0) {
                $functionGetWhitelistTokenAtIndex = 'getWhitelistTokenAtIndex';
                for ($i = 0; $i < $settingInfo['whitelist_token']['address_number']; $i++) {
                    $contractPresaleSettingInstance->call($functionGetWhitelistTokenAtIndex, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
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
        $functionGetListBaseTokenLength = 'getListBaseTokenLength';
        $contractPresaleSettingInstance->call($functionGetListBaseTokenLength, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['base_token']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['base_token']['list_address'] = [];
        if (isset($settingInfo['base_token']['address_number'])) {
            if ($settingInfo['base_token']['address_number'] > 0) {
                $functionGetBaseTokenAtIndex = 'getBaseTokenAtIndex';
                for ($i = 0; $i < $settingInfo['base_token']['address_number']; $i++) {
                    $contractPresaleSettingInstance->call($functionGetBaseTokenAtIndex, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
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

        $settingInfo['presale_setting_address'] = $presaleSettingAddress;
        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey =  "presale_setting_{$platform}_{$network}";
        $dataUpdate = [
            "{$settingKey}" => $settingInfo
        ];

        if (count($settingInfo)) {
            $oldValue = [];
            $newValue = [
                "base_fee_percent" => $settingInfo['setting']['base_fee_percent'],
                "base_fee_address" => $settingInfo['setting']['base_fee_address'],
                "token_fee_percent" => $settingInfo['setting']['token_fee_percent'],
                "token_fee_address" => $settingInfo['setting']['token_fee_address'],
                "creation_fee" => $settingInfo['setting']['creation_fee'],
            ];

            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();
            if ($registry) {
                if (isset($registry[$settingKey])) {
                    $oldValue = [
                        "base_fee_percent" => $registry[$settingKey]['setting']['base_fee_percent'],
                        "base_fee_address" => $registry[$settingKey]['setting']['base_fee_address'],
                        "token_fee_percent" => $registry[$settingKey]['setting']['token_fee_percent'],
                        "token_fee_address" => $registry[$settingKey]['setting']['token_fee_address'],
                        "creation_fee" => $registry[$settingKey]['setting']['creation_fee'],
                    ];
                }

                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $collection->insertOne($dataUpdate);
            }

            $this->createRegistryLog('presale_setting', $network, $platform, $oldValue, $newValue, time());
        }

        return $settingInfo;
    }
}
