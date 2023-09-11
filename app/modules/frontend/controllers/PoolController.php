<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;

class PoolController extends ExtendedControllerBase
{
    protected string $projectType = ContractLibrary::PROJECT_TYPE_POOL;

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

        $conditions["project_type"] = $this->projectType;
        $listPoolStatusWithName = $this->listPoolStatusWithName();
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }
        if (strlen($dataGet['status'])) {
            $flipStatus = array_flip($listPoolStatusWithName);
            $conditions['current_status'] = $flipStatus[$dataGet['status']] ?? null;
            if ($conditions['current_status'] == ContractLibrary::POOL_BURNING_ROUND) {
                unset($conditions['current_status']);
                $conditions['current_round'] = ContractLibrary::POOL_BURNING_ROUND;
            }
        }
        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['pool_token_name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['pool_token_symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['q']);
                array_push(
                    $conditions['$or'],
                    ['contract_address' => $filterAddress],
                    ['pool_token_address' => $filterAddress],
                    ['pool_owner_address' => $filterAddress],
                    ['base_token_address' => $filterAddress],
                );
            }
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $poolCollection = $this->mongo->selectCollection('pool');
        if ($dataGet['export']) {
            $listData = $poolCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $fieldKeys = [
                'hash' => 'Hash',
                'created_at' => 'Time',
                'start_time' => 'Start Time',
                'end_time' => 'End Time',
                'platform' => 'Platform',
                'network' => 'Network',
                'base_token_symbol' => 'Base Token Symbol',
                'pool_token_symbol' => 'Sale Token Symbol',
                'pool_owner_address' => 'Presale owner',
                'contract_address' => 'Contract address',
                'amount' => 'Sale amount',
                'creation_fee' => 'Creation fee',
                'hard_cap' => 'Hard cap',
                'token_price' => 'Sale Price',
            ];
            $this->exportDataByField($listData, $this->projectType, $fieldKeys);
        }

        $listData = $poolCollection->find($conditions, $options);
        $count = $poolCollection->countDocuments($conditions);
        !empty($listData) && $listData = $listData->toArray();
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $activeFlag = ContractLibrary::ACTIVE;

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'activeFlag', 'listPoolStatusWithName'));
    }

    public function detailAction($id)
    {
        $id = new ObjectId($id);
        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $registry = $this->mongo->selectCollection('registry')->findOne();

        $this->view->setVars(compact('pool', 'registry'));
    }

    public function buyLogAction($id)
    {
        $limit = 20;
        $isSearch = true;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $id = new ObjectId($id);
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolBuyLogCollection = $this->mongo->selectCollection('pool_buy_log');
        $pool = $poolCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'pool_address' => $pool['contract_address'],
            'network' => $pool['network'],
            'platform' => $pool['platform'],
        ];
        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['hash' => $dataGet['q']],
            ];
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['$or'][] = ['user_address' => $coinInstance->toCheckSumAddress($dataGet['user_address'])];
            }
        }
        $listPoolBuyLog = $poolBuyLogCollection->find($conditions, $options);
        $count = $poolBuyLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPoolStatusWithName = $this->listPoolStatusWithName();

        $this->view->setVars(compact('pool', 'listPoolBuyLog', 'pagingInfo', 'isSearch', 'dataGet', 'listPoolStatusWithName'));
    }

    public function userLogAction($id)
    {
        $limit = 20;
        $isSearch = true;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $id = new ObjectId($id);
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserLogCollection = $this->mongo->selectCollection('pool_user_log');
        $pool = $poolCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'pool_address' => $pool['contract_address'],
            'network' => $pool['network'],
            'platform' => $pool['platform'],
            'contract_type' => $this->projectType,
        ];
        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            }
        }
        if (strlen($dataGet['withdraw_status'])) {
            $conditions['withdraw_status'] = intval($dataGet['withdraw_status']);
        }

        $listPoolUserLog = $poolUserLogCollection->find($conditions, $options);
        $count = $poolUserLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPoolStatusWithName = $this->listPoolStatusWithName();

        $this->view->setVars(compact('pool', 'listPoolUserLog', 'pagingInfo', 'isSearch', 'dataGet', 'listPoolStatusWithName'));
    }

    public function userZeroRoundAction($id)
    {
        $id = new ObjectId($id);
        $dataGet = $this->getData;
        $isSearch = true;
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolUserZeroRoundCollection = $this->mongo->selectCollection('pool_user_zero_round');
        $pool = $poolCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'pool_address' => $pool['contract_address'],
            'network' => $pool['network'],
            'platform' => $pool['platform'],
        ];
        if (strlen($dataGet['q'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
            }
        }
        $listPoolUserZeroRound = $poolUserZeroRoundCollection->find($conditions);

        $this->view->setVars(compact('pool', 'listPoolUserZeroRound', 'isSearch', 'dataGet'));
    }

    public function whitelistUserAction($id)
    {
        $id = new ObjectId($id);
        $dataGet = $this->getData;
        $isSearch = true;
        $poolCollection = $this->mongo->selectCollection('pool');
        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');
        $pool = $poolCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'pool_address' => $pool['contract_address'],
            'network' => $pool['network'],
            'platform' => $pool['platform'],
        ];
        if (strlen($dataGet['q'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
            }
        }
        $listPoolWhitelist = $poolWhitelistCollection->find($conditions);

        $this->view->setVars(compact('pool', 'listPoolWhitelist', 'isSearch', 'dataGet'));
    }

    public function setShowAction($id)
    {
        $id = new ObjectId($id);
        $dataGet = $this->getData;
        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            '_id' => $id,
        ]);
        if (!empty($pool)) {
            if (isset($dataGet['is_show']) && $dataGet['is_show'] == ContractLibrary::ACTIVE) {
                $dataUpdate['is_show'] = ContractLibrary::ACTIVE;
            } else {
                $dataUpdate['is_show'] = ContractLibrary::INACTIVE;
            }
            $poolCollection->updateOne(['_id' => $pool['_id']], ['$set' => $dataUpdate]);
        }

        $this->flash->success("Update success!");
        return $this->response->redirect($this->request->getHTTPReferer());
    }

    protected function listPoolStatusWithName()
    {
        return [
            ContractLibrary::PRESALE_STATUS_PENDING => 'Pending',
            ContractLibrary::PRESALE_STATUS_ACTIVE => 'Active',
            ContractLibrary::PRESALE_STATUS_SUCCESS => 'Success',
            ContractLibrary::PRESALE_STATUS_FAILED => 'Failed',
            ContractLibrary::POOL_BURNING_ROUND => 'Burning',
        ];
    }

    /**
     * @param $id
     */
    public function updateAction($id)
    {
        $id = new ObjectId($id);
        $poolCollection = $this->mongo->selectCollection('pool');
        $presale = $poolCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);

        if ($this->request->isPost()) {
            $data = $this->postData;
            $dataUpdate = [];
            if ($data['pool_token_name'] && !empty($data['pool_token_name'])) {
                $dataUpdate['pool_token_name'] = $data['pool_token_name'];
            }

            if ($dataUpdate) {
                $dataUpdate['updated_at'] = time();
                $poolCollection->updateOne(['_id' => $id], ['$set' => $dataUpdate]);
                return $this->returnBackRefURL('success', 'Update Pool Successfully!', '/pool');
            }

            $this->flash->error("Something went wrong!");
        }

        $this->view->setVars(compact('presale'));
    }
}
