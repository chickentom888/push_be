<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Collections\Transaction;
use Dcore\Collections\Users;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Object\Account;
use DCrypto\Object\Send;
use Exception;
use MongoDB\BSON\ObjectId;

class WithdrawController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize(['check-role' => [BaseCollection::ROLE_ADMIN]]);
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

        $conditions = [
            '_id' => ['$ne' => -1]
        ];

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['status']);
        }

        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            } else {
                $conditions['user_address'] = ['$regex' => $dataGet['user_address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_connect_id'])) {
                $conditions['user_connect_id'] = new ObjectId($dataGet['user_connect_id']);
            } else {
                $conditions['user_connect_id'] = -1;
            }
        }

        if (strlen($dataGet['blockchain_status'])) {
            $conditions['blockchain_status'] = intval($dataGet['blockchain_status']);
        }

        $sort = '_id';
        if (strlen($dataGet['sort'])) {
            $sort = $dataGet['sort'];
        }
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => [$sort => -1]
        ];
        $withdrawCollection = $this->mongo->selectCollection('withdraw');

        $listData = $withdrawCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $withdrawCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "amount" => ['$sum' => '$amount'],
                    "amount_after_fee" => ['$sum' => '$amount_after_fee'],
                    "fee_amount" => ['$sum' => '$fee_amount'],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "amount" => 1,
                    "amount_after_fee" => 1,
                    "fee_amount" => 1,
                ],
            ],
        ];
        $summaryData = $withdrawCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];
        unset($summaryData['_id']);

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

    /**
     * @throws Exception
     */
    public function approveAction($id)
    {
        $coinInstance = Adapter::getInstance('push_bsc', $this->defaultNetwork);
        if (!Helper::isObjectIdMongo($id)) {
            return $this->returnBackRefURL('error', 'Data not found');
        }
        global $config;
        $withdrawCollection = $this->mongo->selectCollection('withdraw');
        $id = new ObjectId($id);
        $withdraw = $withdrawCollection->findOne(['_id' => $id]);
        if (!$withdraw) {
            return $this->returnBackRefURL('error', 'Data not found');
        }
        if ($withdraw['status'] != BaseCollection::STATUS_PENDING) {
            return $this->returnBackRefURL('error', 'Data processed');
        }

        $userConnect = Users::getUserById($withdraw['user_connect_id']);
        if ($userConnect['lock_withdraw'] == BaseCollection::STATUS_ACTIVE) {
            return $this->returnBackRefURL('error', 'User has been locked withdraw');
        }

        $privateKey = $config->blockchain['withdraw_private_key'];
        $fromAddress = $config->blockchain['withdraw_address'];

        $fromAccount = new Account();
        $fromAccount->address = $fromAddress;
        $fromAccount->private_key = $privateKey;
        $toAccount = new Account();
        $toAccount->address = $withdraw['user_address'];
        $sendObject = new Send();
        $sendObject->with_nonce = true;
        $amount = $withdraw['amount_after_fee'];
        if ($amount <= 0) {
            return $this->returnBackRefURL('error', 'Invalid amount');
        }
        $sendObject->amount = $amount;

        $sendObject = $coinInstance->send($fromAccount, $toAccount, $sendObject);
        $hash = $sendObject->hash;
        if (strlen($hash)) {
            $dataUpdate = [
                'status' => BaseCollection::STATUS_APPROVE,
                'process_at' => time(),
                'hash' => $hash,
                'message' => 'Success'
            ];
            $withdrawCollection->updateOne(['_id' => $id], ['$set' => $dataUpdate]);
            Transaction::createBlockchainTransaction($fromAddress, $withdraw['user_address'], BaseCollection::ACTION_WITHDRAW, $sendObject);
            return $this->returnBackRefURL('success', 'Success');
        }
        return $this->returnBackRefURL('error', 'Something went wrong');
    }

    public function rejectAction($id)
    {
        if (!Helper::isObjectIdMongo($id)) {
            return $this->returnBackRefURL('error', 'Data not found');
        }
        $withdrawCollection = $this->mongo->selectCollection('withdraw');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $id = new ObjectId($id);
        $withdraw = $withdrawCollection->findOne(['_id' => $id]);
        if (!$withdraw) {
            return $this->returnBackRefURL('error', 'Data not found');
        }
        if ($withdraw['status'] != BaseCollection::STATUS_PENDING) {
            return $this->returnBackRefURL('error', 'Data processed');
        }
        $userConnect = $userConnectCollection->findOne(['_id' => $withdraw['user_connect_id']]);
        if ($userConnect) {
            $message = 'Withdraw Rejected';
            $amount = $withdraw['amount'];
            Users::updateBalance($userConnect['_id'], BaseCollection::WALLET_COIN, $amount, BaseCollection::TYPE_WITHDRAW_REJECT, $message);
        }

        $dataUpdate = [
            'status' => BaseCollection::STATUS_REJECT,
            'process_at' => time()
        ];
        $withdrawCollection->updateOne(['_id' => $id], ['$set' => $dataUpdate]);
        return $this->returnBackRefURL('success', 'Success');
    }

}