<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\Collection;
use Web3\Contract;
use Web3p\EthereumTx\Transaction;

class LotteryService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * @throws Exception
     */
    public function processStartLottery($transaction, $dataDecode)
    {
        $divisor = 100;
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $settingKey = "lottery_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $paymentToken = $settingInfo['payment_token'];

        // <editor-fold desc = "Decode Event Data">
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        $abiName = ContractLibrary::LOTTERY;
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data Tx: " . $transaction['hash']);
        }
        // </editor-fold>

        $eventDataDecode = $eventLogData['data_decode'];
        $lotteryContractId = intval($eventDataDecode[0]);
        $startTime = intval($eventDataDecode[1]);
        $endTime = intval($eventDataDecode[2]);
        $price = BigDecimal::of($eventDataDecode[3])->exactlyDividedBy(pow(10, $settingInfo['payment_token']['token_decimals']))->toFloat();
        $firstTicketId = intval($eventDataDecode[4]);
        $pendingInjectedAmount = BigDecimal::of($eventDataDecode[5])->exactlyDividedBy(pow(10, $settingInfo['payment_token']['token_decimals']))->toFloat();

        $lottery = $lotteryCollection->findOne([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform
        ]);

        if ($lottery) {
            // <editor-fold desc = "Update Transaction">
            $this->updateTransaction($transaction, $dataDecode);
            // </editor-fold>
            return;
        }
        $discountDivisor = intval($dataDecode['data_decode'][2]);
        $lotteryAddress = $transaction['to'];
        $lotteryInfo = [
            'lottery_contract_id' => $lotteryContractId,
            'contract_address' => $lotteryAddress,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price' => $price,
            'discount_divisor' => $discountDivisor,
            'first_ticket_id' => $firstTicketId,
            'pending_injected_amount' => $pendingInjectedAmount,
            'status' => ContractLibrary::LOTTERY_STATUS_OPEN,
            'number_buyer' => 0,
            'number_ticket' => 0,
            'number_ticket_win' => 0,
            'amount_round_collected' => 0,
            'amount_withdraw_to_treasury' => 0,
            'amount_to_share_to_winner' => 0,
            'amount_injected' => 0,
            'network' => $network,
            'platform' => $platform,
            'created_at' => time()
        ];
        $lotteryInfo['treasury_fee'] = doubleval($dataDecode['data_decode'][4]) / $divisor;
        $listRewardBreakdown = $dataDecode['data_decode'][3];
        $rewardBreakdown = [];
        foreach ($listRewardBreakdown as $item) {
            $rewardBreakdown[] = BigDecimal::of($item->toString())->exactlyDividedBy($divisor)->toFloat();
        }
        $lotteryInfo['rewards_breakdown'] = $rewardBreakdown;

        // <editor-fold desc = "Init Lottery Contract Instance">
        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $lotteryContract = new Contract($this->web3->rpcConnector->getProvider(), $abiLottery);
        $lotteryContractInstance = $lotteryContract->at($lotteryAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Amount Collected And Number Buyer">
        $functionViewLottery = ContractLibrary::FUNCTION_VIEW_LOTTERY;
        $lotteryContractInstance->call($functionViewLottery, $lotteryContractId, function ($err, $res) use (&$lotteryInfo, $paymentToken) {
            if ($res) {
                $lotteryInfo['amount_collected'] = BigDecimal::of($res['amountCollected']->toString())->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        $lotteryCollection->insertOne($lotteryInfo);

        // <editor-fold desc = "Lottery Cron">
        $cronTime = strtotime(date('m/d/Y H:i', strtotime("+1 minutes", $endTime)));
        $dataLotteryCron = [
            'cron_time' => $cronTime,
            'action' => ContractLibrary::FUNCTION_CLOSE_LOTTERY,
            'contract_address' => $lotteryAddress,
            'lottery_contract_id' => $lotteryContractId,
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING,
            'network' => $network,
            'platform' => $platform,
        ];
        $lotteryCronCollection->insertOne($dataLotteryCron);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function processBuyTickets($transaction, $dataDecode)
    {
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $registryCollection = $this->mongo->selectCollection('registry');
        /** @var Collection $lotteryBuyLogCollection */
        $lotteryBuyLogCollection = $this->mongo->selectCollection('lottery_buy_log');
        $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
        $lotteryUserLogCollection = $this->mongo->selectCollection('lottery_user_log');
        $registry = $registryCollection->findOne();

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $settingKey = "lottery_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $paymentToken = $settingInfo['payment_token'];

        $lotteryAddress = $transaction['to'];
        $userAddress = $transaction['from'];

        $abiLotteryName = ContractLibrary::LOTTERY;
        $abiTokenName = ContractLibrary::TOKEN;

        // <editor-fold desc = "Decode Event Data">
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        $eventLogToken = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiLotteryName);
                }
                if ($logItem['address'] == $paymentToken['token_address']) {
                    $eventLogToken = $this->web3->decodeEventInputData($logItem, $abiTokenName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data Tx: " . $transaction['hash']);
        }
        // </editor-fold>

        // <editor-fold desc = "Init Lottery Contract Instance">
        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $lotteryContract = new Contract($this->web3->rpcConnector->getProvider(), $abiLottery);
        $lotteryContractInstance = $lotteryContract->at($lotteryAddress);
        // </editor-fold>

        $paymentAmount = BigDecimal::of($eventLogToken['data_decode'][2])->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
        $eventDataDecode = $eventLogData['data_decode'];
        $lotteryContractId = intval($eventDataDecode[1]);
        $numberTicket = intval($eventDataDecode[2]);
        $listEventTicketId = $eventDataDecode[3];

        $lottery = $lotteryCollection->findOne([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform
        ]);

        if (!$lottery) {
            // <editor-fold desc = "Update Transaction">
            $this->updateTransaction($transaction, $dataDecode);
            // </editor-fold>
            return;
        }

        $listTicketId = [];
        foreach ($listEventTicketId as $item) {
            $listTicketId[] = intval($item->toString());
        }

        $listOriginRawTicketNumber = $dataDecode['data_decode'][1];
        $contractRawTicketNumber = $contractRealTicketNumber = $userRawTicketNumber = $userRealTicketNumber = [];
        foreach ($listOriginRawTicketNumber as $item) {
            $realNumber = Helper::getRealLotteryNumber($item->toString());
            $contractRawTicketNumber[] = $item->toString();
            $contractRealTicketNumber[] = "1" . $realNumber;
            $userRawTicketNumber[] = strrev($realNumber);
            $userRealTicketNumber[] = $realNumber;
        }

        $lotteryBuyLogData = [
            'lottery_id' => $lottery['_id'],
            'lottery_contract_id' => $lotteryContractId,
            'payment_amount' => $paymentAmount,
            'number_ticket' => $numberTicket,
            'user_address' => $userAddress,
            'hash' => $transaction['hash'],
            'contract_raw_ticket_number' => $contractRawTicketNumber,
            'contract_real_ticket_number' => $contractRealTicketNumber,
            'user_raw_ticket_number' => $userRawTicketNumber,
            'user_real_ticket_number' => $userRealTicketNumber,
            'created_at' => $transaction['timestamp'],
            'network' => $network,
            'platform' => $platform,
        ];

        $lotteryInfo = [];

        // <editor-fold desc = "Get Amount Collected And Number Buyer">
        $functionViewLottery = ContractLibrary::FUNCTION_VIEW_LOTTERY;
        $lotteryContractInstance->call($functionViewLottery, $lotteryContractId, function ($err, $res) use (&$lotteryInfo, $paymentToken) {
            if ($res) {
                $lotteryInfo['amount_collected'] = BigDecimal::of($res['amountCollected']->toString())->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
                $lotteryInfo['amount_round_collected'] = BigDecimal::of($res['amountRoundCollected']->toString())->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
                $lotteryInfo['number_buyer'] = intval($res['numberBuyer']->toString());
            }
        });
        // </editor-fold>

        $lotteryInfo['amount_withdraw_to_treasury'] = $lotteryInfo['amount_round_collected'] / 100 * $lottery['treasury_fee'];
        $lotteryInfo['amount_to_share_to_winner'] = $lotteryInfo['amount_collected'] - $lotteryInfo['amount_withdraw_to_treasury'];

        $lotteryBuyLogId = $lotteryBuyLogCollection->insertOne($lotteryBuyLogData)->getInsertedId();

        // <editor-fold desc = "Lottery Ticket">
        $listLotteryTicket = [];
        foreach ($contractRawTicketNumber as $key => $item) {
            $listLotteryTicket[] = [
                'lottery_id' => $lottery['_id'],
                'lottery_buy_log_id' => $lotteryBuyLogId,
                'user_address' => $userAddress,
                'lottery_contract_id' => $lotteryContractId,
                'ticket_id' => $listTicketId[$key],
                'hash' => $transaction['hash'],
                'contract_raw_ticket_number' => $item,
                'contract_real_ticket_number' => $contractRealTicketNumber[$key],
                'user_raw_ticket_number' => $userRawTicketNumber[$key],
                'user_real_ticket_number' => $userRealTicketNumber[$key],
                'is_claim' => ContractLibrary::INACTIVE,
                'created_at' => $transaction['timestamp'],
                'network' => $network,
                'platform' => $platform,
            ];
        }
        $lotteryTicketCollection->insertMany($listLotteryTicket);
        // </editor-fold>

        // <editor-fold desc = "Lottery User">
        $dataLotteryUser = [
            'user_address' => $userAddress,
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform,
        ];

        $match = $dataLotteryUser;
        $conditions = [
            [
                '$match' => $match
            ],
            [
                '$group' => [
                    '_id' => null,
                    "total" => ['$sum' => '$payment_amount'],
                    "count" => ['$sum' => 1],
                ],
            ],
            [
                '$project' => [
                    "total" => 1,
                    "count" => 1,
                ],
            ],
        ];
        $buyStatistic = $lotteryBuyLogCollection->aggregate($conditions)->toArray();
        $buyTimes = $buyStatistic[0]['count'];
        $totalAmount = $buyStatistic[0]['total'];
        $numberTicket = $lotteryTicketCollection->countDocuments($dataLotteryUser);
        $lotteryUser = $lotteryUserLogCollection->findOne($dataLotteryUser);

        $dataLotteryUser['buy_times'] = $buyTimes;
        $dataLotteryUser['number_ticket'] = $numberTicket;
        $dataLotteryUser['total_amount'] = $totalAmount;
        $dataLotteryUser['lottery_id'] = $lottery['_id'];
        if (!$lotteryUser) {
            $lotteryUserLogCollection->insertOne($dataLotteryUser);
        } else {
            $lotteryUserLogCollection->updateOne(['_id' => $lotteryUser['_id']], ['$set' => $dataLotteryUser]);
        }
        // </editor-fold>

        $totalTicket = $lotteryTicketCollection->countDocuments([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform,
        ]);
        $lotteryInfo['number_ticket'] = $totalTicket;

        $this->updateTransactionAndLottery($transaction, $dataDecode, $lottery, $lotteryInfo);

    }

    /**
     * @throws Exception
     */
    public function processCloseLottery($transaction, $dataDecode)
    {
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');

        $network = $transaction['network'];
        $platform = $transaction['platform'];

        $lotteryAddress = $transaction['to'];

        // <editor-fold desc = "Init Lottery Contract Instance">
        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $lotteryContract = new Contract($this->web3->rpcConnector->getProvider(), $abiLottery);
        $lotteryContractInstance = $lotteryContract->at($lotteryAddress);
        // </editor-fold>

        $lotteryContractId = intval($dataDecode['data_decode'][0]);

        $lottery = $lotteryCollection->findOne([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform
        ]);

        if (!$lottery) {
            // <editor-fold desc = "Update Transaction">
            $this->updateTransaction($transaction, $dataDecode);
            // </editor-fold>
            return;
        }

        $lotteryInfo = [
            'status' => ContractLibrary::LOTTERY_STATUS_CLOSE
        ];

        // <editor-fold desc = "Get First Ticket In Next Lottery">
        $functionViewLottery = ContractLibrary::FUNCTION_VIEW_LOTTERY;
        $lotteryContractInstance->call($functionViewLottery, $lotteryContractId, function ($err, $res) use (&$lotteryInfo) {
            if ($res) {
                $lotteryInfo['first_ticket_in_next_lottery'] = intval($res['firstTicketIdNextLottery']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Lottery Cron">
        $cronTime = strtotime(date('m/d/Y H:i', strtotime("1 minutes")));
        $dataLotteryCron = [
            'cron_time' => $cronTime,
            'action' => ContractLibrary::FUNCTION_CALCULATE_REWARD,
            'contract_address' => $lotteryAddress,
            'lottery_contract_id' => $lotteryContractId,
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING,
            'network' => $network,
            'platform' => $platform,
        ];
        $lotteryCronCollection->insertOne($dataLotteryCron);
        // </editor-fold>

        $this->updateTransactionAndLottery($transaction, $dataDecode, $lottery, $lotteryInfo);
    }

    /**
     * @throws Exception
     */
    public function processCalculateReward($transaction, $dataDecode)
    {
        $divisor = 100;
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $settingKey = "lottery_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $paymentToken = $settingInfo['payment_token'];

        $lotteryAddress = $transaction['to'];

        $abiLotteryName = ContractLibrary::LOTTERY;

        // <editor-fold desc = "Decode Event Data">
        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, $abiLotteryName);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data Tx: " . $transaction['hash']);
        }
        // </editor-fold>

        // <editor-fold desc = "Init Lottery Contract Instance">
        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $lotteryContract = new Contract($this->web3->rpcConnector->getProvider(), $abiLottery);
        $lotteryContractInstance = $lotteryContract->at($lotteryAddress);
        // </editor-fold>

        $lotteryContractId = intval($dataDecode['data_decode'][0]);

        $lottery = $lotteryCollection->findOne([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform
        ]);

        if (!$lottery) {
            // <editor-fold desc = "Update Transaction">
            $this->updateTransaction($transaction, $dataDecode);
            // </editor-fold>
            return;
        }
        $amountTreasury = $lottery['amount_round_collected'] / $divisor * $lottery['treasury_fee'];
        $amountToShareToWinner = $lottery['amount_collected'] - $amountTreasury;
        $lotteryInfo = [
            'status' => ContractLibrary::LOTTERY_STATUS_CLAIMABLE,
            'auto_injection' => $dataDecode['data_decode'][1],
            'amount_withdraw_to_treasury' => $amountTreasury,
            'amount_to_share_to_winner' => $amountToShareToWinner,
            'calculate_reward_at' => time()
        ];

        // <editor-fold desc = "Get Lottery Info">
        $functionViewLottery = ContractLibrary::FUNCTION_VIEW_LOTTERY;
        $lotteryContractInstance->call($functionViewLottery, $lotteryContractId, function ($err, $res) use (&$lotteryInfo) {
            if ($res) {
                $lotteryInfo['first_ticket_in_next_lottery'] = intval($res['firstTicketIdNextLottery']->toString());
                $lotteryInfo['final_number'] = $res['finalNumber']->toString();
            }
        });
        $realFinalNumber = Helper::getRealLotteryNumber($lotteryInfo['final_number']);
        $lotteryInfo['contract_raw_final_number'] = $lotteryInfo['final_number'];
        $lotteryInfo['contract_real_final_number'] = "1" . $realFinalNumber;
        $lotteryInfo['user_raw_final_number'] = strrev($realFinalNumber);
        $lotteryInfo['user_real_final_number'] = $realFinalNumber;
        // </editor-fold>

        // <editor-fold desc = "Get Reward Info">
        $functionGetRewardInfo = ContractLibrary::FUNCTION_GET_REWARD_INFO;
        $lotteryContractInstance->call($functionGetRewardInfo, $lotteryContractId, function ($err, $res) use (&$lotteryInfo) {
            if ($res) {
                $lotteryInfo['rewards_breakdown'] = $res['rewardsBreakdown'];
                $lotteryInfo['token_per_ticket_in_bracket'] = $res['tokenPerTicketInBracket'];
                $lotteryInfo['count_winners_per_bracket'] = $res['countWinnersPerBracket'];
            }
        });
        // </editor-fold>

        foreach ($lotteryInfo['rewards_breakdown'] as $key => $item) {
            $lotteryInfo['rewards_breakdown'][$key] = BigDecimal::of($item->toString())->exactlyDividedBy($divisor)->toFloat();
            $lotteryInfo['token_breakdown'][$key] = $lotteryInfo['amount_to_share_to_winner'] / $divisor * $lotteryInfo['rewards_breakdown'][$key];
        }

        foreach ($lotteryInfo['token_per_ticket_in_bracket'] as $key => $item) {
            $lotteryInfo['token_per_ticket_in_bracket'][$key] = BigDecimal::of($item->toString())->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
        }

        $amountToPayToWinner = 0;
        foreach ($lotteryInfo['count_winners_per_bracket'] as $key => $item) {
            $countWinner = intval($item->toString());
            $lotteryInfo['count_winners_per_bracket'][$key] = $countWinner;
            $payWinnerPerBracket = $countWinner * $lotteryInfo['token_per_ticket_in_bracket'][$key];
            $lotteryInfo['pay_winners_per_bracket'][$key] = $payWinnerPerBracket;
            $amountToPayToWinner += $payWinnerPerBracket;
        }
        $lotteryInfo['amount_to_pay_to_winner'] = $amountToPayToWinner;

        $pendingInjectionNextLottery = 0;
        if ($lotteryInfo['auto_injection']) {
            $pendingInjectionNextLottery = $lottery['amount_withdraw_to_treasury'];
        }
        $pendingInjectionNextLottery += ($lotteryInfo['amount_to_share_to_winner'] - $amountToPayToWinner);
        $lotteryInfo['pending_injection_next_lottery'] = $pendingInjectionNextLottery;

        $lotteryInfo = $this->processRewardBracket($lottery, $lotteryInfo);

        // <editor-fold desc = "Lottery Cron">
        $cronTime = strtotime(date('m/d/Y H:i', strtotime("+1 minutes")));
        $dataLotteryCron = [
            'cron_time' => $cronTime,
            'action' => ContractLibrary::FUNCTION_START_LOTTERY,
            'contract_address' => $lotteryAddress,
            'lottery_contract_id' => $lotteryContractId + 1,
            'status' => ContractLibrary::LOTTERY_CRON_STATUS_PENDING,
            'network' => $network,
            'platform' => $platform,
        ];
        $lotteryCronCollection->insertOne($dataLotteryCron);
        // </editor-fold>

        $this->updateTransactionAndLottery($transaction, $dataDecode, $lottery, $lotteryInfo);
    }

    public function processClaimTickets($transaction, $dataDecode)
    {
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
        $lotteryUserLogCollection = $this->mongo->selectCollection('lottery_user_log');

        $network = $transaction['network'];
        $platform = $transaction['platform'];

        $lotteryContractId = intval($dataDecode['data_decode'][0]);
        $listDataTicketId = $dataDecode['data_decode'][1];

        $userAddress = $transaction['from'];

        $listTicketId = [];
        foreach ($listDataTicketId as $item) {
            $listTicketId[] = intval($item->toString());
        }

        $lottery = $lotteryCollection->findOne([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform
        ]);

        if (!$lottery) {
            // <editor-fold desc = "Update Transaction">
            $this->updateTransaction($transaction, $dataDecode);
            // </editor-fold>
            return;
        }

        $lotteryTicketCollection->updateMany([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform,
            'ticket_id' => ['$in' => $listTicketId]
        ], ['$set' => ['is_claim' => ContractLibrary::ACTIVE]]);

        // <editor-fold desc = "Update Lottery User">
        $match = [
            'user_address' => $userAddress,
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform,
            'is_claim' => ContractLibrary::ACTIVE
        ];
        $conditions = [
            [
                '$match' => $match
            ],
            [
                '$group' => [
                    '_id' => null,
                    "total" => ['$sum' => '$amount_reward'],
                    "count" => ['$sum' => 1],
                ],
            ],
            [
                '$project' => [
                    "total" => 1,
                    "count" => 1,
                ],
            ],
        ];
        $buyStatistic = $lotteryTicketCollection->aggregate($conditions)->toArray();
        $lotteryUserData = [
            'number_claim' => $buyStatistic[0]['count'],
            'amount_claim' => $buyStatistic[0]['total'],
        ];
        $lotteryUserLogCollection->updateOne([
            'user_address' => $userAddress,
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform,
        ], ['$set' => $lotteryUserData]);
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

    }

    /**
     * @throws Exception
     */
    public function processInjectFunds($transaction, $dataDecode)
    {
        $lotteryCollection = $this->mongo->selectCollection('lottery');
        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();

        $network = $transaction['network'];
        $platform = $transaction['platform'];
        $settingKey = "lottery_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $paymentToken = $settingInfo['payment_token'];

        $lotteryContractId = intval($dataDecode['data_decode'][0]);
        $amount = $dataDecode['data_decode'][1];

        $lottery = $lotteryCollection->findOne([
            'lottery_contract_id' => $lotteryContractId,
            'network' => $network,
            'platform' => $platform
        ]);

        if (!$lottery) {
            // <editor-fold desc = "Update Transaction">
            $this->updateTransaction($transaction, $dataDecode);
            // </editor-fold>
            return;
        }

        $lotteryAddress = $transaction['to'];

        // <editor-fold desc = "Init Lottery Contract Instance">
        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $lotteryContract = new Contract($this->web3->rpcConnector->getProvider(), $abiLottery);
        $lotteryContractInstance = $lotteryContract->at($lotteryAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Amount Collected And Number Buyer">
        $functionViewLottery = ContractLibrary::FUNCTION_VIEW_LOTTERY;
        $lotteryContractInstance->call($functionViewLottery, $lotteryContractId, function ($err, $res) use (&$lotteryInfo, $paymentToken) {
            if ($res) {
                $lotteryInfo['amount_collected'] = BigDecimal::of($res['amountCollected']->toString())->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        $amount = BigDecimal::of($amount)->exactlyDividedBy(pow(10, $paymentToken['token_decimals']))->toFloat();
        $lotteryInfo['amount_injected'] = $lottery['amount_injected'] + $amount;
        $lotteryInfo['amount_to_share_to_winner'] = $lotteryInfo['amount_collected'] - $lottery['amount_withdraw_to_treasury'];

        $this->updateTransactionAndLottery($transaction, $dataDecode, $lottery, $lotteryInfo);

    }

    protected function processRewardBracket($lottery, $lotteryInfo)
    {
        $lotteryInfo['number_ticket_win'] = 0;
        $lotteryTicketCollection = $this->mongo->selectCollection('lottery_ticket');
        $lotteryUserLogCollection = $this->mongo->selectCollection('lottery_user_log');
        $listTicket = $lotteryTicketCollection->find([
            'lottery_contract_id' => $lottery['lottery_contract_id'],
            'network' => $lottery['network'],
            'platform' => $lottery['platform']
        ]);
        $finalNumber = intval($lotteryInfo['final_number']);
        $listLotteryUserData = [];
        if (!empty($listTicket)) {
            $listTicket = $listTicket->toArray();
            foreach ($listTicket as $item) {
                $userAddress = $item['user_address'];
                $userNumber = intval($item['contract_raw_ticket_number']);
                $bracket = Helper::calculateBracket($finalNumber, $userNumber);
                $ticketUpdateInfo = [
                    'bracket' => $bracket,
                    'is_win' => $bracket >= 0,
                    'amount_reward' => $lotteryInfo['token_per_ticket_in_bracket'][$bracket] ?? 0
                ];
                !isset($listLotteryUserData[$userAddress]['number_win']) && $listLotteryUserData[$userAddress]['number_win'] = 0;
                !isset($listLotteryUserData[$userAddress]['amount_reward']) && $listLotteryUserData[$userAddress]['amount_reward'] = 0;

                if ($ticketUpdateInfo['is_win']) {
                    $lotteryInfo['number_ticket_win'] += 1;
                    $listLotteryUserData[$userAddress]['number_win'] += 1;
                    $listLotteryUserData[$userAddress]['amount_reward'] += $ticketUpdateInfo['amount_reward'];
                }

                $lotteryTicketCollection->updateOne(['_id' => $item['_id']], ['$set' => $ticketUpdateInfo]);
            }

            if (count($listLotteryUserData)) {
                foreach ($listLotteryUserData as $userAddress => $item) {
                    $item['number_claim'] = 0;
                    $lotteryUserLogCollection->updateOne([
                        'lottery_contract_id' => $lottery['lottery_contract_id'],
                        'user_address' => $userAddress,
                        'network' => $lottery['network'],
                        'platform' => $lottery['platform']
                    ], ['$set' => $item]);
                }
            }
        }
        return $lotteryInfo;
    }

    /**
     * Process Update Setting
     * @throws Exception
     */
    public function processUpdateSetting($transaction, $dataDecode)
    {
        $airdropSettingAddress = $transaction['to'];
        $this->updateLotterySetting($airdropSettingAddress);

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>
    }

    /**
     * @throws Exception
     */
    public function cronCloseLottery($lotteryCron)
    {
        global $config;
        $fromAddress = $config->blockchain['lottery_operator_address'];
        $privateKey = $config->blockchain['lottery_operator_private_key'];
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $contractAddress = $lotteryCron['contract_address'];
        $platform = $lotteryCron['platform'];
        $network = $lotteryCron['network'];
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);
        $gasLimit = $coinInstance->getGasLimit();
        $gasPriceWei = $coinInstance->getGasPriceWei();

        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $contract = new Contract($coinInstance->rpcConnector->getProvider(), $abiLottery);
        $contractLottery = $contract->at($contractAddress);
        $functionName = $lotteryCron['action'];
        $lotteryContractId = $lotteryCron['lottery_contract_id'];
        $functionItem = $coinInstance->getFunctionItem($functionName, ContractLibrary::LOTTERY);
        $functionSignature = $contractLottery->getEthabi()->encodeFunctionSignature($functionItem);
        $encodeParameter = $contract->getEthabi()->encodeParameters($functionItem, [
            $lotteryContractId
        ]);
        $utils = $coinInstance->rpcConnector->getUtils();
        $encodeParameter = $functionSignature . $utils::stripZero($encodeParameter);

        $nonce = $coinInstance->getNonce($fromAddress);
        $txParams = [
            'from' => $fromAddress,
            'to' => $contractAddress,
            'value' => null,
            'nonce' => "0x" . BinanceWeb3::bcdechex($nonce),
            'gas' => "0x" . BinanceWeb3::bcdechex($gasLimit),
            'gasPrice' => "0x" . BinanceWeb3::bcdechex($gasPriceWei),
            'chainId' => $coinInstance->chainId,
            'data' => $encodeParameter,
        ];
        $transaction = new Transaction($txParams);
        $signedTransaction = "0x" . $transaction->sign($privateKey);
        $coinInstance->eth->sendRawTransaction($signedTransaction, function ($err, $res) use (&$hash, $lotteryCron) {
            if ($err) {
                $message = "Cron Close Lottery" . PHP_EOL;
                $message .= "Network: " . $lotteryCron['network'] . PHP_EOL;
                $message .= "Platform: " . $lotteryCron['platform'] . PHP_EOL;
                $message .= "Contract address: " . $lotteryCron['contract_address'] . PHP_EOL;
                $message .= "Lottery contract ID: " . $lotteryCron['lottery_contract_id'] . PHP_EOL;
                $message .= "Error: " . $err->getMessage();
                Helper::sendTelegramMsgMonitor($message);
            }
            $hash = $res;
        });

        if (strlen($hash)) {
            $lotteryCronData = [
                'hash' => $hash,
                'status' => ContractLibrary::LOTTERY_CRON_STATUS_ACTIVE,
                'action_time' => time(),
                'tx_status' => ContractLibrary::TRANSACTION_STATUS_PENDING
            ];
            $lotteryCronCollection->updateOne(['_id' => $lotteryCron['_id']], ['$set' => $lotteryCronData]);
        }
        return $hash;
    }

    /**
     * @throws Exception
     */
    public function cronCalculateReward($lotteryCron)
    {
        global $config;
        $fromAddress = $config->blockchain['lottery_operator_address'];
        $privateKey = $config->blockchain['lottery_operator_private_key'];
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $contractAddress = $lotteryCron['contract_address'];
        $platform = $lotteryCron['platform'];
        $network = $lotteryCron['network'];
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);
        $gasLimit = $coinInstance->getGasLimit();
        $gasPriceWei = $coinInstance->getGasPriceWei();

        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $contract = new Contract($coinInstance->rpcConnector->getProvider(), $abiLottery);
        $contractLottery = $contract->at($contractAddress);
        $functionName = $lotteryCron['action'];
        $lotteryContractId = $lotteryCron['lottery_contract_id'];
        $functionItem = $coinInstance->getFunctionItem($functionName, ContractLibrary::LOTTERY);
        $functionSignature = $contractLottery->getEthabi()->encodeFunctionSignature($functionItem);
        $autoInjection = false;
        $encodeParameter = $contract->getEthabi()->encodeParameters($functionItem, [
            $lotteryContractId,
            $autoInjection
        ]);
        $utils = $coinInstance->rpcConnector->getUtils();
        $encodeParameter = $functionSignature . $utils::stripZero($encodeParameter);

        $nonce = $coinInstance->getNonce($fromAddress);
        $txParams = [
            'from' => $fromAddress,
            'to' => $contractAddress,
            'value' => null,
            'nonce' => "0x" . BinanceWeb3::bcdechex($nonce),
            'gas' => "0x" . BinanceWeb3::bcdechex($gasLimit),
            'gasPrice' => "0x" . BinanceWeb3::bcdechex($gasPriceWei),
            'chainId' => $coinInstance->chainId,
            'data' => $encodeParameter,
        ];
        $transaction = new Transaction($txParams);
        $signedTransaction = "0x" . $transaction->sign($privateKey);
        $coinInstance->eth->sendRawTransaction($signedTransaction, function ($err, $res) use (&$hash, $lotteryCron) {
            if ($err) {
                $message = "Cron Calculate Reward" . PHP_EOL;
                $message .= "Network: " . $lotteryCron['network'] . PHP_EOL;
                $message .= "Platform: " . $lotteryCron['platform'] . PHP_EOL;
                $message .= "Contract address: " . $lotteryCron['contract_address'] . PHP_EOL;
                $message .= "Lottery contract ID: " . $lotteryCron['lottery_contract_id'] . PHP_EOL;
                $message .= "Error: " . $err->getMessage();
                Helper::sendTelegramMsgMonitor($message);
            }
            $hash = $res;
        });

        if (strlen($hash)) {
            $lotteryCronData = [
                'hash' => $hash,
                'status' => ContractLibrary::LOTTERY_CRON_STATUS_ACTIVE,
                'action_time' => time(),
                'tx_status' => ContractLibrary::TRANSACTION_STATUS_PENDING
            ];
            $lotteryCronCollection->updateOne(['_id' => $lotteryCron['_id']], ['$set' => $lotteryCronData]);
        }
        return $hash;
    }

    /**
     * @throws Exception
     */
    public function cronStartLottery($lotteryCron)
    {
        global $config;
        $fromAddress = $config->blockchain['lottery_operator_address'];
        $privateKey = $config->blockchain['lottery_operator_private_key'];
        $lotteryCronCollection = $this->mongo->selectCollection('lottery_cron');
        $registryCollection = $this->mongo->selectCollection('registry');
        $platform = $lotteryCron['platform'];
        $network = $lotteryCron['network'];

        $registry = $registryCollection->findOne();
        $settingKey = "lottery_setting_{$platform}_$network";
        $settingInfo = $registry[$settingKey];
        $paymentToken = $settingInfo['payment_token'];
        $rateUsd = $paymentToken['token_price'];

        $contractAddress = $lotteryCron['contract_address'];
        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);
        $gasLimit = $coinInstance->getGasLimit();
        $gasPriceWei = $coinInstance->getGasPriceWei();

        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $contract = new Contract($coinInstance->rpcConnector->getProvider(), $abiLottery);
        $contractLottery = $contract->at($contractAddress);
        $functionName = $lotteryCron['action'];
        $functionItem = $coinInstance->getFunctionItem($functionName, ContractLibrary::LOTTERY);
        $functionSignature = $contractLottery->getEthabi()->encodeFunctionSignature($functionItem);
        $endTime = strtotime('tomorrow') + 20 * 60 * 60;
        $decimals = BigDecimal::of(10)->power($paymentToken['token_decimals']);
        $priceTicket = $settingInfo['price_ticket'] ?? 1;
        $priceTicket = BigDecimal::of($priceTicket / $rateUsd)->multipliedBy($decimals)->getIntegralPart();
        $discountDivisor = intval($settingInfo['discount_divisor'] ?? 2000);
        $rewardsBreakdown = explode(",", $settingInfo['rewards_breakdown']);
        $rewardsBreakdown = array_map('intval', $rewardsBreakdown);
        if (!count($rewardsBreakdown)) {
            $rewardsBreakdown = [200, 300, 500, 800, 1200, 7000];
        }
        $treasuryFee = intval($settingInfo['treasury_fee'] ?? 5000);
        $encodeParameter = $contract->getEthabi()->encodeParameters($functionItem, [
            $endTime,
            $priceTicket,
            $discountDivisor,
            $rewardsBreakdown,
            $treasuryFee
        ]);
        $utils = $coinInstance->rpcConnector->getUtils();
        $encodeParameter = $functionSignature . $utils::stripZero($encodeParameter);

        $nonce = $coinInstance->getNonce($fromAddress);
        $txParams = [
            'from' => $fromAddress,
            'to' => $contractAddress,
            'value' => null,
            'nonce' => "0x" . BinanceWeb3::bcdechex($nonce),
            'gas' => "0x" . BinanceWeb3::bcdechex($gasLimit),
            'gasPrice' => "0x" . BinanceWeb3::bcdechex($gasPriceWei),
            'chainId' => $coinInstance->chainId,
            'data' => $encodeParameter,
        ];
        $transaction = new Transaction($txParams);
        $signedTransaction = "0x" . $transaction->sign($privateKey);
        $coinInstance->eth->sendRawTransaction($signedTransaction, function ($err, $res) use (&$hash, $lotteryCron) {
            if ($err) {
                $message = "Cron Calculate Reward" . PHP_EOL;
                $message .= "Network: " . $lotteryCron['network'] . PHP_EOL;
                $message .= "Platform: " . $lotteryCron['platform'] . PHP_EOL;
                $message .= "Contract address: " . $lotteryCron['contract_address'] . PHP_EOL;
                $message .= "Lottery contract ID: " . $lotteryCron['lottery_contract_id'] . PHP_EOL;
                $message .= "Error: " . $err->getMessage();
                Helper::sendTelegramMsgMonitor($message);
            }
            $hash = $res;
        });

        if (strlen($hash)) {
            $lotteryCronData = [
                'hash' => $hash,
                'status' => ContractLibrary::LOTTERY_CRON_STATUS_ACTIVE,
                'action_time' => time(),
                'tx_status' => ContractLibrary::TRANSACTION_STATUS_PENDING
            ];
            $lotteryCronCollection->updateOne(['_id' => $lotteryCron['_id']], ['$set' => $lotteryCronData]);
        }
        return $hash;
    }

    /**
     * @throws Exception
     */
    public function updateLotterySetting($lotteryAddress)
    {
        $network = $this->network;
        $platform = $this->platform;
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = $this->web3;
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contractToken = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
        $abiLottery = ContractLibrary::getAbi(ContractLibrary::LOTTERY);
        $contractLottery = new Contract($coinInstance->rpcConnector->getProvider(), $abiLottery);
        $contractLotteryInstance = $contractLottery->at($lotteryAddress);

        $mainCurrency = Adapter::getMainCurrency($platform);
        /** @var BinanceWeb3 $coinInstance */
        $coinInstance = Adapter::getInstance($mainCurrency, $network);

        $settingInfo = [
            'setting_address' => $lotteryAddress
        ];

        // <editor-fold desc = "Get Max Length">
        $functionMaxLengthLottery = ContractLibrary::FUNCTION_MAX_LENGTH_LOTTERY;
        $contractLotteryInstance->call($functionMaxLengthLottery, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['max_length'] = intval($res[0]->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Min Length">
        $functionMinLengthLottery = ContractLibrary::FUNCTION_MIN_LENGTH_LOTTERY;
        $contractLotteryInstance->call($functionMinLengthLottery, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['min_length'] = intval($res[0]->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Max Treasury">
        $functionMaxTreasuryFee = ContractLibrary::FUNCTION_MAX_TREASURY_FEE;
        $contractLotteryInstance->call($functionMaxTreasuryFee, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['max_treasury_fee'] = doubleval($res[0]->toString()) / 100;
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Injector Address">
        $functionInjectorAddress = ContractLibrary::FUNCTION_INJECTOR_ADDRESS;
        $contractLotteryInstance->call($functionInjectorAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['injector_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Max Number Tickets Per Buy Or Claim">
        $functionMaxNumberTicketsPerBuyOrClaim = ContractLibrary::FUNCTION_MAX_NUMBER_TICKETS_PER_BUY_OR_CLAIM;
        $contractLotteryInstance->call($functionMaxNumberTicketsPerBuyOrClaim, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['max_number_tickets_per_buy_or_claim'] = intval($res[0]->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Max Price Ticket">
        $functionMaxPriceTicket = ContractLibrary::FUNCTION_MAX_PRICE_TICKET;
        $contractLotteryInstance->call($functionMaxPriceTicket, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['max_price_ticket'] = $res[0]->toString();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Min Price Ticket">
        $functionMinPriceTicket = ContractLibrary::FUNCTION_MIN_PRICE_TICKET;
        $contractLotteryInstance->call($functionMinPriceTicket, null, function ($err, $res) use (&$settingInfo) {
            if ($res) {
                $settingInfo['min_price_ticket'] = $res[0]->toString();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Payment Token">
        $functionPaymentToken = ContractLibrary::FUNCTION_PAYMENT_TOKEN;
        $contractLotteryInstance->call($functionPaymentToken, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['payment_token']['token_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Payment Token Info">
        if (isset($settingInfo['payment_token']['token_address'])) {
            if (strlen($settingInfo['payment_token']['token_address']) && $settingInfo['payment_token']['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                $paymentTokenInstance = $contractToken->at($settingInfo['payment_token']['token_address']);
                $paymentTokenInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $decimals = intval($res[0]->toString());
                        $settingInfo['payment_token']['token_decimals'] = $decimals;
                        $settingInfo['max_price_ticket'] = (BigDecimal::of($settingInfo['max_price_ticket']))->exactlyDividedBy(pow(10, $decimals))->toFloat();
                        $settingInfo['min_price_ticket'] = (BigDecimal::of($settingInfo['min_price_ticket']))->exactlyDividedBy(pow(10, $decimals))->toFloat();
                    }
                });

                $paymentTokenInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['payment_token']['token_name'] = $res[0];
                    }
                });
                $paymentTokenInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$settingInfo) {
                    if ($res) {
                        $settingInfo['payment_token']['token_symbol'] = $res[0];
                    }
                });
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Get Operator Address">
        $functionOperatorAddress = ContractLibrary::FUNCTION_OPERATOR_ADDRESS;
        $contractLotteryInstance->call($functionOperatorAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['operator_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Random Generator Address">
        $functionRandomGenerator = ContractLibrary::FUNCTION_RANDOM_GENERATOR;
        $contractLotteryInstance->call($functionRandomGenerator, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['random_generator'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Treasury Address">
        $functionTreasuryAddress = ContractLibrary::FUNCTION_TREASURY_ADDRESS;
        $contractLotteryInstance->call($functionTreasuryAddress, null, function ($err, $res) use (&$settingInfo, $coinInstance) {
            if ($res) {
                $settingInfo['treasury_address'] = $coinInstance->toCheckSumAddress($res[0]);
            }
        });
        // </editor-fold>

        $settingInfo['network'] = $network;
        $settingInfo['platform'] = $platform;

        $settingKey = "lottery_setting_{$platform}_$network";
        $dataUpdate = [
            "{$settingKey}" => $settingInfo
        ];

        if (count($settingInfo)) {
            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();
            if ($registry) {
                $settingData = $registry[$settingKey] ?? [];
                $dataUpdate[$settingKey]['discount_divisor'] = $settingData['discount_divisor'] ?? 2000;
                $dataUpdate[$settingKey]['rewards_breakdown'] = $settingData['rewards_breakdown'] ?? "200,300,500,800,1200,7000";
                $dataUpdate[$settingKey]['treasury_fee'] = $settingData['treasury_fee'] ?? 5000;
                $dataUpdate[$settingKey]['price_ticket'] = $settingData['price_ticket'] ?? 1;
                $dataUpdate[$settingKey]['payment_token']['token_price'] = $settingData['payment_token']['token_price'] ?? 1;
                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $dataUpdate[$settingKey]['discount_divisor'] = 2000;
                $dataUpdate[$settingKey]['rewards_breakdown'] = "200,300,500,800,1200,7000";
                $dataUpdate[$settingKey]['treasury_fee'] = 5000;
                $dataUpdate[$settingKey]['price_ticket'] = 1;
                $collection->insertOne($dataUpdate);
            }
        }

        return $settingInfo;
    }
}
