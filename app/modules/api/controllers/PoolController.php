<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use RedisException;

class PoolController extends ApiControllerBase
{
    protected $channel = ContractLibrary::RPUB_PRESALE_CHANGE;
    protected $projectType = ContractLibrary::PROJECT_TYPE_POOL;

    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function indexAction()
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'is_show' => ContractLibrary::ACTIVE,
            'project_type' => $this->projectType,
        ];
        // Get by owner
        if (isset($dataGet['pool_owner_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['pool_owner_address'])) {
                $conditions['pool_owner_address'] = $coinInstance->toCheckSumAddress($dataGet['pool_owner_address']);
            } else {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, [], 'Success');
            }
        }
        $this->getPoolCondition($dataGet, $conditions);

        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');
        $poolCollection = $this->mongo->selectCollection('pool');
        $count = $poolCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $poolCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();

        $listAddress = Arrays::arrayColumn($listData, 'contract_address');
        $whitelist = $poolWhitelistCollection->find(['contract_address' => ['$in' => $listAddress]]);
        !empty($whitelist) && $whitelist = $whitelist->toArray();
        $totalWhitelistByKey = [];
        foreach ($whitelist as $item) {
            if (isset($totalWhitelistByKey["{$item['network']}_{$item['platform']}_{$item['contract_address']}"])) {
                $totalWhitelistByKey["{$item['network']}_{$item['platform']}_{$item['contract_address']}"]++;
            } else {
                $totalWhitelistByKey["{$item['network']}_{$item['platform']}_{$item['contract_address']}"] = 0;
            }
        }

        foreach ($listData as &$data) {
            $data['round_name'] = Helper::getPoolRoundName($data);
            $data['round_define'] = Helper::getPoolRoundDefine($data);
            $data['total_whitelist_registration'] = $totalWhitelistByKey["{$data['network']}_{$data['platform']}_{$data['contract_address']}"] ?? 0;
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     */
    public function detailAction()
    {
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        $contractAddress = $this->getData['contract_address'];
        $userAddress = $this->getData['user_address'];
        if (!strlen($contractAddress) || !$coinInstance->validAddress($contractAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        $data = $this->mongo->selectCollection('pool')->findOne([
            'contract_address' => $coinInstance->toCheckSumAddress($contractAddress),
            'is_show' => ContractLibrary::ACTIVE,
        ]);
        if (!$data) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');
        $whitelist = $poolWhitelistCollection->countDocuments(['contract_address' => $contractAddress]);
        $data['total_whitelist_registration'] = $whitelist;
        $data['round_name'] = Helper::getPoolRoundName($data);
        $data['round_define'] = Helper::getPoolRoundDefine($data);

        if (strlen($userAddress) && $coinInstance->validAddress($userAddress)) {
            if ($data['active_auction_round']) {
                $auction = $this->mongo->selectCollection('pool_user_auction_round')
                    ->findOne([
                        'network' => $data['network'],
                        'platform' => $data['platform'],
                        'pool_address' => $contractAddress,
                        'user_address' => $userAddress,
                    ]);

                if ($auction) {
                    $data['is_burned'] = $auction['is_burned'];
                    $data['auction_amount'] = $auction['auction_amount'];
                    $data['withdraw_status'] = $auction['withdraw_status'];
                    $data['withdraw_at'] = $auction['withdraw_at'];
                }
            }
        }

        if ($this->request->isPost()) {
            $token = $this->jsonData['token'];
            $data['is_joined'] = ContractLibrary::JOINED;
            try {
                $dataDecode = $this->decodeToken($token);
                $dataUser = $dataDecode->data;
                $poolUserLog = $this->mongo->selectCollection('pool_user_log')->findOne([
                    'user_address' => $dataUser->address,
                    'contract_address' => $data['contract_address'],
                    'network' => $data['network'],
                    'platform' => $data['platform'],
                ]);
                if (!$poolUserLog) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
                }
            } catch (Exception $e) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
            }
        }

        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, $data, 'success');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function whitelistAddressAction($contractAddress)
    {
        $dataGet = $this->getData;
        $p = 1;
        $limit = -1;
        $options = [
            'sort' => ['_id' => -1]
        ];
        if (strlen($dataGet['limit'])) {
            $limit = intval($dataGet['limit']);
            $options['limit'] = $limit;
        }
        if (strlen($dataGet['p'])) {
            $p = $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
            if (isset($limit) && $limit > 0) {
                $options['skip'] = ($p - 1) * $limit;
            }
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
        ];

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($contractAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }
        $contractAddress = $coinInstance->toCheckSumAddress($contractAddress);
        $conditions['pool_address'] = $contractAddress;

        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');
        $count = $poolWhitelistCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $poolWhitelistCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $response = Arrays::arrayColumn($listData, 'user_address');

        if (strlen($dataGet['user_address'])) {
            $response = preg_grep('/' . $dataGet['user_address'] . '/i', $response);
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     */
    public function getPositionInAuctionAction()
    {
        $dataGet = $this->getData;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!strlen($dataGet['user_address']) || !$coinInstance->validAddress($dataGet['user_address']) ||
            !strlen($dataGet['pool_address']) || !$coinInstance->validAddress($dataGet['pool_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'pool_address' => $coinInstance->toCheckSumAddress($dataGet['pool_address']),
        ];

        $auctionCollection = $this->mongo->selectCollection('pool_user_auction_round');
        $listData = $auctionCollection->find($conditions, [
            'sort' => ['auction_amount' => -1]
        ]);
        !empty($listData) && $listData = $listData->toArray();
        $listAddress = Arrays::arrayColumn($listData, 'user_address');
        if (count($listData)) {
            $position = array_search($coinInstance->toCheckSumAddress($dataGet['user_address']), $listAddress);
        }
        if (!isset($position) || $position === false) {
            $position = -1;
        }

        $response = [
            'auction' => $listData[$position] ?? null,
            'position' => $position + 1,
        ];

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function listPoolByUserRegisteredAuctionAction()
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $by = -1;
        $order = 'auction_amount';
        if (strlen($dataGet['order'])) {
            $order = $dataGet['order'];
        }
        if (strlen($dataGet['by'])) {
            $by = $dataGet['by'] == 'asc' ? 1 : -1;
        }
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => [$order => $by]
        ];

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!strlen($dataGet['user_address']) || !$coinInstance->validAddress($dataGet['user_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $userAddress = $coinInstance->toCheckSumAddress($dataGet['user_address']);
        $poolConditions = [
            'platform' => $platform,
            'network' => $network,
        ];
        $auctionConditions = [
            'platform' => $platform,
            'network' => $network,
            'user_address' => $userAddress,
        ];
        if (strlen($dataGet['is_burned'])) {
            $auctionConditions['is_burned'] = intval($dataGet['is_burned']);

            // <editor-fold desc = "danh sách pool dc refund ko nằm trong danh sách pool_buy_log">
            if ($dataGet['is_burned'] == ContractLibrary::INACTIVE) {
                $buyLog = $this->mongo->selectCollection('pool_buy_log')
                    ->find([
                        'platform' => $platform,
                        'network' => $network,
                        'user_address' => $userAddress,
                    ]);
                !empty($buyLog) && $buyLog = $buyLog->toArray();
                $buyLogPool = Arrays::arrayColumn($buyLog, 'pool_address');

                $auctionConditions['pool_address'] = ['$nin' => $buyLogPool];
            }
            // </editor-fold>
        }
        if (strlen($dataGet['withdraw_status'])) {
            $auctionConditions['withdraw_status'] = intval($dataGet['withdraw_status']);
        }
        $this->getPoolCondition($dataGet, $poolConditions);

        $listContract = $this->mongo->selectCollection('pool_user_auction_round')
            ->find($auctionConditions);
        !empty($listContract) && $listContract = $listContract->toArray();
        $listContract = Arrays::arrayColumn($listContract, 'pool_address');
        $poolConditions['contract_address'] = ['$in' => $listContract];

        $poolCollection = $this->mongo->selectCollection('pool');
        $listData = $poolCollection->find($poolConditions, $options);
        $count = $poolCollection->countDocuments($poolConditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        !empty($listData) && $listData = $listData->toArray();
        foreach ($listData as &$data) {
            $data['round_name'] = Helper::getPoolRoundName($data);
            $data['round_define'] = Helper::getPoolRoundDefine($data);
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function listPoolByUserRegisteredWhitelistAction()
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $by = -1;
        $order = '_id';
        if (strlen($dataGet['order'])) {
            $order = $dataGet['order'];
        }
        if (strlen($dataGet['by'])) {
            $by = $dataGet['by'] == 'asc' ? 1 : -1;
        }
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => [$order => $by]
        ];

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!strlen($dataGet['user_address']) || !$coinInstance->validAddress($dataGet['user_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $poolConditions = [
            'platform' => $platform,
            'network' => $network,
        ];
        $whitelistConditions = [
            'platform' => $platform,
            'network' => $network,
            'user_address' => $coinInstance->toCheckSumAddress($dataGet['user_address'])
        ];

        $this->getPoolCondition($dataGet, $poolConditions);
        $listContract = $this->mongo->selectCollection('pool_whitelist')
            ->find($whitelistConditions);
        !empty($listContract) && $listContract = $listContract->toArray();
        $listContract = Arrays::arrayColumn($listContract, 'pool_address');
        $poolConditions['contract_address'] = ['$in' => $listContract];

        $poolCollection = $this->mongo->selectCollection('pool');
        $listData = $poolCollection->find($poolConditions, $options);
        $count = $poolCollection->countDocuments($poolConditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        !empty($listData) && $listData = $listData->toArray();
        foreach ($listData as &$data) {
            $data['round_name'] = Helper::getPoolRoundName($data);
            $data['round_define'] = Helper::getPoolRoundDefine($data);
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     */
    public function listPurchasedAction($userAddress)
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($userAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
        }

        $userAddress = $coinInstance->toCheckSumAddress($userAddress);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $userLogConditions = [
            'user_address' => $userAddress,
            'platform' => $platform,
            'network' => $network,
        ];

        $poolUserLog = $this->mongo->selectCollection('pool_user_log')->find($userLogConditions);
        if (!$poolUserLog) {
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');
        }
        $poolUserLog = $poolUserLog->toArray();
        $listPoolAddress = Arrays::arrayColumn($poolUserLog, 'pool_address');
        $listPoolAddress = array_values(array_unique($listPoolAddress));

        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'is_show' => ContractLibrary::ACTIVE,
            'contract_address' => ['$in' => $listPoolAddress],
        ];
        $this->getPoolCondition($dataGet, $conditions);

        $poolCollection = $this->mongo->selectCollection('pool');
        $count = $poolCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $result = $poolCollection->find($conditions, $options);
        !empty($result) && $result = $result->toArray();
        foreach ($result as &$data) {
            $data['round_name'] = Helper::getPoolRoundName($data);
            $data['round_define'] = Helper::getPoolRoundDefine($data);
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result, 'Success', $pagingInfo);
    }

    /**
     * @param $poolAddress
     * @return mixed
     * @throws ConnectionErrorException
     */
    public function listAuctionAction($poolAddress)
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['auction_amount' => -1]
        ];

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($poolAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong!');
        }
        $poolAddress = $coinInstance->toCheckSumAddress($poolAddress);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'pool_address' => $poolAddress,
        ];

        if (strlen($dataGet['user_address'])) {
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            }
        }

        $auctionCollection = $this->mongo->selectCollection('pool_user_auction_round');
        $count = $auctionCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $result = $auctionCollection->find($conditions, $options);
        !empty($result) && $result = $result->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result, 'Success', $pagingInfo);
    }

    /**
     * @param $poolAddress
     * @return mixed
     * @throws ConnectionErrorException
     */
    public function listZeroRoundAction($poolAddress)
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($poolAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
        }

        $poolAddress = $coinInstance->toCheckSumAddress($poolAddress);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'pool_address' => $poolAddress,
        ];
        if (strlen($dataGet['user_address'])) {
            if ($coinInstance->validAddress($dataGet['user_address'])) {
                $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
            }
        }
        if (strlen($dataGet['withdraw_status'])) {
            $conditions['withdraw_status'] = intval($dataGet['withdraw_status']);
        }

        $zeroRoundCollection = $this->mongo->selectCollection('pool_user_zero_round');
        $count = $zeroRoundCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $result = $zeroRoundCollection->find($conditions, $options);
        !empty($result) && $result = $result->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result, 'Success', $pagingInfo);
    }

    /**
     * @return mixed
     * @throws ConnectionErrorException
     */
    public function checkValidWhitelistAction()
    {
        $dataGet = $this->getData;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($dataGet['user_address']) || !$coinInstance->validAddress($dataGet['pool_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Your Address is not valid');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'user_address' => $coinInstance->toCheckSumAddress($dataGet['user_address']),
            'pool_address' => $coinInstance->toCheckSumAddress($dataGet['pool_address']),
        ];

        $poolWhitelistCollection = $this->mongo->selectCollection('pool_whitelist');
        $data = $poolWhitelistCollection->findOne($conditions);
        $isValid = !empty($data);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $isValid, 'Success');
    }

    /**
     * @return mixed
     * @throws ConnectionErrorException
     */
    public function checkCreationPermissionAction()
    {
        $dataGet = $this->getData;
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $userAddress = $dataGet['user_address'];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($userAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
        }

        $userAddress = $coinInstance->toCheckSumAddress($userAddress);
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $key = "pool_setting_{$platform}_$network";
        $poolSetting = $registry[$key];
        $creatorAddress = [];
        if (count($poolSetting['creator_address']['list_address'])) {
            $creatorAddress = $poolSetting['creator_address']['list_address'];
        }

        $isValid = in_array($userAddress, $creatorAddress);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $isValid, 'Success');
    }

    /**
     * @param $id
     * @return Response|ResponseInterface
     * @throws RedisException
     */
    public function updateAction($id)
    {
        $dataPost = $this->jsonData;
        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            '_id' => new ObjectId($id),
            'project_type' => $this->projectType,
            'user_address' => $this->credential->address
        ]);
        if (!$pool) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        try {
            $dataDecode = $this->decodeToken($dataPost['token']);
            $dataUser = $dataDecode->data;
            if (!$dataUser || $dataUser->address !== $pool['pool_owner_address']) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'You are not the owner');
            }
        } catch (Exception $e) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'You are not the owner');
        }

        $dataUpdate = [];
        $updateFilter = [
            'website_url',
            'telegram_url',
            'youtube_url',
            'twitter_url',
            'medium_url',
            'avatar_url',
            'facebook_url',
            'cover_url',
            'research_url',
        ];
        foreach ($updateFilter as $field) {
            if (isset($dataPost[$field])) {
                $dataUpdate[$field] = $dataPost[$field];
            }
        }

        if ($dataUpdate) {
            $dataUpdate['updated_at'] = time();
            $poolCollection->updateOne(['_id' => $pool['_id']], ['$set' => $dataUpdate]);
            $pool = array_merge($pool, $dataUpdate);
            $pool['round_name'] = Helper::getPoolRoundName($pool);
            $pool['round_define'] = Helper::getPoolRoundDefine($pool);
            $this->redis->publish($this->channel, json_encode([$pool]));

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $pool, 'Success');
        }

        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function getUserLogAction()
    {
        $dataGet = $this->getData;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($dataGet['user_address']) || !$coinInstance->validAddress($dataGet['pool_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'User or Pool Address is not valid');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'user_address' => $coinInstance->toCheckSumAddress($dataGet['user_address']),
            'pool_address' => $coinInstance->toCheckSumAddress($dataGet['pool_address']),
        ];
        if (strlen($dataGet['contract_type'])) {
            $conditions['contract_type'] = $dataGet['contract_type'];
        }
        if (strlen($dataGet['withdraw_token_type'])) {
            $conditions['withdraw_token_type'] = $dataGet['withdraw_token_type'];
        }

        $response = $this->mongo->selectCollection('pool_user_log')
            ->findOne($conditions);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function getBuyLogAction()
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
        ];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if ($coinInstance->validAddress($dataGet['user_address'])) {
            $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
        }
        if ($coinInstance->validAddress($dataGet['pool_address'])) {
            $conditions['pool_address'] = $coinInstance->toCheckSumAddress($dataGet['pool_address']);
        }

        $buyLogCollection = $this->mongo->selectCollection('pool_buy_log');
        $count = $buyLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $response = $buyLogCollection->find($conditions, $options);
        !empty($response) && $response = $response->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success', $pagingInfo);
    }

    /**
     * @return mixed
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function auctionByUserAction()
    {
        $dataGet = $this->getData;
        $poolAddress = $dataGet['pool_address'];
        $userAddress = $dataGet['user_address'];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($poolAddress) || !$coinInstance->validAddress($userAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong!');
        }

        $poolAddress = $coinInstance->toCheckSumAddress($poolAddress);
        $userAddress = $coinInstance->toCheckSumAddress($userAddress);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'pool_address' => $poolAddress,
            'user_address' => $userAddress,
        ];

        $auctionCollection = $this->mongo->selectCollection('pool_user_auction_round');
        $result = $auctionCollection->findOne($conditions);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result, 'Success');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    protected function getPoolCondition($dataGet, &$conditions)
    {
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);

        if (isset($dataGet['contract_version']) && $dataGet['contract_version']) {
            $conditions['contract_version'] = (int)$dataGet['contract_version'];
        }
        if (strlen($dataGet['current_status'])) {
            $conditions['current_status'] = ContractLibrary::listPoolStatus()[$dataGet['current_status']] ?? null;
            if ($conditions['current_status'] == ContractLibrary::PRESALE_STATUS_PENDING) {
                $conditions['current_round'] = ['$ne' => ContractLibrary::POOL_BURNING_ROUND];
            }
        }
        if (strlen($dataGet['current_round'])) {
            $conditions['current_round'] = intval($dataGet['current_round']);
        }
        if (isset($dataGet['q']) && $dataGet['q']) {
            $conditions['$or'] = [
                ['pool_token_name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['pool_token_symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];

            if ($coinInstance->validAddress($dataGet['q'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['q']);
                $conditions['$or'][] = ['contract_address' => $filterAddress];
                $conditions['$or'][] = ['pool_token_address' => $filterAddress];
            }
        }
    }
}
