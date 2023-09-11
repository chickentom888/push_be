<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use Phalcon\Http\ResponseInterface;

class LotteryController extends ApiControllerBase
{
    protected string $channel = ContractLibrary::RPUB_LOTTERY_CHANGE;
    /** @var Collection $lotteryCollection */
    public $lotteryCollection;

    /** @var Collection $lotteryUserLogCollection */
    public $lotteryUserLogCollection;

    public function initialize($param = null)
    {
        $this->lotteryCollection = $this->mongo->selectCollection('lottery');
        $this->lotteryUserLogCollection = $this->mongo->selectCollection('lottery_user_log');
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function getListAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['lottery_contract_id' => -1]
            ];
            $conditions = [];
            if (isset($dataGet['lottery_id']) && $dataGet['lottery_id']) {
                $conditions['_id'] = new ObjectId($dataGet['lottery_id']);
            }
            if (isset($dataGet['platform']) && $dataGet['platform']) {
                $conditions['platform'] = strtolower($dataGet['platform']);
            }
            if (isset($dataGet['network']) && $dataGet['network']) {
                $conditions['network'] = strtolower($dataGet['network']);
            }
            if (isset($dataGet['start_time']) && $dataGet['start_time']) {
                $conditions['start_time'] = ['$gte' => intval($dataGet['start_time'])];
            }
            if (isset($dataGet['end_time']) && $dataGet['end_time']) {
                $conditions['end_time'] = ['$lte' => intval($dataGet['end_time'])];
            }
            if (strlen($dataGet['status'])) {
                $conditions['status'] = ContractLibrary::listLotteryStatus()[$dataGet['status']] ?? null;
            }

            if (strlen($dataGet['lottery_contract_id'])) {
                $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
            }

            $listData = $this->lotteryCollection->find($conditions, $options)->toArray();
            $count = $this->lotteryCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * Chi tiết Lottery mới nhất
     * @return false|ResponseInterface
     */
    public function detailLatestAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network']) {
                throw new Exception('Invalid params. Missing require param');
            }
            $dataGet['platform'] = strtolower($dataGet['platform']);
            $dataGet['network'] = strtolower($dataGet['network']);
            $conditions = [
                'platform' => $dataGet['platform'],
                'network' => $dataGet['network']
            ];
            $options = [
                'sort' => ['lottery_contract_id' => -1]
            ];

            $latestLottery = $this->lotteryCollection->findOne($conditions, $options);
            if ($dataGet['user_address'] && $latestLottery) {
                $userAddress = $dataGet['user_address'];
                $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
                if (!$coinInstance->validAddress($userAddress)) {
                    throw new Exception('User Address is not valid');
                }
                unset($conditions);
                $conditions = [
                    'platform' => $dataGet['platform'],
                    'network' => $dataGet['network'],
                    'lottery_id' => $latestLottery['_id'],
                    'user_address' => $userAddress,
                ];

                $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
                $lotteryTicket = $lotteryTicketCollection->find($conditions)->toArray();
                $latestLottery['Lottery_ticket'] = $lotteryTicket;
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $latestLottery, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * Chi tiet lottery theo lottery_id, platform, network
     * @return false|ResponseInterface
     */
    public function detailAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network'] || !$dataGet['lottery_id']) {
                throw new Exception('Invalid params. Missing require param');
            }
            $platform = strtolower($dataGet['platform']);
            $network = strtolower($dataGet['network']);

            $conditions['_id'] = new ObjectId($dataGet['lottery_id']);
            $conditions['platform'] = $platform;
            $conditions['network'] = $network;

            $lottery = $this->lotteryCollection->findOne($conditions);
            if ($dataGet['user_address'] && $lottery) {
                $userAddress = $dataGet['user_address'];
                $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
                $userAddress = $coinInstance->toCheckSumAddress($userAddress);
                if (!$coinInstance->validAddress($userAddress)) {
                    throw new Exception('User Address is not valid');
                }
                unset($conditions);
                $conditions = [
                    'platform' => $platform,
                    'network' => $network,
                    'lottery_id' => $lottery['_id'],
                    'user_address' => $userAddress,
                ];

                $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
                $lotteryTicket = $lotteryTicketCollection->find($conditions)->toArray();
                $lottery['Lottery_ticket'] = $lotteryTicket;
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $lottery, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * Danh sach các lottery đã từng tham gia của User
     * @return false|ResponseInterface
     */
    public function getListJoinedByUserAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            $dataGet['platform'] = $dataGet['platform'] ? strtolower($dataGet['platform']) : '';
            $dataGet['network'] = $dataGet['network'] ? strtolower($dataGet['network']) : '';

            $address = $this->credential->address;
            $platform = $dataGet['platform'];
            $network = $dataGet['network'];

            if (!$address || !$platform || !$network) {
                throw new Exception('Invalid params');
            }
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            $address = $coinInstance->toCheckSumAddress($address);
            if (!$coinInstance->validAddress($address)) {
                throw new Exception('User Address is not valid');
            }

            $conditions['platform'] = $platform;
            $conditions['network'] = $network;
            if (strlen($dataGet['status'])) {
                $conditions['status'] = ContractLibrary::listLotteryStatus()[$dataGet['status']] ?? null;
            }
            if (isset($dataGet['start_time']) && $dataGet['start_time']) {
                $conditions['start_time'] = ['$gte' => intval($dataGet['start_time'])];
            }
            if (isset($dataGet['end_time']) && $dataGet['end_time']) {
                $conditions['end_time'] = ['$lte' => intval($dataGet['end_time'])];
            }

            if (strlen($dataGet['lottery_contract_id'])) {
                $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
            }

            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            $listLottery = $this->lotteryCollection->find($conditions)->toArray();
            if (!count($listLottery)) {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, [], 'Success', Helper::paginginfo(0, $limit, $p));
            }
            $listLotteryById = [];
            $listLotteryId = [];
            foreach ($listLottery as $item) {
                $key = strval($item['_id']);
                $listLotteryById[$key] = $item;
                $listLotteryId[] = $item['_id'];
            }

            $conditionsLotteryUserLog = [
                'platform' => $platform,
                'network' => $network,
                'user_address' => $address,
                'lottery_id' => ['$in' => $listLotteryId],
            ];
            $options = [
                'skip' => ($p - 1) * $limit,
                'limit' => $limit,
                'sort' => ['lottery_contract_id' => -1]
            ];

            $listLotteryUserLog = $this->lotteryUserLogCollection->find($conditionsLotteryUserLog, $options)->toArray();

            foreach ($listLotteryUserLog as &$item) {
                $key = strval($item['lottery_id']);
                $item['Lottery'] = $listLotteryById[$key];
            }
            $count = $this->lotteryUserLogCollection->countDocuments($conditionsLotteryUserLog);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listLotteryUserLog, 'Success', $pagingInfo);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * Check user co trung thuong voi lottery da quay cuoi cung
     * @return false|ResponseInterface
     */
    public function isWinLatestLotteryAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            $dataGet['platform'] = $dataGet['platform'] ? strtolower($dataGet['platform']) : '';
            $dataGet['network'] = $dataGet['network'] ? strtolower($dataGet['network']) : '';

            $userAddress = $this->credential->address;
            $platform = $dataGet['platform'];
            $network = $dataGet['network'];

            if (!$userAddress || !$platform || !$network) {
                throw new Exception('Invalid params');
            }

            $options = [
                'sort' => ['lottery_contract_id' => -1]
            ];

            $latestLottery = $this->lotteryCollection->findOne([
                'platform' => $platform,
                'network' => $network,
                'status' => ContractLibrary::LOTTERY_STATUS_CLAIMABLE
            ], $options);
            if (!$latestLottery) {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');
            }
            unset($conditions);
            $conditions = [
                'user_address' => $userAddress,
                'lottery_id' => new ObjectId($latestLottery['_id']),
                'number_win' => ['$gt' => 0]
            ];
            $lotteryLog = $this->lotteryUserLogCollection->findOne($conditions);
            if ($lotteryLog && $lotteryLog['lottery_id']) {
                $lottery = $this->lotteryCollection->findOne(['_id' => new ObjectId($lotteryLog['lottery_id'])]);
                $lotteryLog['Lottery'] = $lottery;

                if ($lottery) {
                    $conditionsLotteryTicket = [
                        'platform' => $dataGet['platform'],
                        'network' => $dataGet['network'],
                        'lottery_id' => $latestLottery['_id'],
                        'user_address' => $userAddress,
                    ];
                    $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
                    $lotteryTicket = $lotteryTicketCollection->find($conditionsLotteryTicket)->toArray();
                    $lotteryLog['Lottery']['Lottery_ticket'] = $lotteryTicket;
                }
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $lotteryLog, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     * Lấy ra lottery gần nhất mà người chơi trúng thưởng nhưng chưa claim hết
     * @return false|ResponseInterface
     */
    public function getLatestWinLotteryNotClaimAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            $userAddress = $this->credential->address;
            $platform = strtolower(trim($dataGet['platform'])) ?? '';
            $network = strtolower(trim($dataGet['network'])) ?? '';

            if (!$userAddress || !$platform || !$network) {
                throw new Exception('Invalid params');
            }

            $options = [
                'sort' => ['lottery_contract_id' => -1]
            ];

            $conditions = [
                'user_address' => $userAddress,
                'platform' => $platform,
                'network' => $network,
                '$expr' => ['$gt' => ['$number_win', '$number_claim']],
            ];
            $lotteryLog = $this->lotteryUserLogCollection->findOne($conditions, $options);

            if ($lotteryLog) {
                $lottery = $this->lotteryCollection->findOne(['_id' => new ObjectId($lotteryLog['lottery_id'])]);
                if ($lottery) {
                    $lotteryLog['Lottery'] = $lottery;
                    $conditionsLotteryTicket = [
                        'platform' => $dataGet['platform'],
                        'network' => $dataGet['network'],
                        'lottery_id' => $lottery['_id'],
                        'user_address' => $userAddress,
                    ];
                    $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
                    $lotteryTicket = $lotteryTicketCollection->find($conditionsLotteryTicket)->toArray();
                    $lotteryLog['Lottery']['Lottery_ticket'] = $lotteryTicket;
                }
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $lotteryLog, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function getLatestLotteryFinishedAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network']) {
                throw new Exception('Invalid params. Missing require param');
            }
            $dataGet['platform'] = strtolower(trim($dataGet['platform']));
            $dataGet['network'] = strtolower(trim($dataGet['network']));

            $conditions = [
                'platform' => $dataGet['platform'],
                'network' => $dataGet['network'],
                'status' => ContractLibrary::LOTTERY_STATUS_CLAIMABLE
            ];

            if (isset($dataGet['lottery_contract_id'])) {
                $conditions['lottery_contract_id'] = intval($dataGet['lottery_contract_id']);
            }
            $options = [
                'sort' => ['lottery_contract_id' => -1]
            ];

            $latestLottery = $this->lotteryCollection->findOne($conditions, $options);

            if ($dataGet['user_address'] && $latestLottery) {
                $userAddress = $dataGet['user_address'];
                $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
                $userAddress = $coinInstance->toCheckSumAddress($userAddress);
                if (!$coinInstance->validAddress($userAddress)) {
                    throw new Exception('User Address is not valid');
                }
                unset($conditions);
                $conditions = [
                    'platform' => $dataGet['platform'],
                    'network' => $dataGet['network'],
                    'lottery_id' => $latestLottery['_id'],
                    'user_address' => $userAddress,
                ];
                $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
                $lotteryTicket = $lotteryTicketCollection->find($conditions)->toArray();
                $latestLottery['Lottery_ticket'] = $lotteryTicket;
            }

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $latestLottery, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function summaryAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }

            $dataGet = $this->getData;
            if (!$dataGet['platform'] || !$dataGet['network']) {
                throw new Exception('Invalid params. Missing require param');
            }

            $platform = strtolower(trim($dataGet['platform']));
            $network = strtolower(trim($dataGet['network']));
            $match = [
                'platform' => $platform,
                'network' => $network,
                'status' => ContractLibrary::LOTTERY_STATUS_CLAIMABLE
            ];
            $conditions = [
                [
                    '$match' => $match
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "amount_withdraw_to_treasury" => [
                            '$sum' => '$amount_withdraw_to_treasury'
                        ]
                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "amount_withdraw_to_treasury" => 1
                    ],
                ],
            ];

            $summaryData = $this->lotteryCollection->aggregate($conditions);
            $amountWithdrawToTreasury = 0;
            if (!empty($summaryData) && $summaryData = $summaryData->toArray()) {
                $amountWithdrawToTreasury = !empty($summaryData[0]['amount_withdraw_to_treasury']) ? $summaryData[0]['amount_withdraw_to_treasury'] : 0;
            }
            $registryCollection = $this->mongo->selectCollection('registry');
            $registryCollection->findOne();
            $tokenKey = "lottery_setting_{$platform}_{$network}";
            $registry = $this->mongo->selectCollection('registry')->findOne();
            $lotterySetting = $registry[$tokenKey];
            $dataResponse = [
                'amount_withdraw_to_treasury' => $amountWithdrawToTreasury,
                'treasury_address' => $lotterySetting['treasury_address']
            ];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}
