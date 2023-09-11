<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use Exception;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class SaleGeneratorContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Create Presale
     * @throws Exception
     */
    public function processCreateSale($transaction, $dataDecode)
    {
        $coinInstance = $this->web3;
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, 1);
        $presaleCollection = $this->mongo->selectCollection('presale');
        $tokensCollection = $this->mongo->selectCollection('tokens');

        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $tokenContract = new Contract($this->web3->rpcConnector->getProvider(), $abiToken);

        $transactionReceiptData = $this->web3->getTransactionReceipt($transaction['hash']);
        $logsData = Arrays::arrayFrom($transactionReceiptData['logs']);
        $eventLogData = [];
        foreach ($logsData as $logItem) {
            if (isset($logItem['address'])) {
                $logItem['address'] = $this->web3->toCheckSumAddress($logItem['address']);
                if ($logItem['address'] == $transaction['to']) {
                    $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::SALE_GENERATOR);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }

        $eventDataDecode = $eventLogData['data_decode'];
        $saleAddress = $coinInstance->toCheckSumAddress($eventDataDecode[1]);
        $saleInfo['contract_address'] = $saleAddress;
        $saleInfo['platform'] = $transaction['platform'];
        $saleInfo['network'] = $transaction['network'];
        $saleInfo['creation_fee'] = $transaction['value'];
        $saleInfo['created_at'] = $transaction['timestamp'];
        $saleInfo['project_type'] = ContractLibrary::PROJECT_TYPE_SALE;
        $saleInfo['sale_type'] = ContractLibrary::SALE_TYPE_IDO;
        $saleInfo['is_show'] = ContractLibrary::ACTIVE;
        $saleInfo['hash'] = $transaction['hash'];

        // <editor-fold desc = "Init Presale Contract Instance By Default Version">
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Current Contract Version">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_GENERAL_INFO, null, function ($err, $res) use (&$contractVersion, &$saleInfo) {
            if ($res) {
                $contractVersion = intval($res['contractVersion']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Re-init Contract Instance By Right Version">
        $abiSale = ContractLibrary::getAbi(ContractLibrary::SALE, $contractVersion);
        $saleContract = new Contract($this->web3->rpcConnector->getProvider(), $abiSale);
        $saleContractInstance = $saleContract->at($saleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get General Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_GENERAL_INFO, null, function ($err, $res) use (&$contractVersion, &$saleInfo) {
            if ($res) {
                $saleInfo['contract_version'] = $contractVersion;
                $saleInfo['presale_generator'] = $res['saleGenerator'];
                $saleInfo['contract_type'] = $res['contractType'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Address Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_SALE_ADDRESS_INFO, null, function ($err, $res) use (&$saleInfo, $coinInstance) {
            if ($res) {
                $saleInfo['presale_owner_address'] = $coinInstance->toCheckSumAddress($res['saleOwner']);
                $saleInfo['fund_address'] = $coinInstance->toCheckSumAddress($res['fundAddress']);
                $saleInfo['sale_token_address'] = $coinInstance->toCheckSumAddress($res['saleToken']);
                $saleInfo['base_token_address'] = $coinInstance->toCheckSumAddress($res['baseToken']);
                $saleInfo['wrap_token_address'] = $coinInstance->toCheckSumAddress($res['wrapTokenAddress']);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Sale Token Info">
        $saleTokenContractInstance = $tokenContract->at($saleInfo['sale_token_address']);
        $saleTokenContractInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['sale_token_decimals'] = intval($res[0]->toString());
            }
        });
        $saleTokenContractInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['sale_token_name'] = $res[0];
            }
        });
        $saleTokenContractInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['sale_token_symbol'] = $res[0];
            }
        });
        $saleTokenContractInstance->call(ContractLibrary::FUNCTION_TOTAL_SUPPLY, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['sale_token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $saleInfo['sale_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Base Token Info">
        $baseTokenContractInstance = $tokenContract->at($saleInfo['base_token_address']);
        $baseTokenContractInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['base_token_decimals'] = intval($res[0]->toString());
            }
        });
        $baseTokenContractInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['base_token_name'] = $res[0];
            }
        });
        $baseTokenContractInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['base_token_symbol'] = $res[0];
            }
        });
        $baseTokenContractInstance->call(ContractLibrary::FUNCTION_TOTAL_SUPPLY, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['base_token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_SALE_MAIN_INFO, null, function ($err, $res) use (&$saleInfo, $coinInstance) {
            if ($res) {
                $saleInfo['token_price'] = BigDecimal::of($res['tokenPrice']->toString())->exactlyDividedBy(pow(10, $saleInfo['sale_token_decimals']))->toFloat();
                $saleInfo['limit_per_buyer'] = BigDecimal::of($res['limitPerBuyer']->toString())->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
                $saleInfo['amount'] = BigDecimal::of($res['amount']->toString())->exactlyDividedBy(pow(10, $saleInfo['sale_token_decimals']))->toFloat();
                $saleInfo['hard_cap'] = BigDecimal::of($res['hardCap']->toString())->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
                $saleInfo['soft_cap'] = BigDecimal::of($res['softCap']->toString())->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
                $saleInfo['start_time'] = intval($res['startTime']->toString());
                $saleInfo['end_time'] = intval($res['endTime']->toString());
                $saleInfo['presale_in_main_token'] = $res['saleInMainToken'];
            }
        });
        // </editor-fold>

        if ($saleInfo['presale_in_main_token']) {
            $saleInfo['base_token_symbol'] = strtoupper(Adapter::getMainCurrency($transaction['platform']));
        }

        // <editor-fold desc = "Get Status Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_STATUS_INFO, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['whitelist_only'] = $res['whitelistOnly'];
                $saleInfo['active_claim'] = $res['isActiveClaim'];
                $saleInfo['force_failed'] = $res['forceFailed'];
                $saleInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
                $saleInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $saleInfo['sale_token_decimals']))->toFloat();
                $saleInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $saleInfo['sale_token_decimals']))->toFloat();
                $saleInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
                $saleInfo['first_round_length'] = intval($res['firstRoundLength']->toString());
                $saleInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $saleInfo['success_at'] = intval($res['successAt']->toString());
                $saleInfo['is_active_claim'] = $res['isActiveClaim'];
                $saleInfo['active_claim_at'] = intval($res['activeClaimAt']->toString());
                $saleInfo['current_status'] = intval($res['currentStatus']->toString());
                $saleInfo['current_round'] = intval($res['currentRound']->toString());
                $saleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $saleInfo['current_round'] = -1;
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Fee Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_FEE_INFO, null, function ($err, $res) use (&$saleInfo, $coinInstance) {
            if ($res) {
                $baseFeePercent = ((!empty($res['baseFeePercent'])) && ($res['baseFeePercent'] instanceof BigInteger)) ? $res['baseFeePercent']->toString() : 0;
                $saleInfo['base_fee_percent'] = $baseFeePercent / 10;

                $tokenFeePercent = ((!empty($res['tokenFeePercent'])) && ($res['tokenFeePercent'] instanceof BigInteger)) ? $res['tokenFeePercent']->toString() : 0;
                $saleInfo['token_fee_percent'] = $tokenFeePercent / 10;

                $saleInfo['base_fee_address'] = $res['baseFeeAddress'];
                $saleInfo['token_fee_address'] = $res['tokenFeeAddress'];
            }
        });
        // </editor-fold>
        // <editor-fold desc = "Get Round Info">
        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_ROUND_INFO, null, function ($err, $res) use (&$saleInfo, $coinInstance) {
            if ($res) {
                $saleInfo['active_zero_round'] = $res['activeZeroRound'];
                $saleInfo['active_first_round'] = $res['activeFirstRound'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        if ($saleInfo['active_zero_round']) {
            $saleContractInstance->call(ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO, null, function ($err, $res) use (&$saleInfo, $coinInstance) {
                if ($res) {
                    $tokenAmount = ((!empty($res['tokenAmount'])) && ($res['tokenAmount'] instanceof BigInteger)) ? $res['tokenAmount']->toString() : 0;
                    $percent = ((!empty($res['percent'])) && ($res['percent'] instanceof BigInteger)) ? $res['percent']->toString() : 0;
                    $percent = $percent / 10;
                    $finishBeforeFirstRound = ((!empty($res['finishBeforeFirstRound'])) && ($res['finishBeforeFirstRound'] instanceof BigInteger)) ? $res['finishBeforeFirstRound']->toString() : 0;
                    $finishAt = ((!empty($res['finishAt'])) && ($res['finishAt'] instanceof BigInteger)) ? $res['finishAt']->toString() : 0;
                    $maxBaseTokenAmount = ((!empty($res['maxBaseTokenAmount'])) && ($res['maxBaseTokenAmount'] instanceof BigInteger)) ? $res['maxBaseTokenAmount']->toString() : 0;
                    $maxSlot = ((!empty($res['maxSlot'])) && ($res['maxSlot'] instanceof BigInteger)) ? $res['maxSlot']->toString() : 0;
                    $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;

                    $saleInfo['zero_round'] = [
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
            if (strlen($saleInfo['zero_round']['token_address'])) {
                if ($saleInfo['zero_round']['token_address'] != ContractLibrary::ADDRESS_ZERO) {
                    $zeroTokenContractInstance = $tokenContract->at($saleInfo['zero_round']['token_address']);
                    $zeroTokenContractInstance->call(ContractLibrary::FUNCTION_DECIMALS, null, function ($err, $res) use (&$saleInfo) {
                        if ($res) {
                            $saleInfo['zero_round']['token_decimals'] = intval($res[0]->toString());
                        }
                    });
                    $zeroTokenContractInstance->call(ContractLibrary::FUNCTION_NAME, null, function ($err, $res) use (&$saleInfo) {
                        if ($res) {
                            $saleInfo['zero_round']['token_name'] = $res[0];
                        }
                    });
                    $zeroTokenContractInstance->call(ContractLibrary::FUNCTION_SYMBOL, null, function ($err, $res) use (&$saleInfo) {
                        if ($res) {
                            $saleInfo['zero_round']['token_symbol'] = $res[0];
                        }
                    });
                    $zeroTokenContractInstance->call(ContractLibrary::FUNCTION_TOTAL_SUPPLY, null, function ($err, $res) use (&$saleInfo) {
                        if ($res) {
                            $saleInfo['zero_round']['token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $saleInfo['zero_round']['token_decimals']))->toFloat();
                            $saleInfo['zero_round']['token_amount'] = BigDecimal::of($saleInfo['zero_round']['token_amount'])->exactlyDividedBy(pow(10, $saleInfo['zero_round']['token_decimals']))->toFloat();
                            $saleInfo['zero_round']['max_base_token_amount'] = BigDecimal::of($saleInfo['zero_round']['max_base_token_amount'])->exactlyDividedBy(pow(10, $saleInfo['base_token_decimals']))->toFloat();
                        }
                    });
                }
            }
            // </editor-fold>

        }
        // </editor-fold>

        $saleContractInstance->call(ContractLibrary::FUNCTION_GET_VESTING_INFO, null, function ($err, $res) use (&$saleInfo) {
            if ($res) {
                $saleInfo['active_vesting'] = $res['activeVesting'];
                if ($saleInfo['active_vesting']) {
                    $saleInfo['sale_type'] = ContractLibrary::SALE_TYPE_IDOV;
                    $vestingPeriod = $res['vestingPeriod'];
                    $vestingPercent = $res['vestingPercent'];

                    foreach ($vestingPeriod as $item) {
                        $saleInfo['list_vesting_period'][] = intval($item->toString());
                    }

                    foreach ($vestingPercent as $item) {
                        $saleInfo['list_vesting_percent'][] = doubleval($item->toString() / 10);
                    }
                }
            }
        });

        $saleInfo['base_fee_amount'] = 0;
        $saleInfo['sale_token_fee_amount'] = 0;
        $saleTokenFeeAmount = $saleInfo['amount'] / 100 * $saleInfo['token_fee_percent'];
        $saleInfo['sale_token_free_amount'] = $saleInfo['sale_token_total_supply'] - $saleInfo['amount'] - $saleTokenFeeAmount;
        $saleInfo['message'] = "Sale created";

        // <editor-fold desc = "Insert token info">
        $checkTokenInfo = $tokensCollection->findOne([
            'address' => $saleInfo['sale_token_address'],
            'network' => $saleInfo['network'],
            'platform' => $saleInfo['platform']
        ]);
        if (!$checkTokenInfo) {
            $tokenInsertData = [
                'address' => $saleInfo['sale_token_address'],
                'name' => $saleInfo['sale_token_name'],
                'symbol' => $saleInfo['sale_token_symbol'],
                'decimals' => $saleInfo['sale_token_decimals'],
                'total_supply_token' => $saleInfo['sale_token_total_supply'],
                'token_lock_value' => 0,
                'network' => $saleInfo['network'],
                'platform' => $saleInfo['platform'],
                'circulating_supply_amount' => $saleInfo['sale_token_total_supply'],
                'circulating_supply_percent' => 100,
                'status' => ContractLibrary::INACTIVE
            ];
            $tokensCollection->insertOne($tokenInsertData);
        }
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        $presaleCollection->insertOne($saleInfo);
    }
}
