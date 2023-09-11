<?php

namespace Dcore\Library;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Swap
{

    protected $client;
    protected $host;
    protected $feeReceipt;

    public function __construct()
    {
        global $config;
        $this->client = new Client(['cookies' => true]);
        $this->host = $config->swap->base_url;
        $this->feeReceipt = $config->swap->fee_receipt;
    }

    protected function getApiKey()
    {
        $listKey = [
            'ddfe74a4-26bf-429a-a3b0-ffd831908058',
            '34da8937-80ce-4592-a8ec-b45280529f2e',
            'f06eed4c-d7e7-4bc1-a03d-df32b809b427',
            '23ad09e0-39c3-49d8-92f7-583a294419d4',
            '33f5382c-60d4-45e8-872a-49cb16c11b8b',
            '9e311b19-0d71-4d75-ab3d-0da01f400409',
            '163f8799-1a8b-4b48-bd51-6ebdb66eeee3',
            '950423b2-5ad2-478f-ac90-79ae076f07ea',
            '76df7b08-a96e-4d3f-a6a7-2ab360da1e70',
            'ada91d75-ce2b-4df5-a7f4-b7d88128f22c',
            '9ade7fad-f41a-4c97-91be-d49077c6e169',
            '404523ac-f377-4718-8d04-02451bb8dcd9',
            '5db3fb18-e63a-4d17-be7e-74d322ef04f6',
            '964d5058-10bd-4485-a015-70ad7175f198',
            'a5eb7718-8a67-4cc0-8fb7-343f7fc964a0',
            '5a73b16e-8f79-457a-b38e-d5521fde7809',
        ];
        $key = array_rand($listKey, 1);
        return $listKey[$key];
    }

    /**
     * @throws Exception
     */
    public function getQuote($data)
    {
        $url = $this->host . '/swap/v1/quote';
        $apiKey = $this->getApiKey();
        $headers = [
            '0x-api-key' => $apiKey
        ];

        $options = [
            'query' => [
                'sellToken' => $data['sellToken'] ?? '',
                'buyToken' => $data['buyToken'] ?? '',
                'sellAmount' => $data['sellAmount'] ?? null,
                'buyAmount' => $data['buyAmount'] ?? null,
                'gasPrice' => '5000000000',
                'feeRecipient' => $this->feeReceipt ?? '',
                'slippagePercentage' => $data['slippagePercentage'] ?? '0.005',
                'buyTokenPercentageFee' => '0.00',
                'enableSlippageProtection' => 'true',

                //'slippagePercentage' => '0.005',
                //'buyTokenPercentageFee' => '0',
                //'enableSlippageProtection' => 'true'
            ]
        ];
        $request = new Request('GET', $url, $headers);
        $res = $this->client->sendAsync($request, $options)->wait();
        $responseContent = $res->getBody()->getContents();
        if (!Helper::isJson($responseContent)) {
            Log::createLog("Error when get quote" . PHP_EOL . $responseContent);
            throw new Exception('Error when get quote');
        }
        return json_decode($responseContent, true);

    }
}