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