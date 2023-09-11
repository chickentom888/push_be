<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\Helper;
use DCrypto\Adapter;
use MongoDB\BSON\ObjectId;

class SlideController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize();
    }

    public function indexAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $listLanguage = Adapter::listLanguage();
        $conditions = [];
        if (strlen($dataGet['title'])) {
            $conditions['title'] = ['$regex' => ".*" . $dataGet['title'] . ".*"];
        }
        if (strlen($dataGet['language'])) {
            $conditions['language'] = $listLanguage[strtolower($dataGet['language'])] ?? $listLanguage['en'];
        }
        if (strlen($dataGet['group'])) {
            $conditions['group'] = $dataGet['group'];
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $slideCollection = $this->mongo->selectCollection('slide');
        $count = $slideCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $slideCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();

        $this->view->setVars(compact('listData', 'pagingInfo', 'listLanguage', 'dataGet'));
    }

    public function formAction($id = null)
    {
        $slideCollection = $this->mongo->selectCollection('slide');
        $listLanguage = Adapter::listLanguage();
        if ($id) {
            $slideId = new ObjectId($id);
            $object = $slideCollection->findOne(['_id' => $slideId]);
        } else {
            $object = [];
        }
        if ($this->request->isPost()) {
            $data = $this->postData;
            $dataUpdate = [];
            if ($data['title'] && !empty($data['title'])) {
                $dataUpdate['title'] = $data['title'];
            }
            if ($data['language'] && !empty($data['language'])) {
                $dataUpdate['language'] = $data['language'];
            }
            if ($data['link'] && !empty($data['link'])) {
                $dataUpdate['link'] = $data['link'];
            }
            if ($data['url_img'] && !empty($data['url_img'])) {
                $dataUpdate['url_img'] = $data['url_img'];
            }
            if ($data['group'] && !empty($data['group'])) {
                $dataUpdate['group'] = $data['group'];
            }
            if ($slideId && $dataUpdate) {
                $dataUpdate['updated_at'] = time();
                $slideCollection->updateOne(['_id' => $slideId], ['$set' => $dataUpdate]);
            } else {
                $dataUpdate['created_at'] = time();
                $slideCollection->insertOne($dataUpdate);
            }

            return $this->returnBackRefURL('success', 'Success', '/slide');
        }

        $this->view->setVars(compact('object', 'listLanguage'));
    }

    public function deleteAction($id)
    {
        $slideCollection = $this->mongo->selectCollection('slide');
        $slideId = new ObjectId($id);
        $slideCollection->deleteOne(['_id' => $slideId]);

        return $this->returnBackRefURL('success', 'Success');
    }

}
