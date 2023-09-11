<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class LockSettingContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Update Lock Setting
     * @param $transaction
     * @param $dataDecode
     * @throws Exception
     */
    public function processUpdateLockSetting($transaction, $dataDecode)
    {
        $lockSettingAddress = $transaction['to'];
        $this->updateLockSetting($lockSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function updateLockSetting($lockSettingAddress)
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $tokenCollection = $this->mongo->selectCollection('tokens');

        $network = $this->network;
        $platform = $this->platform;
        $coinInstance = $this->web3;
        $abiLockSetting = ContractLibrary::getAbi(ContractLibrary::LOCK_SETTING);
        $contractTokenSetting = new Contract($coinInstance->rpcConnector->getProvider(), $abiLockSetting);
        $contractTokenSettingInstance = $contractTokenSetting->at($lockSettingAddress);
        $settingInfo = [];

        // <editor-fold desc = "Get Base Fee">
        $functionGetBaseFee = ContractLibrary::FUNCTION_GET_BASE_FEE;
        $contractTokenSettingInstance->call($functionGetBaseFee, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['base_fee'] = doubleval($res[0]->toString() / pow(10, $coinInstance->decimals));
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Token Fee">
        $functionGetTokenFee = ContractLibrary::FUNCTION_GET_TOKEN_FEE;
        $contractTokenSettingInstance->call($functionGetTokenFee, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['token_fee_percent'] = intval($res[0]->toString()) / 10;
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Whitelist Address">
        $functionGetWhitelistAddressLength = ContractLibrary::FUNCTION_GET_WHITELIST_ADDRESS_LENGTH;
        $functionGetWhitelistAddressAtIndex = ContractLibrary::FUNCTION_GET_WHITELIST_ADDRESS_AT_INDEX;
        $whiteListAddress = [];
        $whiteListLength = 0;
        $contractTokenSettingInstance->call($functionGetWhitelistAddressLength, null, function ($err, $res) use (&$whiteListLength) {
            if ($res) {
                $whiteListLength = intval($res[0]->toString());
            }
        });
        if ($whiteListLength > 0) {
            for ($i = 0; $i < $whiteListLength; $i++) {
                $contractTokenSettingInstance->call($functionGetWhitelistAddressAtIndex, $i, function ($err, $res) use (&$whiteListAddress, $coinInstance) {
                    if ($res) {
                        $whiteListAddress[] = $coinInstance->toCheckSumAddress($res[0]);
                    }
                });
            }
        }
        $settingInfo['whitelist_address'] = $whiteListAddress;
        // </editor-fold>

        // <editor-fold desc = "Get Whitelist Fee Token">
        $functionGetWhitelistFeeTokenLength = ContractLibrary::FUNCTION_GET_WHITELIST_FEE_TOKEN_LENGTH;
        $functionGetWhitelistFeeTokenAtIndex = ContractLibrary::FUNCTION_GET_WHITELIST_FEE_TOKEN_AT_INDEX;
        $whiteListFeeToken = [];
        $whiteListFeeTokenLength = 0;
        $contractTokenSettingInstance->call($functionGetWhitelistFeeTokenLength, null, function ($err, $res) use (&$whiteListFeeTokenLength) {
            if ($res) {
                $whiteListFeeTokenLength = intval($res[0]->toString());
            }
        });
        if ($whiteListFeeTokenLength > 0) {
            for ($i = 0; $i < $whiteListFeeTokenLength; $i++) {
                $contractTokenSettingInstance->call($functionGetWhitelistFeeTokenAtIndex, $i, function ($err, $res) use (&$whiteListFeeToken, $coinInstance) {
                    if ($res) {
                        $tokenAddress = $coinInstance->toCheckSumAddress($res[0]);
                        $tokenAmount = $res[1]->toString();
                        $whiteListFeeToken[] = [
                            "address" => $tokenAddress,
                            "amount" => $tokenAmount
                        ];
                    }
                });
            }
        }

        if (count($whiteListFeeToken)) {
            $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
            $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;
            $functionName = ContractLibrary::FUNCTION_NAME;
            $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
            $tokenContract = new Contract($coinInstance->rpcConnector->getProvider(), $tokenAbi);

            foreach ($whiteListFeeToken as &$feeTokenItem) {
                $tokenContractInstance = $tokenContract->at($feeTokenItem['address']);

                // <editor-fold desc = "Get Decimal, Name, Ticker Main Token">
                $tokenInfo = $tokenCollection->findOne(['address' => $feeTokenItem['address']]);
                if ($tokenInfo) {
                    $feeTokenItem['decimals'] = $tokenInfo['decimals'];
                    $feeTokenItem['name'] = $tokenInfo['name'];
                    $feeTokenItem['symbol'] = $tokenInfo['symbol'];
                } else {
                    $tokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$feeTokenItem) {
                        if ($res) {
                            $feeTokenItem['decimals'] = intval($res[0]->toString());
                        }
                    });

                    $tokenContractInstance->call($functionName, null, function ($err, $res) use (&$feeTokenItem) {
                        if ($res) {
                            $feeTokenItem['name'] = $res[0];
                        }
                    });

                    $tokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$feeTokenItem) {
                        if ($res) {
                            $feeTokenItem['symbol'] = $res[0];
                        }
                    });
                    // </editor-fold>
                }

                $feeTokenItem['amount'] = BigDecimal::of($feeTokenItem['amount'])->exactlyDividedBy(pow(10, $feeTokenItem['decimals']))->toFloat();
            }
        }
        // </editor-fold>

        $settingInfo['whitelist_token'] = $whiteListFeeToken;

        // <editor-fold desc = "Get Discount Percent">
        $functionGetDiscountPercent = ContractLibrary::FUNCTION_GET_DISCOUNT_PERCENT;
        $contractTokenSettingInstance->call($functionGetDiscountPercent, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['discount_percent'] = intval($res[0]->toString()) / 10;
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Address Fee">
        $functionGetAddressFee = ContractLibrary::FUNCTION_GET_ADDRESS_FEE;
        $contractTokenSettingInstance->call($functionGetAddressFee, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['address_fee'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        $settingInfo['lock_setting_address'] = $lockSettingAddress;
        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey = "lock_setting_{$platform}_$network";
        $dataUpdate = [
            "{$settingKey}" => $settingInfo
        ];

        if (count($settingInfo)) {
            $oldValue = [];
            $newValue = [
                "base_fee" => $settingInfo['base_fee'],
                "token_fee_percent" => $settingInfo['token_fee_percent'],
                "address_fee" => $settingInfo['address_fee'],
            ];
            $registry = $registryCollection->findOne();
            if ($registry) {
                if (isset($registry[$settingKey])) {
                    $oldValue = [
                        "base_fee" => $registry[$settingKey]['base_fee'],
                        "token_fee_percent" => $registry[$settingKey]['token_fee_percent'],
                        "address_fee" => $registry[$settingKey]['address_fee'],
                    ];
                }

                $registryCollection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $registryCollection->insertOne($dataUpdate);
            }
            $this->createRegistryLog('lock_setting', $network, $platform, $oldValue, $newValue, time());
        }

        return $settingInfo;
    }
}
