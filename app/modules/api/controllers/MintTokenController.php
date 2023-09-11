<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;

class MintTokenController extends ApiControllerBase
{

    /** @var BinanceWeb3|EthereumWeb3 */
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
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];
        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        $conditions['user_address'] = $this->credential->address;

        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['symbol' => $dataGet['q']]
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['$or'][] = ['contract_address' => $coinInstance->toCheckSumAddress($dataGet['q'])];
            }
        }

        $tokenMintedCollection = $this->mongo->selectCollection('token_minted');
        $count = $tokenMintedCollection->countDocuments($conditions);
        $listMinted = $tokenMintedCollection->find($conditions, $options);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        !empty($listMinted) && $listMinted = $listMinted->toArray();

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listMinted, 'Success', $pagingInfo);
    }
}
