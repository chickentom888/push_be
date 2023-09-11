<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class PresaleController extends ExtendedControllerBase
{
    protected string $projectType = ContractLibrary::PROJECT_TYPE_PRESALE;

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

        $conditions["project_type"] = $this->projectType;
        $listPresaleStatus = $this->listPresaleStatus();
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['status'])) {
            $flipStatus = array_flip($listPresaleStatus);
            $conditions['current_status'] = $flipStatus[$dataGet['status']] ?? null;
        }

        if (strlen($dataGet['sale_type'])) {
            $conditions['sale_type'] = $dataGet['sale_type'];
        }

        $mainCurrency = Adapter::getMainCurrency($dataGet['platform']);
        $coinInstance = Adapter::getInstance($mainCurrency ?? BinanceWeb3::MAIN_CURRENCY);
        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['sale_token_name' => $dataGet['q']],
                ['sale_token_symbol' => $dataGet['q']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['q']);
                array_push(
                    $conditions['$or'],
                    ['contract_address' => $filterAddress],
                    ['sale_token_address' => $filterAddress],
                    ['presale_owner_address' => $filterAddress],
                    ['base_token_address' => $filterAddress],
                    ['dex_factory_address' => $filterAddress],
                );
            }
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $presaleCollection = $this->mongo->selectCollection('presale');
        if ($dataGet['export']) {
            $listData = $presaleCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $fieldKeys = [
                'hash' => 'Hash',
                'created_at' => 'Time',
                'start_time' => 'Start Time',
                'end_time' => 'End Time',
                'success_at' => 'Success Time',
                'platform' => 'Platform',
                'network' => 'Network',
                'base_token_symbol' => 'Base Token Symbol',
                'sale_token_symbol' => 'Sale Token Symbol',
                'sale_type' => 'Sale Type',
                'presale_owner_address' => 'Presale owner',
                'contract_address' => 'Contract address',
                'amount' => 'Sale amount',
                'base_token_liquidity_amount' => 'Base liquidity amount',
                'sale_token_liquidity_amount' => 'Sale token liquidity',
                'token_price' => 'Sale price',
                'soft_cap' => 'Soft cap',
                'hard_cap' => 'Hard cap',
                'creation_fee' => 'Creation fee',
                'base_fee_amount' => 'Base token fee',
                'sale_token_fee_amount' => 'Sale token fee',
                'current_status' => 'Status',
            ];
            $this->exportDataByField($listData, $this->projectType, $fieldKeys);
        }

        $listData = $presaleCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $presaleCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $activeFlag = ContractLibrary::ACTIVE;

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'activeFlag', 'listPresaleStatus'));
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
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleBuyLogCollection = $this->mongo->selectCollection('presale_buy_log');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'presale_address' => $presale['contract_address'],
            'project_type' => $this->projectType,
            'network' => $presale['network'],
            'platform' => $presale['platform'],
        ];
        if (strlen($dataGet['user_address'])) {
            $conditions['$or'] = [
                ['hash' => $dataGet['q']],
            ];
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['$or'][] = ['user_address' => $coinInstance->toCheckSumAddress($dataGet['user_address'])];
            }
        }
        $listPresaleBuyLog = $presaleBuyLogCollection->find($conditions, $options);
        $count = $presaleBuyLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $this->view->setVars(compact('presale', 'listPresaleBuyLog', 'pagingInfo', 'isSearch', 'dataGet'));
    }

    public function setShowAction($id)
    {
        $id = new ObjectId($id);
        $dataGet = $this->getData;
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
        ]);
        if (!empty($presale)) {
            if (isset($dataGet['is_show']) && $dataGet['is_show'] == ContractLibrary::ACTIVE) {
                $dataUpdate['is_show'] = ContractLibrary::ACTIVE;
            } else {
                $dataUpdate['is_show'] = ContractLibrary::INACTIVE;
            }
            $presaleCollection->updateOne(['_id' => $presale['_id']], ['$set' => $dataUpdate]);
        }

        $this->flash->success("Update success!");
        return $this->response->redirect($this->request->getHTTPReferer());
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
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
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'presale_address' => $presale['contract_address'],
            'project_type' => $this->projectType,
            'network' => $presale['network'],
            'platform' => $presale['platform'],
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

        $listPresaleUserLog = $presaleUserLogCollection->find($conditions, $options);
        $count = $presaleUserLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPresaleStatus = $this->listPresaleStatus();

        $this->view->setVars(compact('presale', 'listPresaleUserLog', 'pagingInfo', 'isSearch', 'dataGet', 'listPresaleStatus'));
    }

    public function detailAction($id)
    {
        $id = new ObjectId($id);
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $registry = $this->mongo->selectCollection('registry')->findOne();

        $this->view->setVars(compact('presale', 'registry'));
    }

    public function userZeroRoundAction($id)
    {
        $id = new ObjectId($id);
        $dataGet = $this->getData;
        $isSearch = true;
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'presale_address' => $presale['contract_address'],
            'project_type' => $this->projectType,
            'network' => $presale['network'],
            'platform' => $presale['platform'],
        ];
        if (strlen($dataGet['q'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
            }
        }
        $listPresaleUserZeroRound = $presaleUserZeroRoundCollection->find($conditions);

        $this->view->setVars(compact('presale', 'listPresaleUserZeroRound', 'isSearch', 'dataGet'));
    }

    public function whitelistUserAction($id)
    {
        $id = new ObjectId($id);
        $dataGet = $this->getData;
        $isSearch = true;
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleWhitelistCollection = $this->mongo->selectCollection('presale_whitelist');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);
        $conditions = [
            'presale_address' => $presale['contract_address'],
            'project_type' => $this->projectType,
            'network' => $presale['network'],
            'platform' => $presale['platform'],
        ];
        if (strlen($dataGet['q'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
            }
        }
        $listPresaleWhitelist = $presaleWhitelistCollection->find($conditions);

        $this->view->setVars(compact('presale', 'listPresaleWhitelist', 'isSearch', 'dataGet'));
    }

    /**
     * @param $id
     */
    public function updateAction($id)
    {
        $id = new ObjectId($id);
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            '_id' => $id,
            'project_type' => $this->projectType,
        ]);

        if ($this->request->isPost()) {
            $data = $this->postData;
            $dataUpdate = [];
            if ($data['sale_token_name'] && !empty($data['sale_token_name'])) {
                $dataUpdate['sale_token_name'] = $data['sale_token_name'];
            }

            if ($dataUpdate) {
                $dataUpdate['updated_at'] = time();
                $presaleCollection->updateOne(['_id' => $id], ['$set' => $dataUpdate]);
                return $this->returnBackRefURL('success', 'Update Presale Successfully!', '/presale');
            }

            $this->flash->error("Something went wrong!");
        }

        $this->view->setVars(compact('presale'));
    }
}
