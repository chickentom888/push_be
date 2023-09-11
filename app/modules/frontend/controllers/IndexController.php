<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;

class IndexController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        parent::initialize($param);
        $this->checkLogin();
    }

    /**
     * @throws Exception
     */
    public function indexAction()
    {

    }

    public function selectedAddressAction()
    {
        $dataPost = $this->postData;
        $bscChainId = [
            "56" => ContractLibrary::MAIN_NETWORK,
            "97" => ContractLibrary::TEST_NETWORK,
        ];

        $listChainIdBsc = array_keys($bscChainId);
        $chainId = $dataPost['chain_id'];

        if (in_array($chainId, $listChainIdBsc)) {
            $platform = BinanceWeb3::PLATFORM;
            $network = $bscChainId[$chainId];
        } else {
            $platform = BinanceWeb3::PLATFORM;
            $network = ContractLibrary::MAIN_NETWORK;
        }

        $dataPost['platform'] = $platform;
        $dataPost['network'] = $network;

        if (strlen($dataPost['address'])) {
            $this->session->set('connected_wallet', $dataPost);
        } else {
            $this->session->remove('connected_wallet');
        }

        $data = ['status' => 1];
        return $this->setDataJson(BaseCollection::STATUS_INACTIVE, $data, 'Success');

    }
    /**
     * @throws Exception
     */
    public function transactionAction()
    {
        $dataGet = $this->getData;
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listContractType = ContractLibrary::listContractType();

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }
        if (strlen($dataGet['contract_type'])) {
            $conditions['contract_type'] = isset($listContractType[$dataGet['contract_type']]) ? $dataGet['contract_type'] : null;
        }

        if (strlen($dataGet['function'])) {
            $conditions['function'] = $dataGet['function'];
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }
        if (strlen($dataGet['address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['address']);
                $conditions['$or'] = [
                    ['from' => $filterAddress],
                    ['to' => $filterAddress],
                ];
            }
        }

        $limit = 20;
        $p = $dataGet['p'] ?? 1;
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $collection = $this->mongo->selectCollection('transaction');
        $count = $collection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $collection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'listPlatform', 'listNetwork', 'listContractType'));
    }

    public function transactionDetailAction($id)
    {
        $collection = $this->mongo->selectCollection('transaction');
        $object = $collection->findOne(['_id' => new ObjectId($id)]);
        $this->view->setVars(compact('object'));
    }

    public function resetScanTxAction()
    {
        try {
            $dataPost = $this->postData;
            if ($dataPost['id']) {
                $objId = new ObjectId($dataPost['id']);
                $transactionCollection = $this->mongo->selectCollection('transaction');
                $transactionCollection->updateOne(['_id' => $objId], ['$set' => ['is_process' => 0]]);
                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');
            }
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
    public function getConfigAddressAction()
    {
        try {
            $dataPost = $this->postData;
            $platform = strtolower($dataPost['platform']);
            $network = strtolower($dataPost['network']);
            $network = $network == ContractLibrary::MAIN_NETWORK ? ContractLibrary::MAIN_NETWORK : ContractLibrary::TEST_NETWORK;
            $type = $dataPost['type'] ?? ContractLibrary::PRESALE_FACTORY;

            $configAddress = ContractLibrary::getConfigAddress($platform, $network, $type);
            $data = [
                'address' => $configAddress
            ];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function mainTokenAction()
    {
        $dataGet = $this->getData;
        $platform = strtolower($dataGet['platform']);
        $network = strtolower($dataGet['network']);
        $key = "main_token_{$platform}_{$network}";
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $token = $registry[$key];
        $this->view->setVars(compact('token', 'dataGet'));
    }

    public function upsertMainTokenAction()
    {
        try {
            if (!$this->request->isPost()) {
                throw new Exception('Invalid method');
            }
            $dataPost = $this->postData;
            $platform = strtolower($dataPost['token_platform']);
            $network = strtolower($dataPost['token_network']);
            $tokenAddress = trim($dataPost['token_address']);
            $tokenDecimals = trim($dataPost['token_decimals']);
            $tokenName = trim($dataPost['token_name']);
            $tokenSymbol = trim($dataPost['token_symbol']);
            $tokenSupply = trim($dataPost['token_total_supply']);
            $tokenPrice = trim($dataPost['token_price']);
            $tokenIcon = trim($dataPost['token_icon']);
            if (!$platform || !$network || !$tokenAddress || !$tokenDecimals || !$tokenName || !$tokenSymbol || !is_numeric($tokenSupply) || !$tokenPrice || !$tokenIcon) {
                throw new Exception('Missing require params');
            }

            $key = "main_token_{$platform}_{$network}";
            $registryCollection = $this->mongo->selectCollection('registry');
            $registry = $registryCollection->findOne();
            if (!$registry) {
                throw new Exception('Not found registry');
            }

            $upsertMainToken = [
                'token_address' => $tokenAddress,
                'token_name' => $tokenName,
                'token_decimals' => $tokenDecimals,
                'token_symbol' => $tokenSymbol,
                'total_supply' => $tokenSupply,
                'token_price' => doubleval($tokenPrice),
                'token_icon' => $tokenIcon,
            ];
            $registryCollection->findOneAndUpdate(['_id' => $registry['_id']], ['$set' => [$key => $upsertMainToken]], ['upsert' => true]);
            return $this->returnBackRefURL('success', 'Success');
        } catch (Exception $exception) {
            return $this->returnBackRefURL('error', $exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function registryAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        if ($this->request->isPost()) {
            $dataPost = $this->postData;

            $dataUpdate = [
                'min_withdraw' => doubleval($dataPost['min_withdraw']),
                'fee_withdraw' => doubleval($dataPost['fee_withdraw']),
                'auto_withdraw_amount' => doubleval($dataPost['auto_withdraw_amount']),
                'min_staking' => doubleval($dataPost['min_staking']),
            ];
            $registryCollection->updateOne(['_id' => $registry['_id']], ['$set' => $dataUpdate], ['upsert' => true]);
            return $this->returnBackRefURL('success', 'Success');
        }
        $this->view->setVars(compact('registry'));

    }

}