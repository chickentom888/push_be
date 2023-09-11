<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class SaleController extends ApiControllerBase
{
    protected string $projectType = ContractLibrary::PROJECT_TYPE_SALE;

    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function getListAction()
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
            'project_type' => $this->projectType,
            'is_show' => ContractLibrary::ACTIVE,
            'platform' => $platform,
            'network' => $network,
        ];
        if (isset($dataGet['contract_version']) && $dataGet['contract_version']) {
            $conditions['contract_version'] = (int)$dataGet['contract_version'];
        }
        if (isset($dataGet['start_date']) && $dataGet['start_date']) {
            $conditions['created_at'] = ['$gte' => (int)$dataGet['start_date']];
        }
        if (isset($dataGet['end_date']) && $dataGet['end_date']) {
            $conditions['created_at'] = ['$lte' => (int)$dataGet['end_date']];
        }
        if (strlen($dataGet['current_status'])) {
            $conditions['current_status'] = ContractLibrary::listProjectStatus()[$dataGet['current_status']] ?? null;
        }

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
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
        if (strlen($dataGet['sale_type'])) {
            $conditions['sale_type'] = $dataGet['sale_type'];
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $count = $presaleCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $presaleCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();

        if (count($listData)) {
            $registry = $this->mongo->selectCollection('registry')->findOne();
            $listBaseToken = $this->mongo->selectCollection('sale_setting_address')->find([
                'type' => ContractLibrary::BASE_TOKEN
            ]);
            !empty($listBaseToken) && $listBaseToken = $listBaseToken->toArray();
            $listBaseToken = Arrays::reAssignKey($listBaseToken, 'token_address');
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
                $key = "sale_setting_{$platform}_$network";
                $saleSetting = $registry[$key];
                $data['max_success_to_claim'] = $saleSetting['setting']['max_success_to_claim'];
                $data['max_time_to_claim'] = $data['success_at'] + $data['max_success_to_claim'];
                $data['current_round'] = Helper::getCurrentRound($data);
                $data['base_token_avatar_url'] = $listBaseToken[$data['base_token_address']]['avatar'] ?? '';
                $data['is_locked'] = 0;
                if (isset($listLockByKey["{$data['network']}_{$data['platform']}_{$data['sale_token_address']}"])) {
                    $data['is_locked'] = ContractLibrary::LOCKED;
                }
            }
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

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
            'project_type' => $this->projectType,
            'platform' => $platform,
            'network' => $network,
        ]);
        if (!$presaleUserLog) {
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');
        }

        $presaleUserLog = $presaleUserLog->toArray();
        $listPresaleAddress = [];
        foreach ($presaleUserLog as $userLog) {
            $listPresaleAddress[] = $userLog['presale_address'];
        }

        $conditions = [
            'platform' => $platform,
            'network' => $network,
            'contract_address' => ['$in' => array_unique($listPresaleAddress)],
            'project_type' => $this->projectType,
            'is_show' => ContractLibrary::ACTIVE,
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
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        $conditions = $this->mongo->selectCollection('presale_user_log')->find([
            'project_type' => $this->projectType,
            'platform' => $platform,
            'network' => $network,
        ]);
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
}
