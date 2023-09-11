<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class SaleSettingContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processUpdateSaleSettingByTransaction($transaction, $dataDecode)
    {
        $saleSettingAddress = $transaction['to'];
        $settingInfo = $this->updateSaleSetting($saleSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        if ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_BASE_TOKEN) {
            $this->processUpdateSaleBaseToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_WHITELIST_TOKEN) {
            $this->processUpdateSaleWhitelistToken($settingInfo);
        } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_ZERO_ROUND) {
            $this->processSaleZeroRoundToken($settingInfo);
        }
    }

    public function processUpdateSaleBaseToken($settingInfo)
    {
        $saleSettingAddressCollection = $this->mongo->selectCollection('sale_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $listBaseToken = $settingInfo['base_token']['list_address'] ?? [];
        $listBaseTokenAddress = array_column($listBaseToken, 'token_address');
        $saleSettingAddressCollection->deleteMany([
            'network' => $network,
            'platform' => $platform,
            'token_address' => ['$nin' => $listBaseTokenAddress],
            'type' => ContractLibrary::BASE_TOKEN,
        ]);
        foreach ($listBaseToken as $item) {
            if ($item['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $checkExists = $saleSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $item['token_address'],
                    'type' => ContractLibrary::BASE_TOKEN,
                ]);
                if (!$checkExists) {
                    $item['network'] = $network;
                    $item['platform'] = $platform;
                    $item['type'] = ContractLibrary::BASE_TOKEN;
                    $saleSettingAddressCollection->insertOne($item);
                }
            }
        }
    }

    // Sale
    public function processUpdateSaleWhitelistToken($settingInfo)
    {
        $saleSettingAddressCollection = $this->mongo->selectCollection('sale_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $listWhitelistToken = $settingInfo['whitelist_token']['list_address'] ?? [];
        $listWhitelistTokenAddress = array_column($listWhitelistToken, 'token_address');
        $saleSettingAddressCollection->deleteMany([
            'network' => $network,
            'platform' => $platform,
            'token_address' => ['$nin' => $listWhitelistTokenAddress],
            'type' => ContractLibrary::WHITELIST_TOKEN,
        ]);
        foreach ($listWhitelistToken as $item) {
            if ($item['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $checkExists = $saleSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $item['token_address'],
                    'type' => ContractLibrary::WHITELIST_TOKEN,
                ]);
                if (!$checkExists) {
                    $item['network'] = $network;
                    $item['platform'] = $platform;
                    $item['type'] = ContractLibrary::WHITELIST_TOKEN;
                    $saleSettingAddressCollection->insertOne($item);
                }
            }
        }
    }

    /**
     * Process Update Zero Round Token In DB
     * @param $settingInfo
     */
    public function processSaleZeroRoundToken($settingInfo)
    {
        $saleSettingAddressCollection = $this->mongo->selectCollection('sale_setting_address');
        $platform = $settingInfo['platform'];
        $network = $settingInfo['network'];
        $zeroRound = $settingInfo['zero_round'] ?? [];
        if (!empty($zeroRound)) {
            $tokenAddress = $zeroRound['token_address'];
            if (!strlen($tokenAddress) || $tokenAddress == ContractLibrary::ADDRESS_ZERO) {
                $saleSettingAddressCollection->deleteMany([
                    'network' => $network,
                    'platform' => $platform,
                    'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                ]);
            } else {
                $checkExists = $saleSettingAddressCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'token_address' => $tokenAddress,
                    'type' => ContractLibrary::ZERO_ROUND_TOKEN,
                ]);
                if (!$checkExists) {
                    $saleSettingAddressCollection->deleteMany([
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

                    $saleSettingAddressCollection->insertOne($zeroRoundToken);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function updateSaleSetting($saleSettingAddress)
    {
        $network = $this->network;
        $platform = $this->platform;

        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = $this->web3;
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $abiSaleSetting = ContractLibrary::getAbi(ContractLibrary::SALE_SETTING);
        $contractSaleSetting = new Contract($coinInstance->rpcConnector->getProvider(), $abiSaleSetting);
        $contractToken = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
        $contractSaleSettingInstance = $contractSaleSetting->at($saleSettingAddress);

        $settingInfo = [];

        // <editor-fold desc = "Get Setting Info">
        $functionGetSettingInfo = ContractLibrary::FUNCTION_GET_SETTING_INFO;
        $contractSaleSettingInstance->call($functionGetSettingInfo, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['setting']['base_fee_percent'] = doubleval($res['baseFeePercent']->toString() / 10);
                $settingInfo['setting']['token_fee_percent'] = doubleval($res['tokenFeePercent']->toString() / 10);
                $settingInfo['setting']['creation_fee'] = doubleval($res['creationFee']->toString() / pow(10, $coinInstance->decimals));
                $settingInfo['setting']['first_round_length'] = intval($res['firstRoundLength']->toString());
                $settingInfo['setting']['max_sale_length'] = intval($res['maxSaleLength']->toString());
                $settingInfo['setting']['max_success_to_claim'] = intval($res['maxSuccessToClaim']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Setting Address">
        $contractSaleSettingInstance->call(ContractLibrary::FUNCTION_SALE_GET_SETTING_ADDRESS, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['setting']['base_fee_address'] = $coinInstance->toCheckSumAddress($res['baseFeeAddress']);
                $settingInfo['setting']['token_fee_address'] = $coinInstance->toCheckSumAddress($res['tokenFeeAddress']);
                $settingInfo['setting']['admin_address'] = $coinInstance->toCheckSumAddress($res['adminAddress']);
                $settingInfo['setting']['wrap_token_address'] = $coinInstance->toCheckSumAddress($res['wrapTokenAddress']);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $contractSaleSettingInstance->call(ContractLibrary::FUNCTION_SALE_GET_ZERO_ROUND, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
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
        $contractSaleSettingInstance->call(ContractLibrary::FUNCTION_SALE_WHITE_LIST_TOKEN_LENGTH, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['whitelist_token']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['whitelist_token']['list_address'] = [];

        if (isset($settingInfo['whitelist_token']['address_number'])) {
            if ($settingInfo['whitelist_token']['address_number'] > 0) {
                for ($i = 0; $i < $settingInfo['whitelist_token']['address_number']; $i++) {
                    $contractSaleSettingInstance->call(ContractLibrary::FUNCTION_SALE_GET_WHITE_LIST_TOKEN_AT_INDEX, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
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
        $contractSaleSettingInstance->call(ContractLibrary::FUNCTION_SALE_GET_LIST_BASE_TOKEN_LENGTH, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['base_token']['address_number'] = intval($res[0]->toString());
            }
        });
        $settingInfo['base_token']['list_address'] = [];
        if (isset($settingInfo['base_token']['address_number'])) {
            if ($settingInfo['base_token']['address_number'] > 0) {
                for ($i = 0; $i < $settingInfo['base_token']['address_number']; $i++) {
                    $contractSaleSettingInstance->call(ContractLibrary::FUNCTION_SALE_GET_BASE_TOKEN_AT_INDEX, $i, function ($err, $res) use (&$settingInfo, $coinInstance) {
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

        $settingInfo['sale_setting_address'] = $saleSettingAddress;
        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey =  "sale_setting_{$platform}_$network";
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
            $this->createRegistryLog('sale_setting', $network, $platform, $oldValue, $newValue, time());
        }

        return $settingInfo;
    }
}
