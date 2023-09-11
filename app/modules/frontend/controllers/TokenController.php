<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Httpful\Exception\ConnectionErrorException;

class TokenController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize();
        $this->getConnectedWallet();
    }

    /**
     * @throws ConnectionErrorException
     */
    public function indexAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [];
        $listStatus = $this->listStatus();
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['status'])) {
            $conditions['status'] = $dataGet['status'] == $listStatus[ContractLibrary::ACTIVE] ? ContractLibrary::ACTIVE : ContractLibrary::INACTIVE;
        }

        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            $address = trim($dataGet['q']);
            if ($coinInstance->validAddress($address)) {
                array_push(
                    $conditions['$or'],
                    ['address' => $coinInstance->toCheckSumAddress($address)],
                );
            }
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $listData = $tokenCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $tokenCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();

        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count', 'listStatus'));
    }

    protected function listStatus()
    {
        return [
            ContractLibrary::INACTIVE => 'INACTIVE',
            ContractLibrary::ACTIVE => 'ACTIVE',
        ];
    }
}
