<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class StakingController extends ApiControllerBase
{
    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws ConnectionErrorException
     */
    public function userPackageAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $userConnectId = new ObjectId($this->credential->_id);
            $userConnectCollection = $this->mongo->selectCollection('user_connect');
            $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
            $listField = [
                '_id',
                'address',
                'code',
                'personal_invest',
                'left_invest',
                'right_invest',
                'coin_balance',
                'interest_balance',
                'total_interest',
                'total_bonus',
                'system_invest',
                'direct_system_invest',
            ];
            $userConnect = Arrays::selectKeys($userConnect, $listField);
            $userConnect['total_interest'] *= 2;

            $dataGet = $this->getData;
            $limit = 20;
            $platform = empty($dataGet['platform']) ? BinanceWeb3::PLATFORM : $dataGet['platform'];
            $network = empty($dataGet['network']) ? ContractLibrary::MAIN_NETWORK : $dataGet['network'];
            if ($_ENV['env'] == 'sandbox') {
                $network == null && $network = ContractLibrary::TEST_NETWORK;
            } else {
                $network == null && $network = ContractLibrary::MAIN_NETWORK;
            }

            $registryCollection = $this->mongo->selectCollection('registry');
            $registryCollection->findOne();
            $tokenKey = "staking_setting_{$platform}_{$network}";
            $registry = $this->mongo->selectCollection('registry')->findOne();
            $stakingSetting = $registry[$tokenKey];

            $conditions = [
                'platform' => $platform,
                'network' => $network,
                'user_connect_id' => $userConnectId,
            ];
            if (strlen($dataGet['status'])) {
                $conditions['status'] = intval($dataGet['status']);
            }
            $p = $dataGet['p'];
            if ($p <= 1) $p = 1;
            $cp = ($p - 1) * $limit;
            $options = [
                'skip' => $cp,
                'limit' => $limit,
                'sort' => ['_id' => -1]
            ];
            $userPackageCollection = $this->mongo->selectCollection('user_package');
            $listData = $userPackageCollection->find($conditions, $options);
            !$listData && $listData = $listData->toArray();
            $count = $userPackageCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            // <editor-fold desc="Summary">
            $conditionsSummary = [
                [
                    '$match' => $conditions
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "token_amount" => [
                            '$sum' => '$token_amount'
                        ],
                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "token_amount" => 1,
                    ],
                ],
            ];
            $summaryData = $userPackageCollection->aggregate($conditionsSummary);
            !empty($summaryData) && $summaryData = $summaryData->toArray();
            $summaryData = $summaryData[0];
            unset($summaryData['_id']);
            // </editor-fold>

            $optional = [
                'paging_info' => $pagingInfo,
                'summary' => $summaryData
            ];

            $listField = [
                '_id',
                'user_address',
                'network',
                'platform',
                'status',
                'expired_at',
                'user_connect_id',
                'token_amount',
                'interest_max_day',
                'interest_amount_paid',
                'created_at',
            ];
            $listDataResponse = [];
            foreach ($listData as $key => $item) {
                $item = Arrays::selectKeys($item, $listField);
                $item['interest_amount_paid'] *= 2;
                $listDataResponse[$key] = $item;
            }

            $dataResponse = [
                'staking_setting' => $stakingSetting,
                'list_data' => $listDataResponse,
                'user_connect' => $userConnect
            ];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success', $optional);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * @throws ConnectionErrorException
     */
    public function userPackageHistoryAction()
    {
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userPackageHistoryCollection = $this->mongo->selectCollection('user_package_history');

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

        $userConnectId = new ObjectId($this->credential->_id);
        $userPackage = null;
        $optional = [];
        $conditions = [
            '_id' => ['$ne' => -1],
            'user_connect_id' => $userConnectId
        ];

        if (strlen($dataGet['user_package_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_package_id'])) {
                $conditions['user_package_id'] = new ObjectId($dataGet['user_package_id']);
                $userPackage = $userPackageCollection->findOne([
                    '_id' => $conditions['user_package_id']
                ]);
                $listFieldUserPackage = [
                    '_id',
                    'user_address',
                    'network',
                    'platform',
                    'status',
                    'expired_at',
                    'user_connect_id',
                    'token_amount',
                    'interest_max_day',
                    'interest_amount_paid',
                ];
                $userPackage = Arrays::selectKeys($userPackage, $listFieldUserPackage);
            } else {
                $conditions['user_package_id'] = -1;
            }
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }

        if (strlen($dataGet['contract_id'])) {
            $conditions['contract_id'] = intval($dataGet['contract_id']);
        }

        if (strlen($dataGet['code'])) {
            $conditions['code'] = $dataGet['code'];
        }

        $listData = $userPackageHistoryCollection->find($conditions, $options);
        !$listData && $listData = $listData->toArray();
        $count = $userPackageHistoryCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $listField = [
            '_id',
            'user_address',
            'user_connect_id',
            'hash',
            'network',
            'platform',
            'status',
            'expired_at',
            'user_package_id',
            'token_amount',
            'created_at'
        ];
        $listDataResponse = [];
        foreach ($listData as $key => $item) {
            $item = Arrays::selectKeys($item, $listField);
            $listDataResponse[$key] = $item;
        }

        // <editor-fold desc="Summary">
        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "token_amount" => [
                        '$sum' => '$token_amount'
                    ],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "token_amount" => 1,
                ],
            ],
        ];
        $summaryData = $userPackageHistoryCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];
        unset($summaryData['_id']);
        // </editor-fold>

        $dataResponse = [
            'user_package' => $userPackage,
            'list_data' => $listDataResponse
        ];
        $optional['paging_info'] = $pagingInfo;
        $optional['summary'] = $summaryData;

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success', $optional);
    }

    /**
     * @throws ConnectionErrorException
     */
    public function interestAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $userPackageCollection = $this->mongo->selectCollection('user_package');
            $userPackageInterestCollection = $this->mongo->selectCollection('user_package_interest');
            $limit = 20;
            $dataGet = $this->getData;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['created_at' => -1]
            ];

            $userConnectId = new ObjectId($this->credential->_id);
            $conditions = [
                'user_connect_id' => $userConnectId,
            ];

            $userPackage = null;
            if (strlen($dataGet['user_package_id'])) {
                if (Helper::isObjectIdMongo($dataGet['user_package_id'])) {
                    $conditions['user_package_id'] = new ObjectId($dataGet['user_package_id']);
                    $userPackage = $userPackageCollection->findOne([
                        '_id' => $conditions['user_package_id']
                    ]);
                    $listFieldUserPackage = [
                        '_id',
                        'user_address',
                        'network',
                        'platform',
                        'status',
                        'expired_at',
                        'user_connect_id',
                        'token_amount',
                        'interest_max_day',
                        'interest_amount_paid',
                    ];
                    $userPackage = Arrays::selectKeys($userPackage, $listFieldUserPackage);
                } else {
                    $conditions['user_package_id'] = -1;
                }
            }

            if (strlen($dataGet['type'])) {
                $conditions['type'] = $dataGet['type'];
            } else {
                $conditions['type'] = BaseCollection::TYPE_STAKING_INTEREST;
            }

            if (is_numeric($dataGet['from_date']) && $dataGet['from_date'] > 0 && is_numeric($dataGet['to_date']) && $dataGet['to_date'] > 0) {
                $conditions['$and'] = [['created_at' => ['$gte' => intval($dataGet['from_time'])]], ['created_at' => ['$lte' => intval($dataGet['to_date'])]]];
            } else {
                if (is_numeric($dataGet['from_date']) && $dataGet['from_date'] > 0) {
                    $conditions['created_at'] = ['$gte' => intval($dataGet['from_date'])];
                }
                if (is_numeric($dataGet['to_date']) && $dataGet['to_date'] > 0) {
                    $conditions['created_at'] = ['$lte' => intval($dataGet['to_date'])];
                }
            }
            $listData = $userPackageInterestCollection->find($conditions, $options);
            !$listData && $listData = $listData->toArray();
            $count = $userPackageInterestCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            $listField = [
                '_id',
                'user_address',
                'user_connect_id',
                'type',
                'real_amount',
                'base_amount',
                'bonus_amount',
                'created_at',
                'user_package_id',
            ];
            $listDataResponse = [];
            foreach ($listData as $key => $item) {
                $item = Arrays::selectKeys($item, $listField);
                $item['real_amount'] = $item['type'] == BaseCollection::TYPE_STAKING_INTEREST ? $item['base_amount'] : $item['real_amount'];
                $item['type'] = $item['type'] == BaseCollection::TYPE_STAKING_FUND_INTEREST ? 'Fund Reward' : 'Staking Reward';
                $listDataResponse[$key] = $item;
            }

            // <editor-fold desc="Summary">
            $conditionsSummary = [
                [
                    '$match' => $conditions
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "real_amount" => [
                            '$sum' => '$bonus_amount'
                        ],
                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "bonus_amount" => 1,
                    ],
                ],
            ];
            $summaryData = $userPackageInterestCollection->aggregate($conditionsSummary);
            !empty($summaryData) && $summaryData = $summaryData->toArray();
            $summaryData = $summaryData[0];
            unset($summaryData['_id']);
            // </editor-fold>

            $dataResponse = [
                'user_package' => $userPackage,
                'list_data' => $listDataResponse,
            ];
            $optional['paging_info'] = $pagingInfo;
            $optional['summary'] = $summaryData;
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success', $optional);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function bonusLogAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $userPackageCollection = $this->mongo->selectCollection('user_package');
            $bonusLogCollection = $this->mongo->selectCollection('bonus_log');

            $limit = 20;
            $dataGet = $this->getData;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['created_at' => -1]
            ];

            $userAddress = $this->credential->address;
            $conditions = [
                'to_user_address' => $userAddress,
            ];

            if (strlen($dataGet['type'])) {
                $conditions['type'] = $dataGet['type'];
            }

            if (strlen($dataGet['contract_id'])) {
                $conditions['contract_id'] = intval($dataGet['contract_id']);
            }

            if (strlen($dataGet['user_package_history_id'])) {
                if (Helper::isObjectIdMongo($dataGet['user_package_history_id'])) {
                    $conditions['user_package_history_id'] = new ObjectId($dataGet['user_package_history_id']);
                } else {
                    $conditions['user_package_history_id'] = -1;
                }
            }

            $userPackage = null;
            if (strlen($dataGet['user_package_id'])) {
                if (Helper::isObjectIdMongo($dataGet['user_package_id'])) {
                    $conditions['user_package_id'] = new ObjectId($dataGet['user_package_id']);
                    $userPackage = $userPackageCollection->findOne([
                        '_id' => $conditions['user_package_id']
                    ]);
                } else {
                    $conditions['user_package_id'] = -1;
                }
            }

            if (is_numeric($dataGet['from_date']) && $dataGet['from_date'] > 0 && is_numeric($dataGet['to_date']) && $dataGet['to_date'] > 0) {
                $conditions['$and'] = [['created_at' => ['$gte' => intval($dataGet['from_time'])]], ['created_at' => ['$lte' => intval($dataGet['to_date'])]]];
            } else {
                if (is_numeric($dataGet['from_date']) && $dataGet['from_date'] > 0) {
                    $conditions['created_at'] = ['$gte' => intval($dataGet['from_date'])];
                }
                if (is_numeric($dataGet['to_date']) && $dataGet['to_date'] > 0) {
                    $conditions['created_at'] = ['$lte' => intval($dataGet['to_date'])];
                }
            }
            $listData = $bonusLogCollection->find($conditions, $options)->toArray();
            $count = $bonusLogCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);
            $listDataResponse = [];
            $listField = ['to_user_address', 'type', 'message', 'bonus_amount', 'created_at'];
            foreach ($listData as $item) {
                $item = Arrays::selectKeys($item, $listField);
                $item['type'] = BaseCollection::listBalanceLog($item['type']);
                $listDataResponse[] = $item;
            }

            // <editor-fold desc="Summary">
            $conditionsSummary = [
                [
                    '$match' => $conditions
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "bonus_amount" => [
                            '$sum' => '$bonus_amount'
                        ]

                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "bonus_amount" => 1,
                    ],
                ],
            ];
            $summaryData = $bonusLogCollection->aggregate($conditionsSummary);
            !empty($summaryData) && $summaryData = $summaryData->toArray();
            $summaryData = $summaryData[0];
            unset($summaryData['_id']);
            // </editor-fold>

            $dataResponse = [
                'user_package' => $userPackage,
                'list_data' => $listDataResponse,
            ];
            $optional['paging_info'] = $pagingInfo;
            $optional['summary'] = $summaryData;
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success', $optional);

        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}