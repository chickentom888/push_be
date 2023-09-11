<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Arrays;
use Dcore\Library\Helper;
use Exception;
use MongoDB\BSON\ObjectId;

class UserController extends ApiControllerBase
{

    public function initialize($param = null)
    {
        parent::initialize();
    }

    public function indexAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $userConnect = $this->getUserInfo();
            $listField = [
                '_id',
                'address',
                'code',
                'level',
                'personal_invest',
                'left_invest',
                'right_invest',
                'coin_balance',
                'interest_balance',
                'total_interest',
                'total_bonus',
                'system_invest',
                'direct_system_invest',
                "direct_bonus",
                "team_bonus",
                "matching_bonus"
            ];
            $userConnect = Arrays::selectKeys($userConnect, $listField);
            $userConnect['total_interest'] *= 2;
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $userConnect, 'User connect info');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function referralAction($id = null)
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $userConnectCollection = $this->mongo->selectCollection('user_connect');

            $id = Helper::isObjectIdMongo($id) ? $id : $this->credential->_id;

            /*if ($id != $this->credential->_id) {
                $cacheKey = "system_id:direct_system_id_" . $this->credential->_id;
                $listDirectSystemId = $this->redis->get($cacheKey);
                if (strlen($listDirectSystemId)) {
                    $listDirectSystemId = json_decode($listDirectSystemId, true);
                    if (!in_array($id, $listDirectSystemId)) {
                        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, [], 'Error');
                    }
                } else {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, [], 'Error');
                }
            }*/

            $id = new ObjectId($id);
            $conditions = ['inviter_id' => $id];
            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['created_at' => -1]
            ];

            $listData = $userConnectCollection->find($conditions, $options)->toArray();
            $count = $userConnectCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);
            $listField = [
                '_id',
                'address',
                'code',
                'level',
                'personal_invest',
                'left_invest',
                'right_invest',
                'coin_balance',
                'interest_balance',
                'direct_bonus'
            ];
            foreach ($listData as $key => $item) {
                $listData[$key] = Arrays::selectKeys($item, $listField);
            }
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function treeAction($id = null)
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $userConnectCollection = $this->mongo->selectCollection('user_connect');
            $id = Helper::isObjectIdMongo($id) ? $id : $this->credential->_id;

            /*if ($id != $this->credential->_id) {
                $cacheKey = "system_id:tree_system_id_" . $this->credential->_id;
                $listTreeSystemId = $this->redis->get($cacheKey);
                if (strlen($listTreeSystemId)) {
                    $listTreeSystemId = json_decode($listTreeSystemId, true);
                    if (!in_array($id, $listTreeSystemId)) {
                        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, [], 'Error');
                    }
                } else {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, [], 'Error');
                }
            }*/

            $id = new ObjectId($id);
            $conditions = ['_id' => $id];
            $userConnect = $userConnectCollection->findOne($conditions);
            if (!$userConnect) {
                throw new Exception('Not found user connect Info');
            }
            $conditions = ['parent_id' => $userConnect['_id']];
            $options = [
                'sort' => ['created_at' => 1]
            ];

            $listField = [
                '_id',
                'address',
                'code',
                'level',
                'personal_invest',
                'left_invest',
                'right_invest',
                'coin_balance',
                'interest_balance',
                'branch',
                'parent_id'
            ];
            $dataResponse = Arrays::selectKeys($userConnect, $listField);

            $listData = $userConnectCollection->find($conditions, $options)->toArray();
            foreach ($listData as $key => $item) {
                $item = Arrays::selectKeys($item, $listField);
                unset($conditions, $options);
                $conditions = ['parent_id' => $item['_id']];
                $options = [
                    'sort' => ['created_at' => 1]
                ];
                $item['child'] = $userConnectCollection->find($conditions, $options)->toArray();
                $listData[$key] = $item;
            }
            $dataResponse['child'] = $listData;
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}