<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Collections\Users;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;

class UserConnectController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize(['check-role' => [BaseCollection::ROLE_ADMIN]]);
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
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $conditions = [
            '_id' => ['$ne' => null]
        ];

        if (strlen($dataGet['branch'])) {
            $conditions['branch'] = $dataGet['branch'];
        }

        if (strlen($dataGet['branch_for_child'])) {
            $conditions['branch_for_child'] = $dataGet['branch_for_child'];
        }

        if (strlen($dataGet['id'])) {
            if (Helper::isObjectIdMongo($dataGet['id'])) {
                $conditions['_id'] = new ObjectId($dataGet['id']);
            } else {
                $conditions['_id'] = -1;
            }
        }

        if (strlen($dataGet['inviter_id'])) {
            if (Helper::isObjectIdMongo($dataGet['inviter_id'])) {
                $conditions['inviter_id'] = new ObjectId($dataGet['inviter_id']);
            } else {
                $conditions['inviter_id'] = -1;
            }
        }

        if (strlen($dataGet['parent_id'])) {
            if (Helper::isObjectIdMongo($dataGet['parent_id'])) {
                $conditions['parent_id'] = new ObjectId($dataGet['parent_id']);
            } else {
                $conditions['parent_id'] = -1;
            }
        }

        if (strlen($dataGet['address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address'])) {
                $conditions['address'] = $coinInstance->toCheckSumAddress($dataGet['address']);
            } else {
                $conditions['address'] = ['$regex' => $dataGet['address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['inviter_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['inviter_address'])) {
                $conditionsInviter = [
                    'address' => $coinInstance->toCheckSumAddress($dataGet['inviter_address'])
                ];
            } else {
                $conditionsInviter['address'] = ['$regex' => $dataGet['inviter_address'] . '$', '$options' => 'i'];
            }

            $inviter = $userConnectCollection->findOne($conditionsInviter);
            $conditions['inviter_id'] = !empty($inviter) ? $inviter['_id'] : -1;
        }

        if (strlen($dataGet['parent_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['parent_address'])) {
                $conditionsParent = [
                    'address' => $coinInstance->toCheckSumAddress($dataGet['parent_address'])
                ];
            } else {
                $conditionsParent['address'] = ['$regex' => $dataGet['parent_address'] . '$', '$options' => 'i'];
            }

            $parent = $userConnectCollection->findOne($conditionsParent);
            $conditions['parent_id'] = !empty($parent) ? $parent['_id'] : -1;
        }

        if (strlen($dataGet['lock_withdraw'])) {
            $conditions['lock_withdraw'] = intval($dataGet['lock_withdraw']);
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

        $listData = $userConnectCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $userConnectCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "coin_balance" => ['$sum' => '$coin_balance'],
                    "interest_balance" => ['$sum' => '$interest_balance'],
                    "personal_invest" => ['$sum' => '$personal_invest'],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "coin_balance" => 1,
                    "interest_balance" => 1,
                    "personal_invest" => 1,
                ],
            ],
        ];
        $summaryData = $userConnectCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];
        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

    public function updateAction($id)
    {
        if (!$id) {
            return $this->returnBackRefURL('error', 'ID not found', '/user_connect');
        }
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $object = $userConnectCollection->findOne([
            '_id' => new ObjectId($id)
        ]);
        if (!$object) {
            return $this->returnBackRefURL('error', 'Data not found', '/user_connect');
        }
        if ($this->request->isPost()) {
            $dataPost = $this->postData;
            $dataPost['lock_withdraw'] = intval($dataPost['lock_withdraw']);
            $userConnectCollection->updateOne(['_id' => new ObjectId($id)], ['$set' => ['lock_withdraw' => $dataPost['lock_withdraw']]]);
            return $this->returnBackRefURL('success', 'Success');
        }
        $inviter = Users::getUserById($object['inviter_id']);
        $parent = Users::getUserById($object['parent_id']);
        $this->view->setVars(compact('object', 'inviter', 'parent'));
    }
}
