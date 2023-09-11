<?php

namespace Dcore\Modules\Cli\Tasks;

use Brick\Math\BigDecimal;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Services\AirdropContractService;
use Dcore\Services\AirdropSettingContractService;
use Dcore\Services\LockContractService;
use Dcore\Services\LockSettingContractService;
use Dcore\Services\LotteryService;
use Dcore\Services\MintTokenFactoryContractService;
use Dcore\Services\MintTokenGeneratorContractService;
use Dcore\Services\MintTokenSettingContractService;
use Dcore\Services\PoolContractService;
use Dcore\Services\PoolFactoryContractService;
use Dcore\Services\PoolGeneratorContractService;
use Dcore\Services\PoolSettingContractService;
use Dcore\Services\PresaleContractService;
use Dcore\Services\PresaleFactoryContractService;
use Dcore\Services\PresaleGeneratorContractService;
use Dcore\Services\PresaleSettingContractService;
use Dcore\Services\SaleContractService;
use Dcore\Services\SaleFactoryContractService;
use Dcore\Services\SaleGeneratorContractService;
use Dcore\Services\SaleSettingContractService;
use Dcore\Services\StakingService;
use DCrypto\Adapter;
use Exception;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class Web3Task extends TaskBase
{

    public $platform;

    /**
     * Process Function Type
     * @param $transaction
     * @param $dataDecode
     * @throws Exception
     */
    public function processFunction($transaction, $dataDecode)
    {
        $network = $transaction['network'];
        $platform = $transaction['platform'];
        switch ($transaction['contract_type']) {
            case ContractLibrary::PRESALE_SETTING:
                $presaleSettingService = PresaleSettingContractService::getInstance($network, $platform);
                $presaleSettingService->processUpdatePresaleSettingByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::PRESALE_FACTORY:
                $presaleFactoryService = PresaleFactoryContractService::getInstance($network, $platform);
                $presaleFactoryService->processUpdatePresaleGeneratorByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::PRESALE_GENERATOR:
                $presaleGeneratorService = PresaleGeneratorContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_CREATE_PRESALE) {
                    $presaleGeneratorService->processCreatePresale($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::PRESALE:
                $presaleService = PresaleContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_BUY_TOKEN) {
                    $presaleService->processBuyToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_ADD_LIQUIDITY) {
                    $presaleService->processAddLiquidity($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_SALE_TOKEN) {
                    $presaleService->processUserWithdrawSaleToken($transaction, $dataDecode);
                } elseif (in_array($dataDecode['name'], [ContractLibrary::FUNCTION_USER_FORCE_FAIL, ContractLibrary::FUNCTION_ADMIN_FORCE_FAIL])) {
                    $presaleService->processForceFail($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_BASE_TOKEN) {
                    $presaleService->processUserWithdrawBaseToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_OWNER_WITHDRAW_SALE_TOKEN) {
                    $presaleService->processOwnerWithdrawSaleToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_LIMIT_PER_BUYER) {
                    $presaleService->processUpdateLimitPerBuyer($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_TIME) {
                    $presaleService->processUpdateTime($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_WHITELIST_FLAG) {
                    $presaleService->processSetWhitelistFlag($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_EDIT_WHITELIST) {
                    $presaleService->processEditWhitelist($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_REGISTER_ZERO_ROUND) {
                    $presaleService->processRegisterZeroRound($transaction, $dataDecode);
                }
                break;

            case ContractLibrary::SALE_SETTING:
                $saleSettingService = SaleSettingContractService::getInstance($network, $platform);
                $saleSettingService->processUpdateSaleSettingByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::SALE_FACTORY:
                $saleFactoryService = SaleFactoryContractService::getInstance($network, $platform);
                $saleFactoryService->processUpdateSaleGeneratorByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::SALE_GENERATOR:
                $saleGeneratorService = SaleGeneratorContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_CREATE_SALE) {
                    $saleGeneratorService->processCreateSale($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::SALE:
                $saleService = SaleContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_BUY_TOKEN) {
                    $saleService->processBuyTokenOfContractSale($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_ACTIVE_CLAIM) {
                    $saleService->processSaleActiveClaim($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_SALE_TOKEN) {
                    $saleService->processSaleContractUserWithdrawSaleToken($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_BASE_TOKEN) {
                    $saleService->processSaleContractUserWithdrawBaseToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_OWNER_WITHDRAW_SALE_TOKEN) {
                    $saleService->processSaleContractOwnerWithdrawSaleToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_LIMIT_PER_BUYER) {
                    $saleService->processSaleContractUpdateLimitPerBuyer($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_TIME) {
                    $saleService->processSaleContractUpdateTime($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_WHITELIST_FLAG) {
                    $saleService->processSaleContractSetWhitelistFlag($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_EDIT_WHITELIST) {
                    $saleService->processSaleContractEditWhitelist($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_REGISTER_ZERO_ROUND) {
                    $saleService->processSaleContractRegisterZeroRound($transaction, $dataDecode);
                } elseif (in_array($dataDecode['name'], [ContractLibrary::FUNCTION_OWNER_FORCE_FAIL, ContractLibrary::FUNCTION_ADMIN_FORCE_FAIL])) {
                    $saleService->processForceFailContractSale($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_VESTING_INFO) {
                    $saleService->processUpdateVestingInfo($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_FUND_ADDRESS) {
                    $saleService->processUpdateFundAddress($transaction, $dataDecode);
                }
                break;

            case ContractLibrary::MINT_TOKEN_FACTORY:
                $mintTokenFactory = MintTokenFactoryContractService::getInstance($network, $platform);
                $mintTokenFactory->processUpdateMintTokenGenerator($transaction, $dataDecode);
                break;
            case ContractLibrary::MINT_TOKEN_SETTING:
                $mintTokenSettingService = MintTokenSettingContractService::getInstance($network, $platform);
                $mintTokenSettingService->processUpdateMintTokenSettingByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::MINT_TOKEN_GENERATOR:
                $mintTokenGeneratorService = MintTokenGeneratorContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_CREATE_TOKEN) {
                    $mintTokenGeneratorService->processCreateToken($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::AIRDROP_SETTING:
                $airdropSettingService = AirdropSettingContractService::getInstance($network, $platform);
                $airdropSettingService->processUpdateAirdropSetting($transaction, $dataDecode);
                break;
            case ContractLibrary::AIRDROP_CONTRACT:
                $airdropService = AirdropContractService::getInstance($network, $platform);
                $airdropService->processAirdrop($transaction, $dataDecode);
                break;
            case ContractLibrary::LOCK_SETTING:
                $lockSettingService = LockSettingContractService::getInstance($network, $platform);
                $lockSettingService->processUpdateLockSetting($transaction, $dataDecode);
                break;
            case ContractLibrary::LOCK_CONTRACT:
                $lockService = LockContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_LOCK_TOKEN) {
                    $lockService->processLock($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_EXTEND_LOCK) {
                    $lockService->processExtendLock($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_TRANSFER_LOCK) {
                    $lockService->processTransferLock($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_WITHDRAW_LOCK) {
                    $lockService->processWithdrawLock($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::POOL_SETTING:
                $poolSettingService = PoolSettingContractService::getInstance($network, $platform);
                $poolSettingService->processUpdatePoolSettingByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::POOL_FACTORY:
                $poolFactoryService = PoolFactoryContractService::getInstance($network, $platform);
                $poolFactoryService->processUpdatePoolGeneratorByTransaction($transaction, $dataDecode);
                break;
            case ContractLibrary::POOL_GENERATOR:
                $poolGeneratorService = PoolGeneratorContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_CREATE_POOL) {
                    $poolGeneratorService->processCreatePool($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::POOL:
                $poolService = PoolContractService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_BUY_TOKEN) {
                    $poolService->processBuyToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_ACTIVE_CLAIM) {
                    $poolService->processPoolActiveClaim($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_ADMIN_FORCE_FAIL) {
                    $poolService->processForceFailContractPool($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_TIME) {
                    $poolService->processPoolContractUpdateTime($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_SET_WHITELIST_FLAG) {
                    $poolService->processPoolContractSetWhitelistFlag($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_EDIT_WHITELIST) {
                    $poolService->processPoolContractEditWhitelist($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_REGISTER_ZERO_ROUND) {
                    $poolService->processPoolContractRegisterZeroRound($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_REGISTER_AUCTION_ROUND) {
                    $poolService->processPoolContractRegisterAuctionRound($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_LIMIT_PER_BUYER) {
                    $poolService->processPoolContractUpdateLimitPerBuyer($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_POOL_TOKEN) {
                    $poolService->processPoolContractUserWithdrawPoolToken($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_BASE_TOKEN) {
                    $poolService->processPoolContractUserWithdrawBaseToken($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_USER_WITHDRAW_AUCTION_TOKEN) {
                    $poolService->processUserWithdrawAuctionToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_OWNER_WITHDRAW_BASE_TOKEN) {
                    $poolService->processPoolContractOwnerWithdrawBaseToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_OWNER_WITHDRAW_POOL_TOKEN) {
                    $poolService->processPoolContractOwnerWithdrawPoolToken($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_UPDATE_VESTING_INFO) {
                    $poolService->processUpdateVestingInfo($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::LOTTERY:
                $lotteryService = LotteryService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_START_LOTTERY) {
                    $lotteryService->processStartLottery($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_BUY_TICKETS) {
                    $lotteryService->processBuyTickets($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_CLOSE_LOTTERY) {
                    $lotteryService->processCloseLottery($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_CALCULATE_REWARD) {
                    $lotteryService->processCalculateReward($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_CLAIM_TICKETS) {
                    $lotteryService->processClaimTickets($transaction, $dataDecode);
                } elseif ($dataDecode['name'] == ContractLibrary::FUNCTION_INJECT_FUNDS) {
                    $lotteryService->processInjectFunds($transaction, $dataDecode);
                } else if (in_array($dataDecode['name'], [ContractLibrary::FUNCTION_CHANGE_RANDOM_GENERATOR, ContractLibrary::FUNCTION_SET_ADDRESS, ContractLibrary::FUNCTION_SET_MIN_AND_MAX_TICKET_PRICE])) {
                    $lotteryService->processUpdateSetting($transaction, $dataDecode);
                }
                break;
            case ContractLibrary::STAKING:
                $stakingService = StakingService::getInstance($network, $platform);
                if ($dataDecode['name'] == ContractLibrary::FUNCTION_STAKING) {
                    $stakingService->processStaking($transaction, $dataDecode);
                } else if ($dataDecode['name'] == ContractLibrary::FUNCTION_BUY) {
                    $stakingService->processBuy($transaction, $dataDecode);
                } else {
                    $stakingService->processUpdateSetting($transaction, $dataDecode);
                }
                break;
        }
    }

    /**
     * Get Price Main Token
     * @return float|mixed
     */
    protected function getPricePlatformToken($platform)
    {
        $mainCurrency = Adapter::getMainCurrency($platform);
        $registry = $this->mongo->selectCollection('registry')->findOne();
        $price = 0;
        if ($registry) {
            $key = "{$mainCurrency}_price";
            $price = $registry[$key];
        }
        if ($price <= 0) {
            $price = ContractLibrary::getPriceByPlatform($platform);
        }

        return $price;
    }

    /**
     * Get Price Data
     * By Get Platform Token And Main Token In Pool
     * @param $platform
     * @param $network
     * @param $mainTokenInfo
     * @return float[]|int[]
     * @throws Exception
     */
    protected function getPriceTokenData($platform, $network, $mainTokenInfo)
    {
        $dexFactoryAbi = ContractLibrary::getAbi(ContractLibrary::DEX_FACTORY);
        $dexFactoryAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::DEX_FACTORY);
        $dexWrapTokenAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::DEX_WRAP_TOKEN);

        $mainTokenAddress = $mainTokenInfo['address'];
        $web3Provider = $this->web3->rpcConnector->getProvider();
        $factoryContract = new Contract($web3Provider, $dexFactoryAbi);
        $dexFactoryContract = $factoryContract->at($dexFactoryAddress);
        $functionGetPair = 'getPair';
        $addressPair = '';
        $dexFactoryContract->call($functionGetPair, $mainTokenAddress, $dexWrapTokenAddress, function ($err, $res) use (&$addressPair) {
            if ($res) {
                $addressPair = $res[0];
            }
        });

        if (strlen($addressPair)) {
            $addressPair = $this->web3->toCheckSumAddress($addressPair);
            $addressPair == ContractLibrary::ADDRESS_ZERO && $addressPair = '';
        }

        $tokenPriceUsd = $mainTokenBalance = $wrapTokenBalance = $wrapTokenUsd = 0;

        if (strlen($addressPair)) {
            $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
            $tokenContract = new Contract($web3Provider, $tokenAbi);
            $mainTokenContract = $tokenContract->at($mainTokenAddress);
            $functionBalance = 'balanceOf';

            $mainTokenContract->call($functionBalance, $addressPair, function ($err, $res) use (&$mainTokenBalance) {
                if ($res) {
                    $mainTokenBalance = $res[0]->toString();
                }
            });
            $mainTokenBalance = $mainTokenBalance / pow(10, $mainTokenInfo['decimals']);

            $wrapTokenContract = $tokenContract->at($dexWrapTokenAddress);
            $wrapTokenContract->call($functionBalance, $addressPair, function ($err, $res) use (&$wrapTokenBalance) {
                if ($res) {
                    $wrapTokenBalance = $res[0]->toString();
                }
            });
            $wrapTokenBalance = $wrapTokenBalance / pow(10, ContractLibrary::DEFAULT_DECIMALS);

            $pricePlatformToken = $this->getPricePlatformToken($platform);
            $wrapTokenUsd = $wrapTokenBalance * $pricePlatformToken;
            $tokenPriceUsd = $mainTokenBalance > 0 ? $wrapTokenUsd / $mainTokenBalance : 0;
        }

        return [
            'token_pool' => $mainTokenBalance,
            'token_price_usd' => $tokenPriceUsd,
            'total_supply_usd' => $tokenPriceUsd * $mainTokenInfo['total_supply'],
            'sub_token_balance' => $wrapTokenBalance,
            'sub_token_usd' => $wrapTokenUsd,
            'dex_address_pair' => $addressPair
        ];
    }

    /**
     * Update And Calculate Liquid Percent
     * @param $tokenInfo
     * @return float|int
     * @throws Exception
     */
    protected function calculateLiquidPercent($tokenInfo)
    {
        $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $tokenAbi);
        $functionSupply = 'totalSupply';
        $lockHistoryCollection = $this->mongo->selectCollection('lock_histories');
        $listData = $lockHistoryCollection->find([
            'token_address' => $tokenInfo['address'],
            'network' => $tokenInfo['network'],
            'platform' => $tokenInfo['platform'],
            'type' => ContractLibrary::LOCK_TYPE_LIQUID,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW
        ]);
        $liquidPercent = 0;
        $totalLiquidSupply = 0;
        $totalLiquidLock = 0;

        if (!empty($listData)) {
            $listData = $listData->toArray();
            $listData = Arrays::groupArray($listData, 'contract_address');

            if (count($listData)) {
                foreach ($listData as $contractAddress => $listHistory) {

                    // <editor-fold desc = "Get Total Supply Main Token">
                    $contractInstance = $contract->at($contractAddress);
                    $contractInstance->call($functionSupply, null, function ($err, $res) use (&$totalSupply) {
                        if ($res) {
                            $totalSupply = BigDecimal::of($res[0]->toString())->exactlyDividedBy(pow(10, ContractLibrary::DEFAULT_DECIMALS))->toFloat();
                        }
                    });
                    $totalLiquidSupply += $totalSupply;
                    // </editor-fold>

                    foreach ($listHistory as $historyItem) {
                        $dataUpdate = [
                            'percent' => $historyItem['amount'] / $totalSupply * 100
                        ];
                        $lockHistoryCollection->updateOne(['_id' => $historyItem['_id']], ['$set' => $dataUpdate]);
                        $totalLiquidLock += $historyItem['amount'];
                    }
                }
                $liquidPercent = $totalLiquidLock / $totalLiquidSupply * 100;
            }
        }
        return $liquidPercent;
    }

    /**
     * Calculate Token Percent
     * @param $tokenInfo
     * @return array
     * @throws Exception
     */
    protected function calculateTokenPercent($tokenInfo)
    {
        /*$dataAggregate = $this->mongo->selectCollection('lock_histories')->aggregate([
            [
                '$match' => [
                    'token_address' => $tokenInfo['address'],
                    'type' => ContractLibrary::LOCK_TYPE_TOKEN,
                    'withdraw_status' => ContractLibrary::NOT_WITHDRAW
                ],
            ],
            [
                '$group' => [
                    '_id' => null,
                    'amount' => ['$sum' => '$amount']
                ]
            ],
        ]);
        $data = $dataAggregate->toArray();*/

        $listData = $this->mongo->selectCollection('lock_histories')->find([
            'token_address' => $tokenInfo['address'],
            'network' => $tokenInfo['network'],
            'platform' => $tokenInfo['platform'],
            'type' => ContractLibrary::LOCK_TYPE_TOKEN,
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW
        ]);

        $tokenLockAmount = 0;
        $contractAddress = $tokenInfo['address'];
        $tokenAbi = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contract = new Contract($this->web3->rpcConnector->getProvider(), $tokenAbi);
        $functionSupply = 'totalSupply';
        $contractInstance = $contract->at($contractAddress);
        $contractInstance->call($functionSupply, null, function ($err, $res) use (&$totalSupply) {
            if ($res) {
                $totalSupply = $res[0]->toString();
            }
        });
        [$totalSupply] = (new BigInteger($totalSupply))->divide((new BigInteger(pow(10, $tokenInfo['decimals']))));
        $totalSupply = (doubleval($totalSupply->toString()));

        if (!empty($listData)) {
            $collection = $this->mongo->selectCollection('lock_histories');
            $listData = $listData->toArray();
            foreach ($listData as $item) {
                $dataUpdate = [
                    'percent' => $item['amount'] / $totalSupply * 100
                ];
                $collection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
                $tokenLockAmount += $item['amount'];
            }
        }

        $circulatingSupplyAmount = $totalSupply - $tokenLockAmount;
        $circulatingSupplyPercent = $circulatingSupplyAmount / $totalSupply * 100;
        return [
            'token_lock_amount' => $tokenLockAmount,
            'total_supply' => $totalSupply,
            'token_lock_percent' => $tokenLockAmount <= 0 ? 0 : ($tokenLockAmount / $totalSupply * 100),
            'circulating_supply_amount' => $circulatingSupplyAmount,
            'circulating_supply_percent' => $circulatingSupplyPercent,
            'token_lock_value' => $tokenLockAmount * $tokenInfo['token_price_usd']
        ];
    }

    /**
     * @param $network
     * @param $toAddress
     * @return array
     */
    protected function checkInListenAddress($network, $toAddress)
    {
        $listConfigAddress = ContractLibrary::getListConfigAddressByNetworkAndPlatform($network, $this->platform);
        if (!empty($listConfigAddress)) {
            foreach ($listConfigAddress as $configAddress) {
                if ($configAddress['address'] == $toAddress && $configAddress['is_listen'] == ContractLibrary::ACTIVE) {
                    return [
                        'in_condition' => true,
                        'contract_type' => $configAddress['type']
                    ];
                }
            }
        }

        $presaleCollection = $this->mongo->selectCollection('presale');
        $presale = $presaleCollection->findOne([
            'network' => $network,
            'platform' => $this->platform,
            'contract_address' => $toAddress
        ]);
        if (!empty($presale)) {
            if ($presale['project_type'] == ContractLibrary::SALE) {
                return [
                    'in_condition' => true,
                    'contract_type' => ContractLibrary::SALE
                ];
            }
            return [
                'in_condition' => true,
                'contract_type' => ContractLibrary::PRESALE
            ];
        }

        $poolCollection = $this->mongo->selectCollection('pool');
        $pool = $poolCollection->findOne([
            'network' => $network,
            'platform' => $this->platform,
            'contract_address' => $toAddress
        ]);
        if (!empty($pool)) {
            return [
                'in_condition' => true,
                'contract_type' => ContractLibrary::POOL
            ];
        }

        return [
            'in_condition' => false,
            'contract_type' => null
        ];
    }
}