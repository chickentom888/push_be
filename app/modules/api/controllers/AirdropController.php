<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class AirdropController extends ApiControllerBase
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
        $options = [
            'skip' => ($p - 1) * $limit,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];
        $conditions = [
            'user_address' => $this->credential->address
        ];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
        if (isset($dataGet['platform']) && $dataGet['platform']) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (isset($dataGet['network']) && $dataGet['network']) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['hash' => $dataGet['q']],
                ['token_name' => ['$regex' => $dataGet['q'], '$options' => 'i']],
                ['token_symbol' => ['$regex' => $dataGet['q'], '$options' => 'i']],
            ];
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['$or'][] = ['list_address' => ['$all' => [$dataGet['q']]]];
                $conditions['$or'][] = ['token_address' => $dataGet['q']];
            }
        }

        $airdropCollection = $this->mongo->selectCollection('airdrop');
        $count = $airdropCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $airdropCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        foreach ($listData as &$airdrop) {
            foreach ($airdrop['list_amount'] as $key => $item) {
                $address = $airdrop['list_address'][$key];
                $airdrop['list_amount'][$address] = $item;
                unset($airdrop['list_amount'][$key]);
            }
        }

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    public function detailAction($id)
    {
        $airdropCollection = $this->mongo->selectCollection('airdrop');
        $airdropId = new ObjectId($id);
        $dataGet = $this->getData;
        $data = $airdropCollection->findOne([
            '_id' => $airdropId,
            'user_address' => $this->credential->address
        ]);

        // <editor-fold desc = "search %like% address in array and return corresponding amount">
        if (strlen($address = $dataGet['address'])) {
            $data['list_address'] = array_filter($data['list_address'], function ($item) use ($address) {
                if (stripos($item, $address) !== false) {
                    return true;
                }
                return false;
            });

            $listAmount = [];
            foreach ($data['list_address'] as $key => $item) {
                $listAmount[] = $data['list_amount'][intval($key)];
            }

            $data['list_amount'] = $listAmount;
            $data['list_address'] = array_values($data['list_address']);
        }
        // </editor-fold>

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
    }
}
