<?php

namespace Dcore\Services;

use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class AirdropSettingContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Update Airdrop Setting
     * @throws Exception
     */
    public function processUpdateAirdropSetting($transaction, $dataDecode)
    {
        $airdropSettingAddress = $transaction['to'];
        $this->updateAirdropSetting($airdropSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    public function updateAirdropSetting($airdropSettingAddress)
    {
        $network = $this->network;
        $platform = $this->platform;

        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = $this->web3;
        $abiAirdropSetting = ContractLibrary::getAbi(ContractLibrary::AIRDROP_SETTING);
        $contractAirdropSetting = new Contract($coinInstance->rpcConnector->getProvider(), $abiAirdropSetting);
        $contractAirdropSettingInstance = $contractAirdropSetting->at($airdropSettingAddress);

        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);

        $settingInfo = [];

        // <editor-fold desc = "Get Setting Info">
        $functionGetSettingInfo = ContractLibrary::FUNCTION_GET_SETTING_INFO;
        $contractAirdropSettingInstance->call($functionGetSettingInfo, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['fee_amount'] = doubleval($res['feeAmount']->toString() / pow(10, $coinInstance->decimals));
                $settingInfo['fee_address'] = $coinInstance->toCheckSumAddress($res['feeAddress']);
            }
        });
        // </editor-fold>

        $settingInfo['airdrop_setting_address'] = $airdropSettingAddress;
        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey = "airdrop_setting_{$platform}_$network";
        $dataUpdate = [
            "{$settingKey}" => $settingInfo
        ];

        if (count($settingInfo)) {
            $oldValue = [];
            $newValue = [
                "fee_amount" => $settingInfo['fee_amount'],
                "fee_address" => $settingInfo['fee_address'],
            ];

            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();
            if ($registry) {
                if (isset($registry[$settingKey])) {
                    $oldValue = [
                        "fee_amount" => $registry[$settingKey]['fee_amount'],
                        "fee_address" => $registry[$settingKey]['fee_address'],
                    ];
                }

                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $collection->insertOne($dataUpdate);
            }
            $this->createRegistryLog('airdrop_setting', $network, $platform, $oldValue, $newValue, time());
        }

        return $settingInfo;
    }
}
