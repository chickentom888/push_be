<?php

namespace DCrypto;

use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Object\ICoin;
use Exception;
use MongoDB\Database;
use Phalcon\Di;

class Adapter
{

    /**
     * @param $coinKey
     * @param string $network
     * @return ICoin
     * @throws Exception
     */
    public static function getInstance($coinKey, $network = ContractLibrary::MAIN_NETWORK)
    {
        if (in_array($coinKey, self::listMainCurrency())) {
            $token = self::getMainTokenInfo($coinKey);
        } else {
            /** @var  Database $mongo */
            $mongo = Di::getDefault()->getShared('mongo');
            $contractCollection = $mongo->selectCollection('contract');
            $token = $contractCollection->findOne([
                'contract_key' => $coinKey
            ]);
            if (!$token) {
                throw new Exception("Token $coinKey not found", 0);
            }
        }

        if ($token['platform'] == BinanceWeb3::PLATFORM) $instance = new BinanceWeb3($token, $network);
        if (empty($instance)) {
            throw new Exception("Coin instance not found", 0);
        }
        return $instance;
    }

    public static function getMainTokenInfo($coinKey)
    {
        global $config;
        if ($coinKey == BinanceWeb3::MAIN_CURRENCY) {
            $token = [
                'name' => 'Binance',
                'symbol' => 'BNB',
                'address' => 'bnb',
                'contract_key' => 'bnb',
                'decimals' => 18,
                'platform' => BinanceWeb3::PLATFORM
            ];
        }
        return $token;
    }

    public static function listMainCurrency()
    {
        return [
            BinanceWeb3::PLATFORM => BinanceWeb3::MAIN_CURRENCY,
        ];
    }

    public static function getMainCurrency($platform)
    {
        return self::listMainCurrency()[$platform];
    }

    public static function listPlatform()
    {
        return [
            BinanceWeb3::PLATFORM => strtoupper(BinanceWeb3::PLATFORM),
        ];
    }

    public static function listNetwork()
    {
        return [
            ContractLibrary::MAIN_NETWORK => strtoupper(ContractLibrary::MAIN_NETWORK),
            ContractLibrary::TEST_NETWORK => strtoupper(ContractLibrary::TEST_NETWORK),
        ];
    }
}
