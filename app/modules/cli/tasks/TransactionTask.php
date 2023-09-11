<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Collections\BaseCollection;
use Dcore\Collections\Transaction;
use Dcore\Collections\Users;
use Dcore\Library\ContractLibrary;
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
        echo "Process Tx" . PHP_EOL;
        $this->processTxAction();
        echo "Accept Withdraw" . PHP_EOL;
        $this->acceptWithdrawAction();
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
        $transaction = $this->getTransactionNotProcessed();
        if (!empty($transaction)) {
            $network = $transaction['network'];
            $platform = $transaction['platform'];
            $mainCurrency = Adapter::getMainCurrency($platform);
            /** @var BinanceWeb3 $coinInstance */
            $coinInstance = Adapter::getInstance($mainCurrency, $network);
            $this->web3 = $coinInstance;
            $abiFileName = $transaction['contract_type'];

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
    public function acceptWithdrawAction()
    {
        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance('push_bsc', $network);
        global $config;
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        $autoWithdrawAmount = doubleval($registry['auto_withdraw_amount']);

        $time = time() - 120;
        $withdrawCollection = $this->mongo->selectCollection('withdraw');
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
        }
    }


}