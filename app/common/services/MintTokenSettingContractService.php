<?php

namespace Dcore\Services;

use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class MintTokenSettingContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Update Token Setting
     * @throws Exception
     */
    public function processUpdateMintTokenSettingByTransaction($transaction, $dataDecode)
    {
        $tokenSettingAddress = $transaction['to'];
        $this->updateMintTokenSetting($tokenSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @param $tokenSettingAddress
     * @var BinanceWeb3 $coinInstance
     * @return array
     * @throws Exception
     */
    public function updateMintTokenSetting($tokenSettingAddress)
    {
        $network = $this->network;
        $platform = $this->platform;

        $coinInstance = $this->web3;
        $abiMintTokenSetting = ContractLibrary::getAbi(ContractLibrary::MINT_TOKEN_SETTING);
        $contractTokenSetting = new Contract($coinInstance->rpcConnector->getProvider(), $abiMintTokenSetting);
        $contractTokenSettingInstance = $contractTokenSetting->at($tokenSettingAddress);

        $settingInfo = [];

        // <editor-fold desc = "Get Setting Info">
        $functionGetSettingInfo = ContractLibrary::FUNCTION_GET_SETTING_INFO;
        $contractTokenSettingInstance->call($functionGetSettingInfo, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['creation_fee'] = doubleval($res['creationFee']->toString() / pow(10, $coinInstance->decimals));
                $settingInfo['total_supply_fee_percent'] = doubleval($res['totalSupplyFee']->toString() / 10);
                $settingInfo['token_fee_address'] = $coinInstance->toCheckSumAddress($res['tokenFeeAddress']);
            }
        });
        // </editor-fold>

        $settingInfo['mint_token_setting_address'] = $tokenSettingAddress;
        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey = "mint_token_setting_{$platform}_$network";
        $dataUpdate = [
            "{$settingKey}" => $settingInfo
        ];

        if (count($settingInfo)) {
            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();
            $oldValue = [];
            $newValue = [
                "creation_fee" => $settingInfo['creation_fee'],
                "total_supply_fee_percent" => $settingInfo['total_supply_fee_percent'],
                "token_fee_address" => $settingInfo['token_fee_address'],
            ];
            if ($registry) {
                if (isset($registry[$settingKey])) {
                    $oldValue = [
                        "creation_fee" => $registry[$settingKey]['creation_fee'],
                        "total_supply_fee_percent" => $registry[$settingKey]['total_supply_fee_percent'],
                        "token_fee_address" => $registry[$settingKey]['token_fee_address'],
                    ];
                }

                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $collection->insertOne($dataUpdate);
            }
            $this->createRegistryLog('mint_token_setting', $network, $platform, $oldValue, $newValue, time());
        }

        return $settingInfo;
    }
}
