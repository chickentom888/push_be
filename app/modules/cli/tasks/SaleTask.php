<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;
use Dcore\Services\SaleContractService;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class SaleTask extends TaskBase
{
    protected string $channel = ContractLibrary::RPUB_PRESALE_CHANGE;

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    /**
     * @throws Exception
     * @var BinanceWeb3 $coinInstance
     */
    public function updateStatusAction()
    {
        $listSaleChanged = [];
        $listSale = $this->getListSaleUpdateStatus();
        if (empty($listSale)) {
            return;
        }

        foreach ($listSale as $sale) {
            $network = $sale['network'];
            $platform = $sale['platform'];

            $saleService = SaleContractService::getInstance($network, $platform);
            $saleUpdate = $saleService->updateStatusByABI($sale, ContractLibrary::SALE);

            if ($saleUpdate) {
                $listSaleChanged[(string)$sale['_id']] = $saleUpdate;
            }
        }

        if (count($listSaleChanged)) {
            $this->redis->publish($this->channel, json_encode(array_values($listSaleChanged)));
        }
    }

    private function getListSaleUpdateStatus()
    {
        $conditions = [
            'start_time' => ['$lte' => time()],
            'current_status' => ['$in' => [ContractLibrary::PRESALE_STATUS_PENDING, ContractLibrary::PRESALE_STATUS_ACTIVE]],
            'project_type' => ContractLibrary::SALE
        ];
        $presaleCollection = $this->mongo->selectCollection('presale');
        $listPresale = $presaleCollection->find($conditions);
        if (!empty($listPresale)) {
            return $listPresale->toArray();
        }
        return [];
    }
}
