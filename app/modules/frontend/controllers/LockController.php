<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class LockController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        parent::initialize($param);
        $this->checkLogin();
        $this->getConnectedWallet();
    }

    /**
     * @throws Exception
     */
    public function indexAction()
    {
        $collection = $this->mongo->selectCollection('lock_histories');
        $dataGet = $this->getData;
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listContractType = $this->listContractType();
        $listWithdrawnStatus = ContractLibrary::getWithdrawStatusName();
        $withdrawnStatus = ContractLibrary::WITHDRAWN;
        $notWithdrawnStatus = ContractLibrary::NOT_WITHDRAW;

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }
        if (strlen($dataGet['withdraw_status'])) {
            $conditions['withdraw_status'] = isset($listWithdrawnStatus[$dataGet['withdraw_status']]) ? intval($dataGet['withdraw_status']) : null;
        }
        if (strlen($dataGet['address'])) {
            $conditions['$or'] = [
                ['address_lock' => $dataGet['address']],
            ];
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['address']);
                array_push(
                    $conditions['$or'],
                    ['from' => $filterAddress],
                    ['to' => $filterAddress],
                );
            }
        }

        if (strlen($dataGet['address_token'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address_token'])) {
                $tokenAddress = $coinInstance->toCheckSumAddress($dataGet['address_token']);
                $conditions['token_address'] = $tokenAddress;
            }
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }

        $limit = 20;
        $p = $dataGet['p'] ?? 1;
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];


        $count = $collection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $collection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'listPlatform',
            'listNetwork', 'listContractType', 'listWithdrawnStatus', 'withdrawnStatus', 'notWithdrawnStatus'));
    }

    /**
     * @return string[]
     */
    protected static function listContractType(): array
    {
        return [
            ContractLibrary::DEX_FACTORY => 'Dex factory',
        ];
    }
}
