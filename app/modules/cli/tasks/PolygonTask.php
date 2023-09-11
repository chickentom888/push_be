<?php

namespace Dcore\Modules\Cli\Tasks;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\BlockTaskLibrary;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use DCrypto\Networks\PolygonWeb3;
use Exception;

class PolygonTask extends Web3Task
{
    public $tokenKey = PolygonWeb3::MAIN_CURRENCY;
    public $platform = PolygonWeb3::PLATFORM;

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    public function processTransactionAction($network = ContractLibrary::MAIN_NETWORK)
    {
        $blockchainSyncCollection = $this->mongo->selectCollection('blockchain_sync');
        $transactionCollection = $this->mongo->selectCollection('transaction');
        $blockInfoCollection = $this->mongo->selectCollection('block_info');
        $blockTaskCollection = $this->mongo->selectCollection('block_task');
        while (true) {
            $coinInstance = Adapter::getInstance($this->tokenKey, $network);

            $blockchainSync = $blockchainSyncCollection->findOne(['platform' => $coinInstance->platform, 'network' => $network]);
            $block = $blockchainSync['last_block'] + 1;

            $blockInfo = $blockInfoCollection->findOne([
                'block' => $block,
                'network' => $network,
                'platform' => $this->platform
            ]);

            if ($blockInfo) {
                $timeStamp = $coinInstance->convertHex2Dec($blockInfo['timestamp']);
                $listTransaction = $blockInfo['transactions'];
                $transactionCount = 0;
                if (count($listTransaction)) {
                    foreach ($listTransaction as $transaction) {
                        $transaction = Arrays::arrayFrom($transaction);

                        if (!strlen($transaction['to'])) {
                            continue;
                        }

                        $fromAddress = strlen($transaction['from']) ? $coinInstance->toCheckSumAddress($transaction['from']) : '';
                        $toAddress = $coinInstance->toCheckSumAddress($transaction['to']);

                        $hash = strtolower($transaction['hash']);
                        $blockHash = strtolower($transaction['blockHash']);
                        $checkInListenAddress = $this->checkInListenAddress($network, $toAddress);
                        $inCondition = $checkInListenAddress['in_condition'];
                        if ($inCondition) {

                            // <editor-fold desc = "Check Exist Tx">
                            $isExistsHash = $transactionCollection->findOne([
                                'platform' => $this->platform,
                                'network' => $network,
                                'hash' => $hash
                            ]);
                            if ($isExistsHash) {
                                continue;
                            }
                            // </editor-fold>

                            $txStatus = $coinInstance->getTransactionStatus($hash);
                            if ($txStatus != 1) {
                                continue;
                            }
                            $contractType = $checkInListenAddress['contract_type'];
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

                $blockInfoCollection->deleteMany([
                    'block' => ['$lte' => $block],
                    'platform' => $this->platform,
                    'network' => $network
                ]);
                $blockchainSyncUpdate['last_block'] = $block;
                $blockchainSyncUpdate['updated_at'] = time();
                $blockchainSyncCollection->updateOne(['_id' => $blockchainSync['_id']], ['$set' => $blockchainSyncUpdate]);
                $blockTaskCollection->deleteMany(['block' => ['$lte' => $block]]);
                echo "Block: $block. Tx: $transactionCount" . PHP_EOL;
            } else {
                $blockTaskData = [
                    'platform' => $blockchainSync['platform'],
                    'network' => $blockchainSync['network'],
                    'block' => $block,
                    'status' => BlockTaskLibrary::STATUS_NOT_PROCESS,
                    'version' => 1,
                    'created_at' => time(),
                    'processed_at' => null,
                ];
                $checkBlock = $this->mongo->selectCollection('block_task')->findOne([
                    'platform' => $blockchainSync['platform'],
                    'network' => $blockchainSync['network'],
                    'block' => $block
                ]);
                if (!$checkBlock) {
                    $this->mongo->selectCollection('block_task')->insertOne($blockTaskData);
                } else {
                    if ($checkBlock['status'] == BlockTaskLibrary::STATUS_PROCESSING && time() - $checkBlock['processed_at'] >= 30) {
                        $blockTaskCollection->updateOne([
                            'platform' => $blockchainSync['platform'],
                            'network' => $blockchainSync['network'],
                            'block' => $block
                        ], ['$set' => [
                            'status' => BlockTaskLibrary::STATUS_NOT_PROCESS,
                            'processed_at' => null
                        ]
                        ]);
                    }
                }
                echo "Not found block info: $block" . PHP_EOL;
            }

            echo "=====" . PHP_EOL;
            usleep(350000);
        }
    }

    /**
     * @throws Exception
     */
    public function createBlockTaskAction($network = ContractLibrary::MAIN_NETWORK)
    {
        $coinInstance = Adapter::getInstance($this->tokenKey, $network);
        $blockchainSyncCollection = $this->mongo->selectCollection('blockchain_sync');
        $blockTaskCollection = $this->mongo->selectCollection('block_task');
        $maxRangeNumber = 10;
        while (true) {
            $blockchainInfo = $coinInstance->getBlockchainInfo();
            $currentBlock = $blockchainInfo[0]['info']['headers'];
            if ($currentBlock > 0) {
                $blockchainSync = $blockchainSyncCollection->findOne(['platform' => $coinInstance->platform, 'network' => $network]);
                if (empty($blockchainSync)) {
                    $lastBlock = ($currentBlock - 1) > 0 ? ($currentBlock - 1) : $currentBlock;
                    $blockchainSync = [
                        'ticker' => $coinInstance->ticker,
                        'key' => $coinInstance->key,
                        'name' => $coinInstance->name,
                        'platform' => $coinInstance->platform,
                        'last_block' => $lastBlock,
                        'scan_block' => $lastBlock,
                        'network' => $network
                    ];
                    $blockchainSync['_id'] = $blockchainSyncCollection->insertOne($blockchainSync)->getInsertedId();
                }

                if (!isset($blockchainSync['scan_block_created'])) {
                    $blockchainSync['scan_block_created'] = $blockchainSync['last_block'];
                }

                $nextBlock = $blockchainSync['scan_block_created'] + 1;
                if ($nextBlock < $currentBlock) {
                    $listBlock = range($nextBlock, $currentBlock);
                    if (count($listBlock) > $maxRangeNumber) {
                        $listBlock = range($nextBlock, $blockchainSync['scan_block_created'] + $maxRangeNumber);
                    }
                    if (count($listBlock)) {
                        $listBlockTaskData = [];
                        foreach ($listBlock as $block) {
                            $listBlockTaskData[] = [
                                'platform' => $this->platform,
                                'network' => $network,
                                'block' => $block,
                                'status' => BlockTaskLibrary::STATUS_NOT_PROCESS,
                                'version' => 1,
                                'created_at' => time(),
                                'processed_at' => null,
                            ];
                            echo "Create  Block Task: $block" . PHP_EOL;
                        }
                        $blockTaskCollection->insertMany($listBlockTaskData);
                        $blockchainSyncUpdate['scan_block_created'] = end($listBlock);
                        $blockchainSyncCollection->updateOne(['_id' => $blockchainSync['_id']], ['$set' => $blockchainSyncUpdate]);
                    }
                }
            }
            echo date('d/m/Y H:i:s') . " - Sleep 1" . PHP_EOL;
            usleep(1000000);
        }
    }

    /**
     * @throws Exception
     */
    public function scanBlockAction($network = ContractLibrary::MAIN_NETWORK)
    {
        $coinInstance = Adapter::getInstance($this->tokenKey, $network);
        $blockchainSyncCollection = $this->mongo->selectCollection('blockchain_sync');
        $blockTaskCollection = $this->mongo->selectCollection('block_task');
        $blockInfoCollection = $this->mongo->selectCollection('block_info');
        $blockNumber = null;
        while (true) {
            $blockNotScan = $this->getBlockNotScan($blockNumber, $network);
            if ($blockNotScan) {
                $blockNotScanUpdate['status'] = BlockTaskLibrary::STATUS_PROCESSING;
                $blockNotScanUpdate['processed_at'] = time();
                $blockTaskCollection->updateOne(['_id' => $blockNotScan['_id']], ['$set' => $blockNotScanUpdate]);
                try {
                    $blockNumber = $blockNotScan['block'];
                    $rs = $coinInstance->getTransactionsByNumberBlock($coinInstance->convertDec2Hex($blockNumber));
                    if ($rs) {
                        $rs['block'] = $blockNumber;
                        $rs['network'] = $blockNotScan['network'];
                        $rs['platform'] = $blockNotScan['platform'];
                        $rs['created_at'] = time();
                        $blockInfoCollection->insertOne($rs);
                        $blockNotScanUpdate['status'] = BlockTaskLibrary::STATUS_PROCESSED;
                    } else {
                        $blockNotScanUpdate['status'] = BlockTaskLibrary::STATUS_NOT_PROCESS;
                    }
                    $blockNotScanUpdate['processed_at'] = time();
                    $blockTaskCollection->updateOne(['_id' => $blockNotScan['_id']], ['$set' => $blockNotScanUpdate]);
                    $blockchainSyncCollection->updateOne([
                        'network' => $blockNotScan['network'],
                        'platform' => $blockNotScan['platform'],
                    ], ['$set' => ['scan_block' => $blockNumber]]);
                    echo "Scan Block $blockNumber" . PHP_EOL;
                } catch (Exception $e) {
                    $blockNotScanUpdate['status'] = BlockTaskLibrary::STATUS_NOT_PROCESS;
                    $blockNotScanUpdate['processed_at'] = time();
                    $blockTaskCollection->updateOne(['_id' => $blockNotScan['_id']], ['$set' => $blockNotScanUpdate]);
                    echo "Loi o block thu $blockNumber + " . $e->getMessage();
                }
            } else {
                $blockNumber = null;
            }
            usleep(350000);
        }
    }

    protected function getBlockNotScan($block = null, $network = ContractLibrary::MAIN_NETWORK)
    {
        $conditions = [
            'platform' => $this->platform,
            'network' => $network,
            'status' => BlockTaskLibrary::STATUS_NOT_PROCESS
        ];
        if (strlen($block)) {
            $conditions['block'] = ['$ne' => intval($block)];
        }
        $blockData = $this->mongo->selectCollection('block_task')->aggregate([
            [
                '$match' => $conditions
            ],
            [
                '$sample' => [
                    'size' => 1
                ]
            ]
        ])->toArray();
        return count($blockData) ? $blockData[0] : [];
    }

    public function updateTokenAthAction()
    {
        try {
            $cacheKey = 'update_token_ath';
            $syncingStatus = $this->redis->get($cacheKey);
            if ($syncingStatus == 1) {
                echo "ERROR: Another instance is running..." . PHP_EOL;
                return;
            }
            $this->redis->set($cacheKey, 1, 60 * 60);

            $tokenCollection = $this->mongo->selectCollection('tokens');
            $listToken = $tokenCollection->find([
                'network' => ContractLibrary::MAIN_NETWORK
            ]);

            foreach ($listToken as $token) {
                $data = $this->getCoinGeckoInfo([
                    'address' => $token['address'],
                    'platform' => $token['platform'],
                ]);
                if (!empty($data['market_data']['ath']['usd'])) {
                    $ath = BigDecimal::of($data['market_data']['ath']['usd'])->toFloat();
                    $tokenCollection->updateOne(['_id' => $token['_id']], [
                        '$set' => ['ath' => $ath]
                    ]);
                }
            }

        } catch (Exception $exception) {
            echo "[ERROR] " . $exception->getMessage() . "\r\n";
            $this->redis->set($cacheKey, 0);
        }
    }
}