<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use DCrypto\Networks\EthereumWeb3;

class EthereumTask extends Web3Task
{
    public $tokenKey = EthereumWeb3::MAIN_CURRENCY;
    public $platform = EthereumWeb3::PLATFORM;

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    public function scanBlockAction($network = ContractLibrary::MAIN_NETWORK)
    {
        while (true) {
            $blockchainSyncCollection = $this->mongo->selectCollection('blockchain_sync');
            $transactionCollection = $this->mongo->selectCollection('transaction');
            $presaleCollection = $this->mongo->selectCollection('presale');
            $presaleGeneratorCollection = $this->mongo->selectCollection('presale_generator');
            $listPresaleGenerator = $presaleGeneratorCollection->find([
                'network' => $network,
                'platform' => $this->platform
            ]);
            $listPresaleGeneratorAddress = empty($listPresaleGenerator) ? [] : $listPresaleGenerator->toArray();

            $listPresaleFactoryAddress = ContractLibrary::getAddressByType($this->platform, $network, ContractLibrary::PRESALE_FACTORY);
            $listPresaleSettingAddress = ContractLibrary::getAddressByType($this->platform, $network, ContractLibrary::PRESALE_SETTING);

            $coinInstance = Adapter::getInstance($this->tokenKey, $network);
            $blockchainInfo = $coinInstance->getBlockchainInfo();
            $currentBlock = $blockchainInfo[0]['info']['headers'];

            $blockchainSync = $blockchainSyncCollection->findOne(['platform' => $coinInstance->platform, 'network' => $network]);

            if (empty($blockchainSync)) {
                $lastBlock = ($currentBlock - 1) > 0 ? ($currentBlock - 1) : $currentBlock;
                $blockchainSync = [
                    'ticker' => $coinInstance->ticker,
                    'key' => $coinInstance->key,
                    'name' => $coinInstance->name,
                    'platform' => $coinInstance->platform,
                    'last_block' => $lastBlock,
                    'network' => $network
                ];
                $blockchainSync['_id'] = $blockchainSyncCollection->insertOne($blockchainSync)->getInsertedId();
            }

            if ($blockchainSync['last_block'] > $currentBlock) {
                echo "Lasted block greater than current block" . PHP_EOL;
                continue;
            }
            if ($blockchainSync['last_block'] <= 0) $blockchainSync['last_block'] = $currentBlock;

            $listBlock = range($blockchainSync['last_block'] + 1, $currentBlock);
            $lsBlockToScan = $this->redis->get($this->platform . "_" . $network . "_block_scan");
            $lsBlockToScan = json_decode($lsBlockToScan, true);
            if (isset($lsBlockToScan) && count($lsBlockToScan)) {
                foreach ($lsBlockToScan as $blockScan) {
                    array_unshift($listBlock, $blockScan);
                }
                $this->redis->set($this->platform . "_" . $network . "_block_scan", json_encode([]));
            }

            echo date("d-m-Y H:i:s") . PHP_EOL;
            echo "Total Block: " . count($listBlock) . PHP_EOL;
            echo "From: " . $blockchainSync['last_block'] . PHP_EOL;
            echo "To: " . $currentBlock . PHP_EOL;

            foreach ($listBlock as $block) {
                $transactionCount = 0;
                $rs = $coinInstance->getTransactionsByNumberBlock($coinInstance->convertDec2Hex($block));
                if (count($rs)) {
                    $timeStamp = $coinInstance->convertHex2Dec($rs['timestamp']);
                    $transactions = $rs['transactions'];

                    if (count($transactions)) {
                        foreach ($transactions as $transaction) {
                            $transaction = Arrays::arrayFrom($transaction);

                            $fromAddress = strlen($transaction['from']) ? $coinInstance->toCheckSumAddress($transaction['from']) : '';
                            $toAddress = strlen($transaction['to']) ? $coinInstance->toCheckSumAddress($transaction['to']) : '';

                            $presale = $presaleCollection->findOne([
                                'network' => $network,
                                'platform' => $this->platform,
                                'contract_address' => $toAddress
                            ]);

                            $hash = strtolower($transaction['hash']);
                            $blockHash = strtolower($transaction['blockHash']);
                            $inPresaleGenerator = in_array($toAddress, $listPresaleGeneratorAddress);
                            $inPresaleSetting = in_array($toAddress, $listPresaleSettingAddress);
                            $inPresaleFactory = in_array($toAddress, $listPresaleFactoryAddress);

                            if ($inPresaleGenerator || $inPresaleSetting || $inPresaleFactory || !empty($presale)) {

                                // <editor-fold desc = "Check Exist Tx">
                                $isExistsHash = $transactionCollection->findOne(['hash' => $hash]);
                                if ($isExistsHash) {
                                    continue;
                                }
                                // </editor-fold>

                                $txStatus = $coinInstance->getTransactionStatus($hash);
                                if ($txStatus != 1) {
                                    continue;
                                }

                                if ($inPresaleGenerator) {
                                    $contractType = ContractLibrary::PRESALE_GENERATOR;
                                } else if ($inPresaleSetting) {
                                    $contractType = ContractLibrary::PRESALE_SETTING;
                                } else if ($inPresaleFactory) {
                                    $contractType = ContractLibrary::PRESALE_FACTORY;
                                } else {
                                    $contractType = ContractLibrary::PRESALE;
                                }

                                $value = $coinInstance->convertHex2Dec($transaction['value']);
                                $value = $value / pow(10, $coinInstance->decimals);

                                $dataTransaction = [
                                    'block_hash' => $blockHash,
                                    'block_number' => $block,
                                    'from' => $fromAddress,
                                    'to' => $toAddress,
                                    'hash' => $transaction['hash'],
                                    'input' => $transaction['input'],
                                    'value' => $value,
                                    'network' => $network,
                                    'platform' => $coinInstance->platform,
                                    'timestamp' => $timeStamp,
                                    'created_at' => time(),
                                    'is_process' => 0,
                                    'contract_type' => $contractType
                                ];
                                $transactionCollection->insertOne($dataTransaction);
                                $transactionCount++;
                            }
                        }
                    }
                }

                $blockchainSyncUpdate['last_block'] = $block;
                $blockchainSyncUpdate['updated_at'] = time();
                $blockchainSyncCollection->updateOne(['_id' => $blockchainSync['_id']], ['$set' => $blockchainSyncUpdate]);
                echo "Block: $block. Tx: $transactionCount" . PHP_EOL;
            }
            echo "=====" . PHP_EOL;
            sleep(1);
        }

    }

}