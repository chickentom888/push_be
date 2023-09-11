<?php

namespace Dcore\Library;

use Exception;

class MGateSDK
{
    const API_KEY = "icominepi_com";
    const API_URL = "https://gateway.error-notfound.com/";
    public static $chainMapping = [
        "btc" => "SHA256",
        'eth' => "ERC20",
        'usdt' => "ERC20",
        'usdt_trc20' => "TRC20",
        "trx" => "TRC20"
    ];

    public static function getBalanceAccount($param)
    {
        return 0;
    }

    public static function createAddress($tokenKey, $platform)
    {
        $url = "api/createAddress?token_key={$tokenKey}&platform={$platform}&api_key=" . MGateSDK::API_KEY;
        $addressRs = self::callGet(MGateSDK::API_URL . $url);
        $addressRs = json_decode($addressRs);
        if ($addressRs->status == 1) {
            return $addressRs->data;
        } else {
            throw new Exception($addressRs->message);
        }
    }

    public static function getTransactions()
    {
        $url = "api/getTxs?api_key=" . MGateSDK::API_KEY;
        $txnRs = self::callGet(MGateSDK::API_URL . $url);
        $txnRs = json_decode($txnRs);
        if ($txnRs->status == 1) {
            return $txnRs->data;
        } else {
            throw new Exception($txnRs->message);
        }
    }

    public static function sendCoin($tokenKey, $platform, $password, $amount, $fee, $toAddress)
    {
        $url = "api/send?api_key=" . MGateSDK::API_KEY;
        $data = [
            'token_key' => $tokenKey,
            'platform' => $platform,
            'password' => $password,
            'amount' => $amount,
            'fee' => $fee,
            'to_address' => $toAddress,
        ];
        $result = self::callPost(MGateSDK::API_URL . $url, $data);
        $result = json_decode($result);
        if ($result->status == 1) {
            return $result->data;
        } else {
            throw new Exception($result->message);
        }
    }

    public static function callPost($url, $data)
    {

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "cache-control: no-cache",
                "content-type: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }

    public static function callGet($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            /*CURLOPT_HTTPHEADER => array(
                "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZWxpdGVhbS5jbyIsImF1ZCI6Imh0dHBzOlwvXC9lbGl0ZWFtLmNvIiwiaWF0IjoxNTI4MzA1MTM2LCJuYmYiOjE1MjgzMDUxMzYsImRhdGEiOnsiaWQiOiIxNyIsImVtYWlsIjoidi5pZXRwaWFub0BnbWFpbC5jb20ifX0.bPS1lq7K9UzkQCy1XC0OxHq7Mvr4zQl_K-TKzzLbCjU"
            ),*/
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return null;
        } else {
            return $response;
        }
    }

    public static function transferAddress($data)
    {
        $url = "api/transfer_address?api_key=" . MGateSDK::API_KEY;
        $data = [
            'token_key' => $data['token_key'],
            'platform' => $data['platform'],
            'account_id' => $data['account_id'],
            'password' => $data['password'],
            'private_key' => $data['private_key'],
            'address' => $data['address'],
        ];
        $result = self::callPost(MGateSDK::API_URL . $url, $data);
        $result = json_decode($result);
        if ($result->status == 1) {
            return $result->data;
        } else {
            throw new Exception($result->message);
        }
    }

    public static function getSendTransactions()
    {
        $url = "api/getSendTxs?api_key=" . MGateSDK::API_KEY;
        $txnRs = self::callGet(MGateSDK::API_URL . $url);
        $txnRs = json_decode($txnRs);
        if ($txnRs->status == 1) {
            return $txnRs->data;
        } else {
            throw new Exception($txnRs->message);
        }
    }
}
