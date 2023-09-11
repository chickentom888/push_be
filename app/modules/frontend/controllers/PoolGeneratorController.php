<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class PoolGeneratorController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize();
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

        if (strlen($dataGet['q'])) {
            $mainCurrency = Adapter::getMainCurrency($dataGet['platform']);
            $coinInstance = Adapter::getInstance($mainCurrency ?? BinanceWeb3::MAIN_CURRENCY);
            $conditions['address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $poolGeneratorCollection = $this->mongo->selectCollection('pool_generator');
        $listData = $poolGeneratorCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $poolGeneratorCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    public function formAction()
    {
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listPlatform', 'listNetwork'));
    }
}
