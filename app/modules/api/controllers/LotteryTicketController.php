<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

class LotteryTicketController extends ApiControllerBase
{
    protected string $channel = ContractLibrary::RPUB_LOTTERY_CHANGE;

    /** @var Collection $lotteryCollection */
    public $lotteryCollection;

    /** @var Collection lotteryTicketCollection */
    public $lotteryTicketCollection;

    public function initialize($param = null)
    {
        $this->lotteryCollection = $this->mongo->selectCollection('lottery');
        $this->lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function getPersonalTicketAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $dataGet = $this->getData;
            $userAddress = $this->credential->address;
            $platform = strtolower(trim($dataGet['platform']));
            $network = strtolower(trim($dataGet['network']));

            if (!$userAddress || !$platform || !$network) {
                throw new Exception('Invalid params');
            }

            $conditions['user_address'] = $userAddress;
            $conditions['platform'] = $platform;
            $conditions['network'] = $network;

            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['created_at' => -1]
            ];

            if (isset($dataGet['lottery_id']) && $dataGet['lottery_id']) {
                $conditions['lottery_id'] = new ObjectId($dataGet['lottery_id']);
            }

            if (is_numeric($dataGet['is_win'])) {
                if ($dataGet['is_win'] == 1) {
                    $conditions['is_win'] = true;
                } elseif ($dataGet['is_win'] == 0) {
                    $conditions['is_win'] = false;
                }
            }

            if (is_numeric($dataGet['is_claim'])) {
                $conditions['is_claim'] = (int)$dataGet['is_claim'];
            }

            if (is_numeric($dataGet['lottery_contract_id'])) {
                $conditions['lottery_contract_id'] = (int)$dataGet['lottery_contract_id'];
            }

            $listData = $this->lotteryTicketCollection->find($conditions, $options);
            !empty($listData) && $listData = $listData->toArray();
            $count = $this->lotteryTicketCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getTopBuyerLastRoundAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network']) {
                throw new Exception('Invalid params. Missing require param');
            }
            if ($dataGet['lottery_contract_id']) {
                $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
            }

            $conditions['platform'] = strtolower(trim($dataGet['platform']));
            $conditions['network'] = strtolower(trim($dataGet['network']));
            $conditions['status'] = ContractLibrary::LOTTERY_STATUS_CLAIMABLE;

            $options = [
                'sort' => ['lottery_contract_id' => -1]
            ];

            $latestLottery = $this->lotteryCollection->findOne($conditions, $options);

            if (!$latestLottery) {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, [], 'Success');
            }
            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $skip = ($p - 1) * $limit;

            //<editor-fold desc="Get from redis">
            $stringLotteryId = (string)$latestLottery['_id'];
            $keyRedis = "{$dataGet['platform']}-{$dataGet['network']}-lottery-ticket-get-top-buyer-last-round-{$stringLotteryId}-{$limit}-{$p}";
            $result = $this->redis->get($keyRedis);
            if ($result) {
                $result = (array)json_decode($result);
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result['lotteryTicket'], 'Success', $result['pagingInfo']);
            }
            //</editor-fold>

            $dateLottery = date("M d, Y H:i", $latestLottery['end_time']);
            unset($conditions);
            unset($options);
            $conditions = [
                'platform' => $dataGet['platform'],
                'network' => $dataGet['network'],
                'lottery_contract_id' => $latestLottery['lottery_contract_id'],
            ];

            $pipeline = [
                [
                    '$match' => $conditions
                ],
                [
                    '$group' => [
                        '_id' => '$user_address',
                        'number_ticket' => ['$sum' => 1],
                        'user_address' => ['$first' => '$user_address']
                    ],
                ],
                [
                    '$project' => [
                        'number_ticket' => 1,
                        'user_address' => 1
                    ],
                ],
                [
                    '$addFields' => [
                        'lottery_date' => $dateLottery,
                    ]
                ],
                [
                    '$facet' => [
                        'paginatedResults' => [['$sort' => ['number_ticket' => -1]], ['$skip' => $skip], ['$limit' => $limit]],
                        'totalCount' => [['$count' => 'count']]
                    ]
                ]
            ];

            $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
            $result = $lotteryTicketCollection->aggregate($pipeline)->toArray();
            $lotteryTicket = [];
            $pagingInfo = null;

            if ($result && $result[0]) {
                $lotteryTicket = $result[0]['paginatedResults'];
                $pagingInfo = Helper::paginginfo($result[0]['totalCount']['count'], $limit, $p);

                $this->redis->set($keyRedis, json_encode(['lotteryTicket' => $lotteryTicket, 'pagingInfo' => $pagingInfo]), 3 * 60 * 60);
            }
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $lotteryTicket, 'Success', $pagingInfo);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getWinTicketOfLatestLotteryFinishedAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network']) {
                throw new Exception('Invalid params. Missing require param');
            }
            if ($dataGet['lottery_contract_id']) {
                $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
            }

            $conditions['platform'] = strtolower($dataGet['platform']);
            $conditions['network'] = strtolower($dataGet['network']);
            $conditions['status'] = ContractLibrary::LOTTERY_STATUS_CLAIMABLE;

            $options = [
                'sort' => ['lottery_contract_id' => -1]
            ];

            $latestLottery = $this->lotteryCollection->findOne($conditions, $options);

            if (!$latestLottery) {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, [], 'Success');
            }
            unset($conditions);
            unset($options);

            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['bracket' => -1]
            ];

            $conditions = [
                'platform' => $dataGet['platform'],
                'network' => $dataGet['network'],
                'lottery_id' => $latestLottery['_id'],
                'is_win' => true
            ];

            $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
            $lotteryTicket = $lotteryTicketCollection->find($conditions, $options)->toArray();
            $count = $lotteryTicketCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $lotteryTicket, 'Success', $pagingInfo);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}
