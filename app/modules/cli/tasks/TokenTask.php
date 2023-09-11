<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Collections\BaseCollection;
use Dcore\Library\ContractLibrary;
use Dcore\Services\TokenService;
use DCrypto\Adapter;
use DCrypto\Object\Account;
use DCrypto\Object\Send;
use Exception;
use Httpful\Exception\ConnectionErrorException;

class TokenTask extends Web3Task
{
    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function minuteAction()
    {
        echo "Update unlock time" . PHP_EOL;
        $this->updateUnlockTimeAction();
        echo "Update token info" . PHP_EOL;
        $this->updateInfoAction();
        echo "Airdrop token" . PHP_EOL;
        $this->airdropAction();
    }

    /**
     * @throws Exception
     */
    public function updateUnlockTimeAction()
    {

        $listData = $this->mongo->selectCollection('tokens')->find([
            'status' => ContractLibrary::ACTIVE,
            '$or' => [
                ['unlock_time' => ['$lte' => time()]],
                ['unlock_time' => ['$exists' => false]],
            ],
        ]);
        $listData = !empty($listData) ? $listData->toArray() : [];

        if (count($listData)) {
            foreach ($listData as $tokenItem) {
                $network = $tokenItem['network'];
                $platform = $tokenItem['platform'];
                $tokenService = TokenService::getInstance($network, $platform);
                $tokenService->updateUnlockTime($tokenItem);
            }
        }
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function updateInfoAction()
    {

        $tokenCollection = $this->mongo->selectCollection('tokens');
        $listData = $tokenCollection->find([
            'status' => ContractLibrary::ACTIVE
        ]);
        if (!empty($listData)) {
            $listData = $listData->toArray();
        }

        if (count($listData)) {
            foreach ($listData as $tokenItem) {
                $network = $tokenItem['network'];
                $platform = $tokenItem['platform'];
                $tokenService = TokenService::getInstance($network, $platform);
                $tokenService->updateInfo($tokenItem);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function airdropAction()
    {
        $rand = rand(2, 3);
        $second = intval(date('s'));
        if ($second > $rand) {
            return;
        }

        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance('push_bsc', $network);
        $privateKey = $_ENV['WITHDRAW_PRIVATE_KEY'];
        $fromAddress = $_ENV['WITHDRAW_ADDRESS'];

        $airdropAddressCollection = $this->mongo->selectCollection('airdrop_address');
        $conditions = [
            'status' => BaseCollection::STATUS_PENDING,
        ];
        $airdrop = $airdropAddressCollection->findOne($conditions);
        if ($airdrop) {
            $fromAccount = new Account();
            $fromAccount->address = $fromAddress;
            $fromAccount->private_key = $privateKey;
            $toAccount = new Account();
            $toAccount->address = $airdrop['address'];
            $sendObject = new Send();
            $sendObject->with_nonce = true;
            $amount = $airdrop['amount'];
            $sendObject->amount = $amount + (rand(1000, 1000000) / 1000000);

            $sendObject = $coinInstance->send($fromAccount, $toAccount, $sendObject);
            $hash = $sendObject->hash;
            if (strlen($hash)) {
                $dataUpdate = [
                    'status' => BaseCollection::STATUS_APPROVE,
                    'process_at' => time(),
                    'hash' => $hash,
                    'message' => 'Success'
                ];
                $airdropAddressCollection->updateOne(['_id' => $airdrop['_id']], ['$set' => $dataUpdate]);
                echo "Success: " . $hash;
            } else {
                echo "Fail";
            }
        }

    }
}
