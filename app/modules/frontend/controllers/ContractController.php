<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class ContractController extends ExtendedControllerBase
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

        if (strlen($dataGet['address'])) {
            $conditions['address'] = $dataGet['address'];
        }

        if (strlen($dataGet['contract_key'])) {
            $conditions['contract_key'] = $dataGet['contract_key'];
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['created_at' => -1]
        ];
        $contractCollection = $this->mongo->selectCollection('contract');
        $count = $contractCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $contractCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();

        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    /**
     * @param null $id
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function formAction($id = null)
    {
        $contractCollection = $this->mongo->selectCollection('contract');
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listConfigAddress = ContractLibrary::getListTypeConfigAddress();
        $object = [];
        if ($id) {
            $objId = new ObjectId($id);
            $object = $contractCollection->findOne(['_id' => $objId]);
        }

        if ($this->request->isPost()) {
            $dataPost = $this->postData;
            if (!strlen($dataPost['network']) ||
                !strlen($dataPost['platform']) ||
                !strlen($dataPost['address']) ||
                !strlen($dataPost['contract_key'])
            ) {
                return $this->returnBackRefURL('error', 'Something went wrong');
            }

            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if (!$coinInstance->validAddress($dataPost['address'])) {
                return $this->returnBackRefURL('error', 'This address is not valid');
            }

            $conditions['network'] = $dataPost['network'];
            $conditions['platform'] = $dataPost['platform'];
            $conditions['address'] = $coinInstance->toCheckSumAddress($dataPost['address']);
            if (isset($objId)) {
                $conditions['_id'] = ['$ne' => $objId];
            }

            $checkExists = $contractCollection->countDocuments($conditions);
            if ($checkExists) {
                return $this->returnBackRefURL('error', 'This address already exists!');
            }

            if ($dataPost) {
                if (isset($objId)) {
                    $dataPost['updated_at'] = time();
                    $contractCollection->updateOne(['_id' => $objId], ['$set' => $dataPost]);
                } else {
                    $dataPost['created_at'] = time();
                    $contractCollection->insertOne($dataPost);
                }
                return $this->returnBackRefURL('success', 'Success', '/contract');
            }

            return $this->returnBackRefURL('error', 'Something went wrong');
        }

        $this->view->setVars(compact('object', 'listPlatform', 'listNetwork', 'listConfigAddress'));
    }

    public function deleteAction($id)
    {
        $contractCollection = $this->mongo->selectCollection('contract');
        $objId = new ObjectId($id);
        $contractCollection->deleteOne(['_id' => $objId]);
        return $this->returnBackRefURL('success', 'Success');
    }

}
