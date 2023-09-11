<?php

namespace Dcore\Modules\Api\Controllers;

use Brick\Math\BigDecimal;
use Dcore\Collections\BaseCollection;
use Dcore\Library\Log;
use Dcore\Library\Swap;
use Dcore\Modules\Cli\Tasks\SwapTask;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;

class SwapController extends ApiControllerBase
{
    public function initialize($param = null)
    {
        parent::initialize();
    }

    public function getRateAction()
    {
        try {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            $dataGet = $this->getData;
            $sellAmount = $dataGet['sell_amount'] ?? 1;
            $buyAmount = $dataGet['buy_amount'] ?? 1;
            $sellToken = strtolower(trim($dataGet['sell_token'] ?? ''));
            $buyToken = strtolower(trim($dataGet['buy_token'] ?? ''));
            $slippage = floatval(trim($dataGet['slippage']));

            if (!strlen($sellToken)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid sell token');
            }

            if (!$coinInstance->validAddress($sellToken) && $sellToken != BinanceWeb3::MAIN_CURRENCY) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid sell token');
            }

            if (!strlen($buyToken)) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid buy token');
            }

            if (!$coinInstance->validAddress($buyToken) && $buyToken != BinanceWeb3::MAIN_CURRENCY) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid buy token');
            }

            if ($coinInstance->validAddress($sellToken)) {
                $sellToken = $coinInstance->toCheckSumAddress($sellToken);
            }

            if ($coinInstance->validAddress($buyToken)) {
                $buyToken = $coinInstance->toCheckSumAddress($buyToken);
            }

            $inputType = 'sell';
            if (isset($sellAmount) && $sellAmount > 0) {
                $inputType = 'sell';
                unset($buyAmount);
            }
            if (isset($buyAmount) && $buyAmount > 0) {
                $inputType = 'buy';
                unset($sellAmount);
            }
            if ($slippage > 0) {
                $slippage = $slippage / 100;
            } else {
                unset($slippage);
            }

            $swap = new Swap();
            $data = [
                'sellToken' => $sellToken,
                'buyToken' => $buyToken,
                'sellAmount' => $sellAmount ?? null,
                'buyAmount' => $buyAmount ?? null,
                'slippagePercentage' => $slippage ?? null
            ];
            $dataQuote = $swap->getQuote($data);
            $totalOutput = 0;
            $totalAdjustedOutput = 0;
            $listOrders = $dataQuote['orders'];
            if (count($listOrders)) {
                foreach ($listOrders as $order) {
                    $fillData = $order['fill'];
                    $totalOutput = BigDecimal::of($totalOutput)->plus(BigDecimal::of($fillData['output']));
                    $totalAdjustedOutput = BigDecimal::of($totalAdjustedOutput)->plus(BigDecimal::of($fillData['adjustedOutput']));
                }
            }

            $dataResponse['input'] = $inputType == 'sell' ? $sellAmount : $buyAmount;
            $dataResponse['output'] = strval($totalOutput);
            $dataResponse['adjusted_output'] = strval($totalAdjustedOutput);
            $dataResponse['to_address'] = $coinInstance->toCheckSumAddress($dataQuote['to']);
            $dataResponse['spender_address'] = $coinInstance->toCheckSumAddress($dataQuote['allowanceTarget']);
            $dataResponse['data'] = $dataQuote['data'];
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $dataResponse, 'Success');

        } catch (Exception $exception) {
            Log::createLog('Swap: ' . $exception->getMessage());
            $dataResponse = ['error' => 'Cannot get rate'];
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, $dataResponse, 'Error');
        }
    }
    
    public function extendedAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $folder = BASE_PATH . DIRECTORY_SEPARATOR . "swap_coin" . DIRECTORY_SEPARATOR . "extended.json";
            $data = file_get_contents($folder);
            if (!strlen($data)) {
                $swapTask = new SwapTask();
                $swapTask->minuteAction();
            }
            $data = file_get_contents($folder);
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     */
    public function cmcAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $folder = BASE_PATH . DIRECTORY_SEPARATOR . "swap_coin" . DIRECTORY_SEPARATOR . "cmc.json";
            $data = file_get_contents($folder);
            if (!strlen($data)) {
                $swapTask = new SwapTask();
                $swapTask->minuteAction();
            }
            $data = file_get_contents($folder);
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    /**
     */
    public function coingeckoAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $folder = BASE_PATH . DIRECTORY_SEPARATOR . "swap_coin" . DIRECTORY_SEPARATOR . "coingecko.json";
            $data = file_get_contents($folder);
            if (!strlen($data)) {
                $swapTask = new SwapTask();
                $swapTask->minuteAction();
            }
            $data = file_get_contents($folder);
            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $data, 'Success');
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}