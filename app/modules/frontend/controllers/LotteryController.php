<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class LotteryController extends ExtendedControllerBase
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

        $listLotteryStatus = $this->listLotteryStatus();
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['status'])) {
            $flipStatus = array_flip($listLotteryStatus);
            $conditions['status'] = $flipStatus[$dataGet['status']] ?? null;
        }

        if (strlen($dataGet['lottery_id'])) {
            $conditions['lottery_id'] = intval($dataGet['lottery_id']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        if ($dataGet['export']) {
            $listData = $lotteryCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $fieldKeys = [
                'hash' => 'Hash',
                'created_at' => 'Time',
                'start_time' => 'Start Time',
                'end_time' => 'End Time',
                'platform' => 'Platform',
                'network' => 'Network',
                'status' => 'Status',
            ];
            $this->exportDataByField($listData, 'Lottery', $fieldKeys);
        }

        $listData = $lotteryCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $lotteryCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'listLotteryStatus'));
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function buyLogAction($id)
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryBuyLogCollection = $this->mongo->selectCollection('lottery_buy_log');
        $id = new ObjectId($id);
        $lottery = $lotteryCollection->findOne([
            '_id' => $id
        ]);
        $conditions = [
            'lottery_contract_id' => $lottery['lottery_contract_id'],
            'network' => $lottery['network'],
            'platform' => $lottery['platform'],
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
        $listData = $lotteryBuyLogCollection->find($conditions, $options);
        $count = $lotteryBuyLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $this->view->setVars(compact('lottery', 'listData', 'pagingInfo', 'dataGet'));
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function userLogAction($id)
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryUserLogCollection = $this->mongo->selectCollection('lottery_user_log');
        $id = new ObjectId($id);
        $lottery = $lotteryCollection->findOne([
            '_id' => $id,
        ]);
        $conditions = [
            'lottery_contract_id' => $lottery['lottery_contract_id'],
            'network' => $lottery['network'],
            'platform' => $lottery['platform'],
        ];
        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            }
        }

        $listData = $lotteryUserLogCollection->find($conditions, $options);
        $count = $lotteryUserLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listLotteryStatus = $this->listLotteryStatus();

        $this->view->setVars(compact('lottery', 'listData', 'pagingInfo', 'dataGet', 'listLotteryStatus'));
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function ticketAction($id = null)
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');

        $conditions = [];

        $lottery = null;
        if ($id) {
            $id = new ObjectId($id);
            $lottery = $lotteryCollection->findOne([
                '_id' => $id,
            ]);
            $conditions['lottery_contract_id'] = $lottery['lottery_contract_id'];
        }

        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            }
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['lottery_contract_id'])) {
            $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
        }

        if (strlen($dataGet['ticket_id'])) {
            $conditions['ticket_id'] = intval($dataGet['ticket_id']);
        }

        if (strlen($dataGet['user_real_ticket_number'])) {
            $conditions['user_real_ticket_number'] = $dataGet['user_real_ticket_number'];
        }

        if (strlen($dataGet['bracket'])) {
            $conditions['bracket'] = intval($dataGet['bracket']);
        }

        if (strlen($dataGet['is_win'])) {
            $conditions['is_win'] = intval($dataGet['is_win']) == 1;
        }

        if (strlen($dataGet['is_claim'])) {
            $conditions['is_claim'] = intval($dataGet['is_claim']);
        }

        $listData = $lotteryTicketCollection->find($conditions, $options);
        $count = $lotteryTicketCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listLotteryStatus = $this->listLotteryStatus();

        $this->view->setVars(compact('lottery', 'listData', 'pagingInfo', 'dataGet', 'listLotteryStatus'));
    }

    public function cronAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $conditions = [];

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['lottery_contract_id'])) {
            $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
        }

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['status']);
        }

        if (strlen($dataGet['tx_status'])) {
            $conditions['tx_status'] = intval($dataGet['tx_status']);
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }

        if (strlen($dataGet['action'])) {
            $conditions['action'] = $dataGet['action'];
        }

        $listData = $lotteryCronCollection->find($conditions, $options);
        $count = $lotteryCronCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listLotteryCronStatus = $this->listLotteryCronStatus();
        $listLotteryCronTxStatus = $this->listLotteryCronTxStatus();

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'listLotteryCronStatus', 'listLotteryCronTxStatus'));
    }

    public function updateCronAction($id)
    {
        $id = new ObjectId($id);
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $lotteryCron = $lotteryCronCollection->findOne(['_id' => $id]);
        if (!$lotteryCron) {
            return $this->returnBackRefURL('error', 'Data not found');
        }
        if ($lotteryCron['tx_status'] != ContractLibrary::TRANSACTION_STATUS_FAIL) {
            return $this->returnBackRefURL('error', 'Transaction not failed');
        }

        $dataUpdate = [
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING,
            'tx_status' => null
        ];
        $lotteryCronCollection->updateOne(['_id' => $id], ['$set' => $dataUpdate]);
        return $this->returnBackRefURL('error', 'Success');
    }
}
