<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class ReportController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function balanceLogAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => null]
        ];

        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        if (strlen($dataGet['user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_connect_id'])) {
                $conditions['user_connect_id'] = new ObjectId($dataGet['user_connect_id']);
            } else {
                $conditions['user_connect_id'] = -1;
            }

        }

        if (strlen($dataGet['wallet'])) {
            $conditions['wallet'] = $dataGet['wallet'];
        }

        if (strlen($dataGet['user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            } else {
                $conditions['user_address'] = ['$regex' => $dataGet['user_address'] . '$', '$options' => 'i'];
            }
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $balanceLogCollection = $this->mongo->selectCollection('balance_log');

        $listData = $balanceLogCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $balanceLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "amount" => [
                        '$sum' => '$amount'
                    ]

                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "amount" => 1,
                ],
            ],
        ];
        $summaryData = $balanceLogCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

    /**
     * @throws Exception
     */
    public function bonusLogAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => null]
        ];

        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        if (strlen($dataGet['contract_id'])) {
            $conditions['contract_id'] = intval($dataGet['contract_id']);
        }

        if (strlen($dataGet['from_user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['from_user_connect_id'])) {
                $conditions['from_user_connect_id'] = new ObjectId($dataGet['from_user_connect_id']);
            } else {
                $conditions['from_user_connect_id'] = -1;
            }
        }

        if (strlen($dataGet['to_user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['to_user_connect_id'])) {
                $conditions['to_user_connect_id'] = new ObjectId($dataGet['to_user_connect_id']);
            } else {
                $conditions['to_user_connect_id'] = -1;
            }
        }

        if (strlen($dataGet['from_user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['from_user_address'])) {
                $conditions['from_user_address'] = $coinInstance->toCheckSumAddress($dataGet['from_user_address']);
            } else {
                $conditions['from_user_address'] = ['$regex' => $dataGet['from_user_address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['to_user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['to_user_address'])) {
                $conditions['to_user_address'] = $coinInstance->toCheckSumAddress($dataGet['to_user_address']);
            } else {
                $conditions['to_user_address'] = ['$regex' => $dataGet['to_user_address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['user_package_history_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_package_history_id'])) {
                $conditions['user_package_history_id'] = new ObjectId($dataGet['user_package_history_id']);
            } else {
                $conditions['user_package_history_id'] = -1;
            }
        }

        if (strlen($dataGet['user_package_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_package_id'])) {
                $conditions['user_package_id'] = new ObjectId($dataGet['user_package_id']);
            } else {
                $conditions['user_package_id'] = -1;
            }
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $bonusLogCollection = $this->mongo->selectCollection('bonus_log');

        $listData = $bonusLogCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $bonusLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

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

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

    /**
     * @throws Exception
     */
    public function maxOutLogAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => null]
        ];

        if (strlen($dataGet['wallet'])) {
            $conditions['wallet'] = $dataGet['wallet'];
        }

        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        if (strlen($dataGet['user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_connect_id'])) {
                $conditions['user_connect_id'] = new ObjectId($dataGet['user_connect_id']);
            } else {
                $conditions['user_connect_id'] = -1;
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

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $maxOutLogCollection = $this->mongo->selectCollection('max_out_log');

        $listData = $maxOutLogCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $maxOutLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "amount" => [
                        '$sum' => '$amount'
                    ]

                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "amount" => 1,
                ],
            ],
        ];
        $summaryData = $maxOutLogCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

    /**
     * @throws Exception
     */
    public function errorLogAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => null]
        ];

        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        if (strlen($dataGet['contract_id'])) {
            $conditions['contract_id'] = intval($dataGet['contract_id']);
        }

        if (strlen($dataGet['from_user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['from_user_connect_id'])) {
                $conditions['from_user_connect_id'] = new ObjectId($dataGet['from_user_connect_id']);
            } else {
                $conditions['from_user_connect_id'] = -1;
            }
        }

        if (strlen($dataGet['to_user_connect_id'])) {
            if (Helper::isObjectIdMongo($dataGet['to_user_connect_id'])) {
                $conditions['to_user_connect_id'] = new ObjectId($dataGet['to_user_connect_id']);
            } else {
                $conditions['to_user_connect_id'] = -1;
            }
        }

        if (strlen($dataGet['from_user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['from_user_address'])) {
                $conditions['from_user_address'] = $coinInstance->toCheckSumAddress($dataGet['from_user_address']);
            } else {
                $conditions['from_user_address'] = ['$regex' => $dataGet['from_user_address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['to_user_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['to_user_address'])) {
                $conditions['to_user_address'] = $coinInstance->toCheckSumAddress($dataGet['to_user_address']);
            } else {
                $conditions['to_user_address'] = ['$regex' => $dataGet['to_user_address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['user_package_history_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_package_history_id'])) {
                $conditions['user_package_history_id'] = new ObjectId($dataGet['user_package_history_id']);
            } else {
                $conditions['user_package_history_id'] = -1;
            }
        }

        if (strlen($dataGet['user_package_id'])) {
            if (Helper::isObjectIdMongo($dataGet['user_package_id'])) {
                $conditions['user_package_id'] = new ObjectId($dataGet['user_package_id']);
            } else {
                $conditions['user_package_id'] = -1;
            }
        }


        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $errorLogCollection = $this->mongo->selectCollection('error_log');

        $listData = $errorLogCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $errorLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count'));
    }

    /**
     * @throws ConnectionErrorException
     */
    public function userConnectAction()
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
                $conditions['address'] = null;
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
                    "coin_balance" => [
                        '$sum' => '$coin_balance'
                    ],
                    "interest_balance" => [
                        '$sum' => '$interest_balance'
                    ],
                    "personal_invest" => [
                        '$sum' => '$personal_invest'
                    ]
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

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function airdropAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => null]
        ];

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['status']);
        }

        if (strlen($dataGet['address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address'])) {
                $conditions['address'] = $coinInstance->toCheckSumAddress($dataGet['address']);
            } else {
                $conditions['address'] = ['$regex' => $dataGet['address'] . '$', '$options' => 'i'];
            }
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $airdropCollection = $this->mongo->selectCollection('airdrop_address');

        $listData = $airdropCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $airdropCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        $conditionsSummary = [
            [
                '$match' => $conditions
            ],
            [
                '$group' => [
                    '_id' => null,
                    "amount" => [
                        '$sum' => '$amount'
                    ]

                ],
            ],
            [
                '$project' => [
                    "_id" => 1,
                    "amount" => 1,
                ],
            ],
        ];
        $summaryData = $airdropCollection->aggregate($conditionsSummary);
        !empty($summaryData) && $summaryData = $summaryData->toArray();
        $summaryData = $summaryData[0];

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count', 'summaryData'));
    }

}