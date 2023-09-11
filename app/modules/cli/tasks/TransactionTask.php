<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Collections\BaseCollection;
use Dcore\Collections\Transaction;
use Dcore\Collections\Users;
use Dcore\Library\BlockTaskLibrary;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Object\Account;
use DCrypto\Object\Send;
use Exception;
use Httpful\Exception\ConnectionErrorException;

class TransactionTask extends Web3Task
{

    public function initialize($param = [])
    {
        $this->start_time = microtime(true);
        $this->notify = false;
        global $config;
        $this->monitorLabel = $config->site->label;
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function minuteAction()
    {
//        echo "Check last time sync" . PHP_EOL;
//        $this->checkLastTimeSyncAction();
        echo "Process Tx" . PHP_EOL;
        $this->processTxAction();
        echo "Accept Withdraw" . PHP_EOL;
        $this->acceptWithdrawAction();
        echo "Check blockchain status" . PHP_EOL;
        $this->checkBlockchainStatusAction();
        echo "Check pending withdraw" . PHP_EOL;
        $this->checkPendingWithdrawAction();
    }

    /**
     * @throws Exception
     */
    public function processAction()
    {
        while (true) {
            $this->processTxAction();
            echo "Process Transaction. Date: " . date('d/m/Y H:i:s') . PHP_EOL;
            echo "=====" . PHP_EOL;
            sleep(1);
        }
    }

    /**
     * @throws Exception
     */
    public function processTxAction()
    {
        $transactionsCollection = $this->mongo->selectCollection('transaction');
        $presaleCollection = $this->mongo->selectCollection('presale');
        $poolCollection = $this->mongo->selectCollection('pool');
        $transaction = $this->getTransactionNotProcessed();
        if (!empty($transaction)) {
            $network = $transaction['network'];
            $platform = $transaction['platform'];
            $mainCurrency = Adapter::getMainCurrency($platform);
            /** @var BinanceWeb3 $coinInstance */
            $coinInstance = Adapter::getInstance($mainCurrency, $network);
            $this->web3 = $coinInstance;
            $abiFileName = $transaction['contract_type'];

            switch ($transaction['contract_type']) {
                case ContractLibrary::PRESALE:
                case ContractLibrary::SALE:
                    $presale = $presaleCollection->findOne(['contract_address' => $transaction['to']]);
                    if (strlen($presale['contract_version'])) {
                        $abiFileName .= "_" . $presale['contract_version'];
                    }
                    break;
                case ContractLibrary::POOL:
                    $pool = $poolCollection->findOne(['contract_address' => $transaction['to']]);
                    if (strlen($pool['contract_version'])) {
                        $abiFileName .= "_" . $pool['contract_version'];
                    }
                    break;
            }

            $dataDecode = $coinInstance->decodeFunctionInputData($transaction['input'], $abiFileName);
            $this->processFunction($transaction, $dataDecode);
            $transactionsCollection->updateOne(['_id' => $transaction['_id']], ['$set' => ['updated_at' => time(), 'is_process' => ContractLibrary::PROCESSED]]);
            echo "Process Done: " . $transaction['hash'] . PHP_EOL;
        }
    }


    protected function getTransactionNotProcessed()
    {
        $collection = $this->mongo->selectCollection('transaction');
        return $collection->findOne([
            'is_process' => ['$ne' => 1]
        ]);
    }

    /**
     * @throws Exception
     */
    public function checkLastTimeSyncAction()
    {
        $blockchainSyncCollection = $this->mongo->selectCollection('blockchain_sync');
        $listBlockchainSync = $blockchainSyncCollection->find();
        !empty($listBlockchainSync) && $listBlockchainSync = $listBlockchainSync->toArray();
        if (count($listBlockchainSync)) {
            foreach ($listBlockchainSync as $blockchainSync) {
                $lastTime = $blockchainSync['updated_at'];
                $minuteAgo = Helper::minutesAgo($lastTime);
                if ($minuteAgo > 5) {
                    $coinInstance = Adapter::getInstance($blockchainSync['key'], $blockchainSync['network']);
                    $blockchainInfo = $coinInstance->getBlockchainInfo();
                    $currentBlock = $blockchainInfo[0]['info']['headers'];
                    $lastedBlock = $blockchainSync['last_block'];
                    $missingBlock = $currentBlock - $lastedBlock;
                    $message = $this->monitorLabel . PHP_EOL;
                    $message .= strtoupper($blockchainSync['platform'] . " - " . $blockchainSync['network']) . PHP_EOL;
                    $message .= "Minutes not sync: " . $minuteAgo . PHP_EOL;
                    $message .= "Number block missed: " . $missingBlock . PHP_EOL;
                    $message .= "Last Block Sync: " . number_format($lastedBlock) . PHP_EOL;
                    $message .= "Current Block: " . number_format($currentBlock) . PHP_EOL;
                    $blockTaskData = [
                        'platform' => $blockchainSync['platform'],
                        'network' => $blockchainSync['network'],
                        'block' => $lastedBlock + 1,
                        'status' => BlockTaskLibrary::STATUS_NOT_PROCESS,
                        'version' => 1,
                        'created_at' => time(),
                        'processed_at' => null,
                    ];
                    $checkBlock = $this->mongo->selectCollection('block_task')->findOne([
                        'platform' => $blockchainSync['platform'],
                        'network' => $blockchainSync['network'],
                        'block' => $lastedBlock + 1
                    ]);
                    if (!$checkBlock) {
                        $this->mongo->selectCollection('block_task')->insertOne($blockTaskData);
                    }
                    Helper::sendTelegramMsgMonitor($message);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function acceptWithdrawAction()
    {
        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance('push_bsc', $network);
        global $config;
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $autoWithdrawAmount = doubleval($registry['auto_withdraw_amount']);

        $time = time() - 120;
        $options = [
            'skip' => 0,
            'limit' => 10,
            'sort' => ['_id' => -1]
        ];
        $withdrawCollection = $this->mongo->selectCollection('withdraw');
        $balanceLogCollection = $this->mongo->selectCollection('balance_log');
        $conditions = [
            'status' => BaseCollection::STATUS_PENDING,
            'created_at' => ['$lte' => $time]
        ];
        $autoWithdrawAmount > 0 && $conditions['amount'] = ['$lte' => $autoWithdrawAmount];
        $withdraw = $withdrawCollection->findOne($conditions);
        if (!$withdraw) {
            return;
        }
        $userConnect = Users::getUserById($withdraw['user_connect_id']);
        if ($userConnect['lock_withdraw'] == BaseCollection::STATUS_ACTIVE) {
            $message = 'Lock withdraw';
            $amount = $withdraw['amount'];
            Users::updateBalance($userConnect['_id'], BaseCollection::WALLET_COIN, $amount, BaseCollection::TYPE_WITHDRAW_REJECT, $message);

            $dataUpdate = [
                'message' => $message,
                'status' => BaseCollection::STATUS_REJECT,
                'process_at' => time()
            ];
            $withdrawCollection->updateOne(['_id' => $withdraw['_id']], ['$set' => $dataUpdate]);
            return;
        }

        /*$isContinue = false;
        $listBalanceLog = $balanceLogCollection->find([
            'user_connect_id' => $withdraw['user_connect_id'],
            'created_at' => ['$lte' => $withdraw['created_at']],
            'wallet' => BaseCollection::WALLET_COIN
        ], $options);
        !empty($listBalanceLog) && $listBalanceLog = $listBalanceLog->toArray();
        $countBalanceLog = count($listBalanceLog);
        if ($countBalanceLog >= 2) {
            foreach ($listBalanceLog as $balanceLogKey => $balanceLogItem) {
                if ($balanceLogKey != $countBalanceLog - 1) {
                    if ($balanceLogItem['before_amount'] != $listBalanceLog[$balanceLogKey + 1]['last_amount']) {
                        if ($userConnect) {
                            $message = 'Withdraw Rejected';
                            $amount = $withdraw['amount'];
                            Users::updateBalance($userConnect['_id'], BaseCollection::WALLET_COIN, $amount, BaseCollection::TYPE_WITHDRAW_REJECT, $message);
                        }
                        $dataUpdate = [
                            'message' => 'Balance log error',
                            'status' => BaseCollection::STATUS_REJECT,
                            'process_at' => time()
                        ];
                        $withdrawCollection->updateOne(['_id' => $withdraw['_id']], ['$set' => $dataUpdate]);
                        $isContinue = true;
                        break;
                    }
                }
            }
        }
        if ($isContinue) {
            return;
        }*/

        $privateKey = $config->blockchain['withdraw_private_key'];
        $fromAddress = $config->blockchain['withdraw_address'];

        $fromAccount = new Account();
        $fromAccount->address = $fromAddress;
        $fromAccount->private_key = $privateKey;
        $toAccount = new Account();
        $toAccount->address = $withdraw['user_address'];
        $sendObject = new Send();
        $sendObject->with_nonce = true;
        $amount = $withdraw['amount_after_fee'];
        $sendObject->amount = $amount;

        $sendObject = $coinInstance->send($fromAccount, $toAccount, $sendObject);
        $hash = $sendObject->hash;
        if (strlen($hash)) {
            $dataUpdate = [
                'status' => BaseCollection::STATUS_APPROVE,
                'process_at' => time(),
                'hash' => $hash,
                'message' => 'Success'
            ];
            $withdrawCollection->updateOne(['_id' => $withdraw['_id']], ['$set' => $dataUpdate]);

            Transaction::createBlockchainTransaction($fromAddress, $withdraw['user_address'], BaseCollection::ACTION_WITHDRAW, $sendObject);

            // <editor-fold desc="Notify">
            $message = "WITHDRAW ACCEPTED" . PHP_EOL;
            $message .= "Address: " . $withdraw['user_address'] . PHP_EOL;
            $message .= "Amount: " . Helper::numberFormat($amount, 8) . " " . $withdraw['ticker'] . PHP_EOL;
            /*$message .= "Hash: $hash" . PHP_EOL;
            $message .= $coinInstance->explorer_link['transaction'] . $hash . PHP_EOL;*/
            Helper::sendTelegramMsg($message);
            // </editor-fold>
        }
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function checkBlockchainStatusAction()
    {
        $time = strtotime('-1 minutes');
        $blockchainTransactionCollection = $this->mongo->selectCollection('blockchain_transaction');
        $withdrawCollection = $this->mongo->selectCollection('withdraw');
        $listData = $blockchainTransactionCollection->find([
            'status' => BaseCollection::STATUS_PENDING,
            'blockchain_status' => ContractLibrary::TRANSACTION_STATUS_PENDING,
            'created_at' => ['$lte' => $time]
        ]);
        !empty($listData) && $listData = $listData->toArray();
        if (count($listData)) {
            foreach ($listData as $item) {
                $hashData = $this->getDataFromHash($item['hash']);
                if ($hashData['status'] != null) {
                    if ($hashData['status'] != 1) {
                        $dataUpdate = [
                            'blockchain_status' => ContractLibrary::TRANSACTION_STATUS_FAIL,
                            'status' => BaseCollection::STATUS_REJECT,
                            'contract_address' => $hashData['contract_address'],
                            'timestamp' => $hashData['timestamp'],
                        ];
                    } else {
                        $dataUpdate = [
                            'blockchain_status' => ContractLibrary::TRANSACTION_STATUS_SUCCESS,
                            'status' => BaseCollection::STATUS_APPROVE,
                            'contract_address' => $hashData['contract_address'],
                            'timestamp' => $hashData['timestamp'],
                        ];
                    }
                    $blockchainTransactionCollection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
                    $withdraw = $withdrawCollection->findOne(['hash' => $item['hash']]);
                    if ($withdraw) {
                        $withdrawCollection->updateOne(['_id' => $withdraw['_id']], ['$set' => ['blockchain_status' => $dataUpdate['blockchain_status']]]);
                    }
                }
            }
        }
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    protected function getDataFromHash($hash)
    {
        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance('push_bsc', $network);
        $data = $coinInstance->getTransactionDetail($hash);
        $status = $coinInstance->getTransactionStatus($hash);
        if (empty($data)) {
            return [
                'from_address' => ContractLibrary::ADDRESS_ZERO,
                'to_address' => ContractLibrary::ADDRESS_ZERO,
                'contract_address' => ContractLibrary::ADDRESS_ZERO,
                'status' => $status,
                'timestamp' => null
            ];
        }

        $fromAddress = $coinInstance->toCheckSumAddress($data['from']);
        $contractAddress = $coinInstance->toCheckSumAddress($data['to']);

        $dataDecode = $coinInstance->decodeFunctionInputData($data['input'], ContractLibrary::TOKEN);
        if (empty($dataDecode)) {
            return [
                'from_address' => $fromAddress,
                'to_address' => ContractLibrary::ADDRESS_ZERO,
                'contract_address' => $contractAddress,
                'status' => $status,
                'timestamp' => null
            ];
        }
        $blockInfo = $coinInstance->getTransactionsByNumberBlock($data['blockNumber'], false);
        $timeStamp = $coinInstance->convertHex2Dec($blockInfo['timestamp']);

        $toAddress = $coinInstance->toCheckSumAddress($dataDecode['data_decode'][0]);
        return [
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'contract_address' => $contractAddress,
            'status' => $status,
            'timestamp' => $timeStamp
        ];
    }

    public function checkPendingWithdrawAction()
    {
        $time = strtotime('-2 minutes');
        $withdrawCollection = $this->mongo->selectCollection('withdraw');
        $conditions = [
            'status' => BaseCollection::STATUS_PENDING,
            'created_at' => ['$lte' => $time]
        ];
        $pendingWithdraw = $withdrawCollection->findOne($conditions);
        if ($pendingWithdraw) {
            // <editor-fold desc="Notify Telegram">
            $message = "PENDING WITHDRAW" . PHP_EOL;
            $message .= "Address: " . $pendingWithdraw['user_address'] . PHP_EOL;
            $message .= "Amount: " . Helper::numberFormat($pendingWithdraw['amount']) . PHP_EOL;
            $message .= "Created: " . date('d/m/Y H:i:s', $pendingWithdraw['created_at']) . PHP_EOL;
//            Helper::sendTelegramMsgMonitor($message);
            Helper::sendTelegramMsg($message);
            // </editor-fold>
        }
    }

}