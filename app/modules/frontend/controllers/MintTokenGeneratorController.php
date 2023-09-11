<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class MintTokenGeneratorController extends ExtendedControllerBase
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
    public function indexAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['q'])) {
            $mainCurrency = Adapter::getMainCurrency($dataGet['platform']);
            $coinInstance = Adapter::getInstance($mainCurrency ?? BinanceWeb3::MAIN_CURRENCY);
            $conditions['address'] = $coinInstance->toCheckSumAddress($dataGet['q']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $mintTokenGeneratorCollection = $this->mongo->selectCollection('mint_token_generator');
        $listData = $mintTokenGeneratorCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $mintTokenGeneratorCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    public function formAction()
    {
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listPlatform', 'listNetwork'));
    }

    public function getMintTokenFactoryAddressAction()
    {
        try {
            $dataPost = $this->postData;
            $platform = $dataPost['platform'];
            $network = $dataPost['network'] == ContractLibrary::MAIN_NETWORK ? ContractLibrary::MAIN_NETWORK : ContractLibrary::TEST_NETWORK;

            $mintTokenFactoryAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::MINT_TOKEN_FACTORY);
            $data = [
                'mint_token_factory_address' => $mintTokenFactoryAddress
            ];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}
