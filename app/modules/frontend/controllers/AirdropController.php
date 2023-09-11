<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;

class AirdropController extends ExtendedControllerBase
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
    public function indexAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['token_type'])) {
            $conditions['token_type'] = $dataGet['token_type'];
        }

        if (strlen($dataGet['token_address'])) {
            $mainCurrency = Adapter::getMainCurrency($dataGet['platform']);
            $coinInstance = Adapter::getInstance($mainCurrency ?? BinanceWeb3::MAIN_CURRENCY);
            $conditions['token_address'] = $coinInstance->toCheckSumAddress($dataGet['token_address']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $airdropCollection = $this->mongo->selectCollection('airdrop');

        //<editor-fold desc="Export excel">
        if ($dataGet['export'] == 1) {
            $listData = $airdropCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $fieldKeys = [
                'hash' => 'Hash',
                'created_at' => 'Time',
                'platform' => 'Platform',
                'network' => 'Network',
                'airdrop_contract_address' => 'Airdrop Contract',
                'user_address' => 'User Address',
                'token_type' => 'Type',
                'token_address' => 'Token Address',
                'token_name' => 'Token Name',
                'token_symbol' => 'Token Symbol',
                'list_address' => 'Address Number',
                'total_token_amount' => 'Total Amount',
                'fee_amount' => 'Fee Amount',
            ];
            $this->exportDataByField($listData, 'Airdrop', $fieldKeys);
        }
        //</editor-fold>

        $listData = $airdropCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $airdropCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();

        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    public function detailAction($id)
    {
        $airdropId = new ObjectId($id);
        $airdrop = $this->mongo->selectCollection('airdrop')->findOne(['_id' => $airdropId]);
        if (!$airdrop) {
            return $this->returnBackRefURL('error', 'Data not found');
        }
        $this->view->setVars(compact('airdrop'));
    }
}
