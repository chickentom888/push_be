<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use DCrypto\Adapter;
use Exception;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class PresaleGeneratorContractService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Create Presale
     * @throws Exception
     */
    public function processCreatePresale($transaction, $dataDecode)
    {
        $coinInstance = $this->web3;
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, 1);
        $presaleCollection = $this->mongo->selectCollection('presale');
        $tokensCollection = $this->mongo->selectCollection('tokens');

        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $tokenContract = new Contract($this->web3->rpcConnector->getProvider(), $abiToken);

        $functionGetZeroRoundInfo = ContractLibrary::FUNCTION_GET_ZERO_ROUND_INFO;
        $functionGetFeeInfo = ContractLibrary::FUNCTION_GET_FEE_INFO;
        $functionGetGeneralInfo = ContractLibrary::FUNCTION_GET_GENERAL_INFO;
        $functionGetPresaleAddressInfo = ContractLibrary::FUNCTION_GET_PRESALE_ADDRESS_INFO;
        $functionGetPresaleMainInfo = ContractLibrary::FUNCTION_GET_PRESALE_MAIN_INFO;
        $functionGetStatusInfo = ContractLibrary::FUNCTION_GET_STATUS_INFO;
        $functionGetRoundInfo = ContractLibrary::FUNCTION_GET_ROUND_INFO;
        $functionGetVestingInfo = ContractLibrary::FUNCTION_GET_VESTING_INFO;

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
                    $eventLogData = $this->web3->decodeEventInputData($logItem, ContractLibrary::PRESALE_GENERATOR);
                }
            }
        }

        if (!isset($eventLogData['data_decode'][0])) {
            throw new Exception("Invalid Event Data");
        }

        $eventDataDecode = $eventLogData['data_decode'];
        $presaleAddress = $coinInstance->toCheckSumAddress($eventDataDecode[1]);

        $presaleInfo['contract_address'] = $presaleAddress;
        $presaleInfo['platform'] = $transaction['platform'];
        $presaleInfo['network'] = $transaction['network'];
        $presaleInfo['creation_fee'] = $transaction['value'];
        $presaleInfo['created_at'] = $transaction['timestamp'];
        $presaleInfo['project_type'] = ContractLibrary::PROJECT_TYPE_PRESALE;
        $presaleInfo['sale_type'] = ContractLibrary::SALE_TYPE_ILO;
        $presaleInfo['is_show'] = ContractLibrary::ACTIVE;
        $presaleInfo['hash'] = $transaction['hash'];

        // <editor-fold desc = "Init Presale Contract Instance By Default Version">
        $presaleContract = new Contract($coinInstance->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Get Current Contract Version">
        $presaleContractInstance->call($functionGetGeneralInfo, null, function ($err, $res) use (&$contractVersion, &$presaleInfo) {
            if ($res) {
                $contractVersion = intval($res['contractVersion']->toString());
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Re-init Contract Instance By Right Version">
        $abiPresale = ContractLibrary::getAbi(ContractLibrary::PRESALE, $contractVersion);
        $presaleContract = new Contract($coinInstance->rpcConnector->getProvider(), $abiPresale);
        $presaleContractInstance = $presaleContract->at($presaleAddress);
        // </editor-fold>

        // <editor-fold desc = "Update General Info">
        $presaleContractInstance->call($functionGetGeneralInfo, null, function ($err, $res) use (&$contractVersion, &$presaleInfo) {
            if ($res) {
                $presaleInfo['contract_version'] = $contractVersion;
                $presaleInfo['presale_generator'] = $res['presaleGenerator'];
                $presaleInfo['contract_type'] = $res['contractType'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Address Info">
        $presaleContractInstance->call($functionGetPresaleAddressInfo, null, function ($err, $res) use (&$presaleInfo, $coinInstance) {
            if ($res) {
                $presaleInfo['presale_owner_address'] = $coinInstance->toCheckSumAddress($res['presaleOwner']);
                $presaleInfo['sale_token_address'] = $coinInstance->toCheckSumAddress($res['saleToken']);
                $presaleInfo['base_token_address'] = $coinInstance->toCheckSumAddress($res['baseToken']);
                $presaleInfo['wrap_token_address'] = $coinInstance->toCheckSumAddress($res['wrapTokenAddress']);
                $presaleInfo['dex_locker_address'] = $coinInstance->toCheckSumAddress($res['dexLockerAddress']);
                $presaleInfo['dex_factory_address'] = $coinInstance->toCheckSumAddress($res['dexFactoryAddress']);
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Sale Token Info">
        $saleTokenContractInstance = $tokenContract->at($presaleInfo['sale_token_address']);
        $saleTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['sale_token_decimals'] = intval($res[0]->toString());
            }
        });
        $saleTokenContractInstance->call($functionName, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['sale_token_name'] = $res[0];
            }
        });
        $saleTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['sale_token_symbol'] = $res[0];
            }
        });
        $saleTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['sale_token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $presaleInfo['sale_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Base Token Info">
        $baseTokenContractInstance = $tokenContract->at($presaleInfo['base_token_address']);
        $baseTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['base_token_decimals'] = intval($res[0]->toString());
            }
        });
        $baseTokenContractInstance->call($functionName, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['base_token_name'] = $res[0];
            }
        });
        $baseTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['base_token_symbol'] = $res[0];
            }
        });
        $baseTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['base_token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Main Info">
        $presaleContractInstance->call($functionGetPresaleMainInfo, null, function ($err, $res) use (&$presaleInfo, $coinInstance) {
            if ($res) {
                $presaleInfo['token_price'] = BigDecimal::of($res['tokenPrice']->toString())->exactlyDividedBy(pow(10, $presaleInfo['sale_token_decimals']))->toFloat();
                $presaleInfo['limit_per_buyer'] = BigDecimal::of($res['limitPerBuyer']->toString())->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
                $presaleInfo['amount'] = BigDecimal::of($res['amount']->toString())->exactlyDividedBy(pow(10, $presaleInfo['sale_token_decimals']))->toFloat();
                $presaleInfo['hard_cap'] = BigDecimal::of($res['hardCap']->toString())->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
                $presaleInfo['soft_cap'] = BigDecimal::of($res['softCap']->toString())->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
                $presaleInfo['listing_price'] = BigDecimal::of($res['listingPrice']->toString())->exactlyDividedBy(pow(10, $presaleInfo['sale_token_decimals']))->toFloat();
                $presaleInfo['listing_price_percent'] = 100 - ($presaleInfo['listing_price'] / $presaleInfo['token_price'] * 100);
                $presaleInfo['liquidity_percent'] = $res['liquidityPercent']->toString() / 10;
                $presaleInfo['start_time'] = intval($res['startTime']->toString());
                $presaleInfo['end_time'] = intval($res['endTime']->toString());
                $presaleInfo['lock_period'] = intval($res['lockPeriod']->toString());
                $presaleInfo['presale_in_main_token'] = $res['presaleInMainToken'];
                $presaleInfo['max_buyer'] = ceil($presaleInfo['hard_cap'] / $presaleInfo['limit_per_buyer']);
            }
        });
        // </editor-fold>

        if ($presaleInfo['presale_in_main_token']) {
            $presaleInfo['base_token_symbol'] = strtoupper(Adapter::getMainCurrency($transaction['platform']));
        }

        // <editor-fold desc = "Get Status Info">
        $presaleContractInstance->call($functionGetStatusInfo, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['whitelist_only'] = $res['whitelistOnly'];
                $presaleInfo['lp_generation_complete'] = $res['lpGenerationComplete'];
                $presaleInfo['force_failed'] = $res['forceFailed'];
                $presaleInfo['total_base_collected'] = BigDecimal::of($res['totalBaseCollected']->toString())->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
                $presaleInfo['total_token_sold'] = BigDecimal::of($res['totalTokenSold']->toString())->exactlyDividedBy(pow(10, $presaleInfo['sale_token_decimals']))->toFloat();
                $presaleInfo['total_token_withdrawn'] = BigDecimal::of($res['totalTokenWithdrawn']->toString())->exactlyDividedBy(pow(10, $presaleInfo['sale_token_decimals']))->toFloat();
                $presaleInfo['total_base_withdrawn'] = BigDecimal::of($res['totalBaseWithdrawn']->toString())->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
                $presaleInfo['first_round_length'] = intval($res['firstRoundLength']->toString());
                $presaleInfo['num_buyers'] = intval($res['numBuyers']->toString());
                $presaleInfo['success_at'] = intval($res['successAt']->toString());
                $presaleInfo['liquidity_at'] = intval($res['liquidityAt']->toString());
                $presaleInfo['current_status'] = intval($res['currentStatus']->toString());
                $presaleInfo['current_round'] = intval($res['currentRound']->toString());
                $presaleInfo['current_round'] > ContractLibrary::MAX_CURRENT_ROUND && $presaleInfo['current_round'] = -1;
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Fee Info">
        $presaleContractInstance->call($functionGetFeeInfo, null, function ($err, $res) use (&$presaleInfo, $coinInstance) {
            if ($res) {
                $baseFeePercent = ((!empty($res['baseFeePercent'])) && ($res['baseFeePercent'] instanceof BigInteger)) ? $res['baseFeePercent']->toString() : 0;
                $presaleInfo['base_fee_percent'] = $baseFeePercent / 10;

                $tokenFeePercent = ((!empty($res['tokenFeePercent'])) && ($res['tokenFeePercent'] instanceof BigInteger)) ? $res['tokenFeePercent']->toString() : 0;
                $presaleInfo['token_fee_percent'] = $tokenFeePercent / 10;


                $presaleInfo['base_fee_address'] = $res['baseFeeAddress'];
                $presaleInfo['token_fee_address'] = $res['tokenFeeAddress'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Round Info">
        $presaleContractInstance->call($functionGetRoundInfo, null, function ($err, $res) use (&$presaleInfo, $coinInstance) {
            if ($res) {
                $presaleInfo['active_zero_round'] = $res['activeZeroRound'];
                $presaleInfo['active_first_round'] = $res['activeFirstRound'];
            }
        });
        // </editor-fold>

        // <editor-fold desc = "Get Zero Round Info">
        if ($presaleInfo['active_zero_round']) {
            $presaleContractInstance->call($functionGetZeroRoundInfo, null, function ($err, $res) use (&$presaleInfo, $coinInstance) {
                if ($res) {
                    $tokenAmount = ((!empty($res['tokenAmount'])) && ($res['tokenAmount'] instanceof BigInteger)) ? $res['tokenAmount']->toString() : 0;
                    $percent = ((!empty($res['percent'])) && ($res['percent'] instanceof BigInteger)) ? $res['percent']->toString() : 0;
                    $percent = $percent / 10;
                    $finishBeforeFirstRound = ((!empty($res['finishBeforeFirstRound'])) && ($res['finishBeforeFirstRound'] instanceof BigInteger)) ? $res['finishBeforeFirstRound']->toString() : 0;
                    $finishAt = ((!empty($res['finishAt'])) && ($res['finishAt'] instanceof BigInteger)) ? $res['finishAt']->toString() : 0;
                    $maxBaseTokenAmount = ((!empty($res['maxBaseTokenAmount'])) && ($res['maxBaseTokenAmount'] instanceof BigInteger)) ? $res['maxBaseTokenAmount']->toString() : 0;
                    $maxSlot = ((!empty($res['maxSlot'])) && ($res['maxSlot'] instanceof BigInteger)) ? $res['maxSlot']->toString() : 0;
                    $registeredSlot = ((!empty($res['registeredSlot'])) && ($res['registeredSlot'] instanceof BigInteger)) ? $res['registeredSlot']->toString() : 0;

                    $presaleInfo['zero_round'] = [
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
            if (strlen($presaleInfo['zero_round']['token_address'])) {
                if ($presaleInfo['zero_round']['token_address'] != ContractLibrary::ADDRESS_ZERO) {

                    $zeroTokenContractInstance = $tokenContract->at($presaleInfo['zero_round']['token_address']);
                    $zeroTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$presaleInfo) {
                        if ($res) {
                            $presaleInfo['zero_round']['token_decimals'] = intval($res[0]->toString());
                        }
                    });
                    $zeroTokenContractInstance->call($functionName, null, function ($err, $res) use (&$presaleInfo) {
                        if ($res) {
                            $presaleInfo['zero_round']['token_name'] = $res[0];
                        }
                    });
                    $zeroTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$presaleInfo) {
                        if ($res) {
                            $presaleInfo['zero_round']['token_symbol'] = $res[0];
                        }
                    });

                    $zeroTokenContractInstance->call($functionSupply, null, function ($err, $res) use (&$presaleInfo) {
                        if ($res) {
                            $presaleInfo['zero_round']['token_total_supply'] = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, $presaleInfo['zero_round']['token_decimals']))->toFloat();
                            $presaleInfo['zero_round']['token_amount'] = BigDecimal::of($presaleInfo['zero_round']['token_amount'])->exactlyDividedBy(pow(10, $presaleInfo['zero_round']['token_decimals']))->toFloat();
                            $presaleInfo['zero_round']['max_base_token_amount'] = BigDecimal::of($presaleInfo['zero_round']['max_base_token_amount'])->exactlyDividedBy(pow(10, $presaleInfo['base_token_decimals']))->toFloat();
                        }
                    });
                }
            }
            // </editor-fold>
        }
        // </editor-fold>

        $presaleContractInstance->call($functionGetVestingInfo, null, function ($err, $res) use (&$presaleInfo) {
            if ($res) {
                $presaleInfo['active_vesting'] = $res['activeVesting'];
                if ($presaleInfo['active_vesting']) {
                    $presaleInfo['sale_type'] = ContractLibrary::SALE_TYPE_ILOV;
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

        $presaleInfo['base_fee_amount'] = 0;
        $baseTokenFeeAmount = $presaleInfo['hard_cap'] / 100 * $presaleInfo['base_fee_percent'];
        $hardCapAfterFee = $presaleInfo['hard_cap'] - $baseTokenFeeAmount;
        $presaleInfo['base_token_liquidity_amount'] = $hardCapAfterFee / 100 * $presaleInfo['liquidity_percent'];

        $presaleInfo['sale_token_liquidity_amount'] = $presaleInfo['base_token_liquidity_amount'] * $presaleInfo['listing_price'];
        $presaleInfo['sale_token_fee_amount'] = 0;
        $presaleTokenFeeAmount = $presaleInfo['amount'] / 100 * $presaleInfo['token_fee_percent'];
        $presaleInfo['sale_token_free_amount'] = $presaleInfo['sale_token_total_supply'] - $presaleInfo['amount'] - $presaleTokenFeeAmount - $presaleInfo['sale_token_liquidity_amount'];
        $presaleInfo['message'] = "Presale created";

        // <editor-fold desc = "Insert token info">
        $checkTokenInfo = $tokensCollection->findOne([
            'address' => $presaleInfo['sale_token_address'],
            'network' => $presaleInfo['network'],
            'platform' => $presaleInfo['platform']
        ]);

        if (!$checkTokenInfo) {
            $tokenInsertData = [
                'address' => $presaleInfo['sale_token_address'],
                'name' => $presaleInfo['sale_token_name'],
                'symbol' => $presaleInfo['sale_token_symbol'],
                'decimals' => $presaleInfo['sale_token_decimals'],
                'total_supply_token' => $presaleInfo['sale_token_total_supply'],
                'token_lock_value' => 0,
                'network' => $presaleInfo['network'],
                'platform' => $presaleInfo['platform'],
                'circulating_supply_amount' => $presaleInfo['sale_token_total_supply'],
                'circulating_supply_percent' => 100,
                'status' => ContractLibrary::INACTIVE
            ];

            $tokensCollection->insertOne($tokenInsertData);
        }
        // </editor-fold>

        // <editor-fold desc = "Update Transaction">
        $this->updateTransaction($transaction, $dataDecode);
        // </editor-fold>

        $presaleCollection->insertOne($presaleInfo);
    }
}
