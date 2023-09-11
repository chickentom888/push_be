<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\ContractLibrary;
use Dcore\Models\Registry;
use Exception;

class RateTask extends TaskBase
{
    public function updateAction()
    {
        $dataUpdate = [];
        $priceBNB = ContractLibrary::getPriceBNB();
        if ($priceBNB > 0) {
            $dataUpdate['bnb_price'] = $priceBNB;
        }

        $priceETH = ContractLibrary::getPriceETH();
        if ($priceETH > 0) {
            $dataUpdate['eth_price'] = $priceETH;
        }

        if (count($dataUpdate)) {
            $collection = $this->mongo->selectCollection('registry');
            $registry = $collection->findOne();

            if ($registry) {
                $collection->updateOne([
                    '_id' => $registry['_id']
                ], ['$set' => $dataUpdate]);
            } else {
                $collection->insertOne($dataUpdate);
            }
        }
    }

    protected function getBTCPrice()
    {
        try {
            $usd = $this->getData("https://mailer.notresponse.com/rate/rate.php?from=BTC&to=USD");
            $rs = json_decode($usd);
            return $rs->data;
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function getETHPrice()
    {
        try {
            $usd = $this->getData("https://mailer.notresponse.com/rate/rate.php?from=ETH&to=USD");
            $rs = json_decode($usd);
            return $rs->data;
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function getUSDTPrice()
    {
        try {
            $usd = $this->getData("https://mailer.notresponse.com/rate/rate.php?from=USDT&to=USD");
            $rs = json_decode($usd);
            return $rs->data;
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function getBCHPrice()
    {
        try {
            $usd = $this->getData("https://mailer.notresponse.com/rate/rate.php?from=BCH&to=USD");
            $rs = json_decode($usd);
            return $rs->data;
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function getCoinPrice()
    {
        $usd = $this->getData("https://openapi.digifinex.vip/v2/ticker?apiKey=15da930a69ac0d&symbol=usdt_mch");
        $usd = json_decode($usd);
        return $usd->ticker->usdt_mch->last;
    }

    protected function getData($url)
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

    protected function getBTCPriceFromApi()
    {
        $usd = file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    protected function getBCHPriceFromApi()
    {
        $usd = file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=BCH&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    protected function getETHPriceFromApi()
    {
        $usd = file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD");
        $usd = json_decode($usd);
        return $usd->USD;
    }

    protected function getChangePercent()
    {
        $data = file_get_contents("https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,tether,bitcoin-cash&vs_currencies=usd&include_24hr_change=true");
        $data = json_decode($data, true);
        $registry = Registry::findFirst();
        $usdtChange = $data['tether']['usd_24h_change'];
        $btcChange = $data['bitcoin']['usd_24h_change'];
        $ethChange = $data['ethereum']['usd_24h_change'];
        $bchChange = $data['bitcoin-cash']['usd_24h_change'];
        $registry->usdt_change = round($usdtChange, 4);
        $registry->btc_change = round($btcChange, 4);
        $registry->eth_change = round($ethChange, 4);
        $registry->bch_change = round($bchChange, 4);
        $registry->save();
    }

}
