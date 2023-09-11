<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use Web3\Contract;

class AirdropContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Airdrop
     * @throws Exception
     */
    public function processAirdrop($transaction, $dataDecode)
    {
        $airdropCollection = $this->mongo->selectCollection('airdrop');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $airdropContractAddress = $transaction['to'];

        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::AIRDROP_CONTRACT);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }

        $eventDataDecode = $eventLogData['data_decode'];
        $tokenType = $eventDataDecode[1];
        $feeAmount = $eventDataDecode[4] / pow(10, $coinInstance->decimals);

        $airdropInfo = [
            'user_address' => $transaction['from'],
            'hash' => $transaction['hash'],
            'token_type' => $tokenType,
            'fee_amount' => $feeAmount,
            'created_at' => $transaction['timestamp'],
            'network' => $network,
            'platform' => $platform
        ];
        // <editor-fold desc="Check Airdrop">
        $airdrop = $airdropCollection->findOne($airdropInfo);
        if (!empty($airdrop)) {
            $this->updateTransaction($transaction, $dataDecode);
            return;
        }
        // </editor-fold>
        // <editor-fold desc="Find User Connect">
        $userConnect = $userConnectCollection->findOne([
            'address' => $airdropInfo['user_address']
        ]);
        if (!empty($userConnect)) {
            $airdropInfo['user_connect_id'] = $userConnect['_id'];
        }
        // </editor-fold>

        if ($tokenType == 'main') {
            $airdropInfo['list_address'] = $dataDecode['data_decode'][0];
            $airdropInfo['token_address'] = $mainCurrency;
            $airdropInfo['token_name'] = $coinInstance->name;
            $airdropInfo['token_decimals'] = $coinInstance->decimals;
            $airdropInfo['token_symbol'] = strtoupper($mainCurrency);
            $listAmountInput = $dataDecode['data_decode'][1];
        } else {
            $tokenAddress = $dataDecode['data_decode'][0];
            $airdropInfo['token_address'] = $tokenAddress;
            $airdropInfo['list_address'] = $dataDecode['data_decode'][1];
            $listAmountInput = $dataDecode['data_decode'][2];

            $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
            $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;
            $functionName = ContractLibrary::FUNCTION_NAME;

            $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
            $tokenContract = new Contract($this->web3->rpcConnector->getProvider(), $abiToken);

            $tokenContractInstance = $tokenContract->at($tokenAddress);
            $tokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$airdropInfo) {
                if ($res) {
                    $airdropInfo['token_decimals'] = intval($res[0]->toString());
                }
            });
            $tokenContractInstance->call($functionName, null, function ($err, $res) use (&$airdropInfo) {
                if ($res) {
                    $airdropInfo['token_name'] = $res[0];
                }
            });
            $tokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$airdropInfo) {
                if ($res) {
                    $airdropInfo['token_symbol'] = $res[0];
                }
            });
        }

        foreach ($listAmountInput as $item) {
            $airdropInfo['list_amount'][] = BigDecimal::of($item->toString())->exactlyDividedBy(pow(10, $airdropInfo['token_decimals']))->toFloat();
        }

        $airdropInfo['total_token_amount'] = array_sum($airdropInfo['list_amount']);
        $airdropInfo['airdrop_contract_address'] = $airdropContractAddress;

        $this->mongo->selectCollection('airdrop')->insertOne($airdropInfo);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }
}
