<?php
/**
 * Created by PhpStorm.
 * User: Unknown
 */

namespace DCrypto\Networks;

use DCrypto\Connector;
use DCrypto\Object\Account;
use DCrypto\Object\ICoin;
use DCrypto\Object\Information;
use DCrypto\Object\Send;
use Exception;

class Bitcoin extends ICoin
{

    public function getCoinInfo()
    {
        $coinInfo = new Information();
        $coinInfo->ticker = "BTC";
        $coinInfo->name = "Bitcoin";
        $coinInfo->key = "btc";
        $coinInfo->platform = 'btc';
        $coinInfo->hosts = [
            [
                "host" => "45.76.178.162",
                "port" => 8332,
                'username' => 'Kjklajdlqjqebnsh4',
                'password' => 'kljljaldjqej2Kjladjlh5',
                "passphrase" => "",
                "cert_file" => ""
            ]
        ];
        $coinInfo->args = [
            "fields" => [
                'id' => 'btc_id',
                'address' => 'btc_address',
                'balance' => 'btc_balance',
            ],
            'uri' => 'bitcoin',
            'wallet_to_send' => [],
            'tab_view' => [
                ['name' => "Transactions", 'key' => "transactions"],
                ['name' => "Chart", 'key' => "chart"]
            ],
        ];

        global $config;
        $coinInfo->explorer = [
            'link' => "https://www.blockchain.com/vi/btc",
            'tx' => "/tx/%s",
            'chart_link' => $config->site->link . "/dmz/btcchart"
        ];
        $coinInfo->cover_image = [
            's256x256' => $config->site->link . "/images/btc-cover.png",
        ];
        $coinInfo->icon = [
            's16x16' => $config->site->link . "/images/bitcoin256.png",
            's256x256' => $config->site->link . "/images/bitcoin256.png",
        ];


        return $coinInfo;
    }

    /**
     * @param array $param
     * @return array
     * @throws Exception
     */
    public function getTransactions($param = [])
    {
        try {
            $coinInfo = $this->getCoinInfo();
            $response = [];
            foreach ($coinInfo->hosts as $host) {
                $host = (object)$host;
                $connector = new Connector($host->username, $host->password, $host->host, $host->port);
                if (!empty($host->pass_phrase)) $connector->walletpassphrase($host->pass_phrase, 10);
                if ($param['category'] == 'received') {
                    $minconf = !empty($param['minconf']) ? $param['minconf'] : 0;
                    $include_empty = !empty($param['include_empty']) ? $param['include_empty'] : false;
                    $listTransaction = $connector->listreceivedbyaddress(intval($minconf), intval($include_empty));
                } else if ($param['category'] == 'unspent') {
                    $minconf = !empty($param['minconf']) ? $param['minconf'] : 0;
                    $maxconf = !empty($param['maxconf']) ? $param['maxconf'] : 999999;
                    $listTransaction = $connector->listunspent(intval($minconf), intval($maxconf));
                } else {
                    $limit = !empty($param['limit']) ? $param['limit'] : 100;
                    $offset = !empty($param['offset']) ? $param['offset'] : 0;
                    $listTransaction = $connector->listtransactions("*", intval($limit), intval($offset), false);
                    $listTransaction = array_reverse($listTransaction);
                }
                if (!empty($connector->response['error'])) throw new Exception($connector->response['error']['message']);
                else if (!empty($listTransaction)) $response = array_merge($response, $listTransaction);
            }
            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Account $account
     * @param array $param
     * @return mixed
     * @throws Exception
     */
    public function getTransactionsByAccount(Account $account, $param = [])
    {
        try {
            if (!empty($this->__hostInstance->pass_phrase)) $this->__connector->walletpassphrase($this->__hostInstance->pass_phrase, 10);
            $limit = !empty($param['limit']) ? $param['limit'] : 50;
            $offset = !empty($param['offset']) ? $param['offset'] : 0;
            $listTransaction = $this->__connector->listtransactions($account->id, $limit, $offset, false);
            return $listTransaction;
        } catch (Exception $e) {
            throw $e;
        }
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
            if (!empty($sendObject->fee) && $sendObject->fee > 0) $this->__connector->settxfee(number_format(floatval($sendObject->fee), 8));
            $hash = $this->__connector->sendtoaddress($to->address, number_format(floatval($sendObject->amount), 8), "withdraw", 'user', true, true);
            if ($this->__connector->status != 200) throw new Exception($this->__connector->error);
            $sendObject->hash = $hash;
            return $sendObject;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
