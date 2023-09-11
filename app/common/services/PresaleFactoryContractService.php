<?php

namespace Dcore\Services;

use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class PresaleFactoryContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Update Presale Generator
     * @throws Exception
     */
    public function processUpdatePresaleGeneratorByTransaction($transaction, $dataDecode)
    {
        $presaleFactoryAddress = $transaction['to'];

        $this->updatePresaleGenerator($presaleFactoryAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function updatePresaleGenerator($presaleFactoryAddress)
    {
        $network = $this->network;
        $platform = $this->platform;
        $presaleGeneratorCollection = $this->mongo->selectCollection('presale_generator');
        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $coinInstance = $this->web3;
        $abiPresaleFactory = ContractLibrary::getAbi(ContractLibrary::PRESALE_FACTORY);
        $contractPresaleFactory = new Contract($coinInstance->rpcConnector->getProvider(), $abiPresaleFactory);
        $contractPresaleFactoryInstance = $contractPresaleFactory->at($presaleFactoryAddress);

        $presaleGeneratorLength = 0;
        $functionPresaleGeneratorsLength = 'presaleGeneratorsLength';
        $contractPresaleFactoryInstance->call($functionPresaleGeneratorsLength, null, function ($err, $res) use (&$presaleGeneratorLength) {
            if ($res) {
                $presaleGeneratorLength = intval($res[0]->toString());
            }
        });

        $dataPresaleGenerator = [
            'network' => $network,
            'platform' => $platform,
            'presale_factory_address' => $presaleFactoryAddress
        ];
        $presaleGeneratorCollection->deleteMany($dataPresaleGenerator);

        $dataPresaleGeneratorConfigAddress = [
            'network' => $network,
            'platform' => $platform,
            'type' => ContractLibrary::PRESALE_GENERATOR,
            'factory_address' => $presaleFactoryAddress
        ];
        $configAddressCollection->deleteMany($dataPresaleGeneratorConfigAddress);
        $dataPresaleGeneratorConfigAddress['is_listen'] = ContractLibrary::ACTIVE;

        if ($presaleGeneratorLength > 0) {
            $listPresaleGeneratorAddress = [];
            $functionPresaleGeneratorAtIndex = 'presaleGeneratorAtIndex';
            for ($i = 0; $i < $presaleGeneratorLength; $i++) {
                $contractPresaleFactoryInstance->call($functionPresaleGeneratorAtIndex, $i, function ($err, $res) use (&$listPresaleGeneratorAddress, $coinInstance) {
                    if ($res) {
                        $listPresaleGeneratorAddress[] = $coinInstance->toCheckSumAddress($res[0]);
                    }
                });
            }

            //<editor-fold desc="cập nhật lại presale_generator_address mới trong presale_generator và config_address">
            $listDataPresaleGenerator = [];
            $listDataPresaleGeneratorConfigAddress = [];
            foreach ($listPresaleGeneratorAddress as $presaleGeneratorAddress) {
                $dataPresaleGenerator['presale_generator_address'] = $presaleGeneratorAddress;
                $listDataPresaleGenerator[] = $dataPresaleGenerator;

                $dataPresaleGeneratorConfigAddress['address'] = $presaleGeneratorAddress;
                $dataPresaleGeneratorConfigAddress['created_at'] = time();
                $listDataPresaleGeneratorConfigAddress[] = $dataPresaleGeneratorConfigAddress;
            }
            $presaleGeneratorCollection->insertMany($listDataPresaleGenerator);
            $configAddressCollection->insertMany($listDataPresaleGeneratorConfigAddress);
            //</editor-fold>
        }
    }
}
