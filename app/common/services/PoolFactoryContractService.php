<?php

namespace Dcore\Services;

use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class PoolFactoryContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processUpdatePoolGeneratorByTransaction($transaction, $dataDecode)
    {
        $poolFactoryAddress = $transaction['to'];
        $this->updatePoolGenerator($poolFactoryAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function updatePoolGenerator($poolFactoryAddress)
    {
        $poolGeneratorCollection = $this->mongo->selectCollection('pool_generator');
        $configAddressCollection = $this->mongo->selectCollection('config_address');

        $network = $this->network;
        $platform = $this->platform;
        $coinInstance = $this->web3;
        $abiPoolFactory = ContractLibrary::getAbi(ContractLibrary::POOL_FACTORY);
        $contractPoolFactory = new Contract($coinInstance->rpcConnector->getProvider(), $abiPoolFactory);
        $contractPoolFactoryInstance = $contractPoolFactory->at($poolFactoryAddress);

        $poolGeneratorLength = 0;
        $functionPoolGeneratorsLength = 'poolGeneratorsLength';
        $contractPoolFactoryInstance->call($functionPoolGeneratorsLength, null, function ($err, $res) use (&$poolGeneratorLength) {
            if ($res) {
                $poolGeneratorLength = intval($res[0]->toString());
            }
        });

        $dataPoolGenerator = [
            'network' => $network,
            'platform' => $platform,
            'pool_factory_address' => $poolFactoryAddress
        ];
        $dataPoolGeneratorConfigAddress = [
            'network' => $network,
            'platform' => $platform,
            'type' => ContractLibrary::POOL_GENERATOR,
            'factory_address' => $poolFactoryAddress
        ];

        $poolGeneratorCollection->deleteMany($dataPoolGenerator);
        $configAddressCollection->deleteMany($dataPoolGeneratorConfigAddress);
        $dataPoolGeneratorConfigAddress['is_listen'] = ContractLibrary::ACTIVE;

        if ($poolGeneratorLength > 0) {
            $listPoolGeneratorAddress = [];
            $functionPoolGeneratorAtIndex = 'poolGeneratorAtIndex';
            for ($i = 0; $i < $poolGeneratorLength; $i++) {
                $contractPoolFactoryInstance->call($functionPoolGeneratorAtIndex, $i, function ($err, $res) use (&$listPoolGeneratorAddress, $coinInstance) {
                    if ($res) {
                        $listPoolGeneratorAddress[] = $coinInstance->toCheckSumAddress($res[0]);
                    }
                });
            }

            //<editor-fold desc="cập nhật lại pool_generator_address mới trong pool_generator và config_address">
            $listDataPoolGenerator = [];
            $listDataPoolGeneratorConfigAddress = [];
            foreach ($listPoolGeneratorAddress as $poolGeneratorAddress) {
                $dataPoolGenerator['pool_generator_address'] = $poolGeneratorAddress;
                $listDataPoolGenerator[] = $dataPoolGenerator;

                $dataPoolGeneratorConfigAddress['address'] = $poolGeneratorAddress;
                $dataPoolGeneratorConfigAddress['created_at'] = time();
                $listDataPoolGeneratorConfigAddress[] = $dataPoolGeneratorConfigAddress;
            }
            $poolGeneratorCollection->insertMany($listDataPoolGenerator);
            $configAddressCollection->insertMany($listDataPoolGeneratorConfigAddress);
            //</editor-fold>
        }
    }
}
