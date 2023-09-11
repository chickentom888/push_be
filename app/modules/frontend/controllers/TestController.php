<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\ControllerBase\ControllerBase;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;


class TestController extends ControllerBase
{
    public function indexAction()
    {
    }

    /**
     * @throws Exception
     */
    public function addressAction()
    {
        $listData = [];
        for ($i = 1; $i <= 50; $i++) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            $account = $coinInstance->createAccount();
            $listData[] = [
                'address' => $account->address,
                'private_key' => $account->private_key,
                'status' => BaseCollection::STATUS_INACTIVE,
                'amount' => rand(120, 160) + rand(1, 100) / 100
            ];
        }
        $this->mongo->selectCollection('airdrop_address')->insertMany($listData);
        Helper::debug($listData);
    }
}