<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;

class RateTask extends TaskBase
{
    public function updateAction()
    {
        $dataUpdate = [];
        $priceBNB = ContractLibrary::getPriceBNB();
        if ($priceBNB > 0) {
            $dataUpdate['bnb_price'] = $priceBNB;
        }

        if (count($dataUpdate)) {
            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();

            if ($registry) {
                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $collection->insertOne($dataUpdate);
            }
        }


    }
}
