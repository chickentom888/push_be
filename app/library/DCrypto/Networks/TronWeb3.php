<?php

namespace DCrypto\Networks;

use Dcore\Library\Arrays;
use Dcore\Library\ContractLibrary;
use Dcore\Modules\Api\Controllers\ApiControllerBase;
use DCrypto\Object\Account;
use DCrypto\Object\ICoin;
use DCrypto\Object\Information;
use DCrypto\Object\Send;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Mime;
use Httpful\Request;
use IEXBase\TronAPI\Tron;
use function bcadd;
use function bcmul;

class TronWeb3 extends ICoin
{
    const MAIN_CURRENCY = 'trx';
    const PLATFORM = 'trx';
    const DEFAULT_FEE = 0.008;
    const GAS_MAIN_CURRENCY = 21000;
    const GAS_TOKEN = 80000;
    const API_URL = "https://trx.tbotransfer.com/api/";

    public $ticker;
    public $key;
    public $name;
    public $contract;
    public $optFields;
    public $decimals;
    public $explorer_link;
    public $network;
    /** @var Tron */
    public $rpcConnector;
    /** @var Tron */
    public $tron;
    public $chainId;
    public $platform = self::PLATFORM;

    /**
     * @throws Exception
     */
    function __construct($token, $network = null)
    {
        $this->name = $token['name'];
        $this->ticker = $token['symbol'];
        $this->key = $token['contract_key'];
        $this->contract = $token['address'];
        $this->decimals = intval($token['decimals']);
        $this->explorer_link = parent::$explorerTRC20Link[$network ?? ContractLibrary::MAIN_NETWORK];
        if (strlen($network)) {
            $this->connect(null, $network);
        }
    }

    public function connect($serverIp = null, string $network = ContractLibrary::MAIN_NETWORK)
    {
        return $this;
    }

    public function getCoinInfo()
    {
        global $config;
        $coinInfo = new Information();
        $coinInfo->key = $this->key;
        $coinInfo->name = $this->name;
        $coinInfo->ticker = $this->ticker;
        $coinInfo->decimals = $this->decimals;
        $coinInfo->state = ICoin::STATE_RUNNING;
        $coinInfo->platform = self::PLATFORM;
        $coinInfo->hosts = [
            'main' => [
                [
                    "host" => $config->blockchain['trx_rpc_main_net'],
                    "port" => '',
                    'username' => '',
                    'password' => '',
                    "passphrase" => "",
                    "cert_file" => "",
                    "url" => "",
                    'pass_phrase' => '',
                    'chain_id' => 0
                ]
            ],
            'test' => [
                [
                    "host" => $config->blockchain['trx_rpc_test_net'],
                    "port" => '',
                    'username' => '',
                    'password' => '',
                    "passphrase" => "",
                    "cert_file" => "",
                    "url" => "",
                    'pass_phrase' => '',
                    'chain_id' => 0
                ]
            ],
        ];
        $coinInfo->args = [
            'contract_address' => $this->contract,
            'technology' => "TRC20",
            "fields" => strlen($this->optFields) ? json_decode($this->optFields, true) : [],
            'explorer' => $this->explorer_link
        ];
        return $coinInfo;
    }

    public function createAccount($prefix = '')
    {
        try {
            $dataRequest = $_GET;
            $apiKey = $dataRequest['api_key'];
            $url = self::API_URL . "instance_address/create?api_key=$apiKey&token_key={$this->key}&platform={$this->platform}";
            $dataResponse = Request::get($url)->expectsJson()->send();
            $addressRs = json_decode($dataResponse);

            if ($addressRs->status == 1) {
                $addressResponse = $addressRs->data;
                $id = sprintf("%s-%s-%s-%s", $this->ticker, $prefix, time(), rand(11111, 99999));
                $password = md5(md5($id));

                $addressData = [
                    'address' => $addressResponse->address,
                    'private_key' => $addressResponse->private_key,
                    'password' => $addressResponse->password
                ];

                $coinAccount = new Account($id, $addressData['address'], $addressData['address'], $password, null, $this->ticker, null, $addressData['private_key']);
                $this->saveAccountToFile($coinAccount);
                return $coinAccount;
            } else {
                throw new Exception($addressRs->message);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getBlockchainInfo()
    {
        try {
            $url = self::API_URL . "index/get_blockchain_info?token_key={$this->key}&platform={$this->platform}";
            $dataResponse = Request::get($url)->expectsJson()->send();
            $dataResponse = json_decode($dataResponse);
            if ($dataResponse->status == 1) {
                return Arrays::arrayFrom($dataResponse->data);
            } else {
                throw new Exception($dataResponse->message);
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getBalanceAccount(Account $account)
    {
        try {
            $dataRequest = $_GET;
            $apiKey = $dataRequest['api_key'];
            $url = self::API_URL . "instance_address/get_balance?api_key=$apiKey&address={$account->address}&token_key={$this->key}&platform={$this->platform}";
            $dataResponse = Request::get($url)->expectsJson()->send();
            $dataResponse = json_decode($dataResponse);
            if ($dataResponse->status == 1) {
                return $dataResponse->data;
            } else {
                throw new Exception($dataResponse->message);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function send(Account $from, Account $to, Send $sendObject)
    {
        try {
            $dataRequest = $_GET;
            $apiKey = $dataRequest['api_key'];
            $apiControllerBase = new ApiControllerBase();
            $apiControllerBase->escapeData($apiControllerBase->request->getJsonRawBody(true), $apiControllerBase->jsonData);

            $url = self::API_URL . "txs/send?api_key=$apiKey&token_key={$this->key}&platform={$this->platform}";
            $dataResponse = Request::post($url)->body($apiControllerBase->jsonData, Mime::JSON)->expectsJson()->send();
            $dataResponse = json_decode($dataResponse);
            if ($dataResponse->status == 1) {
                return $dataResponse->data;
            } else {
                throw new Exception($dataResponse->message);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws ConnectionErrorException
     */
    public function validAddress($address)
    {
        $url = self::API_URL . "instance_address/valid_address?token_key={$this->key}&platform={$this->platform}&address=$address";
        $dataResponse = Request::get($url)->expectsJson()->send();
        $dataResponse = json_decode($dataResponse);
        if ($dataResponse->status == 1) {
            return $dataResponse->data;
        } else {
            throw new Exception($dataResponse->message);
        }
    }

    public function getTransactionDetail($hash)
    {
        $url = self::API_URL . "txs/get_detail_transaction?token_key={$this->key}&platform={$this->platform}&hash=$hash";
        $dataResponse = Request::get($url)->expectsJson()->send();
        $dataResponse = json_decode($dataResponse);
        if ($dataResponse->status == 1) {
            return $dataResponse->data;
        } else {
            throw new Exception($dataResponse->message);
        }
    }

    /**
     * @throws ConnectionErrorException
     */
    public function getTransactionInfo($hash)
    {
        $url = self::API_URL . "txs/get_detail_transaction?token_key={$this->key}&platform={$this->platform}&hash=$hash";
        $dataResponse = Request::get($url)->expectsJson()->send();
        $dataResponse = json_decode($dataResponse);
        if ($dataResponse->status == 1) {
            return $dataResponse->data;
        } else {
            throw new Exception($dataResponse->message);
        }
    }

    /**
     * @param $blockNumber
     * @return mixed
     * @throws ConnectionErrorException
     */
    public function getTransactionsByNumberBlock($blockNumber)
    {
        $url = self::API_URL . "txs/get_transactions_by_block_number?token_key={$this->key}&platform={$this->platform}&block_number=$blockNumber";
        $dataResponse = Request::get($url)->expectsJson()->send();
        $dataResponse = json_decode($dataResponse);
        if ($dataResponse->status == 1) {
            return Arrays::arrayFrom($dataResponse->data);
        } else {
            throw new Exception($dataResponse->message);
        }
    }

    public function getBlocksRange($start, $end)
    {
        try {
            $rs = $this->__connector->trx_getBlockRange([
                'from' => $start, 'to' => $end
            ]);
            return $rs;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getTransactionReceipt($hash)
    {
        try {
            return $this->__connector->eth_getTransactionReceipt($hash);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function mbchexdec($hex)
    {
        if (!strlen($hex)) {
            return 0;
        }
        if (strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, $this->mbchexdec($remain)), hexdec($last));
        }
    }

    public function mbcdechex($dec)
    {
        $last = bcmod($dec, 16);
        $remain = bcdiv(bcsub($dec, $last), 16);

        if ($remain == 0) {
            return dechex($last);
        } else {
            return $this->mbcdechex($remain) . dechex($last);
        }
    }

    public function convertHex2Dec($hex)
    {
        return hexdec(ltrim($hex, '0x'));
    }

    public function convertDec2Hex($dec)
    {
        return "0x" . dechex($dec);
    }

    public static function abi_encode(string $type, $value)
    {
        switch ($type) {
            case "hash":
            case "address":
                if (substr($value, 0, 2) === "0x") {
                    $value = substr($value, 2);
                }
                break;
            case "uint":
            case "int":
                $value = self::bcdechex($value);
                break;
            case "bool":
                $value = $value === true ? 1 : 0;
                break;
            case "string":
                $value = self::Str2Hex($value);
                break;
            default:
                break;
        }

        return substr(str_pad(strval($value), 64, "0", STR_PAD_LEFT), 0, 64);
    }

    public static function wei2eth($wei, $decimal = 18)
    {
        $divisor = pow(10, $decimal);
        return bcdiv($wei, $divisor, $decimal);
    }

    public static function eth2wei($eth, $scale = 18)
    {
        $number = pow(10, $scale);
        return bcmul($eth, $number, $scale);
    }

    public static function bchexdec($hex)
    {
        try {
            if (!strlen($hex)) {
                return 0;
            }

            if (strlen($hex) == 1) {
                return hexdec($hex);
            } else {
                $remain = substr($hex, 0, -1);
                $last = substr($hex, -1);
                return bcadd(bcmul(16, self::bchexdec($remain)), hexdec($last));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function bcdechex($dec)
    {
        try {
            $last = bcmod($dec, 16);
            $remain = bcdiv(bcsub($dec, $last), 16);

            if ($remain == 0) {
                return dechex($last);
            } else {
                return self::bcdechex($remain) . dechex($last);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function Str2Hex(string $str)
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $hex .= dechex(ord($str[$i]));
        }

        return $hex;
    }

    public static function Hex2Str(string $hex)
    {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $str;
    }

    /**
     * @throws ConnectionErrorException
     */
    public function getTransactionStatus($hash)
    {

        $url = self::API_URL . "txs/get_transaction_status?token_key={$this->key}&platform={$this->platform}&hash=$hash";
        $dataResponse = Request::get($url)->expectsJson()->send();
        $dataResponse = json_decode($dataResponse);
        if ($dataResponse->status == 1) {
            return $dataResponse->data;
        } else {
            throw new Exception($dataResponse->message);
        }
    }

}
