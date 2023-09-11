<?php

namespace Dcore\Modules\Admin\Controllers;


use Dcore\ControllerBase\ControllerBase;

class ExtendedControllerBase extends ControllerBase
{
    public function initialize($param = null)
    {
        $paramCheck = $param ? $param : ['check-login', 'check-role' => [self::ROLE_ADMIN]];
        parent::initialize($paramCheck);
    }

    /**
     * Lấy tỉ giá BTC
     * @return mixed
     */
    public function getBTCPrice()
    {
        $usd = $this->getData("https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    /**
     * Lấy tỉ giá ETH
     * @return mixed
     */
    public function getETHPrice()
    {
        $usd = $this->getData("https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    /**
     * Lấy tỉ giá BCH
     * @return mixed
     */
    public function getBCHPrice()
    {
        $usd = $this->getData("https://min-api.cryptocompare.com/data/price?fsym=BCH&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    /**
     * Lấy tỉ giá USDT
     * @return mixed
     */
    public function getUSDTPrice()
    {
        $usd = $this->getData("https://min-api.cryptocompare.com/data/price?fsym=USDT&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    public function getData($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            /*CURLOPT_HTTPHEADER => array(
                "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZWxpdGVhbS5jbyIsImF1ZCI6Imh0dHBzOlwvXC9lbGl0ZWFtLmNvIiwiaWF0IjoxNTI4MzA1MTM2LCJuYmYiOjE1MjgzMDUxMzYsImRhdGEiOnsiaWQiOiIxNyIsImVtYWlsIjoidi5pZXRwaWFub0BnbWFpbC5jb20ifX0.bPS1lq7K9UzkQCy1XC0OxHq7Mvr4zQl_K-TKzzLbCjU"
            ),*/
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) return null;
        else return $response;
    }
}
