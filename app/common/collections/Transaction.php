<?php

namespace Dcore\Collections;

use Dcore\Library\ContractLibrary;
use DCrypto\Object\Send;
use MongoDB\Database;
use Phalcon\Di;

class Transaction extends BaseCollection
{

    public function initialize()
    {
    }


    /**
     * @param $fromAddress
     * @param $toAddress
     * @param $action
     * @param Send $sendObject
     * @return void
     */
    public static function createBlockchainTransaction($fromAddress, $toAddress, $action, $sendObject)
    {
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $blockchainTransactionCollection = $mongo->selectCollection('blockchain_transaction');
        // <editor-fold desc="Blockchain Transaction">
        $blockchainTransactionData = [
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'amount' => $sendObject->amount,
            'tx_param' => $sendObject->tx_param,
            'nonce' => $sendObject->nonce,
            'signed_data' => $sendObject->signed_data,
            'hash' => $sendObject->hash,
            'gas_limit' => $sendObject->gas_limit,
            'gas_price' => $sendObject->gas_price,
            'network' => $sendObject->info['network'],
            'platform' => $sendObject->info['platform'],
            'action' => $action,
            'status' => BaseCollection::STATUS_PENDING,
            'blockchain_status' => ContractLibrary::TRANSACTION_STATUS_PENDING,
            'created_at' => time()
        ];
        $blockchainTransactionCollection->insertOne($blockchainTransactionData);
        // </editor-fold>

    }
}
