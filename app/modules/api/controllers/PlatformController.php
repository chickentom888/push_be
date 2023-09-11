<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Helper;
use Exception;

class PlatformController extends ApiControllerBase
{
    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws Exception
     */
    public function listExchangePlatformAction()
    {
        if ($this->request->isPost()) {
            return false;
        }
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

        return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listData, 'Success', $pagingInfo);
    }
}
