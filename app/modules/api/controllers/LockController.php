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

class LockController extends ApiControllerBase
{

    /** @var BinanceWeb3 */
    public $web3;

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
        $order = $dataGet['order'] ?? 'created_at';
        $by = $dataGet['by'] ?? 'desc';
        $sort = $this->sort($order, $by);

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
        ];
        if (strlen($dataGet['token_address'])) {
            if ($coinInstance->validAddress($dataGet['token_address'])) {
                $userAddress = $coinInstance->toCheckSumAddress($dataGet['token_address']);
                $conditions['token_address'] = $userAddress;
            }
        }
        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['contract_name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['contract_symbol' => $dataGet['q']],
                ['hash' => $dataGet['q']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['$or'][] = ['contract_address' => $coinInstance->toCheckSumAddress($dataGet['q'])];
                $conditions['$or'][] = ['address_lock' => $coinInstance->toCheckSumAddress($dataGet['q'])];
                $conditions['$or'][] = ['address_withdraw' => $coinInstance->toCheckSumAddress($dataGet['q'])];
            }
        }

        $lockHistoriesCollection = $this->mongo->selectCollection('lock_histories');
        $count = $lockHistoriesCollection->countDocuments($conditions);
        $listData = $lockHistoriesCollection->aggregate([
            ['$match' => $conditions],
            ['$skip' => ($p - 1) * $limit],
            ['$limit' => $limit],
            ['$sort' => $sort]
        ]);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        !empty($listData) && $listData = $listData->toArray();
        $this->getTokenImageByLock($listData, $platform, $network);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    public function getByTokenAction($tokenAddress)
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $order = $dataGet['order'] ?? 'created_at';
        $by = $dataGet['by'] ?? 'desc';
        $sort = $this->sort($order, $by);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        if (strlen($dataGet['withdraw_status'])) {
            $withdrawStatus = intval($dataGet['withdraw_status']);
        }
        $conditions = [
            'platform' => $platform,
            'network' => $network
        ];
        if (isset($withdrawStatus) && strlen($withdrawStatus)) {
            $conditions['withdraw_status'] = $withdrawStatus;
        }

        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if ($coinInstance->validAddress($tokenAddress)) {
            $tokenAddress = $coinInstance->toCheckSumAddress($tokenAddress);
            $conditions['token_address'] = $tokenAddress;
            $lockHistoriesCollection = $this->mongo->selectCollection('lock_histories');
            $count = $lockHistoriesCollection->countDocuments($conditions);
            $listData = $lockHistoriesCollection->aggregate([
                ['$match' => $conditions],
                ['$skip' => ($p - 1) * $limit],
                ['$limit' => $limit],
                ['$sort' => $sort]
            ]);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);
            !empty($listData) && $listData = $listData->toArray();
            $this->getTokenImageByLock($listData, $platform, $network);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
        }
        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong!');
    }

    /**
     * @throws ConnectionErrorException
     */
    public function getByUserAction($addressWithdraw)
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $order = $dataGet['order'] ?? 'created_at';
        $by = $dataGet['by'] ?? 'desc';
        $sort = $this->sort($order, $by);
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $network = $dataGet['network'] == ContractLibrary::TEST_NETWORK ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        if (strlen($dataGet['withdraw_status'])) {
            $withdrawStatus = intval($dataGet['withdraw_status']);
        }
        $conditions = [
            'platform' => $platform,
            'network' => $network,
        ];
        if (isset($withdrawStatus) && strlen($withdrawStatus)) {
            $conditions['withdraw_status'] = $withdrawStatus;
        }
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if ($coinInstance->validAddress($addressWithdraw)) {
            $addressWithdraw = $coinInstance->toCheckSumAddress($addressWithdraw);
            $conditions['address_withdraw'] = $addressWithdraw;
            $lockHistoriesCollection = $this->mongo->selectCollection('lock_histories');
            $count = $lockHistoriesCollection->countDocuments($conditions);
            $listData = $lockHistoriesCollection->aggregate([
                ['$match' => $conditions],
                ['$skip' => ($p - 1) * $limit],
                ['$limit' => $limit],
                ['$sort' => $sort]
            ]);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);
            !empty($listData) && $listData = $listData->toArray();
            $this->getTokenImageByLock($listData, $platform, $network);

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
        }
        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong!');
    }

    protected function getTokenImageByLock(&$listData, $platform, $network)
    {
        $listTokenAddress = Arrays::arrayColumn($listData, 'token_address');
        if ($listTokenAddress) {
            $listToken = $this->mongo->selectCollection('tokens')
                ->find([
                    'address' => ['$in' => $listTokenAddress],
                    'platform' => $platform,
                    'network' => $network,
                ]);
            if (!empty($listToken)) {
                $listToken = $listToken->toArray();
                $listToken = Arrays::reAssignKey($listToken, 'address');
                foreach ($listData as &$lock) {
                    if ($listToken[$lock['token_address']]) {
                        $lock['image'] = $listToken[$lock['token_address']]['image'] ?? null;
                    }
                }
            }
        }
    }
}
