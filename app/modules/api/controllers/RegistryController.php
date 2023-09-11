<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use Exception;

class RegistryController extends ApiControllerBase
{

    /** @var BinanceWeb3|EthereumWeb3 */
    public $web3;

    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function getPresaleSettingPlatformAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            $settingKey = "presale_setting_{$platform}_$network";
            if (isset($registry[$settingKey]) && $registry[$settingKey]) {
                $setting = $registry[$settingKey];
                $setting['setting']['max_number_vesting'] = ContractLibrary::MAX_NUMBER_VESTING;
            } else {
                $setting = [];
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $setting, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getPresaleSettingAddressAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $type = $dataGet['type'];
            $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
            $conditions = [
                'platform' => $platform,
                'network' => $network
            ];
            if (strlen($type)) {
                $conditions['type'] = $type;
            }
            $listAddress = $presaleSettingAddressCollection->find($conditions);
            !empty($listAddress) && $listAddress = $listAddress->toArray();
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listAddress, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getTokenSettingPlatformAction()
    {

    }

    public function whitelistTokenAction()
    {
        $dataGet = $this->getData;
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $presaleSetting = $this->mongo->selectCollection('presale_setting_address')->find([
            'type' => ContractLibrary::WHITELIST_TOKEN,
            'network' => $network,
            'platform' => $platform,

        ]);
        !empty($presaleSetting) && $presaleSetting = $presaleSetting->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $presaleSetting, 'Success');
    }

    public function saleWhitelistTokenAction()
    {
        $dataGet = $this->getData;
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $setting = $this->mongo->selectCollection('sale_setting_address')->find([
            'type' => ContractLibrary::WHITELIST_TOKEN,
            'network' => $network,
            'platform' => $platform,

        ]);
        !empty($setting) && $setting = $setting->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $setting, 'Success');
    }

    public function getMintTokenSettingAction()
    {
        $dataGet = $this->getData;
        $network = $dataGet['network'];
        $platform = $dataGet['platform'];
        $tokenKey = "mint_token_setting_{$platform}_{$network}";
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $mintTokenGenerator = $this->mongo->selectCollection('mint_token_generator')->findOne();
        $mintToken = $registry[$tokenKey];
        $mintToken['mint_token_generator_address'] = $mintTokenGenerator['mint_token_generator_address'];

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $mintToken, 'Success');
    }

    public function getLotterySettingAction()
    {
        $dataGet = $this->getData;
        $network = $dataGet['network'];
        $platform = $dataGet['platform'];
        $tokenKey = "lottery_setting_{$platform}_{$network}";
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $lotterySetting = $registry[$tokenKey];

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $lotterySetting, 'Success');
    }

    public function getAirdropSettingAction()
    {
        $dataGet = $this->getData;
        $network = $dataGet['network'];
        $platform = $dataGet['platform'];
        $airdropKey = "airdrop_setting_{$platform}_{$network}";
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $airdrop = $registry[$airdropKey];
        $airdropContract = ContractLibrary::getConfigAddress($dataGet['platform'], $dataGet['network'], ContractLibrary::AIRDROP_CONTRACT);
        $airdrop['airdrop_contract_address'] = $airdropContract;

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $airdrop, 'Success');
    }

    public function getLockSettingPlatformAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            $settingKey = "lock_setting_{$platform}_$network";
            $setting = $registry[$settingKey] ?? [];
            $setting['lock_contract_address'] = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::LOCK_CONTRACT);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $setting, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getSaleSettingAddressAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $conditions = [
                'platform' => $platform,
                'network' => $network
            ];
            if (strlen($dataGet['type'])) {
                $conditions['type'] = $dataGet['type'];
            }

            $saleSettingAddressCollection = $this->mongo->selectCollection('sale_setting_address');
            $listAddress = $saleSettingAddressCollection->find($conditions);
            !empty($listAddress) && $listAddress = $listAddress->toArray();

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listAddress, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getSaleSettingPlatformAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            $settingKey = "sale_setting_{$platform}_$network";
            if (isset($registry[$settingKey]) && $registry[$settingKey]) {
                $setting = $registry[$settingKey];
                $setting['setting']['max_number_vesting'] = ContractLibrary::MAX_NUMBER_VESTING;
            } else {
                $setting = [];
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $setting, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getPoolSettingAddressAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $conditions = [
                'platform' => $platform,
                'network' => $network
            ];
            if (strlen($dataGet['type'])) {
                $conditions['type'] = $dataGet['type'];
            }

            $saleSettingAddressCollection = $this->mongo->selectCollection('pool_setting_address');
            $listAddress = $saleSettingAddressCollection->find($conditions);
            !empty($listAddress) && $listAddress = $listAddress->toArray();

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listAddress, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getPoolSettingPlatformAction()
    {
        try {
            $dataGet = $this->getData;
            $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
            $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            $settingKey = "pool_setting_{$platform}_$network";
            $setting = [];
            if (isset($registry[$settingKey]) && $registry[$settingKey]) {
                $setting = $registry[$settingKey];
                $setting['setting']['max_number_vesting'] = ContractLibrary::MAX_NUMBER_VESTING;
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $setting, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}
