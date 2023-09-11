<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use Exception;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class PoolGeneratorContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Create Presale
     * @throws Exception
     */
    public function processCreatePool($transaction, $dataDecode)
    {

        $coinInstance = $this->web3;
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, 1);
        $poolCollection = $this->mongo->selectCollection('pool');
        $tokensCollection = $this->mongo->selectCollection('tokens');

        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $tokenContract = new Contract($this->web3->rpcConnector->getProvider(), $abiToken);

        $functionGetGeneralInfo = ContractLibrary::FUNCTION_GET_GENERAL_INFO;
        $functionGetPoolAddressInfo = ContractLibrary::FUNCTION_GET_POOL_ADDRESS_INFO;
        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $functionGetRoundInfo = ContractLibrary::FUNCTION_GET_ROUND_INFO;
        $functionGetZeroRoundInfo = ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO;
        $functionGetAuctionRoundInfo = ContractLibrary::FUNCTION_GET_AUCTION_ROUND_INFO;
        $functionGetVestingInfo = ContractLibrary::FUNCTION_GET_VESTING_INFO;
        $functionGetPoolMainInfo = ContractLibrary::FUNCTION_GET_POOL_MAIN_INFO;

        $functionDecimals = 'decimals';
        $functionSymbol = 'symbol';
        $functionName = 'name';
        $functionSupply = 'totalSupply';

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::POOL_GENERATOR);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }

        $eventDataDecode = $eventLogData['data_decode'];
        $poolAddress = $coinInstance->toCheckSumAddress($eventDataDecode[1]);

        $poolInfo['contract_address'] = $poolAddress;
        $poolInfo['platform'] = $transaction['platform'];
        $poolInfo['network'] = $transaction['network'];
        $poolInfo['creation_fee'] = $transaction['value'];
        $poolInfo['created_at'] = $transaction['timestamp'];
        $poolInfo['project_type'] = ContractLibrary::PROJECT_TYPE_POOL;
        $poolInfo['is_show'] = ContractLibrary::ACTIVE;
        $poolInfo['owner_withdraw_base_token'] = ContractLibrary::INACTIVE;
        $poolInfo['withdraw_base_token_at'] = 0;
        $poolInfo['hash'] = $transaction['hash'];

        // <editor-fold desc = "Init Presale Contract Instance By Default Version">
        $poolContract = new Contract($coinInstance->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get General Info">
        $poolContractInstance->call($functionGetGeneralInfo, null, function ($err, $res) use (&$contractVersion, &$poolInfo) {
            if ($res) {
                $contractVersion = intval($res['contractVersion']->toString());
                $poolInfo['contract_version'] = $contractVersion;
                $poolInfo['pool_generator'] = $res['poolGenerator'];
                $poolInfo['contract_type'] = $res['contractType'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Re-init Contract Instance By Right Version">
        $abiPool = ContractLibrary::getAbi(ContractLibrary::POOL, $contractVersion);
        $poolContract = new Contract($coinInstance->rpcConnector->getProvider(), $abiPool);
        $poolContractInstance = $poolContract->at($poolAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Address Info">
        $poolContractInstance->call($functionGetPoolAddressInfo, null, function ($err, $res) use (&$poolInfo, $coinInstance) {
            if ($res) {

                $poolInfo['pool_owner_address'] = $coinInstance->toCheckSumAddress($res['poolOwner']);
                $poolInfo['pool_token_address'] = $coinInstance->toCheckSumAddress($res['poolToken']);
                $poolInfo['base_token_address'] = $coinInstance->toCheckSumAddress($res['baseToken']);
                $poolInfo['wrap_token_address'] = $coinInstance->toCheckSumAddress($res['wrapTokenAddress']);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Pool Token Info">
        $poolTokenContractInstance = $tokenContract->at($poolInfo['pool_token_address']);
        $poolTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['pool_token_decimals'] = intval($res[0]->toString());
            }
        });
        $poolTokenContractInstance->call($functionName, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['pool_token_name'] = $res[0];
            }
        });
        $poolTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['pool_token_symbol'] = $res[0];
            }
        });
        $poolTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['pool_token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $poolInfo['pool_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Base Token Info">
        $baseTokenContractInstance = $tokenContract->at($poolInfo['base_token_address']);
        $baseTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['base_token_decimals'] = intval($res[0]->toString());
            }
        });
        $baseTokenContractInstance->call($functionName, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['base_token_name'] = $res[0];
            }
        });
        $baseTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['base_token_symbol'] = $res[0];
            }
        });
        $baseTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['base_token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $poolContractInstance->call($functionGetPoolMainInfo, null, function ($err, $res) use (&$poolInfo, $coinInstance) {
            if ($res) {
                $poolInfo['token_price'] = BigDecimal::of($res['tokenPrice']->toString())->exactlyDividedBy(pow(10, $poolInfo['pool_token_decimals']))->toFloat();
                $poolInfo['limit_per_buyer'] = BigDecimal::of($res['limitPerBuyer']->toString())->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
                $poolInfo['amount'] = BigDecimal::of($res['amount']->toString())->exactlyDividedBy(pow(10, $poolInfo['pool_token_decimals']))->toFloat();
                $poolInfo['hard_cap'] = BigDecimal::of($res['hardCap']->toString())->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
                $poolInfo['soft_cap'] = BigDecimal::of($res['softCap']->toString())->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
                $poolInfo['start_time'] = intval($res['startTime']->toString());
                $poolInfo['end_time'] = intval($res['endTime']->toString());
                $poolInfo['pool_in_main_token'] = $res['poolInMainToken'];
                $poolInfo['max_buyer'] = ceil($poolInfo['hard_cap'] / $poolInfo['limit_per_buyer']);
            }
        });
        // </editor-fold>

        if ($poolInfo['pool_in_main_token']) {
            $poolInfo['base_token_symbol'] = strtoupper(Adapter::getMainCurrency($transaction['platform']));
        }

        // <editor-fold desc = "Get Status Info">
        $poolContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['whitelist_only'] = $res['whitelistOnly'];
                $poolInfo['is_active_claim'] = $res['isActiveClaim'];
                $poolInfo['force_failed'] = $res['forceFailed'];
                $poolInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
                $poolInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $poolInfo['pool_token_decimals']))->toFloat();
                $poolInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $poolInfo['pool_token_decimals']))->toFloat();
                $poolInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
                $poolInfo['first_round_length'] = intval($res['firstRoundLength']->toString());
                $poolInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $poolInfo['success_at'] = intval($res['successAt']->toString());
                $poolInfo['active_claim_at'] = intval($res['activeClaimAt']->toString());
                $poolInfo['current_status'] = intval($res['currentStatus']->toString());
                $poolInfo['current_round'] = intval($res['currentRound']->toString());
                $poolInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $poolInfo['current_round'] = -1;
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Round Info">
        $poolContractInstance->call($functionGetRoundInfo, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['active_zero_round'] = $res['activeZeroRound'];
                $poolInfo['active_first_round'] = $res['activeFirstRound'];
                $poolInfo['active_auction_round'] = $res['activeAuctionRound'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        if ($poolInfo['active_zero_round']) {
            $poolContractInstance->call($functionGetZeroRoundInfo, null, function ($err, $res) use (&$poolInfo, $coinInstance) {
                if ($res) {
                    $tokenAmount = ((!empty($res['tokenAmount'])) && ($res['tokenAmount'] instanceof BigInteger)) ? $res['tokenAmount']->toString() : 0;
                    $percent = ((!empty($res['percent'])) && ($res['percent'] instanceof BigInteger)) ? $res['percent']->toString() : 0;
                    $percent = $percent / 10;
                    $finishBeforeFirstRound = ((!empty($res['finishBeforeFirstRound'])) && ($res['finishBeforeFirstRound'] instanceof BigInteger)) ? $res['finishBeforeFirstRound']->toString() : 0;
                    $finishAt = ((!empty($res['finishAt'])) && ($res['finishAt'] instanceof BigInteger)) ? $res['finishAt']->toString() : 0;
                    $maxBaseTokenAmount = ((!empty($res['maxBaseTokenAmount'])) && ($res['maxBaseTokenAmount'] instanceof BigInteger)) ? $res['maxBaseTokenAmount']->toString() : 0;
                    $maxSlot = ((!empty($res['maxSlot'])) && ($res['maxSlot'] instanceof BigInteger)) ? $res['maxSlot']->toString() : 0;
                    $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;

                    $poolInfo['zero_round'] = [
                        'token_address' => $coinInstance->toCheckSumAddress($res['tokenAddress']),
                        'token_amount' => $tokenAmount,
                        'percent' => $percent,
                        'finish_before_first_round' => intval($finishBeforeFirstRound),
                        'finish_at' => intval($finishAt),
                        'max_base_token_amount' => $maxBaseTokenAmount,
                        'max_slot' => intval($maxSlot),
                        'registered_slot' => intval($registeredSlot),
                    ];
                }
            });

            // <editor-fold desc = "Get Zero Round Token Info">
            if (strlen($poolInfo['zero_round']['token_address'])) {
                if ($poolInfo['zero_round']['token_address'] != ContractLibrary::ADDRESS_ZERO) {

                    $zeroTokenContractInstance = $tokenContract->at($poolInfo['zero_round']['token_address']);
                    $zeroTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['zero_round']['token_decimals'] = intval($res[0]->toString());
                        }
                    });
                    $zeroTokenContractInstance->call($functionName, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['zero_round']['token_name'] = $res[0];
                        }
                    });
                    $zeroTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['zero_round']['token_symbol'] = $res[0];
                        }
                    });

                    $zeroTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['zero_round']['token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $poolInfo['zero_round']['token_decimals']))->toFloat();
                            $poolInfo['zero_round']['token_amount'] = BigDecimal::of($poolInfo['zero_round']['token_amount'])->exactlyDividedBy(pow(10, $poolInfo['zero_round']['token_decimals']))->toFloat();
                            $poolInfo['zero_round']['max_base_token_amount'] = BigDecimal::of($poolInfo['zero_round']['max_base_token_amount'])->exactlyDividedBy(pow(10, $poolInfo['base_token_decimals']))->toFloat();
                        }
                    });
                }
            }
            // </editor-fold>
        }
        // </editor-fold>

        // <editor-fold desc = "Get Auction Round Info">
        if ($poolInfo['active_auction_round']) {
            $poolContractInstance->call($functionGetAuctionRoundInfo, null, function ($err, $res) use (&$poolInfo, $coinInstance) {
                if ($res) {
                    $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;
                    $totalTokenAmount = ((!empty($res['totalTokenAmount'])) && ($res['totalTokenAmount'] instanceof BigInteger)) ? $res['totalTokenAmount']->toString() : 0;
                    $burnedTokenAmount = ((!empty($res['burnedTokenAmount'])) && ($res['burnedTokenAmount'] instanceof BigInteger)) ? $res['burnedTokenAmount']->toString() : 0;
                    $refundTokenAmount = ((!empty($res['refundTokenAmount'])) && ($res['refundTokenAmount'] instanceof BigInteger)) ? $res['refundTokenAmount']->toString() : 0;
                    $poolInfo['auction_round'] = [
                        'token_address' => $coinInstance->toCheckSumAddress($res['tokenAddress']),
                        'start_time' => intval($res['startTime']->toString()),
                        'end_time' => intval($res['endTime']->toString()),
                        'registered_slot' => intval($registeredSlot),
                        'total_token_amount' => $totalTokenAmount,
                        'burned_token_amount' => $burnedTokenAmount,
                        'refund_token_amount' => $refundTokenAmount,
                    ];
                }
            });

            // <editor-fold desc = "Get Auction Round Token Info">
            if (strlen($poolInfo['auction_round']['token_address'])) {
                if ($poolInfo['auction_round']['token_address'] != ContractLibrary::ADDRESS_ZERO) {

                    $auctionTokenContractInstance = $tokenContract->at($poolInfo['auction_round']['token_address']);
                    $auctionTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['auction_round']['token_decimals'] = intval($res[0]->toString());
                        }
                    });
                    $auctionTokenContractInstance->call($functionName, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['auction_round']['token_name'] = $res[0];
                        }
                    });
                    $auctionTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['auction_round']['token_symbol'] = $res[0];
                        }
                    });

                    $auctionTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$poolInfo) {
                        if ($res) {
                            $poolInfo['auction_round']['token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $poolInfo['auction_round']['token_decimals']))->toFloat();
                            $poolInfo['auction_round']['total_token_amount'] = BigDecimal::of($poolInfo['auction_round']['total_token_amount'])->exactlyDividedBy(pow(10, $poolInfo['auction_round']['token_decimals']))->toFloat();
                            $poolInfo['auction_round']['burned_token_amount'] = BigDecimal::of($poolInfo['auction_round']['burned_token_amount'])->exactlyDividedBy(pow(10, $poolInfo['auction_round']['token_decimals']))->toFloat();
                            $poolInfo['auction_round']['refund_token_amount'] = BigDecimal::of($poolInfo['auction_round']['refund_token_amount'])->exactlyDividedBy(pow(10, $poolInfo['auction_round']['token_decimals']))->toFloat();
                        }
                    });
                }
            }
            // </editor-fold>
        }
        // </editor-fold>

        $poolContractInstance->call($functionGetVestingInfo, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['active_vesting'] = $res['activeVesting'];
                if ($poolInfo['active_vesting']) {
                    $vestingPeriod = $res['vestingPeriod'];
                    $vestingPercent = $res['vestingPercent'];
                    foreach ($vestingPeriod as $item) {
                        $poolInfo['list_vesting_period'][] = intval($item->toString());
                    }

                    foreach ($vestingPercent as $item) {
                        $poolInfo['list_vesting_percent'][] = doubleval($item->toString() / 10);
                    }
                }
            }
        });

        $poolContractInstance->call(ContractLibrary::FUNCTION_GET_WHITELISTED_USER_LENGTH, null, function ($err, $res) use (&$poolInfo) {
            if ($res) {
                $poolInfo['whitelisted_users_length'] = intval($res[0]->toString());
            }
        });

        $poolInfo['message'] = "Pool created";

        // <editor-fold desc = "Insert token info">
        $checkTokenInfo = $tokensCollection->findOne([
            'address' => $poolInfo['pool_token_address'],
            'network' => $poolInfo['network'],
            'platform' => $poolInfo['platform']
        ]);

        if (!$checkTokenInfo) {
            $tokenInsertData = [
                'address' => $poolInfo['pool_token_address'],
                'name' => $poolInfo['pool_token_name'],
                'symbol' => $poolInfo['pool_token_symbol'],
                'decimals' => $poolInfo['pool_token_decimals'],
                'total_supply_token' => $poolInfo['pool_token_total_supply'],
                'token_lock_value' => 0,
                'network' => $poolInfo['network'],
                'platform' => $poolInfo['platform'],
                'circulating_supply_amount' => $poolInfo['pool_token_total_supply'],
                'circulating_supply_percent' => 100,
                'status' => ContractLibrary::INACTIVE
            ];

            $tokensCollection->insertOne($tokenInsertData);
        }
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        $poolCollection->insertOne($poolInfo);
    }
}
