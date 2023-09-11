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
use RedisException;

class PresaleController extends ApiControllerBase
{
    protected string $channel = ContractLibrary::RPUB_PRESALE_CHANGE;
    protected string $projectType = ContractLibrary::PROJECT_TYPE_PRESALE;

    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function getListAction()
    {
        if ($this->request->isPost()) {
            return false;
        }

        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];
        $conditions = [
            'project_type' => $this->projectType,
            'is_show' => ContractLibrary::ACTIVE
        ];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (isset($dataGet['platform']) && $dataGet['platform']) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (isset($dataGet['network']) && $dataGet['network']) {
            $conditions['network'] = $dataGet['network'];
        }
        if (isset($dataGet['contract_version']) && $dataGet['contract_version']) {
            $conditions['contract_version'] = (int)$dataGet['contract_version'];
        }
        if (isset($dataGet['start_date']) && $dataGet['start_date']) {
            $conditions['created_at'] = ['$gte' => (int)$dataGet['start_date']];
        }
        if (isset($dataGet['end_date']) && $dataGet['end_date']) {
            $conditions['created_at'] = ['$lte' => (int)$dataGet['end_date']];
        }

        // if addr is valid then checkSum to filter
        if (isset($dataGet['q']) && $dataGet['q']) {
            $conditions['$or'] = [
                ['sale_token_name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['sale_token_symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['q']);
                $conditions['$or'][] = ['contract_address' => $filterAddress];
                $conditions['$or'][] = ['sale_token_address' => $filterAddress];
            }
        }
        if (isset($dataGet['presale_owner_address'])) {
            if ($coinInstance->validAddress($dataGet['presale_owner_address'])) {
                $conditions['presale_owner_address'] = $coinInstance->toCheckSumAddress($dataGet['presale_owner_address']);
            } else {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, [], 'Success');
            }
        }
        if (strlen($dataGet['current_status'])) {
            $conditions['current_status'] = ContractLibrary::listProjectStatus()[$dataGet['current_status']] ?? null;
        }

        $exchangePlatformCollection = $this->mongo->selectCollection('exchange_platform');
        if (isset($dataGet['exchange_key']) && $dataGet['exchange_key']) {
            $exchangePlatform = $exchangePlatformCollection->findOne(['exchange_key' => $dataGet['exchange_key']]);
            if (empty($exchangePlatform)) {
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Exchange Platform not exists');
            }
            $conditions['dex_factory_address'] = $exchangePlatform['dex_factory_address'];
        }

        if (strlen($dataGet['sale_type'])) {
            $conditions['sale_type'] = $dataGet['sale_type'];
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $listData = $presaleCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();

        if (count($listData)) {
            $registry = $this->mongo->selectCollection('registry')->findOne();

            $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
            $listBaseToken = $presaleSettingAddressCollection->find([
                'type' => ContractLibrary::BASE_TOKEN
            ]);
            !empty($listBaseToken) && $listBaseToken = $listBaseToken->toArray();
            $listBaseToken = Arrays::reAssignKey($listBaseToken, 'token_address');

            // <editor-fold desc = "Get Exchange Platform">
            $listExchangePlatform = $exchangePlatformCollection->find();
            if (!empty($listExchangePlatform)) {
                $listExchangePlatform = $listExchangePlatform->toArray();
                $listExchangePlatform = Arrays::reAssignKey($listExchangePlatform, 'dex_factory_address');
            }
            $listTokenAddress = Arrays::arrayColumn($listData, 'sale_token_address');
            $lockHistories = $this->mongo->selectCollection('lock_histories')->find(
                ['token_address' => ['$in' => $listTokenAddress]],
            );
            if (!empty($lockHistories)) {
                $lockHistories = $lockHistories->toArray();
                foreach ($lockHistories as $lock) {
                    $listLockByKey["{$lock['network']}_{$lock['platform']}_{$lock['token_address']}"] = $lock;
                }
            }

            foreach ($listData as &$data) {
                $network = $data['network'];
                $platform = $data['platform'];
                $key = "presale_setting_{$platform}_{$network}";
                $presaleSetting = $registry[$key];
                $data['max_success_to_liquidity'] = $presaleSetting['setting']['max_success_to_liquidity'];
                $data['max_time_to_add_liquidity'] = $data['success_at'] + $data['max_success_to_liquidity'];
                $data['current_round'] = Helper::getCurrentRound($data);
                if ($data['dex_factory_address'] && $listExchangePlatform[$data['dex_factory_address']]) {
                    $data['exchange_platform'] = $listExchangePlatform[$data['dex_factory_address']] ?? [];
                }
                $data['base_token_avatar_url'] = $listBaseToken[$data['base_token_address']] ? $listBaseToken[$data['base_token_address']]['avatar'] : '';
                $data['is_locked'] = 0;
                if (isset($listLockByKey["{$data['network']}_{$data['platform']}_{$data['sale_token_address']}"])) {
                    $data['is_locked'] = ContractLibrary::LOCKED;
                }
            }
            // </editor-fold>
        }

        $count = $presaleCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    public function detailAction()
    {
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        $contractAddress = $this->getData['contract_address'];
        if (!strlen($contractAddress) || !$coinInstance->validAddress($contractAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        $data = $this->mongo->selectCollection('presale')->findOne([
            'contract_address' => $coinInstance->toCheckSumAddress($contractAddress),
            'is_show' => ContractLibrary::ACTIVE,
        ]);
        if (!$data) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }

        // <editor-fold desc = "Check joined">
        if ($this->request->isPost()) {
            $token = $this->jsonData['token'];
            $data['is_joined'] = ContractLibrary::JOINED;
            try {
                $dataDecode = $this->decodeToken($token);
                $dataUser = $dataDecode->data;
                $presaleUserLog = $this->mongo->selectCollection('presale_user_log')->findOne([
                    'user_address' => $dataUser->address,
                    'presale_address' => $data['contract_address'],
                    'network' => $data['network'],
                    'platform' => $data['platform'],
                ]);
                if (!$presaleUserLog) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
                }
            } catch (Exception $e) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
            }
        }
        // </editor-fold>

        if ($data['dex_factory_address']) {
            $data['exchange_platform'] = $this->mongo->selectCollection('exchange_platform')
                ->findOne(['dex_factory_address' => $data['dex_factory_address']]);
        }
        $data['current_round'] = Helper::getCurrentRound($data);
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $network = $data['network'];
        $platform = $data['platform'];
        $data['is_locked'] = 0;
        $lockHistories = $this->mongo->selectCollection('lock_histories')->findOne([
            'network' => $network,
            'platform' => $platform,
            'token_address' => $data['sale_token_address'],
        ]);
        if (count($lockHistories)) {
            $data['is_locked'] = ContractLibrary::LOCKED;
        }

        if ($this->projectType == $data['project_type']) {
            $presaleSettingAddressCollection = $this->mongo->selectCollection('presale_setting_address');
            $baseToken = $presaleSettingAddressCollection->findOne([
                'token_address' => $data['base_token_address']
            ]);
            if ($baseToken) {
                $data['base_token_avatar_url'] = $baseToken['avatar'];
            }
            $key = "presale_setting_{$platform}_{$network}";
            $presaleSetting = $registry[$key];
            $data['max_success_to_liquidity'] = $presaleSetting['setting']['max_success_to_liquidity'];
            $data['max_time_to_add_liquidity'] = $data['success_at'] + $data['max_success_to_liquidity'];
        } else {
            $saleSettingAddressCollection = $this->mongo->selectCollection('sale_setting_address');
            $baseToken = $saleSettingAddressCollection->findOne([
                'token_address' => $data['base_token_address']
            ]);
            if ($baseToken) {
                $data['base_token_avatar_url'] = $baseToken['avatar'];
            }
            $key = "sale_setting_{$platform}_{$network}";
            $saleSetting = $registry[$key];
            $data['max_success_to_claim'] = $saleSetting['setting']['max_success_to_claim'];
            $data['max_time_to_claim'] = $data['success_at'] + $data['max_success_to_claim'];
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
    }

    /**
     * @throws RedisException
     */
    public function updateAction($id)
    {
        $dataPost = $this->jsonData;
        $credential = $this->credential;
        $presaleCollection = $this->mongo->selectCollection('presale');
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $presaleId = new ObjectId($id);
        $presale = $presaleCollection->findOne([
            '_id' => $presaleId,
            'user_address' => $credential->address
        ]);
        if (!$presale) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Data not found!');
        }
        try {
            $dataDecode = $this->decodeToken($dataPost['token']);
            $dataUser = $dataDecode->data;
            if (!$dataUser || $dataUser->address !== $presale['presale_owner_address']) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'You are not the owner');
            }
        } catch (Exception $e) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'You are not the owner');
        }
        /*if ($credential->address !== $presale['presale_owner_address']) {
            return $this->setDataJson(0, null, 'Invalid authenticate.');
        }*/

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
        ];
        if ($presale['project_type'] == ContractLibrary::PROJECT_TYPE_SALE) {
            $updateFilter[] = 'research_url';
        }

        foreach ($updateFilter as $field) {
            if (isset($dataPost[$field])) {
                $dataUpdate[$field] = $dataPost[$field];
            }
        }

        if ($dataUpdate) {
            $dataUpdate['updated_at'] = time();
            $presaleCollection->updateOne(['_id' => $presaleId], ['$set' => $dataUpdate]);
            if (strlen($dataUpdate['avatar_url'])) {
                $tokenCollection->updateOne(
                    [
                        'address' => $presale['sale_token_address'],
                        'platform' => $presale['platform'],
                        'network' => $presale['network'],
                        '$or' => [
                            ["image" => ['$exists' => false]],
                            [
                                "image" => ['$exists' => true],
                                '$where' => "this.image.length == 0",
                            ],
                        ]
                    ],
                    ['$set' => ['image' => $dataUpdate['avatar_url']]]
                );
            }
            $presale = array_merge($presale, $dataUpdate);
            $this->redis->publish($this->channel, json_encode([$presale]));

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $presale, 'Success');
        }

        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
    }

    /**
     * @param $userAddress
     * @return mixed
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function listPurchasedAction($userAddress)
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
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($userAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
        }

        $userAddress = $coinInstance->toCheckSumAddress($userAddress);
        $presaleUserLog = $this->mongo->selectCollection('presale_user_log')->find([
            'user_address' => $userAddress,
            'platform' => $platform,
            'network' => $network,
        ]);
        if (!$presaleUserLog) {
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');
        }
        $presaleUserLog = $presaleUserLog->toArray();
        $listPresaleAddress = Arrays::arrayColumn($presaleUserLog, 'presale_address');
        $listPresaleAddress = array_values(array_unique($listPresaleAddress));

        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'contract_address' => ['$in' => $listPresaleAddress],
            'project_type' => $this->projectType,
            'is_show' => ContractLibrary::ACTIVE
        ];
        if (strlen($dataGet['sale_type'])) {
            $conditions['sale_type'] = $dataGet['sale_type'];
        }
        if (strlen($dataGet['current_status'])) {
            $conditions['current_status'] = ContractLibrary::listProjectStatus()[$dataGet['current_status']] ?? null;
        }
        if (isset($dataGet['q']) && $dataGet['q']) {
            $conditions['$or'] = [
                ['sale_token_name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['sale_token_symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['q']);
                $conditions['$or'][] = ['contract_address' => $filterAddress];
                $conditions['$or'][] = ['sale_token_address' => $filterAddress];
            }
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $count = $presaleCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $result = $presaleCollection->find($conditions, $options);
        !empty($result) && $result = $result->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     */
    public function getUserLogAction()
    {
        $dataGet = $this->getData;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($dataGet['user_address']) || !$coinInstance->validAddress($dataGet['presale_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'User or Presale Address is not valid');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'user_address' => $coinInstance->toCheckSumAddress($dataGet['user_address']),
            'presale_address' => $coinInstance->toCheckSumAddress($dataGet['presale_address']),
        ];

        $response = $this->mongo->selectCollection('presale_user_log')
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
            'project_type' => $this->projectType,
        ];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if ($coinInstance->validAddress($dataGet['user_address'])) {
            $conditions['user_address'] = $coinInstance->toCheckSumAddress($dataGet['user_address']);
        }
        if ($coinInstance->validAddress($dataGet['presale_address'])) {
            $conditions['presale_address'] = $coinInstance->toCheckSumAddress($dataGet['presale_address']);
        }
        if (strlen($dataGet['sale_type'])) {
            $conditions['sale_type'] = $dataGet['sale_type'];
        }

        $buyLogCollection = $this->mongo->selectCollection('presale_buy_log');
        $count = $buyLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $response = $buyLogCollection->find($conditions, $options);
        !empty($response) && $response = $response->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $response, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function listUserRegisterZeroRoundAction($presaleAddress)
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
        if (!$coinInstance->validAddress($presaleAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Presale Address is not valid');
        }
        $presaleAddress = $coinInstance->toCheckSumAddress($presaleAddress);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'presale_address' => $presaleAddress,
        ];
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');
        $count = $presaleUserZeroRoundCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $result = $presaleUserZeroRoundCollection->find($conditions, $options);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $result, 'Success', $pagingInfo);
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function whitelistAddressAction($presaleAddress)
    {
        $dataGet = $this->getData;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($presaleAddress)) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Presale Address is not valid');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'presale_address' => $coinInstance->toCheckSumAddress($presaleAddress),
        ];
        $presaleWhitelistCollection = $this->mongo->selectCollection('presale_whitelist');
        $listData = $presaleWhitelistCollection->find($conditions);
        !empty($listData) && $listData = $listData->toArray();
        $whitelist = Arrays::arrayColumn($listData, 'user_address');

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $whitelist, 'Success');
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function checkValidWhitelistAction()
    {
        $isValid = false;
        $dataGet = $this->getData;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (!$coinInstance->validAddress($dataGet['address']) || !$coinInstance->validAddress($dataGet['presale_address'])) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Your Address is not valid');
        }

        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'user_address' => $coinInstance->toCheckSumAddress($dataGet['address']),
            'presale_address' => $coinInstance->toCheckSumAddress($dataGet['presale_address']),
        ];
        $presaleWhitelistCollection = $this->mongo->selectCollection('presale_whitelist');
        $data = $presaleWhitelistCollection->findOne($conditions);
        if (!empty($data)) {
            $isValid = true;
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $isValid, 'Success');
    }
}
