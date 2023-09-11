<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Services\AirdropSettingContractService;
use Dcore\Services\LockSettingContractService;
use Dcore\Services\LotteryService;
use Dcore\Services\MintTokenFactoryContractService;
use Dcore\Services\MintTokenSettingContractService;
use Dcore\Services\PoolFactoryContractService;
use Dcore\Services\PoolSettingContractService;
use Dcore\Services\PresaleFactoryContractService;
use Dcore\Services\PresaleSettingContractService;
use Dcore\Services\SaleFactoryContractService;
use Dcore\Services\SaleSettingContractService;
use Dcore\Services\StakingService;
use Exception;

class RegistryTask extends Web3Task
{
    /**
     * @throws Exception
     */
    public function importMasterDataAction()
    {
        $this->updateRateAction();
        $this->importPresaleGeneratorAction();
        $this->importPresaleSettingAction();
        $this->importMintTokenGeneratorAction();
        $this->importMintTokenSettingAction();
        $this->importAirdropSettingAction();
        $this->importLockSettingAction();
        $this->updatePriceBaseTokenAction();
        $this->importSaleGeneratorAction();
        $this->importSaleSettingAction();
        $this->importPoolSettingAction();
        $this->importPoolGeneratorAction();
        $this->importLotterySettingAction();
        $this->importStakingSettingAction();
    }

    public function updateRateAction()
    {
        $dataUpdate = [];
        $priceBNB = ContractLibrary::getPriceBNB();
        if ($priceBNB > 0) {
            $dataUpdate['bnb_rate'] = $priceBNB;
        }

        $priceETH = ContractLibrary::getPriceETH();
        if ($priceETH > 0) {
            $dataUpdate['eth_rate'] = $priceETH;
        }

        if (count($dataUpdate)) {
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
    }

    public function importSaleGeneratorAction()
    {
        $type = ContractLibrary::SALE_FACTORY;
        $listSaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleFactory)) {
            foreach ($listSaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $saleFactoryService = SaleFactoryContractService::getInstance($network, $platform);
                $saleFactoryService->updateSaleGenerator($address);
            }
        }
    }

    public function importSaleSettingAction()
    {
        $type = ContractLibrary::SALE_SETTING;
        $listSaleSetting = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleSetting)) {
            foreach ($listSaleSetting as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $saleSettingService = SaleSettingContractService::getInstance($network, $platform);
                $settingInfo = $saleSettingService->updateSaleSetting($address);

                $saleSettingService->processUpdateSaleBaseToken($settingInfo);
                $saleSettingService->processUpdateSaleWhitelistToken($settingInfo);
                $saleSettingService->processSaleZeroRoundToken($settingInfo);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importPresaleGeneratorAction()
    {
        $type = ContractLibrary::PRESALE_FACTORY;
        $listPresaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listPresaleFactory)) {
            foreach ($listPresaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $presaleFactoryService = PresaleFactoryContractService::getInstance($network, $platform);
                $presaleFactoryService->updatePresaleGenerator($address);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importMintTokenGeneratorAction()
    {
        $type = ContractLibrary::MINT_TOKEN_FACTORY;
        $listPresaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listPresaleFactory)) {
            foreach ($listPresaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $mintTokenFactoryService = MintTokenFactoryContractService::getInstance($network, $platform);
                $mintTokenFactoryService->updateMintTokenGenerator($address);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importPresaleSettingAction()
    {
        $type = ContractLibrary::PRESALE_SETTING;
        $listPresaleSetting = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listPresaleSetting)) {
            foreach ($listPresaleSetting as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $presaleSettingService = PresaleSettingContractService::getInstance($network, $platform);
                $settingInfo = $presaleSettingService->updatePresaleSetting($address);

                $presaleSettingService->processUpdatePresaleBaseToken($settingInfo);
                $presaleSettingService->processUpdatePresaleWhitelistToken($settingInfo);
                $presaleSettingService->processPresaleZeroRoundToken($settingInfo);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importMintTokenSettingAction()
    {
        $type = ContractLibrary::MINT_TOKEN_SETTING;
        $listMintTokenSetting = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listMintTokenSetting)) {
            foreach ($listMintTokenSetting as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $mintTokenSettingService = MintTokenSettingContractService::getInstance($network, $platform);
                $mintTokenSettingService->updateMintTokenSetting($address);
            }
        }
    }

    public function importAirdropSettingAction()
    {
        $type = ContractLibrary::AIRDROP_SETTING;
        $listAirdropSetting = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listAirdropSetting)) {
            foreach ($listAirdropSetting as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $airdropSettingService = AirdropSettingContractService::getInstance($network, $platform);
                $airdropSettingService->updateAirdropSetting($address);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importLockSettingAction()
    {
        $type = ContractLibrary::LOCK_SETTING;
        $listLockSetting = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listLockSetting)) {
            foreach ($listLockSetting as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $lockSettingService = LockSettingContractService::getInstance($network, $platform);
                $lockSettingService->updateLockSetting($address);
            }
        }
    }

    public function updatePriceBaseTokenAction()
    {
        $cacheKey = 'update_price_base_token';
        try {
            $syncingStatus = $this->redis->get($cacheKey);
            if ($syncingStatus == 1) {
                echo "Update price Base token is running..." . PHP_EOL;
                return;
            }
            $this->redis->set($cacheKey, 1, 60 * 60);
            $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
            $saleSettingAddressCollection = $this->mongo->selectCollection('sale_setting_address');
            $poolSettingAddressCollection = $this->mongo->selectCollection('pool_setting_address');
            $conditions = [
                'type' => ContractLibrary::BASE_TOKEN,
                'network' => ContractLibrary::MAIN_NETWORK,
            ];
            $options = ['projection' => ['token_symbol' => 1]];

            $presaleSetting = $presaleSettingAddressCollection->find($conditions, $options);
            $saleSetting = $saleSettingAddressCollection->find($conditions, $options);
            $poolSetting = $poolSettingAddressCollection->find($conditions, $options);
            !empty($presaleSetting) && $presaleSetting = $presaleSetting->toArray();
            !empty($saleSetting) && $saleSetting = $saleSetting->toArray();
            !empty($poolSetting) && $poolSetting = $poolSetting->toArray();
            $presaleSetting = Arrays::arrayColumn($presaleSetting, 'token_symbol');
            $saleSetting = Arrays::arrayColumn($saleSetting, 'token_symbol');
            $poolSetting = Arrays::arrayColumn($poolSetting, 'token_symbol');

            $listToken = array_values(array_unique(array_merge($presaleSetting, $saleSetting)));
            $strAddress = implode(",", $listToken);

            if (strlen($strAddress)) {
                $usd = file_get_contents("https://min-api.cryptocompare.com/data/pricemulti?fsyms={$strAddress}&tsyms=USD");
                $usd = json_decode($usd);
                if (isset($usd->Response) && $usd->Response == 'Error') {
                    return;
                }
            }

            foreach ($presaleSetting as $baseToken) {
                if (isset($usd->{$baseToken}) && $usd->{$baseToken}) {
                    $arrPresaleUpdate[] = [
                        'updateMany' => [
                            ['token_symbol' => $baseToken],
                            ['$set' => ['current_price' => $usd->{$baseToken}->USD]],
                        ]
                    ];
                }
            }

            foreach ($saleSetting as $baseToken) {
                if (isset($usd->{$baseToken}) && $usd->{$baseToken}) {
                    $arrSaleUpdate[] = [
                        'updateMany' => [
                            ['token_symbol' => $baseToken],
                            ['$set' => ['current_price' => $usd->{$baseToken}->USD]],
                        ]
                    ];
                }
            }

            foreach ($poolSetting as $baseToken) {
                if (isset($usd->{$baseToken}) && $usd->{$baseToken}) {
                    $arrPoolUpdate[] = [
                        'updateMany' => [
                            ['token_symbol' => $baseToken],
                            ['$set' => ['current_price' => $usd->{$baseToken}->USD]],
                        ]
                    ];
                }
            }

            if (!empty($arrPresaleUpdate)) {
                $presaleSettingAddressCollection->bulkWrite($arrPresaleUpdate);
            }
            if (!empty($arrSaleUpdate)) {
                $saleSettingAddressCollection->bulkWrite($arrSaleUpdate);
            }
            if (!empty($arrPoolUpdate)) {
                $poolSettingAddressCollection->bulkWrite($arrPoolUpdate);
            }

            $this->redis->del($cacheKey);
        } catch (Exception $exception) {
            echo "[ERROR] " . $exception->getMessage() . "\r\n";
        }
        $this->redis->del($cacheKey);
    }

    /**
     * @throws Exception
     */
    public function importPoolSettingAction()
    {
        $type = ContractLibrary::POOL_SETTING;
        $listSaleSetting = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleSetting)) {
            foreach ($listSaleSetting as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $poolSettingService = PoolSettingContractService::getInstance($network, $platform);
                $settingInfo = $poolSettingService->updatePoolSetting($address);

                $poolSettingService->processUpdatePoolBaseToken($settingInfo);
                $poolSettingService->processUpdatePoolWhitelistToken($settingInfo);
                $poolSettingService->processPoolZeroRoundToken($settingInfo);
                $poolSettingService->processPoolAuctionRoundToken($settingInfo);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importPoolGeneratorAction()
    {
        $type = ContractLibrary::POOL_FACTORY;
        $listSaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleFactory)) {
            foreach ($listSaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $poolFactoryService = PoolFactoryContractService::getInstance($network, $platform);
                $poolFactoryService->updatePoolGenerator($address);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importLotterySettingAction()
    {
        $type = ContractLibrary::LOTTERY;
        $listSaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleFactory)) {
            foreach ($listSaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $lotteryService = LotteryService::getInstance($network, $platform);
                $lotteryService->updateLotterySetting($address);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function importStakingSettingAction()
    {
        $type = ContractLibrary::STAKING;
        $listSaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleFactory)) {
            foreach ($listSaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $stakingService = StakingService::getInstance($network, $platform);
                $stakingService->updateStakingSetting($address);
            }
        }
    }
}
