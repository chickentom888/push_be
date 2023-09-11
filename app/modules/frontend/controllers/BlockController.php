<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Arrays;
use Dcore\Library\BlockTaskLibrary;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class BlockController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize($param);
    }

    public function blockTaskAction()
    {
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

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['status']);
        }

        if (strlen($dataGet['block'])) {
            $conditions['block'] = intval($dataGet['block']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $blockTaskCollection = $this->mongo->selectCollection('block_task');
        $listData = $blockTaskCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $blockTaskCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    public function createBlockTaskAction()
    {
        if ($this->request->isPost()) {
            $dataPost = $this->postData;
            $dataPost['status'] = BlockTaskLibrary::STATUS_NOT_PROCESS;
            $dataPost['created_at'] = time();
            $dataPost['block'] = intval($dataPost['block']);
            $this->mongo->selectCollection('block_task')->insertOne($dataPost);
            return $this->returnBackRefURL('success', 'Success');
        }

        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listPlatform', 'listNetwork'));
    }

    public function changeStatusBlockTaskAction($id, $status = BlockTaskLibrary::STATUS_NOT_PROCESS)
    {
        $blockTaskCollection = $this->mongo->selectCollection('block_task');
        $id = new ObjectId($id);
        $dataUpdate = ['status' => intval($status)];
        $blockTaskCollection->updateOne(['_id' => $id], ['$set' => $dataUpdate]);
        return $this->returnBackRefURL('success', 'Success');
    }

    public function blockInfoAction()
    {
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

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['network']);
        }

        if (strlen($dataGet['block'])) {
            $conditions['block'] = intval($dataGet['block']);
        }

        $sort = ['_id' => -1];

        if (strlen($dataGet['sort'])) {
            $sort = [$dataGet['sort'] => -1];
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => $sort
        ];
        $blockInfoCollection = $this->mongo->selectCollection('block_info');
        $listData = $blockInfoCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $blockInfoCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }

    public function deleteBlockTaskAction($id = null)
    {
        $blockTaskCollection = $this->mongo->selectCollection('block_task');
        if (strlen($id)) {
            $id = new ObjectId($id);
            $blockTaskCollection->deleteOne(['_id' => $id]);
        } else {
            if ($this->request->isPost()) {
                $dataPost = $this->request->getPost();
                $listId = $dataPost['id'];
                $listObjectId = [];
                if (count($listId)) {
                    foreach ($listId as $id) {
                        $listObjectId[] = new ObjectId($id);
                    }
                    $blockTaskCollection->deleteMany(['_id' => ['$in' => $listObjectId]]);
                }
            }
        }
        return $this->returnBackRefURL('success', 'Success');
    }

    public function deleteBlockInfoAction($id = null)
    {
        $blockInfoCollection = $this->mongo->selectCollection('block_info');
        if (strlen($id)) {
            $id = new ObjectId($id);
            $blockInfoCollection->deleteOne(['_id' => $id]);
        } else {
            if ($this->request->isPost()) {
                $dataPost = $this->request->getPost();
                $listId = $dataPost['id'];
                $listObjectId = [];
                if (count($listId)) {
                    foreach ($listId as $id) {
                        $listObjectId[] = new ObjectId($id);
                    }
                    $blockInfoCollection->deleteMany(['_id' => ['$in' => $listObjectId]]);
                }
            }
        }

        return $this->returnBackRefURL('success', 'Success');
    }

    /**
     * @throws ConnectionErrorException
     */
    public function scanMissBlockAction()
    {
        if ($this->request->isPost()) {
            $transactionCollection = $this->mongo->selectCollection('transaction');

            $dataPost = $this->postData;
            $network = $dataPost['network'];
            $platform = $dataPost['platform'];
            $tokenKey = Adapter::getMainCurrency($platform);

            $block = intval($dataPost['block']);
            $coinInstance = Adapter::getInstance($tokenKey, $network);

            $transactionCount = 0;
            $rs = $coinInstance->getTransactionsByNumberBlock($coinInstance->convertDec2Hex($block));
            $timeStamp = $coinInstance->convertHex2Dec($rs['timestamp']);
            $transactions = $rs['transactions'];
            if (count($transactions)) {
                foreach ($transactions as $transaction) {
                    $transaction = Arrays::arrayFrom($transaction);

                    if (!strlen($transaction['to'])) {
                        continue;
                    }

                    $fromAddress = strlen($transaction['from']) ? $coinInstance->toCheckSumAddress($transaction['from']) : '';
                    $toAddress = $coinInstance->toCheckSumAddress($transaction['to']);

                    $hash = strtolower($transaction['hash']);
                    $blockHash = strtolower($transaction['blockHash']);

                    $checkInListenAddress = $this->checkInListenAddress($network, $platform, $toAddress);
                    $inCondition = $checkInListenAddress['in_condition'];

                    if ($inCondition) {

                        // <editor-fold desc = "Check Exist Tx">
                        $isExistsHash = $transactionCollection->findOne([
                            'platform' => $platform,
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

            $message = "Block: $block. Tx: $transactionCount";
            return $this->returnBackRefURL('success', $message);
        }
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listPlatform', 'listNetwork'));
    }

    /**
     * @param $network
     * @param $platform
     * @param $toAddress
     * @return array
     */
    protected function checkInListenAddress($network, $platform, $toAddress)
    {
        $listConfigAddress = ContractLibrary::getListConfigAddressByNetworkAndPlatform($network, $platform);
        if (!empty($listConfigAddress)) {
            foreach ($listConfigAddress as $configAddress) {
                if ($configAddress['address'] == $toAddress && $configAddress['is_listen'] == ContractLibrary::ACTIVE) {
                    return [
                        'in_condition' => true,
                        'contract_type' => $configAddress['type']
                    ];
                }
            }
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            'network' => $network,
            'platform' => $this->platform,
            'contract_address' => $toAddress
        ]);
        if (!empty($presale)) {
            if ($presale['project_type'] == ContractLibrary::SALE) {
                return [
                    'in_condition' => true,
                    'contract_type' => ContractLibrary::SALE
                ];
            }
            return [
                'in_condition' => true,
                'contract_type' => ContractLibrary::PRESALE
            ];
        }

        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            'network' => $network,
            'platform' => $this->platform,
            'contract_address' => $toAddress
        ]);
        if (!empty($pool)) {
            return [
                'in_condition' => true,
                'contract_type' => ContractLibrary::POOL
            ];
        }

        return [
            'in_condition' => false,
            'contract_type' => null
        ];
    }

    /**
     * @throws Exception
     */
    public function blockchainSyncAction()
    {
        $collection = $this->mongo->selectCollection('blockchain_sync');
        $listData = $collection->find();
        !empty($listData) && $listData = $listData->toArray();
        if (count($listData)) {
            foreach ($listData as &$item) {
                $coinInstance = Adapter::getInstance($item['key'], $item['network']);
                $blockchainInfo = $coinInstance->getBlockchainInfo();
                $currentBlock = $blockchainInfo[0]['info']['headers'];
                $item['current_block'] = $currentBlock;
                $item['miss_block'] = $currentBlock - $item['scan_block'];
            }
        }

        $this->view->setVars(compact('listData'));
    }

    public function updateBlockchainSyncAction($id)
    {
        $blockchainSyncCollection = $this->mongo->selectCollection('blockchain_sync');
        $id = new ObjectId($id);
        $object = $blockchainSyncCollection->findOne(['_id' => $id]);
        if ($this->request->isPost()) {
            $dataPost = $this->postData;
            $blockchainSyncCollection->updateOne(['_id' => $id], ['$set' => $dataPost]);
            return $this->returnBackRefURL('success', 'Success');
        }
        $this->view->setVars(compact('object'));
    }

    /**
     * @throws Exception
     */
    public function transactionAction()
    {
        $dataGet = $this->getData;
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listAction = BaseCollection::listAction();

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['from_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['from_address'])) {
                $fromAddress = $coinInstance->toCheckSumAddress($dataGet['from_address']);
                $conditions['from_address'] = $fromAddress;
            } else {
                $conditions['from_address'] = -1;
            }
        }

        if (strlen($dataGet['to_address'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['to_address'])) {
                $toAddress = $coinInstance->toCheckSumAddress($dataGet['to_address']);
                $conditions['to_address'] = $toAddress;
            } else {
                $conditions['to_address'] = -1;
            }
        }

        if (strlen($dataGet['action'])) {
            $conditions['action'] = $dataGet['action'];
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['status']);
        }

        if (strlen($dataGet['blockchain_status'])) {
            $conditions['blockchain_status'] = intval($dataGet['blockchain_status']);
        }

        if (strlen($dataGet['nonce'])) {
            $conditions['nonce'] = intval($dataGet['nonce']);
        }

        $limit = 20;
        $p = $dataGet['p'] ?? 1;
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $collection = $this->mongo->selectCollection('blockchain_transaction');
        $count = $collection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $collection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();

        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'listPlatform', 'listNetwork', 'listAction'));
    }
}
