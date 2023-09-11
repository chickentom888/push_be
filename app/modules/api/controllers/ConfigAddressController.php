<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;

class ConfigAddressController extends ApiControllerBase
{

    /** @var BinanceWeb3|EthereumWeb3 */
    public $web3;

    public function initialize($param = null)
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $dataGet = $this->getData;
        $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 1000;
        $p = !isset($dataGet['p']) || $dataGet['p'] <= 1 ? 1 : intval($dataGet['p']);
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];

        $network = $dataGet['network'] ?? ContractLibrary::MAIN_NETWORK;
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $conditions = [
            'platform' => $platform,
            'network' => $network,
        ];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (strlen($dataGet['address'])) {
            if ($coinInstance->validAddress($dataGet['address'])) {
                $conditions['address'] = $coinInstance->toCheckSumAddress($dataGet['address']);
            }
        }
        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $count = $configAddressCollection->countDocuments($conditions);
        $listData = $configAddressCollection->find($conditions, $options);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        !empty($listData) && $listData = $listData->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    /**
     * @param $type
     * @return mixed
     */
    public function detailByTypeAction($type)
    {
        $dataGet = $this->getData;
        $network = $dataGet['network'] ?? ContractLibrary::MAIN_NETWORK;
        $platform = $dataGet['platform'] ?? BinanceWeb3::PLATFORM;
        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $configAddress = $configAddressCollection->findOne([
            'type' => trim($type),
            'network' => $network,
            'platform' => $platform,
        ]);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $configAddress, 'Success');
    }
}
