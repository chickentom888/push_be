<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use Exception;
use MongoDB\BSON\ObjectId;

class ExchangePlatformController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize($param);
    }

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

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $exchangePlatformCollection = $this->mongo->selectCollection('exchange_platform');
        $listData = $exchangePlatformCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $exchangePlatformCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    public function formAction($id = null)
    {
        $exchangePlatformCollection = $this->mongo->selectCollection('exchange_platform');
        if ($id) {
            $exchangePlatformId = new ObjectId($id);
            $object = $exchangePlatformCollection->findOne(['_id' => $exchangePlatformId]);
        } else {
            $object = [];
        }
        if ($this->request->isPost()) {
            $data = $this->postData;
            if ($data['exchange_key'] != $object['exchange_key']) {
                $conditions = ['exchange_key' => $data['exchange_key']];
                if ($id) {
                    $conditions['_id'] = ['$ne' => $exchangePlatformId];
                }
                $checkExchangeKey = $exchangePlatformCollection->findOne($conditions);
                if ($checkExchangeKey) {
                    return $this->returnBackRefURL('error', 'Exchange key exists');
                }
            }
            if ($exchangePlatformId) {
                $exchangePlatformCollection->updateOne(['_id' => $exchangePlatformId], ['$set' => $data]);
            } else {
                $exchangePlatformCollection->insertOne($data);
            }

            return $this->returnBackRefURL('success', 'Success', '/exchange_platform');
        }
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('object', 'listPlatform', 'listNetwork'));
    }

    public function deleteAction($id)
    {
        $exchangePlatformCollection = $this->mongo->selectCollection('exchange_platform');
        $exchangePlatformId = new ObjectId($id);
        $exchangePlatformCollection->deleteOne(['_id' => $exchangePlatformId]);
        return $this->returnBackRefURL('success', 'Success');
    }

    /**
     * Get factory address by type
     * @return mixed
     */
    public function getFactoryAddressAction()
    {
        try {
            $dataPost = $this->postData;
            $platform = $dataPost['platform'];
            $network = $dataPost['network'] == ContractLibrary::MAIN_NETWORK ? ContractLibrary::MAIN_NETWORK : ContractLibrary::TEST_NETWORK;
            $type = $dataPost['type'] ?? ContractLibrary::PRESALE_FACTORY;

            $presaleFactoryAddress = ContractLibrary::getConfigAddress($platform, $network, $type);
            $data = [
                'factory_address' => $presaleFactoryAddress
            ];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}
