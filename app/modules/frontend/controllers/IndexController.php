<?php

namespace Dcore\Modules\Frontend\Controllers;

use Brick\Math\BigDecimal;
use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use Dcore\Library\Swap;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use DCrypto\Networks\PolygonWeb3;
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

    public function createPresaleAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $setting = $registry['presale_setting_bsc_test'];
        $this->view->setVars(compact('setting'));
    }

    public function listPresaleAction()
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $listData = $presaleCollection->find([], ['sort' => ['_id' => -1], 'limit' => 20]);
        if ($listData) {
            $listData = $listData->toArray();
        }
        $this->view->setVars(compact('listData'));
    }

    public function presaleDetailAction($id)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleBuyLogCollection = $this->mongo->selectCollection('presale_buy_log');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');
        $presaleWhitelistCollection = $this->mongo->selectCollection('presale_whitelist');
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');

        $id = new ObjectId($id);
        $presale = $presaleCollection->findOne(['_id' => $id]);
        $listPresaleBuyLog = $presaleBuyLogCollection->find([
            'presale_address' => $presale['contract_address']
        ], ['sort' => ['_id' => -1]]);

        $listPresaleUserLog = $presaleUserLogCollection->find([
            'presale_address' => $presale['contract_address']
        ], ['sort' => ['_id' => -1]]);

        $listPresaleWhitelist = $presaleWhitelistCollection->find([
            'presale_address' => $presale['contract_address']
        ], ['sort' => ['_id' => -1]]);

        $listPresaleUserZeroRound = $presaleUserZeroRoundCollection->find([
            'presale_address' => $presale['contract_address']
        ], ['sort' => ['_id' => -1]]);

        $registry = $this->mongo->selectCollection('registry')->findOne();
        $this->view->setVars(compact('presale', 'listPresaleBuyLog', 'listPresaleUserLog', 'listPresaleWhitelist', 'listPresaleUserZeroRound', 'registry'));
    }

    public function codeAction()
    {
        $this->view->setMainView('');
        $auth = $this->getAuth();
        if ($this->request->isPost()) {
            $dataPost = $this->postData;
            $userService = new UserService($this);
            $message = $userService->verifyCodeAdmin($dataPost);
            if ($message == "Ok") {
                $this->flash->success($message);
                return $this->response->redirect('/dashboard/index');
            } else {
                $this->flash->error($message);
                return $this->response->redirect('/dashboard/code');
            }
        }

        $this->createCSRFMethod2($auth->id);
    }

    public function qrAction($str)
    {
        $this->view->disable();
        $link = "https://chart.googleapis.com/chart?cht=qr&chl=" . urlencode($str) . "&chs=160x160&chld=L|0";
        return $this->response->redirect($link);
    }

    public function selectedAddressAction()
    {
        $dataPost = $this->postData;
        $ethChainId = [
            "1" => ContractLibrary::MAIN_NETWORK,
            "3" => ContractLibrary::TEST_NETWORK,
            "4" => ContractLibrary::TEST_NETWORK,
            "5" => ContractLibrary::TEST_NETWORK,
            "42" => ContractLibrary::TEST_NETWORK,
        ];
        $bscChainId = [
            "56" => ContractLibrary::MAIN_NETWORK,
            "97" => ContractLibrary::TEST_NETWORK,
        ];
        $polygonChainId = [
            "137" => ContractLibrary::MAIN_NETWORK,
            "80001" => ContractLibrary::TEST_NETWORK,
        ];

        $listChainIdEth = array_keys($ethChainId);
        $listChainIdBsc = array_keys($bscChainId);
        $listChainIdPolygon = array_keys($polygonChainId);
        $chainId = $dataPost['chain_id'];

        if (in_array($chainId, $listChainIdEth)) {
            $platform = EthereumWeb3::PLATFORM;
            $network = $ethChainId[$chainId];
        } else if (in_array($chainId, $listChainIdBsc)) {
            $platform = BinanceWeb3::PLATFORM;
            $network = $bscChainId[$chainId];
        } else if (in_array($chainId, $listChainIdPolygon)) {
            $platform = PolygonWeb3::PLATFORM;
            $network = $polygonChainId[$chainId];
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

    public function createTokenAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $setting = $registry['mint_token_setting_bsc_test'];
        $this->view->setVars(compact('setting'));
    }

    public function airdropAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $setting = $registry['airdrop_setting_bsc_test'];
        $this->view->setVars(compact('setting'));
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

    public function createSaleAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $setting = $registry['sale_setting_bsc_test'];
        $this->view->setVars(compact('setting'));
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

    public function createPoolAction()
    {
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $setting = $registry['pool_setting_bsc_test'];
        $this->view->setVars(compact('setting'));
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

    public function swapAction()
    {

    }

    public function getQuoteAction()
    {
        try {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            $dataGet = $this->getData;
            $sellAmount = $dataGet['sell_amount'] ?? 1;
            $buyAmount = $dataGet['buy_amount'] ?? 1;
            $sellToken = strtolower(trim($dataGet['sell_token'] ?? ''));
            $buyToken = strtolower(trim($dataGet['buy_token'] ?? ''));
            if (!strlen($sellToken)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid sell token');
            }

            if (!$coinInstance->validAddress($sellToken) && $sellToken != BinanceWeb3::MAIN_CURRENCY) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid sell token');
            }

            if (!strlen($buyToken)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid buy token');
            }

            if (!$coinInstance->validAddress($buyToken) && $buyToken != BinanceWeb3::MAIN_CURRENCY) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid sell token');
            }

            if ($coinInstance->validAddress($sellToken)) {
                $sellToken = $coinInstance->toCheckSumAddress($sellToken);
            }

            if ($coinInstance->validAddress($buyToken)) {
                $buyToken = $coinInstance->toCheckSumAddress($buyToken);
            }

            $inputType = 'sell';
            if (isset($sellAmount) && $sellAmount > 0) {
                $inputType = 'sell';
                unset($buyAmount);
            }
            if (isset($buyAmount) && $buyAmount > 0) {
                $inputType = 'buy';
                unset($sellAmount);
            }
            $swap = new Swap();

            $data = [
                'sellToken' => $sellToken,
                'buyToken' => $buyToken,
                'sellAmount' => $sellAmount ?? null,
                'buyAmount' => $buyAmount ?? null,
            ];
            $dataQuote = $swap->getQuote($data);
            $totalOutput = 0;
            $totalAdjustedOutput = 0;
            $listOrders = $dataQuote['orders'];
            if (count($listOrders)) {
                foreach ($listOrders as $order) {
                    $fillData = $order['fill'];
                    $totalOutput = BigDecimal::of($totalOutput)->plus(BigDecimal::of($fillData['output']));
                    $totalAdjustedOutput = BigDecimal::of($totalAdjustedOutput)->plus(BigDecimal::of($fillData['adjustedOutput']));
                }
            }

            $dataResponse['input'] = $inputType == 'sell' ? $sellAmount : $buyAmount;
            $dataResponse['output'] = strval($totalOutput);
            $dataResponse['adjusted_output'] = strval($totalAdjustedOutput);
            $dataResponse['to_address'] = $coinInstance->toCheckSumAddress($dataQuote['to']);
            $dataResponse['spender_address'] = $coinInstance->toCheckSumAddress($dataQuote['allowanceTarget']);
            $dataResponse['data'] = $dataQuote['data'];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success');

        } catch (Exception $exception) {
            $dataResponse = ['error' => 'Error'];
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, $dataResponse, 'Success');
        }
    }

}