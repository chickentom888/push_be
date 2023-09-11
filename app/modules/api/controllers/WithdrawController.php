<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Collections\Users;
use Dcore\Library\Arrays;
use Dcore\Library\Helper;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;

class WithdrawController extends ApiControllerBase
{
    public function initialize($param = null)
    {
        parent::initialize();
    }

    /**
     * @throws ConnectionErrorException
     */
    public function indexAction()
    {
        try {
            if (!$this->request->isGet()) {
                return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Unsupported method');
            }
            $withdrawCollection = $this->mongo->selectCollection('withdraw');
            $dataGet = $this->getData;
            $limit = isset($dataGet['limit']) ? intval($dataGet['limit']) : 20;
            $userConnectId = new ObjectId($this->credential->_id);

            $conditions = [
                'user_connect_id' => $userConnectId,
            ];
            if (strlen($dataGet['status'])) {
                $conditions['status'] = intval($dataGet['status']);
            }

            $p = $dataGet['p'];
            if ($p <= 1) $p = 1;
            $cp = ($p - 1) * $limit;
            $options = [
                'skip' => $cp,
                'limit' => $limit,
                'sort' => ['_id' => -1]
            ];
            $listData = $withdrawCollection->find($conditions, $options);

            $listDataResponse = [];
            foreach ($listData as $item) {
                Arrays::unsetMulti($item, ['fee_percent', 'fee_amount', 'amount_after_fee', 'rate', 'blockchain_status'], false);
                $listDataResponse[] = $item;
            }
            $count = $withdrawCollection->countDocuments($conditions);
            $pagingInfo = Helper::paginginfo($count, $limit, $p);

            // <editor-fold desc="Summary">
            $conditionsSummary = [
                [
                    '$match' => $conditions
                ],
                [
                    '$group' => [
                        '_id' => null,
                        "amount" => [
                            '$sum' => '$amount',
                        ],
                    ],
                ],
                [
                    '$project' => [
                        "_id" => 1,
                        "amount" => 1,
                    ],
                ],
            ];
            $summaryData = $withdrawCollection->aggregate($conditionsSummary);
            !empty($summaryData) && $summaryData = $summaryData->toArray();
            $summaryData = $summaryData[0];
            unset($summaryData['_id']);
            // </editor-fold>
            $optional = [
                'paging_info' => $pagingInfo,
                'summary' => $summaryData
            ];

            return $this->setDataJson(BaseCollection::STATUS_ACTIVE, $listDataResponse, 'Success', $optional);
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }

    public function createAction()
    {
        try {
            if ($this->request->isPost()) {
                $userConnect = $this->getUserInfo();
                $userAddress = $userConnect['address'];
                if ($this->isLimitRequest($userAddress)) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Slow down');
                }

                global $config;
                $registryCollection = $this->mongo->selectCollection('registry');
                $withdrawCollection = $this->mongo->selectCollection('withdraw');
                $registry = $registryCollection->findOne();
                $rate = $registry['coin_rate'];
                $dataPost = $this->jsonData;

                // <editor-fold desc="Validate Amount">
                $amount = Helper::parseNumber($dataPost['amount']);
                $amount = str_replace(",", ".", $amount);
                if ($amount <= 0) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Invalid Amount');
                }
                $amount = doubleval($amount);
                $minAmount = $registry['min_withdraw'] ?: 0;
                if ($amount < $minAmount) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Your amount must be greater than ' . $minAmount);
                }
                // </editor-fold>

                // <editor-fold desc="Fee">
                $feePercent = $registry['fee_withdraw'] ?: 0;
                $feeAmount = $amount / 100 * $feePercent;
                if ($amount - $feeAmount <= 0) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Amount is not enough to pay fee!');
                }
                $amountAfterFee = doubleval($amount - $feeAmount);
                // </editor-fold>

                // <editor-fold desc="Check Balance">
                $balance = $userConnect['coin_balance'];
                if ($balance < $amountAfterFee || $balance - $amountAfterFee < 0) {
                    return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Your balance is not enough');
                }
                // </editor-fold>

                $wallet = BaseCollection::WALLET_COIN;
                $message = 'Withdraw';
                Users::updateBalance($userConnect['_id'], $wallet, 0 - $amount, BaseCollection::TYPE_WITHDRAW, $message);

                $withdrawData = [
                    'user_address' => $userConnect['address'],
                    'user_connect_id' => $userConnect['_id'],
                    'wallet' => $wallet,
                    'ticker' => $config->site->coin_ticker,
                    'amount' => $amount,
                    'created_at' => time(),
                    'to_address' => $userAddress,
                    'fee_percent' => $feePercent,
                    'fee_amount' => $feeAmount,
                    'amount_after_fee' => $amountAfterFee,
                    'rate' => $rate,
                    'status' => BaseCollection::STATUS_PENDING,
                    'blockchain_status' => BaseCollection::STATUS_PENDING
                ];
                $withdrawCollection->insertOne($withdrawData);

                $autoWithdrawAmount = doubleval($registry['auto_withdraw_amount']);

                // <editor-fold desc = "Send Message Telegram">
                $message = 'New withdrawal' . PHP_EOL;
                $message .= "User id: {$userConnect['_id']}" . PHP_EOL;
                $message .= "Address: {$userConnect['address']}" . PHP_EOL;
                $message .= "Amount: " . Helper::numberFormat($amount) . PHP_EOL;
                $message .= "Prev balance: " . Helper::numberFormat($balance, 2) . PHP_EOL;
                if ($autoWithdrawAmount <= 0) {
                    $message .= "Auto amount not set" . PHP_EOL;
                    $message .= "Approved at: " . date('d/m/Y H:i:s') . PHP_EOL;
                } else {
                    $message .= "Auto amount: " . Helper::numberFormat($autoWithdrawAmount) . PHP_EOL;
                    if ($autoWithdrawAmount > $amount) {
                        $message .= "Approved at: " . date('d/m/Y H:i:s', strtotime('+2 minutes')) . PHP_EOL;
                    } else {
                        $message .= "Need checked" . PHP_EOL;
                    }
                }
                Helper::sendTelegramMsg($message);
                // </editor-fold>

                return $this->setDataJson(BaseCollection::STATUS_ACTIVE, null, 'Success');

            }
        } catch (Exception $exception) {
            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, 'Something went wrong');
//            return $this->setDataJson(BaseCollection::STATUS_INACTIVE, null, $exception->getMessage());
        }
    }
}
