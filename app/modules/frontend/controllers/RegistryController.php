<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use DCrypto\Networks\TronWeb3;

class RegistryController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize(['check-role' => [BaseCollection::ROLE_ADMIN]]);
    }

    public function indexAction()
    {
        $lsChainScan = [
            EthereumWeb3::PLATFORM,
            TronWeb3::PLATFORM,
            BinanceWeb3::PLATFORM,
        ];
        $data = [];
        foreach ($lsChainScan as $item) {
            $lsBlock = $this->redis->get($item . '_block_scan');
            $data[$item] = json_decode($lsBlock, true);
        }
        $this->view->data = $data;
    }

    public function addBlockScanAction($token)
    {
        $lsBlockScan = $this->redis->get($token . '_block_scan');
        $lsBlockScan = json_decode($lsBlockScan, true);
        if ($this->request->isPost()) {
            $data = $this->postData['block'];
            $lsBlockScan[] = $data;
            $this->redis->set($token . '_block_scan', json_encode($lsBlockScan));
            $this->flash->success("Add block $token to scan success");
            return $this->response->redirect("/registry");
        }
    }

    public function presaleSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["presale_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $activeMenu = ['presale_setting', "presale_setting_$platform"];
        $presaleSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::PRESALE_SETTING);
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'presaleSettingAddress'));
    }

    public function mintTokenSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["mint_token_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $mintTokenSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::MINT_TOKEN_SETTING);
        $activeMenu = ['mint_token_setting', "mint_token_setting_$platform"];
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'mintTokenSettingAddress'));
    }

    public function airdropSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["airdrop_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $airdropSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::AIRDROP_SETTING);
        $activeMenu = ['airdrop_setting', "airdrop_setting_$platform"];
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'airdropSettingAddress'));
    }

    public function lockSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["lock_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $activeMenu = ['lock_setting', "lock_setting_$platform"];
        $lockSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::LOCK_SETTING);
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'lockSettingAddress'));
    }

    public function saleSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["sale_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $activeMenu = ['sale_setting', "sale_setting_$platform"];
        $saleSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::SALE_SETTING);
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'saleSettingAddress'));
    }

    public function poolSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["pool_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $activeMenu = ['pool_setting', "pool_setting_$platform"];
        $poolSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::POOL_SETTING);
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'poolSettingAddress'));
    }

    public function lotterySettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $settingKey = "lottery_setting_{$platform}_$network";
        $setting = $registry["lottery_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $activeMenu = ['lottery_setting', "lottery_setting_$platform"];
        $lotterySettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::LOTTERY);

        if ($this->request->isPost()) {
            $postData = $this->postData;
            $dataUpdate[$settingKey.'.discount_divisor'] = intval($postData['discount_divisor']);
            $dataUpdate[$settingKey.'.rewards_breakdown'] = $postData['rewards_breakdown'];
            $dataUpdate[$settingKey.'.treasury_fee'] = intval($postData['treasury_fee']);
            $dataUpdate[$settingKey.'.price_ticket'] = doubleval($postData['price_ticket']);
            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            if ($registry) {
                $registryCollection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $registryCollection->insertOne($dataUpdate);
            }
            return $this->returnBackRefURL('success', 'Update Success');
        }

        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'lotterySettingAddress'));
    }

    public function stakingSettingAction($platform = BinanceWeb3::PLATFORM, $network = ContractLibrary::MAIN_NETWORK)
    {
        $wallet = $this->session->get('connected_wallet');
        if ($wallet) {
            $platform = $wallet['platform'];
            $network = $wallet['network'];
        }
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $setting = $registry["staking_setting_{$platform}_$network"];
        $mainCurrency = Adapter::getMainCurrency($platform);
        $activeMenu = ['staking_setting', "staking_setting_$platform"];
        $stakingSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::STAKING);
        $this->view->setVars(compact('setting', 'platform', 'network', 'mainCurrency', 'activeMenu', 'stakingSettingAddress', 'registry'));
    }
}
