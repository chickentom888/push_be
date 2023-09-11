<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Exception;
use Web3\Contract;

class MintTokenGeneratorContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Create Token
     * @param $transaction
     * @param $dataDecode
     * @throws Exception
     */
    public function processCreateToken($transaction, $dataDecode)
    {
        $coinInstance = $this->web3;
        $abiTokenMinted = ContractLibrary::getAbi(ContractLibrary::TOKEN_MINTED, 1);
        $tokenMintedCollection = $this->mongo->selectCollection('token_minted');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');

        $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
        $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;
        $functionName = ContractLibrary::FUNCTION_NAME;
        $functionSupply = ContractLibrary::FUNCTION_TOTAL_SUPPLY;

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::MINT_TOKEN_GENERATOR);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }

        $eventDataDecode = $eventLogData['data_decode'];
        $tokenAddress = $coinInstance->toCheckSumAddress($eventDataDecode[1]);
        $tokenFeeAmount = $eventDataDecode[3];

        $tokenInfo['hash'] = $transaction['hash'];
        $tokenInfo['contract_address'] = $tokenAddress;
        $tokenInfo['platform'] = $transaction['platform'];
        $tokenInfo['network'] = $transaction['network'];
        $tokenInfo['user_address'] = $transaction['from'];

        $tokenMinted = $tokenMintedCollection->findOne($tokenInfo);
        if (!empty($tokenMinted)) {
            $this->updateTransaction($transaction, $dataDecode);
            return;
        }
        $tokenInfo['creation_fee'] = $transaction['value'];
        $tokenInfo['created_at'] = $transaction['timestamp'];
        $userConnect = $userConnectCollection->findOne([
            'address' => $transaction['from']
        ]);
        if ($userConnect) {
            $tokenInfo['user_connect_id'] = $userConnect['_id'];
        }

        // <editor-fold desc = "Init Presale Contract Instance By Default Version">
        $tokenContract = new Contract($this->web3->rpcConnector->getProvider(), $abiTokenMinted);
        $tokenContractInstance = $tokenContract->at($tokenAddress);
        // </editor-fold>

        // <editor-fold desc = "Get General Info">
        $functionVersion = "VERSION";
        $tokenContractInstance->call($functionVersion, null, function ($err, $res) use (&$contractVersion, &$tokenInfo) {
            if ($res) {
                $contractVersion = intval($res[0]->toString());
                $tokenInfo['contract_version'] = $contractVersion;
            }
        });
        // </editor-fold>
        $tokenInfo['fee_amount'] = $tokenFeeAmount;

        // <editor-fold desc = "Re-init Contract Instance By Right Version">
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $tokenContract = new Contract($this->web3->rpcConnector->getProvider(), $abiToken);
        $tokenContractInstance = $tokenContract->at($tokenAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Token Info">
        $tokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$tokenInfo) {
            if ($res) {
                $tokenInfo['decimals'] = intval($res[0]->toString());
            }
        });
        $tokenContractInstance->call($functionName, null, function ($err, $res) use (&$tokenInfo) {
            if ($res) {
                $tokenInfo['name'] = $res[0];
            }
        });
        $tokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$tokenInfo) {
            if ($res) {
                $tokenInfo['symbol'] = $res[0];
            }
        });
        $tokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$tokenInfo) {
            if ($res) {
                $tokenInfo['total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $tokenInfo['decimals']))->toFloat();
            }
        });
        // </editor-fold>

        $tokenInfo['fee_amount'] = BigDecimal::of($tokenInfo['fee_amount'])->exactlyDividedBy(pow(10, $tokenInfo['decimals']))->toFloat();
        $tokenInfo['mint_token_generator_address'] = $transaction['to'];
        $tokenMintedCollection->insertOne($tokenInfo);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

    }
}
