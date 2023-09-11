<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Helper;
use MongoDB\BSON\ObjectId;

class SlideController extends ApiControllerBase
{
    public function initialize($param = null)
    {
        parent::initialize();
    }

    public function getListAction()
    {
        $dataGet = $this->getData;
        $limit = $dataGet['limit'] ?? 1000;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [];
        if (strlen($dataGet['title'])) {
            $conditions['title'] = ['$regex' => ".*" . $dataGet['title'] . ".*"];
        }
        if (strlen($dataGet['language'])) {
            $conditions['language'] = $dataGet['language'];
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

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }

    public function getDetailAction($id)
    {
        $slideCollection = $this->mongo->selectCollection('slide');
        $slideId = new ObjectId($id);
        $slide = $slideCollection->findOne(['_id' => $slideId]);

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $slide, 'Success');
    }

}
