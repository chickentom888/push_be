<?php

namespace Dcore\Services;

use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class SaleFactoryContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processUpdateSaleGeneratorByTransaction($transaction, $dataDecode)
    {
        $saleFactoryAddress = $transaction['to'];
        $this->updateSaleGenerator($saleFactoryAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function updateSaleGenerator($saleFactoryAddress)
    {
        $saleGeneratorCollection = $this->mongo->selectCollection('sale_generator');
        $configAddressCollection = $this->mongo->selectCollection('config_address');

        $network = $this->network;
        $platform = $this->platform;
        $coinInstance = $this->web3;
        $abiSaleFactory = ContractLibrary::getAbi(ContractLibrary::SALE_FACTORY);
        $contractSaleFactory = new Contract($coinInstance->rpcConnector->getProvider(), $abiSaleFactory);
        $contractSaleFactoryInstance = $contractSaleFactory->at($saleFactoryAddress);

        $saleGeneratorLength = 0;
        $functionSaleGeneratorsLength = 'saleGeneratorsLength';
        $contractSaleFactoryInstance->call($functionSaleGeneratorsLength, null, function ($err, $res) use (&$saleGeneratorLength) {
            if ($res) {
                $saleGeneratorLength = intval($res[0]->toString());
            }
        });

        $dataSaleGenerator = [
            'network' => $network,
            'platform' => $platform,
            'sale_factory_address' => $saleFactoryAddress
        ];
        $dataSaleGeneratorConfigAddress = [
            'network' => $network,
            'platform' => $platform,
            'type' => ContractLibrary::SALE_GENERATOR,
            'factory_address' => $saleFactoryAddress
        ];

        $saleGeneratorCollection->deleteMany($dataSaleGenerator);
        $configAddressCollection->deleteMany($dataSaleGeneratorConfigAddress);
        $dataSaleGeneratorConfigAddress['is_listen'] = ContractLibrary::ACTIVE;

        if ($saleGeneratorLength > 0) {
            $listSaleGeneratorAddress = [];
            $functionSaleGeneratorAtIndex = 'saleGeneratorAtIndex';
            for ($i = 0; $i < $saleGeneratorLength; $i++) {
                $contractSaleFactoryInstance->call($functionSaleGeneratorAtIndex, $i, function ($err, $res) use (&$listSaleGeneratorAddress, $coinInstance) {
                    if ($res) {
                        $listSaleGeneratorAddress[] = $coinInstance->toCheckSumAddress($res[0]);
                    }
                });
            }

            //<editor-fold desc="cập nhật lại sale_generator_address mới trong presale_generator và config_address">
            $listDataSaleGenerator = [];
            $listDataSaleGeneratorConfigAddress = [];
            foreach ($listSaleGeneratorAddress as $saleGeneratorAddress) {
                $dataSaleGenerator['sale_generator_address'] = $saleGeneratorAddress;
                $listDataSaleGenerator[] = $dataSaleGenerator;

                $dataSaleGeneratorConfigAddress['address'] = $saleGeneratorAddress;
                $dataSaleGeneratorConfigAddress['created_at'] = time();
                $listDataSaleGeneratorConfigAddress[] = $dataSaleGeneratorConfigAddress;
            }
            $saleGeneratorCollection->insertMany($listDataSaleGenerator);
            $configAddressCollection->insertMany($listDataSaleGeneratorConfigAddress);
            //</editor-fold>
        }
    }
}
