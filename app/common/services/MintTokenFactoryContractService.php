<?php

namespace Dcore\Services;

use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class MintTokenFactoryContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Update Token Generator
     * @throws Exception
     */
    public function processUpdateMintTokenGenerator($transaction, $dataDecode)
    {
        $mintTokenFactoryAddress = $transaction['to'];

        $this->updateMintTokenGenerator($mintTokenFactoryAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function updateMintTokenGenerator($mintTokenFactoryAddress)
    {
        $network = $this->network;
        $platform = $this->platform;
        $mintTokenGeneratorCollection = $this->mongo->selectCollection('mint_token_generator');
        $configAddressCollection = $this->mongo->selectCollection('config_address');
        $coinInstance = $this->web3;
        $abiMintTokenFactory = ContractLibrary::getAbi(ContractLibrary::MINT_TOKEN_FACTORY);
        $contractMintTokenFactory = new Contract($coinInstance->rpcConnector->getProvider(), $abiMintTokenFactory);
        $contractMintTokenFactoryInstance = $contractMintTokenFactory->at($mintTokenFactoryAddress);

        $tokenGeneratorLength = 0;
        $functionTokenGeneratorsLength = 'tokenGeneratorsLength';
        $contractMintTokenFactoryInstance->call($functionTokenGeneratorsLength, null, function ($err, $res) use (&$tokenGeneratorLength) {
            if ($res) {
                $tokenGeneratorLength = intval($res[0]->toString());
            }
        });

        $dataMintTokenGenerator = [
            'network' => $network,
            'platform' => $platform,
            'mint_token_factory_address' => $mintTokenFactoryAddress
        ];
        $mintTokenGeneratorCollection->deleteMany($dataMintTokenGenerator);

        $dataMintTokenGeneratorConfigAddress = [
            'network' => $network,
            'platform' => $platform,
            'type' => ContractLibrary::MINT_TOKEN_GENERATOR,
            'factory_address' => $mintTokenFactoryAddress
        ];
        $configAddressCollection->deleteMany($dataMintTokenGeneratorConfigAddress);
        $dataMintTokenGeneratorConfigAddress['is_listen'] = ContractLibrary::ACTIVE;
        if ($tokenGeneratorLength > 0) {
            $listTokenGeneratorAddress = [];
            $functionTokenGeneratorAtIndex = 'tokenGeneratorAtIndex';
            for ($i = 0; $i < $tokenGeneratorLength; $i++) {
                $contractMintTokenFactoryInstance->call($functionTokenGeneratorAtIndex, $i, function ($err, $res) use (&$listTokenGeneratorAddress, $coinInstance) {
                    if ($res) {
                        $listTokenGeneratorAddress[] = $coinInstance->toCheckSumAddress($res[0]);
                    }
                });
            }
            //<editor-fold desc="Cập nhật lại mint generator address mới trong presale_generator và config_address">
            $configAddressCollection->deleteMany($dataMintTokenGeneratorConfigAddress);
            $listDataTokenGenerator = [];
            $listDataTokenGeneratorConfigAddress = [];
            foreach ($listTokenGeneratorAddress as $tokenGeneratorAddress) {
                $dataMintTokenGenerator['mint_token_generator_address'] = $tokenGeneratorAddress;
                $listDataTokenGenerator[] = $dataMintTokenGenerator;

                $dataMintTokenGeneratorConfigAddress['address'] = $tokenGeneratorAddress;
                $dataMintTokenGeneratorConfigAddress['created_at'] = time();
                $listDataTokenGeneratorConfigAddress[] = $dataMintTokenGeneratorConfigAddress;
            }
            $mintTokenGeneratorCollection->insertMany($listDataTokenGenerator);
            $configAddressCollection->insertMany($listDataTokenGeneratorConfigAddress);
            //</editor-fold>
        }
    }
}
