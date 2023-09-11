<?php

namespace Dcore\Library;

use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use MongoDB\Database;
use Phalcon\Di;
use Redis;

class ContractLibrary
{

    const TOKEN = 'token';
    const TOKEN_MINTED = 'token_minted';
    const PRESALE_SETTING = 'presale_setting';
    const PRESALE_GENERATOR = 'presale_generator';
    const PRESALE_FACTORY = 'presale_factory';
    const MINT_TOKEN_SETTING = 'mint_token_setting';
    const MINT_TOKEN_GENERATOR = 'mint_token_generator';
    const MINT_TOKEN_FACTORY = 'mint_token_factory';
    const AIRDROP_SETTING = 'airdrop_setting';
    const AIRDROP_CONTRACT = 'airdrop_contract';
    const LOCK_CONTRACT = 'lock_contract';
    const LOCK_SETTING = 'lock_setting';
    const PRESALE = 'presale';
    const DEX_FACTORY = 'dex_factory';
    const DEX_PAIR = 'dex_pair';
    const DEX_ROUTER = 'dex_router';
    const DEX_WRAP_TOKEN = 'dex_wrap_token';
    const SALE_FACTORY = 'sale_factory';
    const SALE_SETTING = 'sale_setting';
    const SALE_GENERATOR = 'sale_generator';
    const SALE = 'sale';
    const POOL_FACTORY = 'pool_factory';
    const POOL_SETTING = 'pool_setting';
    const POOL_GENERATOR = 'pool_generator';
    const POOL = 'pool';
    const LOTTERY = 'lottery';
    const STAKING = 'staking';

    const MAIN_NETWORK = 'main';
    const TEST_NETWORK = 'test';

    const PROCESSED = 1;

    const FUNCTION_GET_SETTING_INFO = 'getSettingInfo';
    const FUNCTION_UPDATE_VESTING_INFO = 'updateVestingInfo';
    const FUNCTION_UPDATE_FUND_ADDRESS = 'updateFundAddress';

    // PRESALE
    const FUNCTION_CREATE_PRESALE = 'createPresale';
    const FUNCTION_GET_STATUS_INFO = 'getStatusInfo';
    const FUNCTION_BUY_TOKEN = 'buyToken';
    const FUNCTION_ADD_LIQUIDITY = 'addLiquidity';
    const FUNCTION_USER_WITHDRAW_SALE_TOKEN = 'userWithdrawSaleToken';
    const FUNCTION_USER_FORCE_FAIL = 'forceFailIfPairExists';
    const FUNCTION_ADMIN_FORCE_FAIL = 'forceFailByAdmin';
    const FUNCTION_OWNER_FORCE_FAIL = 'forceFailByOwner';
    const FUNCTION_USER_WITHDRAW_BASE_TOKEN = 'userWithdrawBaseToken';
    const FUNCTION_OWNER_WITHDRAW_SALE_TOKEN = 'ownerWithdrawSaleToken';
    const FUNCTION_UPDATE_LIMIT_PER_BUYER = 'updateLimitPerBuyer';
    const FUNCTION_UPDATE_TIME = 'updateTime';
    const FUNCTION_SET_WHITELIST_FLAG = 'setWhitelistFlag';
    const FUNCTION_EDIT_WHITELIST = 'editWhitelist';
    const FUNCTION_REGISTER_ZERO_ROUND = 'registerZeroRound';
    const FUNCTION_CREATE_TOKEN = 'createToken';

    // Sale
    const FUNCTION_CREATE_SALE = 'createSale';
    const FUNCTION_ACTIVE_CLAIM = 'activeClaim';

    //Pool
    const FUNCTION_CREATE_POOL = 'createPool';
    const FUNCTION_USER_WITHDRAW_POOL_TOKEN = 'userWithdrawPoolToken';
    const FUNCTION_USER_WITHDRAW_AUCTION_TOKEN = 'userWithdrawAuctionToken';
    const FUNCTION_OWNER_WITHDRAW_BASE_TOKEN = 'ownerWithdrawBaseToken';
    const FUNCTION_OWNER_WITHDRAW_POOL_TOKEN = 'ownerWithdrawPoolToken';
    const FUNCTION_REGISTER_AUCTION_ROUND = 'registerAuctionRound';

    // LOCK
    const FUNCTION_LOCK_TOKEN = 'lockToken';
    const FUNCTION_EXTEND_LOCK = 'extendLockDuration';
    const FUNCTION_TRANSFER_LOCK = 'transferLock';
    const FUNCTION_WITHDRAW_LOCK = 'withdrawToken';

    // Token
    const FUNCTION_DECIMALS = 'decimals';
    const FUNCTION_BALANCE_OF = 'balanceOf';
    const FUNCTION_NAME = 'name';
    const FUNCTION_SYMBOL = 'symbol';
    const FUNCTION_TOTAL_SUPPLY = 'totalSupply';
    const FUNCTION_TOKEN0 = 'token0';
    const FUNCTION_TOKEN1 = 'token1';
    const FUNCTION_KLAST = 'kLast';

    // SETTING LOCK
    const FUNCTION_GET_BASE_FEE = 'getBaseFee';
    const FUNCTION_GET_TOKEN_FEE = 'getTokenFee';
    const FUNCTION_GET_DISCOUNT_PERCENT = 'getDiscountPercent';
    const FUNCTION_GET_WHITELIST_ADDRESS_LENGTH = 'getWhitelistAddressLength';
    const FUNCTION_GET_WHITELIST_ADDRESS_AT_INDEX = 'getWhitelistAddressAtIndex';
    const FUNCTION_GET_WHITELIST_FEE_TOKEN_LENGTH = 'getWhitelistFeeTokenLength';
    const FUNCTION_GET_WHITELIST_FEE_TOKEN_AT_INDEX = 'getWhitelistFeeTokenAtIndex';
    const FUNCTION_GET_ADDRESS_FEE = 'getAddressFee';

    //LOTTERY
    const FUNCTION_START_LOTTERY = 'startLottery';
    const FUNCTION_BUY_TICKETS = 'buyTickets';
    const FUNCTION_CLOSE_LOTTERY = 'closeLottery';
    const FUNCTION_CALCULATE_REWARD = 'calculateReward';
    const FUNCTION_OPERATOR_ADDRESS = 'operatorAddress';
    const FUNCTION_CLAIM_TICKETS = 'claimTickets';
    const FUNCTION_INJECT_FUNDS = 'injectFunds';
    const FUNCTION_CHANGE_RANDOM_GENERATOR = 'changeRandomGenerator';
    const FUNCTION_SET_ADDRESS = 'setAddress';
    const FUNCTION_SET_MIN_AND_MAX_TICKET_PRICE = 'setMinAndMaxTicketPrice';

    // STAKING
    const FUNCTION_STAKING_TOKEN = 'stakingToken';
    const FUNCTION_SWAP_TOKEN = 'swapToken';
    const FUNCTION_STAKING = 'staking';
    const FUNCTION_BUY = 'buy';
    const FUNCTION_FEE_STAKING_ADDRESS = 'feeStakingAddress';
    const FUNCTION_FEE_SWAP_ADDRESS = 'feeSwapAddress';
    const FUNCTION_DEX_PAIR_ADDRESS = 'dexPairAddress';
    const PAYMENT_TYPE_SWAP_TOKEN = 'swap_token';
    const PAYMENT_TYPE_STAKING_TOKEN = 'staking_token';

    const LOCK_TYPE_LIQUID = 'liquid';
    const LOCK_TYPE_TOKEN = 'token';

    const ADDRESS_ZERO = '0x0000000000000000000000000000000000000000';
    const ACTIVE = 1;
    const INACTIVE = 0;
    const BASE_TOKEN = 'base_token';
    const WHITELIST_TOKEN = 'whitelist_token';

    const PRESALE_STATUS_FAILED = 3;
    const PRESALE_STATUS_SUCCESS = 2;
    const PRESALE_STATUS_ACTIVE = 1;
    const PRESALE_STATUS_PENDING = 0;

    const MAX_CURRENT_ROUND = 10;

    const NOT_WITHDRAW = 0;
    const WITHDRAWN = 1;
    const DEFAULT_DECIMALS = 18;

    const MAX_NUMBER_VESTING = 15;

    const PROJECT_TYPE_SALE = 'sale';
    const PROJECT_TYPE_POOL = 'pool';

    const SALE_TYPE_ILO = 'ilo';
    const SALE_TYPE_ILOV = 'ilov';
    const SALE_TYPE_IDO = 'ido';
    const SALE_TYPE_IDOV = 'idov';

    const LISTEN = 1;

    const RPUB_PRESALE_CHANGE = 'RPUB_PRESALE_CHANGE';
    const RPUB_LOTTERY_CHANGE = 'RPUB_LOTTERY_CHANGE';
    const AWAITING_START = -1;


    const LOTTERY_CRON_STATUS_PENDING = 0;
    const LOTTERY_CRON_STATUS_ACTIVE = 1;

    const TRANSACTION_STATUS_PENDING = 0;
    const TRANSACTION_STATUS_SUCCESS = 1;
    const TRANSACTION_STATUS_FAIL = 2;

    /**
     * @param $type
     * @param null $version
     * @return mixed|string
     * @throws Exception
     */
    public static function getAbi($type, $version = null)
    {
        /** @var Redis $redis */
        $redis = DI::getDefault()->getShared('redis');
        $cacheKey = "abi:$type";
        if (strlen($version)) {
            $cacheKey .= "_$version";
        }
        $abi = $redis->get($cacheKey);
        if (!$abi) {
            $abiFileName = $type;
            if (strlen($version)) {
                $abiFileName .= "_$version";
            }
            $abiFileName .= ".json";

            $filePath = APP_PATH . DIRECTORY_SEPARATOR . 'abi' . DIRECTORY_SEPARATOR . $abiFileName;

            $abi = file_get_contents($filePath);
            $redis->set($cacheKey, $abi, 600);
        }
        if (!$abi) {
            throw  new Exception('Abi not found: ' . $abiFileName);
        }
        return $abi;
    }

    public static function getPriceBNB()
    {
        $dataPrice = Helper::curlGetFileContents("https://www.binance.com/api/v3/ticker/24hr?symbol=BNBUSDT");
        $dataPrice = json_decode($dataPrice);
        return doubleval($dataPrice->lastPrice);
    }

    public static function getConfigAddress($platform, $network, $type)
    {
        /** @var Database $mongo */
        $mongo = DI::getDefault()->get('mongo');
        $configAddressCollection = $mongo->selectCollection('config_address');
        $condition = [
            'platform' => $platform,
            'network' => $network,
            'type' => $type
        ];
        $address = $configAddressCollection->findOne($condition);
        if (!$address || !isset($address['address'])) {
            return '';
        }
        return $address['address'];
    }

    public static function getAddressByType($platform = null, $network = null, $type = self::MINT_TOKEN_FACTORY)
    {
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $condition = ['type' => $type];
        if (strlen($platform) && in_array($platform, $listPlatform)) {
            $condition['platform'] = $platform;
        }

        if (strlen($network) && in_array($network, $listNetwork)) {
            $condition['network'] = $network;
        }

        /** @var Database $mongo */
        $mongo = DI::getDefault()->get('mongo');
        $configAddressCollection = $mongo->selectCollection('config_address');
        $listConfigAddress = $configAddressCollection->find($condition);

        if (!empty($listConfigAddress)) {
            return $listConfigAddress->toArray();
        }
        return [];
    }

    public static function getListConfigAddressByNetworkAndPlatform($network = null, $platform = null)
    {
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $condition = [];
        if (strlen($platform) && in_array(strtoupper($platform), $listPlatform)) {
            $condition['platform'] = $platform;
        }
        if (strlen($network) && in_array($network, $listNetwork)) {
            $condition['network'] = $network;
        }

        /** @var Database $mongo */
        $mongo = DI::getDefault()->get('mongo');
        $configAddressCollection = $mongo->selectCollection('config_address');
        $listConfigAddress = $configAddressCollection->find($condition);

        if (!empty($listConfigAddress)) {
            return $listConfigAddress->toArray();
        }
        return [];
    }

    public static function getPriceByPlatform($platform = BinanceWeb3::PLATFORM)
    {
        $price = 0;
        if ($platform == BinanceWeb3::PLATFORM) {
            $price = self::getPriceBNB();
        }
        return $price;
    }

    public static function getWithdrawStatusName()
    {
        return [
            self::NOT_WITHDRAW => 'Not Withdrawn',
            self::WITHDRAWN => 'Withdrawn',
        ];
    }

    public static function getListTypeConfigAddress()
    {
        return [
            self::DEX_ROUTER => 'DEX ROUTER',
            self::DEX_FACTORY => 'DEX FACTORY',
            self::DEX_WRAP_TOKEN => 'DEX WRAP TOKEN',
            self::LOCK_CONTRACT => 'LOCK CONTRACT',
            self::LOCK_SETTING => 'LOCK SETTING',
            self::STAKING => 'STAKING',
        ];
    }

    /**
     * @return string[]
     */
    public static function listContractType(): array
    {
        return [
            ContractLibrary::TOKEN_MINTED => 'Token minted',
            ContractLibrary::DEX_FACTORY => 'Dex factory',
            ContractLibrary::LOCK_CONTRACT => 'Lock Contract',
            ContractLibrary::LOCK_SETTING => 'Lock Setting',
            ContractLibrary::STAKING => 'Staking',
        ];
    }

    public static function getTransactionStaking($address)
    {
        $client = new Client();
        global $config;
        $apiUrl = $config->blockchain['bsc_api_url'];
        $apiKey = $config->blockchain['bsc_api_key'];
        $options = [
            'query' => [
                'apikey' => $apiKey,
                'module' => 'account',
                'action' => 'txlist',
                'address' => $address,
                'startblock' => 0,
                'endblock' => 9999999999,
                'page' => 1,
                'offset' => 10,
                'sort' => 'desc',

            ]
        ];
        $headers = [];
        $request = new Request('GET', $apiUrl, $headers);
        $res = $client->sendAsync($request, $options)->wait();
        $responseContent = $res->getBody()->getContents();
        if (!Helper::isJson($responseContent)) {
            return [];
        }
        return json_decode($responseContent, true);
    }
}