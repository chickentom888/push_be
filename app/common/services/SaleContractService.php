<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Exception;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class SaleContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Buy Token
     * @throws Exception
     */
    public function processBuyTokenOfContractSale($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleBuyLogCollection = $this->mongo->selectCollection('presale_buy_log');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');

        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleAddress = $sale['contract_address'];
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        $abiName = ContractLibrary::SALE . "_" . $sale['contract_version'];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $saleAddress) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Sale BuyToken: Invalid Event Data Tx: " . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $baseTokenAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();
        $saleTokenAmount = BigDecimal::of($eventDataDecode[2])->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();

        // <editor-fold desc = "Init Sale Contract Instance">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$saleInfo, $sale) {
            if ($res) {
                $saleInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();
                $saleInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
                $saleInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $saleInfo['success_at'] = intval($res['successAt']->toString());
                $saleInfo['current_status'] = intval($res['currentStatus']->toString());
                $saleInfo['is_active_claim'] = $res['isActiveClaim'];
                $saleInfo['active_claim_at'] = intval($res['activeClaimAt']->toString());
                $saleInfo['current_round'] = intval($res['currentRound']->toString());
                $saleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $saleInfo['current_round'] = -1;

                if ($saleInfo['success_at'] > 0) {
                    $saleInfo['message'] = 'Sale reached soft cap';
                }
                if ($saleInfo['total_base_collected'] >= $sale['hard_cap']) {
                    $saleInfo['message'] = 'Sale reached hard cap';
                }
            }
        });
        // </editor-fold>

        $saleInfo['token_sold_percent'] = $saleInfo['total_token_sold'] / $sale['amount'] * 100;
        $userAddress = $transaction['from'];

        // <editor-fold desc = "Sale Buy Log">
        $saleBuyLog = $presaleBuyLogCollection->findOne([
            'hash' => $transaction['hash'],
            'network' => $sale['network'],
            'platform' => $sale['platform'],
            'presale_address' => $sale['contract_address'],
            'user_address' => $userAddress
        ]);
        if (empty($saleBuyLog)) {
            $dataSaleBuyLog = [
                'network' => $sale['network'],
                'platform' => $sale['platform'],
                'user_address' => $userAddress,
                'presale_address' => $sale['contract_address'],
                'project_type' => $sale['project_type'],
                'sale_type' => $sale['sale_type'],
                'hash' => $transaction['hash'],
                'base_token_amount' => $baseTokenAmount,
                'sale_token_amount' => $saleTokenAmount,
                'round' => $saleInfo['current_round'],
                'created_at' => $transaction['timestamp']
            ];
            $presaleBuyLogCollection->insertOne($dataSaleBuyLog);
        }
        // </editor-fold>

        // <editor-fold desc = "Get Buyer Info">
        $buyerInfo = [];
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_BUYER_INFO, $userAddress, function ($err, $res) use (&$buyerInfo, $sale) {
            if ($res) {
                $buyerInfo['base_token_amount'] = BigDecimal::of($res['baseDeposited']->toString())->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();
                $buyerInfo['sale_token_amount'] = BigDecimal::of($res['tokenBought']->toString())->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Sale User">
        $dataSaleUser = [
            'network' => $sale['network'],
            'platform' => $sale['platform'],
            'user_address' => $userAddress,
            'presale_address' => $saleAddress
        ];
        $saleUser = $presaleUserLogCollection->findOne($dataSaleUser);
        if (!$saleUser) {
            $dataSaleUser['base_token_amount'] = $buyerInfo['base_token_amount'];
            $dataSaleUser['sale_token_amount'] = $buyerInfo['sale_token_amount'];
            $dataSaleUser['project_type'] = $sale['project_type'];
            $dataSaleUser['sale_type'] = $sale['sale_type'];

            if (isset($sale['active_vesting']) && $sale['active_vesting'] == ContractLibrary::SALE_ACTIVE_VESTING) {
                if (!isset($sale['list_vesting_period']) || empty($sale['list_vesting_period'])
                    || !isset($sale['list_vesting_percent']) || empty($sale['list_vesting_percent'])
                    || count($sale['list_vesting_period']) != count($sale['list_vesting_percent'])) {
                    throw new Exception("Invalid vesting data. Contract Address" . $sale['contract_address']);
                }
                $dataSaleUser['active_vesting'] = $sale['active_vesting'];
                foreach ($sale['list_vesting_period'] as $key => $vestingTime) {
                    $dataSaleUser['list_vesting'][] = [
                        'vesting_period' => $vestingTime,
                        'vesting_percent' => $sale['list_vesting_percent'][$key],
                        'vesting_number' => $key + 1,
                        'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                    ];
                }
            }
            $dataSaleUser['withdraw_status'] = ContractLibrary::NOT_WITHDRAW;
            $presaleUserLogCollection->insertOne($dataSaleUser);
        } else {
            $presaleUserLogCollection->updateOne(['_id' => $saleUser['_id']], ['$set' => $buyerInfo]);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * @throws Exception
     */
    public function processSaleActiveClaim($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }
        $saleAddress = $sale['contract_address'];
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $saleAddress) {
                    $abiType = ContractLibrary::SALE . "_" . $sale['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Sale ActiveClaim: Invalid Event Data Tx: " . $transaction['hash']);
        }
        $remainingSaleTokenBalance = BigDecimal::of($eventLogData['data_decode'][3])->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
        $saleInfo['remaining_sale_token'] = $remainingSaleTokenBalance;
        // <editor-fold desc = "Init Sale Contract Instance">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$saleInfo, $sale) {
            if ($res) {
                $saleInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();
                $saleInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
                $saleInfo['refund_token'] = $saleInfo['remaining_sale_token'] - $saleInfo['total_token_sold'];
                $saleInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
                $saleInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
                $saleInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $saleInfo['success_at'] = intval($res['successAt']->toString());
                $saleInfo['is_active_claim'] = $res['isActiveClaim'];
                $saleInfo['current_status'] = intval($res['currentStatus']->toString());
                $saleInfo['active_claim_at'] = intval($res['activeClaimAt']->toString());
                $saleInfo['current_round'] = intval($res['currentRound']->toString());
                $saleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $saleInfo['current_round'] = -1;
                $saleInfo['base_fee_amount'] = BigDecimal::of($saleInfo['total_token_sold'])->multipliedBy($sale['token_fee_percent'])->dividedBy(100, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat();
                $saleInfo['sale_token_fee_amount'] = BigDecimal::of($saleInfo['total_token_sold'])->multipliedBy($sale['token_fee_percent'])->dividedBy(100, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat();
                if ($saleInfo['success_at'] > 0) {
                    $saleInfo['message'] = 'Sale reached soft cap';
                }
                if ($saleInfo['total_base_collected'] >= $sale['hard_cap']) {
                    $saleInfo['message'] = 'Sale reached hard cap';
                }
            }
        });
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * SALE CONTRACT: Process User Withdraw Sale Token
     * @throws Exception
     */
    public function processSaleContractUserWithdrawSaleToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');

        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleAddress = $sale['contract_address'];
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        // <editor-fold desc = "Init Sale Contract Instance">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $saleAddress) {
                    $abiType = ContractLibrary::SALE . "_" . $sale['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Sale withdraw sale token Invalid Event Data transaction hash:" . $transaction['hash']);
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $withdrawAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
        $numberClaimed = $eventDataDecode[3];

        // <editor-fold desc = "Get Status Info">
        $saleContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$saleInfo, $sale) {
            if ($res) {
                $saleInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $sale['sale_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Sale User">
        $userAddress = $transaction['from'];
        $saleUser = $presaleUserLogCollection->findOne([
            'network' => $sale['network'],
            'platform' => $sale['platform'],
            'presale_address' => $saleAddress,
            'user_address' => $userAddress
        ]);

        if ($saleUser) {
            if (isset($sale['active_vesting']) && $sale['active_vesting'] == ContractLibrary::SALE_ACTIVE_VESTING) {
                foreach ($saleUser['list_vesting'] as &$vesting) {
                    if ($vesting['vesting_number'] == $numberClaimed) {
                        $vesting['withdraw_status'] = ContractLibrary::WITHDRAWN;
                        $vesting['sale_token_withdraw_amount'] = $withdrawAmount;
                        $vesting['withdraw_token_type'] = 'sale_token';
                        $vesting['withdraw_at'] = $transaction['timestamp'];
                    }
                }
            } else {
                $saleUser['sale_token_withdraw_amount'] = $withdrawAmount;
                $saleUser['withdraw_at'] = $transaction['timestamp'];
            }
            $saleUser['withdraw_status'] = ContractLibrary::WITHDRAWN;
            $saleUser['withdraw_token_type'] = 'sale_token';
            $presaleUserLogCollection->updateOne(['_id' => $saleUser['_id']], ['$set' => $saleUser]);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * Process User Withdraw Base Token
     * @throws Exception
     */
    public function processSaleContractUserWithdrawBaseToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');

        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleAddress = $sale['contract_address'];
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $saleAddress) {
                    $abiType = ContractLibrary::SALE . "_" . $sale['contract_version'];
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiType);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }
        $eventDataDecode = $eventLogData['data_decode'];
        $withdrawAmount = BigDecimal::of($eventDataDecode[1])->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();

        // <editor-fold desc = "Get Status Info">
        $saleContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$saleInfo, $sale) {
            if ($res) {
                $saleInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Presale User">
        $userAddress = $transaction['from'];
        $saleUser = $presaleUserLogCollection->findOne([
            'network' => $sale['network'],
            'platform' => $sale['platform'],
            'presale_address' => $saleAddress,
            'user_address' => $userAddress
        ]);
        if ($saleUser) {
            $dataSaleUser = [
                'withdraw_status' => ContractLibrary::ACTIVE,
                'base_token_withdraw_amount' => $withdrawAmount,
                'withdraw_token_type' => 'base_token',
                'withdraw_at' => $transaction['timestamp'],
            ];
            $presaleUserLogCollection->updateOne(['_id' => $saleUser['_id']], ['$set' => $dataSaleUser]);
        }
        // </editor-fold>

        // <editor-fold desc = "Presale User Zero Round">
        $presaleUserZeroRound = $presaleUserZeroRoundCollection->findOne([
            'network' => $sale['network'],
            'platform' => $sale['platform'],
            'presale_address' => $saleAddress,
            'user_address' => $userAddress
        ]);
        if ($presaleUserZeroRound) {
            $dataPresaleUserZeroRound = [
                'withdraw_status' => ContractLibrary::ACTIVE,
                'withdraw_at' => $transaction['timestamp'],
            ];
            $presaleUserZeroRoundCollection->updateOne(['_id' => $presaleUserZeroRound['_id']], ['$set' => $dataPresaleUserZeroRound]);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * Process Owner Withdraw Sale Token
     * @throws Exception
     */
    public function processSaleContractOwnerWithdrawSaleToken($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleInfo['message'] = 'Owner withdraw sale token';
        $saleInfo['owner_withdraw_sale_token'] = ContractLibrary::ACTIVE;
        $saleInfo['withdraw_sale_token_at'] = $transaction['timestamp'];

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * @throws Exception
     */
    public function processSaleContractUpdateTime($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleAddress = $sale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $saleContractInstance = $presaleContract->at($saleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_SALE_MAIN_INFO, null, function ($err, $res) use (&$saleInfo, $sale) {
            if ($res) {
                $saleInfo['start_time'] = intval($res['startTime']->toString());
                $saleInfo['end_time'] = intval($res['endTime']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $finishAt = ((!empty($res['finishAt'])) && ($res['finishAt'] instanceof BigInteger)) ? $res['finishAt']->toString() : 0;
                $saleInfo['zero_round.finish_at'] = intval($finishAt);
            }
        });
        // </editor-fold>

        $saleInfo['message'] = 'Owner update limit per buyer';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * @throws Exception
     */
    public function processSaleContractUpdateLimitPerBuyer($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleAddress = $sale['contract_address'];
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        // <editor-fold desc = "Init Sale Contract Instance">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_SALE_MAIN_INFO, null, function ($err, $res) use (&$saleInfo, $sale) {
            if ($res) {
                $saleInfo['limit_per_buyer'] = BigDecimal::of($res['limitPerBuyer']->toString())->exactlyDividedBy(pow(10, $sale['base_token_decimals']))->toFloat();
                $saleInfo['max_buyer'] = ceil($sale['hard_cap'] / $saleInfo['limit_per_buyer']);
            }
        });
        // </editor-fold>
        $saleInfo['message'] = 'Owner update limit per buyer';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    public function processSaleContractSetWhitelistFlag($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $value = $dataDecode['data_decode'][0];
        $saleInfo['whitelist_only'] = $value;
        $saleInfo['message'] = 'Owner update whitelist flag';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    public function processSaleContractEditWhitelist($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleWhitelistCollection = $this->mongo->selectCollection('presale_whitelist');

        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $listAddress = $dataDecode['data_decode'][0];
        $action = $dataDecode['data_decode'][1];

        if ($action) {
            foreach ($listAddress as $address) {
                $address = $this->web3->toCheckSumAddress($address);
                $dataSaleWhitelist = [
                    'network' => $sale['network'],
                    'platform' => $sale['platform'],
                    'presale_address' => $sale['contract_address'],
                    'user_address' => $address
                ];
                $saleWhitelist = $presaleWhitelistCollection->findOne($dataSaleWhitelist);
                if (!$saleWhitelist) {
                    $dataSaleWhitelist['project_type'] = $sale['project_type'];
                    $dataSaleWhitelist['sale_type'] = $sale['sale_type'];
                    $presaleWhitelistCollection->insertOne($dataSaleWhitelist);
                }
            }
        } else {
            foreach ($listAddress as $address) {
                $address = $this->web3->toCheckSumAddress($address);
                $dataSaleWhitelist = [
                    'network' => $sale['network'],
                    'presale_address' => $sale['contract_address'],
                    'platform' => $sale['platform'],
                    'user_address' => $address
                ];
                $presaleWhitelistCollection->deleteOne($dataSaleWhitelist);
            }
        }

        $saleInfo['message'] = 'Owner update whitelist user';

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * @throws Exception
     */
    public function processSaleContractRegisterZeroRound($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presaleUserZeroRoundCollection = $this->mongo->selectCollection('presale_user_zero_round');

        $functionGetZeroRoundInfo = ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO;
        $sale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$sale) {
            return;
        }

        $saleAddress = $sale['contract_address'];
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $sale['contract_version']);

        // <editor-fold desc = "Init Sale Contract Instance">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        $saleContractInstance->call($functionGetZeroRoundInfo, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;
                $saleInfo['zero_round.registered_slot'] = intval($registeredSlot);
            }
        });
        // </editor-fold>
        $userAddress = $transaction['from'];

        // <editor-fold desc = "Sale User Zero Round">
        $dataSaleUserZeroRound = [
            'network' => $sale['network'],
            'platform' => $sale['platform'],
            'presale_address' => $sale['contract_address'],
            'user_address' => $userAddress
        ];
        $saleUserZeroRound = $presaleUserZeroRoundCollection->findOne($dataSaleUserZeroRound);
        if (empty($saleUserZeroRound)) {
            $dataSaleUserZeroRound['created_at'] = $transaction['timestamp'];
            $dataSaleUserZeroRound['withdraw_status'] = ContractLibrary::INACTIVE;
            $presaleUserZeroRoundCollection->insertOne($dataSaleUserZeroRound);
        }
        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $sale, $saleInfo);
    }

    /**
     * Process Force Fail
     * @throws Exception
     */
    public function processForceFailContractSale($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');

        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }

        $presaleAddress = $presale['contract_address'];
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::SALE, $presale['contract_version']);

        // <editor-fold desc = "Init Presale Contract Instance">
        $presaleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['force_failed'] = $res['forceFailed'];
                $presaleInfo['current_status'] = intval($res['currentStatus']->toString());
                $presaleInfo['current_round'] = intval($res['currentRound']->toString());
                $presaleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $presaleInfo['current_round'] = -1;
            }
        });
        $presaleInfo['message'] = 'Admin force failed';
        if ($dataDecode['name'] == ContractLibrary::FUNCTION_OWNER_FORCE_FAIL) {
            $presaleInfo['message'] = 'Owner force failed';
        }

        // </editor-fold>

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * @throws Exception
     */
    public function processUpdateVestingInfo($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }
        $presaleUserLogCollection = $this->mongo->selectCollection('presale_user_log');

        $coinInstance = $this->web3;
        // <editor-fold desc = "Re-init Contract Instance By Right Version">
        $abiPool = ContractLibrary::getAbi(ContractLibrary::SALE, $presale['contract_version']);
        $presaleContract = new Contract($coinInstance->rpcConnector->getProvider(), $abiPool);
        $presaleContractInstance = $presaleContract->at($presale['contract_address']);
        // </editor-fold>

        $presaleContractInstance->call(ContractLibrary::FUNCTION_GET_VESTING_INFO, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['active_vesting'] = $res['activeVesting'];
                if ($presaleInfo['active_vesting']) {
                    $vestingPeriod = $res['vestingPeriod'];
                    $vestingPercent = $res['vestingPercent'];
                    foreach ($vestingPeriod as $item) {
                        $presaleInfo['list_vesting_period'][] = intval($item->toString());
                    }

                    foreach ($vestingPercent as $item) {
                        $presaleInfo['list_vesting_percent'][] = doubleval($item->toString() / 10);
                    }
                }
            }
        });

        if (isset($presaleInfo['active_vesting']) && $presaleInfo['active_vesting'] == ContractLibrary::POOL_ACTIVE_VESTING) {
            if (!isset($presaleInfo['list_vesting_period']) || empty($presaleInfo['list_vesting_period'])
                || !isset($presaleInfo['list_vesting_percent']) || empty($presaleInfo['list_vesting_percent'])
                || count($presaleInfo['list_vesting_period']) != count($presaleInfo['list_vesting_percent'])) {
                throw new Exception("Invalid vesting data. Contract Address" . $presale['contract_address']);
            }
            $vestingData['active_vesting'] = $presaleInfo['active_vesting'];
            foreach ($presaleInfo['list_vesting_period'] as $key => $vestingTime) {
                $vestingData['list_vesting'][] = [
                    'vesting_period' => $vestingTime,
                    'vesting_percent' => $presaleInfo['list_vesting_percent'][$key],
                    'vesting_number' => $key + 1,
                    'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                ];
            }
            $condition = [
                'presale_address' => $transaction['to'],
                'network' => $transaction['network'],
                'platform' => $transaction['platform']
            ];
            $presaleUserLogCollection->updateMany($condition, ['$set' => $vestingData]);
        }


        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }

    /**
     * @throws Exception
     */
    public function processUpdateFundAddress($transaction, $dataDecode)
    {
        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            'contract_address' => $transaction['to'],
            'network' => $transaction['network'],
            'platform' => $transaction['platform']
        ]);
        if (!$presale) {
            return;
        }

        $fundAddress = $this->web3->toCheckSumAddress($dataDecode['data_decode'][0]);
        $presaleInfo['fund_address'] = $fundAddress;

        $this->updateTransactionAndPresale($transaction, $dataDecode, $presale, $presaleInfo);
    }
}
