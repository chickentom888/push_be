<?php

namespace DCrypto\Object;


use Dcore\Library\Helper;
use DCrypto\Connector;
use Exception;

abstract class ICoin
{

    const STATE_RUNNING = "Running";
    const STATE_TESTING = "Testing";
    const STATE_DISABLED = "Disabled";
    const STATE_INITIALIZING = "Initializing";
    const ACCOUNT_STORE_PATH = BASE_PATH . "/account_store/";


    public static $explorerBEP20Link = [
        'main' => [
            "transaction" => "https://bscscan.com/tx/",
            "address" => "https://bscscan.com/address/"
        ],
        'test' => [
            "transaction" => "https://testnet.bscscan.com/tx/",
            "address" => "https://testnet.bscscan.com/address/"
        ],

    ];

    /** @var Host */
    public $__hostInstance = null;

    /** @var Connector */
    public $__connector = null;

    /**
     * @return array
     * @throws Exception
     */
    public function getBlockchainInfo()
    {
        try {
            $coinInfo = $this->getCoinInfo();
            $data = [];
            foreach ($coinInfo->hosts as $hostInstance) {
                $hostInstance = (object)$hostInstance;
                $connector = new Connector($hostInstance->username, $hostInstance->password, $hostInstance->host, $hostInstance->port);
                if (!empty($hostInstance->pass_phrase)) $connector->walletpassphrase($hostInstance->pass_phrase, 10);
                $info = $connector->getinfo();
                $blockchain_info = $connector->getblockchaininfo();
                $data[] = ['ip' => $hostInstance->host, 'info' => $info, 'blockchain_info' => $blockchain_info];
            }
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return Information
     */
    public function getCoinInfo()
    {

    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return $this->__connector;
    }

    /**
     * @param null $serverIp
     * @return ICoin
     * @throws Exception
     */
    public function connect($serverIp = null)
    {
        try {
            $coinInfo = $this->getCoinInfo();
            if ($serverIp == null) $severId = array_rand($coinInfo->hosts, 1);
            else foreach ($coinInfo->hosts as $key => $item) if ($item['host'] == $serverIp) $severId = $key;
            $hostInstance = $coinInfo->hosts[$severId];
            $this->__hostInstance = new Host($hostInstance['host'], $hostInstance['port'], $hostInstance['username'], $hostInstance['password'], $hostInstance['pass_phrase'], $hostInstance['cert_file']);
            $this->__connector = new Connector($this->__hostInstance->username, $this->__hostInstance->password, $this->__hostInstance->host, $this->__hostInstance->port);
            return $this;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $prefix
     * @return Account
     * @throws Exception
     */
    public function createAccount($prefix = '')
    {
        try {
            $coinInfo = $this->getCoinInfo();
            if (!empty($this->__hostInstance->pass_phrase)) $this->__connector->walletpassphrase($this->__hostInstance->pass_phrase, 10);
            $id = sprintf("%s-%s-%s-%s", $coinInfo->key, $prefix, microtime(true), rand(11111, 99999));
            $address = $this->__connector->getnewaddress($id);
            if ($this->__connector->status != 200) throw new Exception($this->__connector->error, $this->__connector->status);
            //if($address->result && empty($address->error)) $address = $address->result;
            $coinAccount = new Account();
            $coinAccount->id = $id;
            $coinAccount->address = $address;
            $coinAccount->server_ip = $this->__hostInstance->host;
            $coinAccount->ticker = $coinInfo->ticker;
            return $coinAccount;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $hash
     * @return mixed
     * @throws Exception
     */
    public function getTransactionDetail($hash)
    {
        try {
            if (!empty($this->__hostInstance->pass_phrase)) $this->__connector->walletpassphrase($this->__hostInstance->pass_phrase, 10);
            return $this->__connector->gettransaction($hash);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $address
     * @return bool
     */
    public function validAddress($address)
    {
        if (!empty($this->__hostInstance->pass_phrase)) $this->__connector->walletpassphrase($this->__hostInstance->pass_phrase, 10);
        $rs = $this->__connector->validateaddress($address);
        if ($rs['isvalid'] != 1) return false;
        return true;
    }

    /**
     * @param Account $from
     * @param Account $to
     * @param Send $sendObject
     * @return Send
     * @throws Exception
     */
    public function send(Account $from, Account $to, Send $sendObject)
    {
        try {
            if (!empty($this->__hostInstance->pass_phrase)) $this->__connector->walletpassphrase($this->__hostInstance->pass_phrase, 10);
            if (!empty($sendObject->wallet_pass_phrase)) $this->__connector->walletpassphrase($sendObject->wallet_pass_phrase, 10);
            if (!empty($sendObject->fee) && $sendObject->fee > 0) $this->__connector->settxfee($sendObject->fee);

            if (empty($sendObject->typeof_method) || $sendObject->typeof_method == Send::TYPEOF_METHOD_SEND_TO_ADDRESS) $hash = $this->__connector->sendtoaddress($to->address, $sendObject->amount);
            else $hash = $this->__connector->sendfrom($from->id, $to->address, $sendObject->amount);

            if ($this->__connector->status != 200) throw new Exception($this->__connector->error);
            $sendObject->hash = $hash;
            return $sendObject;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Account $account
     * @return float
     * @throws Exception
     */
    public function getBalanceAccount(Account $account)
    {

    }

    /**
     * @return array
     * @throws Exception
     */
    public function getBalances()
    {
        try {
            $coinInfo = $this->getCoinInfo();
            $response = [];
            foreach ($coinInfo->hosts as $host) {
                $host = (object)$host;
                $connector = new Connector($host->username, $host->password, $host->host, $host->port);
                if (!empty($host->pass_phrase)) $connector->walletpassphrase($host->pass_phrase, 10);
                $balance = $connector->getbalance();
                $response[] = ['ip' => $host->host, 'balance' => $balance, 'ticker' => $coinInfo->ticker, 'name' => $coinInfo->name];
            }
            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Account $account
     * @throws Exception
     */
    protected function saveAccountToFile(Account $account)
    {
        try {
            $info = $this->getCoinInfo();
            $platformPath = ICoin::ACCOUNT_STORE_PATH . "platform/" . $info->platform;
            if (!file_exists($platformPath)) {
                mkdir($platformPath, 0777, true);
            }

            $tokenKeyPath = ICoin::ACCOUNT_STORE_PATH . "token_key/" . $info->key;
            if (!file_exists($tokenKeyPath)) {
                mkdir($tokenKeyPath, 0777, true);
            }

            $filePlatform = fopen($platformPath . "/" . $account->address, "wb");
            fwrite($filePlatform, json_encode($account));
            fclose($filePlatform);

            $fileTokenKey = fopen($tokenKeyPath . "/" . $account->address, "wb");
            fwrite($fileTokenKey, json_encode($account));
            fclose($fileTokenKey);

        } catch (Exception $e) {
            Helper::debug($e->getMessage());
        }
    }

    /**
     * @param $transaction
     */
    public function saveTransactionToFile($transaction)
    {
        try {
            $info = $this->getCoinInfo();
            $path = ICoin::ACCOUNT_STORE_PATH . $info->key . "/transactions";
            if (!file_exists($path)) mkdir($path, 0777, true);
            $fp = fopen($path . "/" . $transaction['hash'], "wb");
            fwrite($fp, json_encode($transaction));
            fclose($fp);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $data
     * @param $file_name
     * @param null $custom_dir
     */
    public function saveInfoToFile($data, $file_name, $custom_dir = null)
    {
        try {
            $info = $this->getCoinInfo();
            $path = ICoin::ACCOUNT_STORE_PATH . $info->key . "/info";
            if ($custom_dir != null) $path .= "/$custom_dir";
            if (!file_exists($path)) mkdir($path, 0777, true);
            $fp = fopen($path . "/" . $file_name, "wb");
            fwrite($fp, json_encode($data));
            fclose($fp);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $address
     * @return false|mixed
     */
    public function getAccountInfoByFile($address)
    {
        $coin = $this->getCoinInfo();
        $path = ICoin::ACCOUNT_STORE_PATH . $coin->key;
        $file_account = $path . "/accounts/" . $address;
        if (file_exists($file_account)) {
            $data = file_get_contents($file_account);
            return json_decode($data);
        } else return false;
    }

    /**
     * @param $hash
     * @return false|mixed
     */
    public function getTransactionByFile($hash)
    {
        $coin = $this->getCoinInfo();
        $path = ICoin::ACCOUNT_STORE_PATH . $coin->key;
        $file_account = $path . "/transactions/" . $hash;
        if (file_exists($file_account)) {
            $data = file_get_contents($file_account);
            return json_decode($data);
        } else return false;
    }

    /**
     * @param string $network
     * @return mixed|array
     */
    public function getHostByNetwork($network = 'main')
    {
        $coinInfo = $this->getCoinInfo();
        $network = $network == 'main' ? 'main' : 'test';
        return $coinInfo->hosts[$network];
    }

}
