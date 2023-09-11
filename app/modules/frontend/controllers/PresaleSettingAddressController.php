<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;

class PresaleSettingAddressController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize($param);
        $this->getConnectedWallet();
    }

    /**
     * @throws Exception
     */
    public function indexAction($type = ContractLibrary::BASE_TOKEN)
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            'type' => $type
        ];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['q'])) {
            $mainCurrency = Adapter::getMainCurrency($dataGet['platform']);
            $coinInstance = Adapter::getInstance($mainCurrency ?? BinanceWeb3::MAIN_CURRENCY);
            $conditions['token_address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
        $listData = $presaleSettingAddressCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $presaleSettingAddressCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listPresaleSettingAddressType = [
            ContractLibrary::BASE_TOKEN => 'Base Token',
            ContractLibrary::WHITELIST_TOKEN => 'Whitelist Token',
            ContractLibrary::ZERO_ROUND_TOKEN => 'Zero Round Token'
        ];
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count', 'listPresaleSettingAddressType', 'type'));
    }

    public function formAction($id)
    {
        $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
        $presaleSettingAddressId = new ObjectId($id);
        $object = $presaleSettingAddressCollection->findOne(['_id' => $presaleSettingAddressId]);
        if ($this->request->isPost()) {
            $data = $this->postData;
            $presaleSettingAddressCollection->updateOne(['_id' => $presaleSettingAddressId], ['$set' => $data]);
            return $this->returnBackRefURL('success', 'Success', "/presale_setting_address/index/{$object['type']}");
        }
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('object', 'listPlatform', 'listNetwork'));
    }
}
