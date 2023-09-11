<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;
use Dcore\Services\StakingService;
use Exception;

class RegistryTask extends Web3Task
{
    /**
     * @throws Exception
     */
    public function importMasterDataAction()
    {
        $this->updateRateAction();
        $this->importStakingSettingAction();
    }

    public function updateRateAction()
    {
        $dataUpdate = [];
        $priceBNB = ContractLibrary::getPriceBNB();
        if ($priceBNB > 0) {
            $dataUpdate['bnb_rate'] = $priceBNB;
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
    public function importStakingSettingAction()
    {
        $type = ContractLibrary::STAKING;
        $listSaleFactory = ContractLibrary::getAddressByType(null, null, $type);
        if (count($listSaleFactory)) {
            foreach ($listSaleFactory as $item) {
                $network = $item['network'];
                $platform = $item['platform'];
                $address = $item['address'];

                $stakingService = StakingService::getInstance($network, $platform);
                $stakingService->updateStakingSetting($address);
            }
        }
    }
}
