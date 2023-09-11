<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class StakingController extends ExtendedControllerBase
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
    public function userPackageAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => -1]
        ];

        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

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

        if (strlen($dataGet['code'])) {
            $conditions['code'] = $dataGet['code'];
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        if ($dataGet['export']) {
            $listData = $userPackageCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $fieldKeys = [
                'hash' => 'Hash',
                'created_at' => 'Time',
                'last_claim_interest_timestamp' => 'Last Claim Interest',
                'last_claim_principal_timestamp' => 'Last Claim Principal',
                'extend_expire_timestamp' => 'Extend Expire',
                'platform' => 'Platform',
                'network' => 'Network',
                'status' => 'Status',
            ];
            $this->exportDataByField($listData, 'Lottery', $fieldKeys);
        }

        $listData = $userPackageCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $userPackageCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

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

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function userPackageHistoryAction()
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

        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userPackageHistoryCollection = $this->mongo->selectCollection('user_package_history');
        $conditions = [
            '_id' => ['$ne' => -1]
        ];

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

        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            } else {
                $conditions['user_address'] = ['$regex' => $dataGet['user_address'] . '$', '$options' => 'i'];
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

        if (strlen($dataGet['is_staking_token'])) {
            $conditions['is_staking_token'] = intval($dataGet['is_staking_token']);
        }

        if (strlen($dataGet['is_direct_bonus'])) {
            $conditions['is_direct_bonus'] = intval($dataGet['is_direct_bonus']);
        }

        if (strlen($dataGet['is_team_bonus'])) {
            $conditions['is_team_bonus'] = intval($dataGet['is_team_bonus']);
        }

        if (strlen($dataGet['support_liquid_status'])) {
            $conditions['support_liquid_status'] = intval($dataGet['support_liquid_status']);
        }

        $listData = $userPackageHistoryCollection->find($conditions, $options);
        $count = $userPackageHistoryCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "token_amount" => [
                        '$sum' => '$token_amount',
                    ],
                    "usd_amount" => [
                        '$sum' => '$usd_amount',
                    ],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "token_amount" => 1,
                    "usd_amount" => 1,
                ],
            ],
        ];
        $summaryData = $userPackageHistoryCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];

        $this->view->setVars(compact('userPackage', 'listData', 'pagingInfo', 'dataGet', 'summaryData'));
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function interestAction()
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

        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userPackageInterestCollection = $this->mongo->selectCollection('user_package_interest');
        $conditions = [
            '_id' => ['$ne' => -1]
        ];
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

        if (strlen($dataGet['user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_connect_id'])) {
                $conditions['user_connect_id'] = new ObjectId($dataGet['user_connect_id']);
            } else {
                $conditions['user_package_id'] = -1;
            }
        }

        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            } else {
                $conditions['user_address'] = ['$regex' => $dataGet['user_address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        $listData = $userPackageInterestCollection->find($conditions, $options);
        $count = $userPackageInterestCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "real_amount" => [
                        '$sum' => '$real_amount'
                    ],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "real_amount" => 1,
                ],
            ],
        ];
        $summaryData = $userPackageInterestCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];

        $this->view->setVars(compact('userPackage', 'listData', 'pagingInfo', 'dataGet', 'summaryData'));
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function principalAction()
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

        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userPackagePrincipalCollection = $this->mongo->selectCollection('user_package_principal');
        $conditions = [
            '_id' => ['$ne' => -1]
        ];
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

        if (strlen($dataGet['user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_connect_id'])) {
                $conditions['user_connect_id'] = new ObjectId($dataGet['user_connect_id']);
            } else {
                $conditions['user_package_id'] = -1;
            }
        }

        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            } else {
                $conditions['user_address'] = ['$regex' => $dataGet['user_address'] . '$', '$options' => 'i'];
            }
        }

        $listData = $userPackagePrincipalCollection->find($conditions, $options);
        $count = $userPackagePrincipalCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "principal_amount" => [
                        '$sum' => '$principal_amount'
                    ],
                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "principal_amount" => 1,
                ],
            ],
        ];
        $summaryData = $userPackagePrincipalCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];

        $this->view->setVars(compact('userPackage', 'listData', 'pagingInfo', 'dataGet', 'summaryData'));
    }
}
