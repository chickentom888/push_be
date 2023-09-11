<?php

namespace DCrypto;

use Dcore\Library\ContractLibrary;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\EthereumWeb3;
use DCrypto\Networks\FantomWeb3;
use DCrypto\Networks\PolygonWeb3;
use DCrypto\Networks\TronWeb3;
use DCrypto\Object\ICoin;
use Exception;
use MongoDB\Database;
use Phalcon\Di;

class Adapter
{
    /**
     * @param null $coinKey
     * @return array
     * @throws Exception
     */
    public static function getMapper($coinKey = null)
    {
        $contracts = Contract::find();
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $contractItem = $contract->toArray();
            if ($contract->platform == EthereumWeb3::PLATFORM) $listMapper[$contract->contract_key] = new Networks\EthereumWeb3($contractItem);
            else if ($contract->platform == TronWeb3::PLATFORM) $listMapper[$contract->contract_key] = new Networks\TronWeb3($contractItem);
            else if ($contract->platform == BinanceWeb3::PLATFORM) $listMapper[$contract->contract_key] = new Networks\BinanceWeb3($contractItem);
            else if ($contract->platform == PolygonWeb3::PLATFORM) $listMapper[$contract->contract_key] = new Networks\PolygonWeb3($contractItem);
            else if ($contract->platform == FantomWeb3::PLATFORM) $listMapper[$contract->contract_key] = new Networks\FantomWeb3($contractItem);
        }

        if ($coinKey == null) return $listMapper;
        else {
            if (empty($listMapper[$coinKey])) {
                throw new Exception("Coin instance not found", 0);
            }
            return $listMapper[$coinKey];
        }
    }

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

        if ($token['platform'] == EthereumWeb3::PLATFORM) $instance = new EthereumWeb3($token, $network);
        if ($token['platform'] == BinanceWeb3::PLATFORM) $instance = new BinanceWeb3($token, $network);
        if ($token['platform'] == TronWeb3::PLATFORM) $instance = new TronWeb3($token, $network);
        if ($token['platform'] == PolygonWeb3::PLATFORM) $instance = new PolygonWeb3($token, $network);
        if ($token['platform'] == FantomWeb3::PLATFORM) $instance = new FantomWeb3($token, $network);
        if (empty($instance)) {
            throw new Exception("Coin instance not found", 0);
        }
        return $instance;
    }

    public static function getChain()
    {
        return [
            strtoupper(EthereumWeb3::PLATFORM),
            strtoupper(TronWeb3::PLATFORM),
            strtoupper(BinanceWeb3::PLATFORM),
        ];
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
        } else if ($coinKey == EthereumWeb3::MAIN_CURRENCY) {
            $token = [
                'name' => 'Ether',
                'symbol' => 'ETH',
                'address' => 'eth',
                'contract_key' => 'eth',
                'decimals' => 18,
                'platform' => EthereumWeb3::PLATFORM
            ];
        } else if ($coinKey == TronWeb3::MAIN_CURRENCY) {
            $token = [
                'name' => 'Tron',
                'symbol' => 'TRX',
                'address' => 'trx',
                'contract_key' => 'trx',
                'decimals' => 6,
                'platform' => TronWeb3::PLATFORM
            ];
        } else if ($coinKey == PolygonWeb3::MAIN_CURRENCY) {
            $token = [
                'name' => 'Polygon',
                'symbol' => 'MATIC',
                'address' => 'matic',
                'contract_key' => 'matic',
                'decimals' => 18,
                'platform' => PolygonWeb3::PLATFORM
            ];
        } else if ($coinKey == FantomWeb3::MAIN_CURRENCY) {
            $token = [
                'name' => 'Fantom',
                'symbol' => 'FTM',
                'address' => 'ftm',
                'contract_key' => 'ftm',
                'decimals' => 18,
                'platform' => FantomWeb3::PLATFORM
            ];
        }
        return $token;
    }

    public static function listMainCurrency()
    {
        return [
            BinanceWeb3::PLATFORM => BinanceWeb3::MAIN_CURRENCY,
            /*EthereumWeb3::PLATFORM => EthereumWeb3::MAIN_CURRENCY,
            TronWeb3::PLATFORM => TronWeb3::MAIN_CURRENCY,
            PolygonWeb3::PLATFORM => PolygonWeb3::MAIN_CURRENCY,
            FantomWeb3::PLATFORM => FantomWeb3::MAIN_CURRENCY,*/
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
            /*PolygonWeb3::PLATFORM => strtoupper(PolygonWeb3::PLATFORM),
            EthereumWeb3::PLATFORM => strtoupper(EthereumWeb3::PLATFORM),
            FantomWeb3::PLATFORM => strtoupper(FantomWeb3::PLATFORM),*/
        ];
    }

    public static function listNetwork()
    {
        return [
            ContractLibrary::MAIN_NETWORK => strtoupper(ContractLibrary::MAIN_NETWORK),
            ContractLibrary::TEST_NETWORK => strtoupper(ContractLibrary::TEST_NETWORK),
        ];
    }

    public static function listLanguage()
    {
        return [
            'vi' => 'VI',
            'en' => 'EN',
        ];
    }
}
