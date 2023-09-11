<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class ConfigAddressController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize($param);
    }

    /**
     * @throws ConnectionErrorException
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

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['type'])) {
            $conditions['type'] = $dataGet['type'];
        }

        if (strlen($dataGet['q'])) {
            $conditions['$or'] = [
                ['type' => ['$regex' => $dataGet['q'], '$options' => 'i']]
            ];
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['q'])) {
                $conditions['$or'][] = ['address' => $coinInstance->toCheckSumAddress($dataGet['q'])];
            }
        }

        if (strlen($dataGet['is_listen'])) {
            $conditions['is_listen'] = intval($dataGet['is_listen']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];
        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $count = $configAddressCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $configAddressCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listConfigAddress = ContractLibrary::getListTypeConfigAddress();

        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count', 'listConfigAddress'));
    }

    /**
     * @param null $id
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function formAction($id = null)
    {
        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listConfigAddress = ContractLibrary::getListTypeConfigAddress();
        $object = [];
        if ($id) {
            $objId = new ObjectId($id);
            $object = $configAddressCollection->findOne(['_id' => $objId]);
        }

        if ($this->request->isPost()) {
            $data = $this->postData;
            if (!strlen($data['network']) ||
                !strlen($data['platform']) ||
                !strlen($data['address']) ||
                !strlen($data['type']) ||
                !in_array($data['type'], array_keys($listConfigAddress))) {
                return $this->returnBackRefURL('error', 'Something went wrong');
            }

            $dataUpdate = [];
            $dataUpdate['network'] = $data['network'];
            $dataUpdate['platform'] = $data['platform'];
            $dataUpdate['type'] = trim($data['type']);
            $dataUpdate['is_listen'] = isset($data['is_listen']) && $data['is_listen'] == ContractLibrary::LISTEN ? ContractLibrary::LISTEN : 0;
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if (!$coinInstance->validAddress($data['address'])) {
                return $this->returnBackRefURL('error', 'This address is not valid');
            }
            $conditions = $dataUpdate;
            $dataUpdate['address'] = $coinInstance->toCheckSumAddress($data['address']);
            if (isset($objId)) {
                $conditions['_id'] = ['$ne' => $objId];
            }

            $listTypeGenerator = [
                ContractLibrary::PRESALE_GENERATOR,
                ContractLibrary::MINT_TOKEN_GENERATOR,
                ContractLibrary::SALE_GENERATOR,
            ];

            if (!in_array($dataUpdate['type'], $listTypeGenerator)) {
                $checkExists = $configAddressCollection->countDocuments($conditions);
                if ($checkExists) {
                    return $this->returnBackRefURL('error', 'This type already exists!');
                }

                $dataUpdate['factory_address'] = '';
                $dataUpdate['description'] = '';
            } else {
                if (!strlen($data['factory_address']) ||
                    !$coinInstance->validAddress($data['factory_address'])) {
                    return $this->returnBackRefURL('error', 'This address is not valid');
                }
                $dataUpdate['factory_address'] = $coinInstance->toCheckSumAddress($data['factory_address']);
                $dataUpdate['description'] = $data['description'] ?? '';
                $conditions['address'] = $dataUpdate['address'];
                $conditions['factory_address'] = $dataUpdate['factory_address'];

                $checkExists = $configAddressCollection->countDocuments($conditions);
                if ($checkExists) {
                    return $this->returnBackRefURL('error', 'This type already exists!');
                }
            }

            if ($dataUpdate) {
                if (isset($objId)) {
                    $dataUpdate['updated_at'] = time();
                    $configAddressCollection->updateOne(['_id' => $objId], ['$set' => $dataUpdate]);
                } else {
                    $dataUpdate['created_at'] = time();
                    $configAddressCollection->insertOne($dataUpdate);
                }
                return $this->returnBackRefURL('success', 'Success', '/config_address');
            }

            return $this->returnBackRefURL('error', 'Something went wrong');
        }

        $this->view->setVars(compact('object', 'listPlatform', 'listNetwork', 'listConfigAddress'));
    }

    public function deleteAction($id)
    {
        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $objId = new ObjectId($id);
        $configAddressCollection->deleteOne(['_id' => $objId]);

        return $this->returnBackRefURL('success', 'Success');
    }

}
