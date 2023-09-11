<?php

namespace Dcore\Collections;

use Dcore\Library\Helper;
use MongoDB\BSON\ObjectId;
use MongoDB\Database;
use Phalcon\Di;
use Redis;
use RedisException;

class Users extends BaseCollection
{

    public function initialize()
    {
    }

    /**
     * @throws RedisException
     */
    public static function calcTree($debug = false)
    {
        $cacheKey = 'calc_tree';
        /** @var Redis $redis */
        $redis = Di::getDefault()->getShared('redis');
        $redis->set($cacheKey, 1);
        $msg = "";
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $usersCollection = $mongo->selectCollection('user_connect');
        $listUser = $usersCollection->find([
            'diagram_date' => [
                '$exists' => true,
                '$gt' => 0
            ],
        ], [
            'sort' => [
                'diagram_date' => 1,
                '_id' => 1
            ]
        ]);
        $lastLeft = [];
        $lastRight = [];
        $listBranch = [];
        $listInviter = [];
        $listParent = [];
        !empty($listUser) && $listUser = $listUser->toArray();

        foreach ($listUser as $user) {
            $userId = strval($user['_id']);
            $topId = strval($user['top_id']);
            $lastLeft[$userId] = $userId;
            $lastRight[$userId] = $userId;
            $listBranch[$userId] = $user['branch'];
            if (!empty($topId)) {
                $listInviter[$userId] = $topId;
            } else {
                $listInviter[$userId] = strval($user['inviter_id']);
            }
            $listParent[$userId] = strval($user['parent_id']);
        }

        $count = 0;
        foreach ($listUser as $user) {
            $userId = strval($user['_id']);
            $count++;
            if (($count % 10000) == 0) {
                if ($debug) {
                    echo $count . " ";
                }
            }
            $topId = $listInviter[$userId]; //1
            if (!empty($topId)) {
                $stop = 0;
                while (!$stop) {
                    if ($listBranch[$topId] != $user['branch']) {
                        $stop = 1;
                    }
                    if (empty($listInviter[$topId])) {
                        $stop = 1;
                    }
                    if (!$stop) {
                        $topId = $listInviter[$topId];
                    }
                }
                if ($user['branch'] != BaseCollection::BRANCH_RIGHT) {
                    $user['parent_id'] = $lastLeft[$topId];
                    $lastLeft[$topId] = $userId;
                } else {
                    $user['parent_id'] = $lastRight[$topId];
                    $lastRight[$topId] = $userId;
                }
                $parentId = new ObjectId($user['parent_id']);
                $userId = new ObjectId($userId);
                $usersCollection->updateOne(['_id' => $userId], ['$set' => ['parent_id' => $parentId]]);
            } else {
                if ($user['_id'] != 1) {
                    $msg .= "Invalid User ID#{$user['_id']} | NO INVITER" . PHP_EOL;
                }
            }
        }
        $redis->del($cacheKey);
        return $msg;
    }

    /**
     * @throws RedisException
     */
    public static function calcTreeMatrix($debug = false)
    {
        $cacheKey = 'calc_tree';
        /** @var Redis $redis */
        $redis = Di::getDefault()->getShared('redis');
        $redis->set($cacheKey, 1);
        $msg = "";
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $usersMatrixCollection = $mongo->selectCollection('user_matrix');
        $listUser = $usersMatrixCollection->find([
            'diagram_date' => [
                '$exists' => true,
                '$gt' => 0
            ],
        ], [
            'sort' => [
                'diagram_date' => 1,
                '_id' => 1
            ]
        ]);
        $lastLeft = [];
        $lastMid = [];
        $lastRight = [];
        $listBranch = [];
        $listInviter = [];
        $listParent = [];
        $listUserById = [];
        !empty($listUser) && $listUser = $listUser->toArray();

        foreach ($listUser as $user) {
            $userId = strval($user['_id']);
            $topId = strval($user['top_id']);
            $lastLeft[$userId] = $userId;
            $lastMid[$userId] = $userId;
            $lastRight[$userId] = $userId;
            $listBranch[$userId] = $user['branch'];
            if (!empty($topId)) {
                $listInviter[$userId] = $topId;
            } else {
                $listInviter[$userId] = strval($user['inviter_id']);
            }
            $listParent[$userId] = strval($user['parent_id']);
            $listUserById[$userId] = $user;
        }

        $count = 0;
        foreach ($listUser as $user) {
            $userId = strval($user['_id']);
            $count++;
            if (($count % 10000) == 0) {
                if ($debug) {
                    echo $count . " ";
                }
            }
            $topId = $listInviter[$userId]; //1
            if (!empty($topId)) {
                $stop = 0;
                while (!$stop) {
                    if ($listBranch[$topId] != $user['branch']) {
                        $stop = 1;
                    }
                    if (empty($listInviter[$topId])) {
                        $stop = 1;
                    }
                    if (!$stop) {
                        $topId = $listInviter[$topId];
                    }
                }
                if ($user['branch'] == 'left') {
                    $user['parent_id'] = $lastLeft[$topId];
                    $lastLeft[$topId] = $userId;
                } else if ($user['branch'] == 'mid') {
                    $user['parent_id'] = $lastMid[$topId];
                    $lastMid[$topId] = $userId;
                } else {
                    $user['parent_id'] = $lastRight[$topId];
                    $lastRight[$topId] = $userId;
                }
                $updateParent = ($listParent[$userId] != $user['parent_id']);

                $parentId = new ObjectId($user['parent_id']);
                $userId = new ObjectId($userId);
                $modifiedCount = $usersMatrixCollection->updateOne(['_id' => $userId], ['$set' => ['parent_id' => $parentId]])->getModifiedCount();
                if ($modifiedCount > 0 && $updateParent && strlen($user['parent_id'])) {
                    if (strval($user['inviter_id']) != $user['parent_id']) {
                        $dataParent = [];
                        $parent = $listUserById[$user['parent_id']];
                        if ($parent['branch_for_child'] == 'left') {
                            $dataParent['branch_for_child'] = 'mid';
                        } else if ($parent['branch_for_child'] == 'mid') {
                            $dataParent['branch_for_child'] = 'right';
                        } else {
                            $dataParent['branch_for_child'] = 'left';
                        }
                        $usersMatrixCollection->updateOne(['_id' => new ObjectId($parent['_id'])], ['$set' => $dataParent])->getModifiedCount();
                    }
                }

            } else {
                if ($user['_id'] != 1) {
                    $msg .= "Invalid User ID#{$user['_id']} | NO INVITER" . PHP_EOL;
                }
            }
        }
        $redis->del($cacheKey);
        return $msg;
    }

    public static function getMaxOutBonus($userConnectId)
    {
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return 0;
        }
        $userConnectId = new ObjectId($userConnectId);
        $totalContribute = self::getTotalInvest($userConnectId);
        return $totalContribute * 300 / 100;
    }

    public static function getTotalInvest($userConnectId)
    {
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return 0;
        }
        $userConnectId = new ObjectId($userConnectId);
        $totalTokenAmount = 0;
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userPackageCollection = $mongo->selectCollection('user_package');
        $match = [
            'user_connect_id' => $userConnectId
        ];
        $conditions = [
            [
                '$match' => $match
            ],
            [
                '$group' => [
                    '_id' => null,
                    "token_amount" => [
                        '$sum' => '$token_amount'
                    ]
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "token_amount" => 1
                ],
            ],
        ];
        $summaryData = $userPackageCollection->aggregate($conditions);
        if (!empty($summaryData)) {
            $summaryData = $summaryData->toArray();
            $totalTokenAmount = $summaryData[0]['token_amount'];
        }
        return $totalTokenAmount;

    }

    public static function getAvailableAmountBonus($baseAmount, $userConnectId)
    {
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return 0;
        }
        $userConnectId = new ObjectId($userConnectId);
        $bonusAmount = 0;
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userConnectCollection = $mongo->selectCollection('user_connect');
        $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
        if ($userConnect) {
            $maxOutBonus = $userConnect['max_out_bonus'];
            $totalMaxOut = self::getMaxoutBonus($userConnectId);
            $remainBonus = $totalMaxOut - $maxOutBonus;
            $bonusAmount = min($remainBonus, $baseAmount);
        }
        return $bonusAmount;

    }

    public static function updateBalance($userConnectId, $wallet, $amount, $type, $message, $withUpdateTotalBonus = false)
    {
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $maxOutLogCollection = $mongo->selectCollection('max_out_log');
        $userConnectCollection = $mongo->selectCollection('user_connect');
        $balanceLogCollection = $mongo->selectCollection('balance_log');
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return [];
        }
        $userConnectId = new ObjectId($userConnectId);
        $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
        global $config;
        if ($wallet == $config->site->coin_key) {
            $wallet = BaseCollection::WALLET_COIN;
        }
        $ticker = $config->site->coin_ticker;
        $balanceField = $wallet . '_balance';
        $oldValue = $userConnect[$balanceField] ?: 0;
        $userConnectData[$balanceField] = $oldValue + $amount;

        if ($withUpdateTotalBonus) {
            $beforeMaxOutBonus = $userConnect['max_out_bonus'] ?: 0;
            $userConnectData['max_out_bonus'] = $beforeMaxOutBonus + $amount;
            $maxOutLogData = [
                'user_connect_id' => $userConnect['_id'],
                'user_address' => $userConnect['address'],
                'amount' => $amount,
                'type' => $type,
                'wallet' => $wallet,
                'before_amount' => $beforeMaxOutBonus,
                'last_amount' => $userConnectData['max_out_bonus'],
                'message' => $message,
                'created_at' => time()
            ];
            $maxOutLogId = $maxOutLogCollection->insertOne($maxOutLogData)->getInsertedId();
        }

        if ($wallet == BaseCollection::WALLET_COIN) {
            if (in_array($type, BaseCollection::LIST_TYPE_BONUS)) {
                $userConnectData['total_bonus'] = $userConnect['total_bonus'] + $amount;
                $userConnectData['total_commission'] = $userConnect['total_commission'] + $amount;
            }
            if (in_array($type, BaseCollection::LIST_TYPE_INTEREST)) {
                $userConnectData['total_interest'] = $userConnect['total_interest'] + $amount;
                $userConnectData['total_commission'] = $userConnect['total_commission'] + $amount;
            }

            if ($type == BaseCollection::TYPE_DIRECT_BONUS) {
                $userConnectData['direct_bonus'] = $userConnect['direct_bonus'] + $amount;
            } else if ($type == BaseCollection::TYPE_TEAM_BONUS) {
                $userConnectData['team_bonus'] = $userConnect['team_bonus'] + $amount;
            } else if ($type == BaseCollection::TYPE_MATCHING_BONUS) {
                $userConnectData['matching_bonus'] = $userConnect['matching_bonus'] + $amount;
            } else if ($type == BaseCollection::TYPE_STAKING_PRINCIPAL) {
                $userConnectData['total_principal'] = $userConnect['total_principal'] + $amount;
            }
        }

        $modifiedCount = $userConnectCollection->updateOne(['_id' => $userConnect['_id']], ['$set' => $userConnectData])->getModifiedCount();
        if ($modifiedCount > 0) {
            $balanceLogData = [
                'user_connect_id' => $userConnect['_id'],
                'user_address' => $userConnect['address'],
                'before_amount' => $oldValue,
                'last_amount' => $userConnectData[$balanceField],
                'amount' => $amount,
                'type' => $type,
                'message' => $message,
                'wallet' => $wallet,
                'ticker' => $ticker,
                'created_at' => time()
            ];
            $balanceLogId = $balanceLogCollection->insertOne($balanceLogData)->getInsertedId();
            if (!empty($maxOutLogId)) {
                $maxOutLogDataUpdate = [
                    'balance_log_id' => $balanceLogId
                ];
                $maxOutLogCollection->updateOne(['_id' => $maxOutLogId], ['$set' => $maxOutLogDataUpdate])->getModifiedCount();
            }
        }
    }

    public static function getInviter($userConnectId)
    {
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return [];
        }
        $userConnectId = new ObjectId($userConnectId);
        $inviter = [];
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userConnectCollection = $mongo->selectCollection('user_connect');
        $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
        if ($userConnect && $userConnect['inviter_id']) {
            $inviter = $userConnectCollection->findOne(['_id' => $userConnect['inviter_id']]);
        }
        return $inviter;
    }

    public static function getParent($userConnectId)
    {
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return [];
        }
        $userConnectId = new ObjectId($userConnectId);
        $parent = [];
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userConnectCollection = $mongo->selectCollection('user_connect');
        $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);

        if ($userConnect && $userConnect['parent_id']) {
            $parent = $userConnectCollection->findOne(['_id' => $userConnect['parent_id']]);
        }
        return $parent;
    }

    public static function getUserById($userConnectId)
    {
        if (!$userConnectId) {
            return [];
        }
        if (!Helper::isObjectIdMongo($userConnectId)) {
            return [];
        }
        $userConnectId = new ObjectId($userConnectId);
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userConnectCollection = $mongo->selectCollection('user_connect');
        return $userConnectCollection->findOne(['_id' => $userConnectId]);
    }
}