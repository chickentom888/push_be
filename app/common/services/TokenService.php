<?php

namespace Dcore\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Networks\EthereumWeb3;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;

class TokenService extends BaseContractService
{
    public function __construct($network, $platform)
    {
        parent::__construct($network, $platform);
    }

    /**
     * Process Create Presale
     * @throws Exception
     */
    public function updateUnlockTime($tokenItem)
    {
        $listLockHistoryCollection = $this->mongo->selectCollection('lock_histories');
        $tokenCollection = $this->mongo->selectCollection('tokens');

        $newUnlockHistory = $listLockHistoryCollection->findOne([
            'token_address' => $tokenItem['address'],
            'network' => $tokenItem['network'],
            'platform' => $tokenItem['platform'],
            'withdraw_status' => ContractLibrary::NOT_WITHDRAW,
            'unlock_time' => [
                '$gte' => time()
            ]
        ], ['sort' => ['unlock_time' => 1]]);
        if ($newUnlockHistory) {
            $tokenUpdateData['lock_time'] = $newUnlockHistory['created_at'];
            $tokenUpdateData['unlock_time'] = $newUnlockHistory['unlock_time'];
            $tokenCollection->updateOne(['_id' => $tokenItem['_id']], ['$set' => $tokenUpdateData]);
        }
    }

    /**
     * @param $tokenItem
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function updateInfo($tokenItem)
    {
        try {
            $listLockHistoryCollection = $this->mongo->selectCollection('lock_histories');
            $listLockHistory = $listLockHistoryCollection->findOne([
                'token_address' => $tokenItem['address'],
                'withdraw_status' => ContractLibrary::NOT_WITHDRAW
            ]);
            $tokenUpdateData = [];
            if (!$tokenItem['token_lock_value']) {
                $tokenUpdateData = [
                    'token_lock_value' => 0,
                ];
            }

            if ($listLockHistory) {
                $tokenPercentInfo = $this->calculateTokenPercent($tokenItem);
                if ($tokenPercentInfo['total_supply']) {
                    $tokenItem['total_supply'] = $tokenPercentInfo['total_supply'];
                    $dataPrice = $this->getPriceTokenData($this->platform, $this->network, $tokenItem);
                    $tokenUpdateData = [
                        'token_price_usd' => $dataPrice['token_price_usd'],
                        'token_pool' => $dataPrice['token_pool'],
                        'total_pool_usd' => $dataPrice['sub_token_usd'],
                        'total_supply_usd' => $dataPrice['total_supply_usd'],
                        'dex_address_pair' => $dataPrice['dex_address_pair'],
                        'total_supply_token' => $tokenPercentInfo['total_supply'],
                        'token_lock_amount' => $tokenPercentInfo['token_lock_amount'],
                        'token_lock_percent' => $tokenPercentInfo['token_lock_percent'],
                        'token_lock_value' => $dataPrice['token_price_usd'] * $tokenPercentInfo['token_lock_amount'] ?? 0,
                        'circulating_supply_amount' => $tokenPercentInfo['circulating_supply_amount'],
                        'circulating_supply_percent' => $tokenPercentInfo['circulating_supply_percent'],
                    ];

                    $liquidLockPercent = $this->calculateLiquidPercent($tokenItem);
                    $tokenUpdateData['liquid_lock_percent'] = $liquidLockPercent;
                    $tokenUpdateData['liquid_lock_value'] = 0;
                    if (BigDecimal::of($liquidLockPercent)->isGreaterThan(0)) {
                        if (strlen($liquidLockPercent) && strlen($dataPrice['sub_token_usd'])) {
                            $tokenUpdateData['liquid_lock_value'] = BigDecimal::of($dataPrice['sub_token_usd'])->multipliedBy(100)->dividedBy($liquidLockPercent, ContractLibrary::DEFAULT_DECIMALS, RoundingMode::HALF_UP)->toFloat();
                        }
                    }

                    if ($tokenItem['network'] == ContractLibrary::MAIN_NETWORK && $tokenItem['address']) {
                        try {
                            $baseUrl = "https://api.coingecko.com/api/v3/coins/";
                            $platformId = 'binance-smart-chain';
                            if ($tokenItem['platform'] == EthereumWeb3::PLATFORM) {
                                $platformId = 'ethereum';
                            }
                            $baseUrl .= $platformId . "/contract/" . $tokenItem['address'];
                            $ch = curl_init($baseUrl);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            $response = Request::get($baseUrl)->expectsJson()->send();

                            if ($response->hasBody()) {
                                $tokenData = Arrays::arrayFrom($response->body);
                                $imageUrl = Helper::getUrl($tokenData['image']['large'] ?? '');
                                if (strlen($imageUrl)) {
                                    $tokenUpdateData['image'] = $imageUrl;
                                }
                                $tokenUpdateData['coingecko_rank'] = $tokenData['coingecko_rank'] ?? '';

                                if ($tokenUpdateData['token_price_usd'] <= 0) {
                                    $tokenUpdateData['token_price_usd'] = floatval($tokenData['market_data']['current_price']['usd']);
                                    $tokenUpdateData['total_pool_usd'] = $tokenUpdateData['token_price_usd'] * $tokenUpdateData['token_pool'];
                                    $tokenUpdateData['total_supply_usd'] = $tokenUpdateData['token_price_usd'] * $tokenUpdateData['total_supply_token'];
                                    $tokenUpdateData['token_lock_value'] = $tokenUpdateData['token_price_usd'] * $tokenUpdateData['token_lock_amount'];
                                }
                            }
                        } catch (Exception $e) {
                            $message = "Error when call api coingecko with token address: {$tokenItem['address']}" . PHP_EOL;
                            $message .= "Message: " . $e->getMessage() . "." . PHP_EOL;

                            Helper::sendTelegramMsgMonitor($message);
                        }
                    }

                    $tokenUpdateData['total_lock_value'] = BigDecimal::of($tokenUpdateData['token_lock_value'] + $tokenUpdateData['liquid_lock_value'])->toFloat();
                }
            }

            $presale = $this->mongo->selectCollection('presale')->findOne([
                'platform' => $tokenItem['platform'],
                'network' => $tokenItem['network'],
                'sale_token_address' => $tokenItem['address'],
                'is_show' => ContractLibrary::ACTIVE,
            ]);
            if (isset($presale['cover_url']) && $presale['cover_url']) {
                $tokenUpdateData['cover_url'] = $presale['cover_url'];
            }
            $presale = $this->mongo->selectCollection('presale')->find([
                'platform' => $tokenItem['platform'],
                'network' => $tokenItem['network'],
                'sale_token_address' => $tokenItem['address'],
                'is_show' => ContractLibrary::ACTIVE,
                'current_status' => ContractLibrary::PRESALE_STATUS_SUCCESS,
                'sale_type' => ['$in' => [ContractLibrary::SALE_TYPE_ILOV, ContractLibrary::SALE_TYPE_ILO]]
            ]);
            !empty($presale) && $presale = $presale->toArray();
            if (!count($presale)) {
                $presale = $this->mongo->selectCollection('presale')->find([
                    'platform' => $tokenItem['platform'],
                    'network' => $tokenItem['network'],
                    'sale_token_address' => $tokenItem['address'],
                    'is_show' => ContractLibrary::ACTIVE,
                    'current_status' => ContractLibrary::PRESALE_STATUS_SUCCESS,
                    'sale_type' => ['$in' => [ContractLibrary::SALE_TYPE_IDOV, ContractLibrary::SALE_TYPE_IDO]]
                ]);
                !empty($presale) && $presale = $presale->toArray();
                if (!count($presale)) {
                    $presale = $this->mongo->selectCollection('pool')->find([
                        'platform' => $tokenItem['platform'],
                        'network' => $tokenItem['network'],
                        'pool_token_address' => $tokenItem['address'],
                        'is_show' => ContractLibrary::ACTIVE,
                        'current_status' => ContractLibrary::PRESALE_STATUS_SUCCESS,
                    ]);
                    !empty($presale) && $presale = $presale->toArray();
                    $isPool = true;
                }
            }

            foreach ($presale as $item) {
                if (isset($isPool) && $isPool) {
                    $tokenUpdateData['contract_address'] = $item['contract_address'];
                    $tokenUpdateData['sale_type'] = $item['project_type'];
                    break;
                } elseif (isset($item['sale_type']) && $item['sale_type']) {
                    $tokenUpdateData['contract_address'] = $item['contract_address'];
                    $tokenUpdateData['sale_type'] = $item['sale_type'];
                    break;
                }
            }

            if (count($tokenUpdateData)) {
                $this->mongo->selectCollection('tokens')->updateOne(['_id' => $tokenItem['_id']], ['$set' => $tokenUpdateData]);
            }
        } catch (Exception $e) {
            $message = "Error when run task update Token info" . PHP_EOL;
            $message .= "Message: " . $e->getMessage() . "." . PHP_EOL;

            Helper::sendTelegramMsgMonitor($message);
        }
    }

}
