<?php

namespace DCrypto\Networks;

use Brick\Math\BigDecimal;
use Dcore\Library\ContractLibrary;
use DCrypto\Object\Account;
use DCrypto\Object\ICoin;
use DCrypto\Object\Information;
use DCrypto\Object\Send;
use Exception;
use kornrunner\Ethereum\Address;
use phpseclib\Math\BigInteger;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;
use Web3p\EthereumTx\Transaction;
use function bcadd;
use function bcmul;

/**
 * Class BinanceWeb3
 * @package DCrypto\Networks
 */
class BinanceWeb3 extends ICoin
{
    const MAIN_CURRENCY = 'bnb';
    const PLATFORM = 'bsc';
    const DEFAULT_FEE = 0.008;
    const GAS_MAIN_CURRENCY = 150000;
    const GAS_TOKEN = 500000;

    public $ticker;
    public $key;
    public $name;
    public $contract;
    public $optFields;
    public $decimals;
    public $explorer_link;
    public $network;
    /** @var Web3 */
    public $rpcConnector;
    public $eth;
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
        $this->explorer_link = parent::$explorerBEP20Link[$network ?? ContractLibrary::MAIN_NETWORK];
        if (strlen($network)) {
            $this->connect(null, $network);
        }
    }

    /**
     * Get Coin Information
     * @return Information
     */
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
                    "host" => $config->blockchain['bsc_rpc_main_net'],
                    "port" => '',
                    'username' => '',
                    'password' => '',
                    "passphrase" => "",
                    "cert_file" => "",
                    "url" => "",
                    'pass_phrase' => '',
                    'chain_id' => 56
                ]
            ],
            'test' => [
                [
                    "host" => $config->blockchain['bsc_rpc_test_net'],
                    "port" => '',
                    'username' => '',
                    'password' => '',
                    "passphrase" => "",
                    "cert_file" => "",
                    "url" => "",
                    'pass_phrase' => '',
                    'chain_id' => 97
                ]
            ],
        ];
        $coinInfo->args = [
            'contract_address' => $this->contract,
            'technology' => "BEP20",
            "fields" => strlen($this->optFields) ? json_decode($this->optFields, true) : [],
            'explorer' => $this->explorer_link
        ];
        return $coinInfo;
    }

    /**
     * Connect to RPC Server By ID and Network
     * @param null $serverIp
     * @param string $network
     * @return $this
     * @throws Exception
     */
    public function connect($serverIp = null, string $network = ContractLibrary::MAIN_NETWORK)
    {
        try {
            $coinInfo = $this->getCoinInfo();
            $network = $network == ContractLibrary::MAIN_NETWORK ? ContractLibrary::MAIN_NETWORK : ContractLibrary::TEST_NETWORK;
            $listHost = $coinInfo->hosts[$network];
            $severId = 0;
            if ($serverIp == null) {
                $severId = array_rand($listHost, 1);
            } else {
                foreach ($listHost as $key => $item) {
                    if ($item['host'] == $serverIp) $severId = $key;
                }
            }
            $hostInstance = $listHost[$severId];
            $this->network = $network;
            $this->rpcConnector = new Web3(new HttpProvider(new HttpRequestManager($hostInstance['host'], 30)));
            $this->eth = $this->rpcConnector->getEth();
            $this->chainId = $hostInstance['chain_id'];
            return $this;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Blockchain Info
     * @return array
     * @throws Exception
     */
    public function getBlockchainInfo()
    {
        try {
            $listHost = $this->getHostByNetwork($this->network);
            $data = [];
            foreach ($listHost as $hostInstance) {
                $rpcConnector = new Web3(new HttpProvider(new HttpRequestManager($hostInstance['host'], 30)));
                $eth = $rpcConnector->getEth();

                $lastBlock = null;
                $eth->blockNumber(function ($err, $res) use (&$lastBlock) {
                    if ($res) {
                        $lastBlock = $res->toString();
                    }
                });

                $gasPrice = '0';
                $eth->gasPrice(function ($err, $res) use (&$gasPrice) {
                    if ($res) {
                        $gasPrice = $res->toString();
                    }
                });

                $utils = $rpcConnector->getUtils();
                [$quotient, $remainder] = $utils::fromWei($gasPrice, 'gwei');
                $gasPriceGwei = $quotient->toString();

                $rs['headers'] = $lastBlock;
                $rs['gas_price_wei'] = $gasPrice;
                $rs['gas_price_gwei'] = $gasPriceGwei;

                $data[] = ['ip' => $hostInstance['host'], 'info' => $rs];
            }
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Balance Of Address
     * @param Account $account
     * @param string $block
     * @return string|null
     * @throws Exception
     */
    public function getBalanceAccount(Account $account, $block = 'latest')
    {
        try {
            $balance = 0;
            if ($this->key == self::MAIN_CURRENCY) {
                $this->eth->getBalance($account->address, $block, function ($err, $res) use (&$balance) {
                    if ($res) {
                        $balance = $res->toString();
                    }
                });
            } else {
                $utils = $this->rpcConnector->getUtils();
                $functionId = substr($utils::sha3('balanceOf(address)'), 0, 10);
                $address = self::abiEncode('address', $account->address);
                $data = $functionId . $address;
                $params = [
                    'to' => $this->contract,
                    'data' => $data
                ];
                $this->eth->call($params, $block, function ($err, $res) use (&$balance) {
                    $balance = self::bchexdec($res);
                });
            }
            return self::wei2eth($balance, $this->decimals);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Send BNB Or Token
     * @param Account $from
     * @param Account $to
     * @param Send $sendObject
     * @return Send
     * @throws Exception
     */
    public function send(Account $from, Account $to, Send $sendObject)
    {
        try {
            $fromAddress = $from->address;
            $toAddress = $to->address;
            $gasMultiply = $sendObject->multiply_gas > 0 ? $sendObject->multiply_gas : 1;
            $gasPriceWei = bcmul($this->getGasPriceWei(), $gasMultiply);

            $hash = null;
            $privateKey = $from->private_key;
            $amount = strval($sendObject->amount);
            $amount = (BigDecimal::of($amount))->multipliedBy(pow(10, $this->decimals))->getIntegralPart();
            $amountWeiHex = (new BigInteger($amount))->toHex();

            if ($this->key == self::MAIN_CURRENCY) {
                $gasLimit = self::GAS_MAIN_CURRENCY;
                $txParams = [
                    'from' => $fromAddress,
                    'to' => $toAddress,
                    'value' => "0x" . $amountWeiHex,
                    'gas' => "0x" . self::bcdechex($gasLimit),
                    'gasPrice' => "0x" . self::bcdechex($gasPriceWei),
                    'chainId' => $this->chainId,
                    'data' => null,
                ];
            } else {
                $gasLimit = self::GAS_TOKEN;
                $contractAddress = $this->contract;
                $utils = $this->rpcConnector->getUtils();
                $functionId = substr($utils::sha3('transfer(address,uint256)'), 0, 10);
                $address = self::abiEncode('address', $toAddress);
                $amount = self::abiEncode('hex', $amountWeiHex);
                $data = $functionId . $address . $amount;

                $txParams = [
                    'from' => $fromAddress,
                    'to' => $contractAddress,
                    'value' => null,
                    'gas' => "0x" . self::bcdechex($gasLimit),
                    'gasPrice' => "0x" . self::bcdechex($gasPriceWei),
                    'chainId' => $this->chainId,
                    'data' => $data,
                ];
            }
            $sendObject->gas_limit = $gasLimit;
            $sendObject->gas_price = $gasPriceWei;
            if ($sendObject->with_nonce) {
                $nonce = $this->getNonce($fromAddress);
                $sendObject->nonce = $nonce;
                $txParams['nonce'] = "0x" . self::bcdechex($nonce);
            }
            $sendObject->tx_param = $txParams;
            $transaction = new Transaction($txParams);
            $signedTransaction = '0x' . $transaction->sign($privateKey);
            $sendObject->signed_data = $signedTransaction;
            $this->eth->sendRawTransaction($signedTransaction, function ($err, $res) use (&$hash) {
                $hash = $res;
            });

            $sendObject->hash = $hash;
            $sendObject->info = ['from_address' => $fromAddress, 'network' => $this->network, 'platform' => $this->platform];
            return $sendObject;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $blockNumber
     * @param bool $showTransaction
     * @return array
     * @throws Exception
     */
    public function getTransactionsByNumberBlock($blockNumber = "latest", $showTransaction = true)
    {
        try {
            $data = [];
            $this->eth->getBlockByNumber($blockNumber, $showTransaction, function ($err, $res) use (&$data) {
                $data = $res;
            });
            return (array)$data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Transaction Receipt
     * @param $hash
     * @return array
     * @throws Exception
     */
    public function getTransactionReceipt($hash)
    {
        try {
            $data = [];
            $this->eth->getTransactionReceipt($hash, function ($err, $res) use (&$data) {
                $data = $res;
            });
            return (array)$data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Transaction Status
     * @param $hash
     * @return float|int|string
     * @throws Exception
     */
    public function getTransactionStatus($hash)
    {
        $txReceipt = $this->getTransactionReceipt($hash);
        if (!$txReceipt) {
            return null;
        }
        return self::bchexdec($txReceipt['status']);
    }

    /**
     * @param $hash
     * @return array
     * @throws Exception
     */
    public function getTransactionDetail($hash)
    {
        try {
            $data = [];
            $this->eth->getTransactionByHash($hash, function ($err, $res) use (&$data) {
                $data = $res;
            });
            return (array)$data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Nonce
     * @param $address
     * @param string $type
     * @return int
     */
    public function getNonce($address, $type = 'pending')
    {
        /** @var BigInteger $nonce */
        $nonce = 0;
        $this->eth->getTransactionCount($address, $type, function ($err, $res) use (&$nonce) {
            if ($res) {
                $nonce = $res->toString();
            }
        });
        return intval($nonce);
    }

    /**
     * Check Valid Address
     * @param $address
     * @return bool
     */
    public function validAddress($address)
    {
        if (!strlen($address)) {
            return false;
        }

        $utils = $this->rpcConnector->getUtils();
        return $utils::isAddress($address);
    }

    /**
     * Check is checksum address
     * @param $address
     * @return bool
     */
    function isChecksumAddress($address)
    {
        if (!strlen($address)) {
            return false;
        }

        $utils = $this->rpcConnector->getUtils();
        return $utils::isAddressChecksum($address);
    }

    /**
     * Convert to checksum address
     * @param $address
     * @return string
     */
    function toCheckSumAddress($address)
    {

        if (!strlen($address)) {
            return '';
        }

        $utils = $this->rpcConnector->getUtils();
        return $utils::toChecksumAddress($address);
    }

    /**
     * @param string $prefix
     * @return Account
     * @throws Exception
     */
    public function createAccount($prefix = '')
    {
        try {
            $addressInstance = new Address();
            $addressInstance->get();
            $addressInstance->getPrivateKey();
            $addressInstance->getPublicKey();

            $id = sprintf("%s-%s-%s-%s", $this->ticker, $prefix, time(), rand(11111, 99999));
            $password = md5(md5($id));

            $addressData = [
                'address' => $this->toCheckSumAddress($addressInstance->get()),
                'public_key' => $addressInstance->getPublicKey(),
                'private_key' => $addressInstance->getPrivateKey(),
                'password' => $password
            ];

            $coinAccount = new Account($id, $addressData['address'], $addressData['address'], $password, null, $this->ticker, null, $addressData['private_key']);
            $this->saveAccountToFile($coinAccount);
            return $coinAccount;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Decode Function Input Data
     * Type: lock, token, liquid
     * @param $inputData
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function decodeFunctionInputData($inputData, $type)
    {
        try {
            if (!$inputData || strlen($inputData) < 10) {
                return [];
            }
            $abi = ContractLibrary::getAbi($type);
            $contractInstance = new Contract($this->rpcConnector->getProvider(), $abi);
            $ethAbi = $contractInstance->getEthabi();
            $utils = $this->rpcConnector->getUtils();

            $functionSelector = substr($inputData, 0, 10);
            $listFunction = $contractInstance->getFunctions();

            foreach ($listFunction as $functionItem) {
                $methodString = $utils::jsonMethodToString($functionItem);
                if ($functionSelector == $ethAbi->encodeFunctionSignature($methodString)) {
                    $functionInfo = $functionItem;
                }
            }

            if (empty($functionInfo)) {
                return [];
            }

            $inputData = substr($inputData, 10);
            $dataDecode = $functionInfo;
            if (count($functionInfo['inputs'])) {
                $listDataType = [];
                foreach ($functionInfo['inputs'] as $inputItem) {
                    $listDataType[] = $inputItem['type'];
                }
                $dataDecode['data_decode'] = $ethAbi->decodeParameters($listDataType, $inputData);
                foreach ($dataDecode['data_decode'] as $key => $decodeItem) {
                    if ($decodeItem instanceof BigInteger) {
                        $dataDecode['data_decode'][$key] = $decodeItem->toString();
                    }
                }
            }
            return $dataDecode;
        } catch (Exception $exception) {
            throw $exception;
        }

    }

    /**
     * Decode Function Input Data
     * Type: lock, token, liquid
     * @param $inputData
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function decodeEventInputData($eventData, $type = ContractLibrary::TOKEN)
    {
        if (!$eventData || !is_array($eventData)) {
            return [];
        }

        $abi = ContractLibrary::getAbi($type);
        $contractInstance = new Contract($this->rpcConnector->getProvider(), $abi);
        $ethAbi = $contractInstance->getEthabi();
        $utils = $this->rpcConnector->getUtils();
        $listEvent = $contractInstance->getEvents();
        $listTopic = $eventData['topics'];
        $topicSignature = $listTopic[0];

        foreach ($listEvent as $eventItem) {
            $methodString = $utils::jsonMethodToString($eventItem);
            if ($topicSignature == $ethAbi->encodeEventSignature($methodString)) {
                $eventInfo = $eventItem;
            }
        }

        if (empty($eventInfo)) {
            return [];
        }

        $inputData = $eventData['data'];
        $inputData = substr($inputData, 2);
        $dataDecode = $eventInfo;
        $dataDecodeIndexed = $dataDecodeNotIndexed = [];
        if (count($eventInfo['inputs'])) {
            $listDataType = [];
            foreach ($eventInfo['inputs'] as $key => $inputItem) {
                if ($inputItem['indexed']) {
                    $dataDecodeIndexed[] = $ethAbi->decodeParameter($inputItem['type'], $listTopic[$key + 1]);
                } else {
                    $listDataType[] = $inputItem['type'];
                }
            }
            if (count($listDataType)) {
                $dataDecodeNotIndexed = $ethAbi->decodeParameters($listDataType, $inputData);
            }

            $dataDecode['data_decode'] = array_merge($dataDecodeIndexed, $dataDecodeNotIndexed);
            foreach ($dataDecode['data_decode'] as $key => $decodeItem) {
                if ($decodeItem instanceof BigInteger) {
                    $dataDecode['data_decode'][$key] = $decodeItem->toString();
                }
            }
        }
        return $dataDecode;

    }

    /**
     * Get Function Item
     * @param $functionName
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function getFunctionItem($functionName, $type)
    {
        try {
            if (!$functionName || !strlen($functionName)) {
                return [];
            }

            $abi = ContractLibrary::getAbi($type);
            $contractInstance = new Contract($this->rpcConnector->getProvider(), $abi);
            $listFunction = $contractInstance->getFunctions();
            $data = [];
            foreach ($listFunction as $functionItem) {

                if ($functionItem['name'] != $functionName) {
                    continue;
                }
                $data = $functionItem;
            }

            return $data;
        } catch (Exception $exception) {
            throw $exception;
        }

    }

    public function decryptEthData($data)
    {
        try {
            $address = $this->__connector->decrypt_address($data);
            if ($this->__connector->status != 200) {
                throw new Exception($this->__connector->error, $this->__connector->status);
            }
            return $address;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createEthData($data)
    {
        try {
            $address = $this->__connector->encrypt_address($data);
            if ($this->__connector->status != 200) {
                throw new Exception($this->__connector->error, $this->__connector->status);
            }
            return $address;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Gas Price Wei
     * @return int
     * @throws Exception
     */
    public function getGasPriceWei()
    {
        try {
            $gasPrice = null;
            $this->eth->gasPrice(function ($err, $res) use (&$gasPrice) {
                if ($res) {
                    $gasPrice = $res->toString();
                }
            });
            return intval($gasPrice);
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

    public static function abiEncode($type, $value)
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

    public static function Str2Hex($str)
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $hex .= dechex(ord($str[$i]));
        }

        return $hex;
    }

    public static function Hex2Str($hex)
    {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $str;

    }

    public function getGasLimit()
    {
        return self::GAS_TOKEN;
    }
}