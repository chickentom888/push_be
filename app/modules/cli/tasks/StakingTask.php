<?php

namespace Dcore\Modules\Cli\Tasks;

use Brick\Math\BigDecimal;
use Dcore\Collections\BaseCollection;
use Dcore\Collections\Transaction;
use Dcore\Collections\Users;
use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Object\Account;
use DCrypto\Object\Send;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use MongoDB\BSON\ObjectId;
use RedisException;
use Web3\Contract;

class StakingTask extends TaskBase
{

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    /**
     * @throws RedisException
     * @throws ConnectionErrorException
     * @throws Exception
     */
    public function minuteAction()
    {
        echo "Delete Error Log" . PHP_EOL;
        $this->deleteErrorLogAction();
        echo "Support Liquid" . PHP_EOL;
        $this->supportLiquidAction();
        echo "Interest" . PHP_EOL;
        $this->interestAction();
        echo "Principal" . PHP_EOL;
        $this->principalAction();
        echo "Fund Interest" . PHP_EOL;
        $this->fundInterestAction();
        echo "Direct Bonus" . PHP_EOL;
        $this->directBonusAction();
        echo "Team Bonus" . PHP_EOL;
        $this->teamBonusAction();
        echo "Update Coin Rate" . PHP_EOL;
        $this->updateCoinRateAction();
//        echo "Calculate Tree" . PHP_EOL;
//        $this->calculateTreeAction();
        echo "Get Transaction Staking From BSC SCAN" . PHP_EOL;
        $this->getTransactionStakingAction();
        echo "=====" . PHP_EOL;
    }

    public function interestAction()
    {
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $userPackageInterestCollection = $this->mongo->selectCollection('user_package_interest');
        $now = time();
        $listData = $userPackageCollection->find([
            'next_interest_at' => ['$lte' => $now],
            '$where' => 'this.interest_paid_day < this.interest_max_day'
        ]);
        !empty($listData) && $listData = $listData->toArray();

        foreach ($listData as $item) {
            $userConnectId = $item['user_connect_id'];
            $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
            if (!$userConnect) {
                continue;
            }
            $interestPaidDay = $item['interest_paid_day'] + 1;
            $fromTokenAmount = 0;
            $listTokenChange = $item['token_change'] ?? [];
            foreach ($listTokenChange as $timestamp => $tokenAmount) {
                if ($item['next_interest_at'] >= $timestamp) {
                    $fromTokenAmount = $tokenAmount;
                }
            }
            $fromTokenAmount == 0 && $fromTokenAmount = $item['token_amount'];
            $bonusPercentMonth = Helper::getStakingInterestPercent($fromTokenAmount);
            $bonusPercent = $bonusPercentMonth / 30;
            $baseAmount = $fromTokenAmount * $bonusPercent / 100;
            $bonusAmount = $baseAmount / 2;

            // <editor-fold desc="Process User Staking Interest">
            $userPackageInterestData = [
                'user_package_id' => $item['_id'],
                'user_connect_id' => $userConnect['_id'],
                'user_address' => $userConnect['address'],
                'interest_day' => $interestPaidDay,
                'type' => BaseCollection::TYPE_STAKING_INTEREST,
                'base_amount' => $baseAmount,
                'bonus_amount' => $bonusAmount,
                'real_amount' => $bonusAmount,
                'user_package_amount' => $fromTokenAmount,
                'bonus_percent' => $bonusPercent,
                'created_at' => time(),
            ];
            $userPackageInterest = $userPackageInterestCollection->findOne([
                'user_package_id' => $item['_id'],
                'user_connect_id' => $userConnect['_id'],
                'interest_day' => $interestPaidDay,
                'type' => BaseCollection::TYPE_STAKING_INTEREST,
            ]);
            if (!$userPackageInterest) {
                $userPackageInterestCollection->insertOne($userPackageInterestData);
            }
            // </editor-fold>

            // <editor-fold desc="Update User Package Info">
            $dataUpdate = [
                'interest_paid_day' => $interestPaidDay,
                'total_fund_interest_amount' => $item['total_fund_interest_amount'] + $bonusAmount,
                'interest_amount_paid' => $item['interest_amount_paid'] + $bonusAmount,
                'last_interest_at' => time(),
                'next_interest_at' => strtotime("+1 days", $item['next_interest_at'])
            ];
            $dataUpdate['fund_interest_amount_pending'] = $dataUpdate['total_fund_interest_amount'] - $item['fund_interest_amount_paid'];
            if ($item['interest_max_day'] == $interestPaidDay) {
                $dataUpdate['status'] = BaseCollection::STATUS_INACTIVE;
            }
            $userPackageCollection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
            // </editor-fold>

            $message = 'Staking interest';
            Users::updateBalance($userConnectId, BaseCollection::WALLET_COIN, $bonusAmount, BaseCollection::TYPE_STAKING_INTEREST, $message);
            Users::updateBalance($userConnectId, BaseCollection::WALLET_INTEREST, $bonusAmount, BaseCollection::TYPE_STAKING_INTEREST, $message);

        }
    }

    public function principalAction()
    {
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $userPackagePrincipalCollection = $this->mongo->selectCollection('user_package_principal');
        $now = time();
        $listData = $userPackageCollection->find([
            'status' => BaseCollection::STATUS_INACTIVE,
            'next_principal_at' => ['$gte' => $now],
            '$where' => 'this.principal_paid_day < this.principal_max_day'
        ]);
        !empty($listData) && $listData = $listData->toArray();

        foreach ($listData as $item) {
            $userConnectId = $item['user_connect_id'];
            $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
            if (!$userConnect) {
                continue;
            }
            $principalPaidDay = $item['principal_paid_day'] + 1;
            $principalMaxDay = $item['principal_max_day'];
            $principalAmount = $item['token_amount'] / $principalMaxDay;
            if ($principalPaidDay == $principalMaxDay) {
                $principalAmount = $item['token_amount'] - $item['principal_amount_paid'];
            }

            // <editor-fold desc="Process User Staking Principal">
            $userPackagePrincipalData = [
                'user_package_id' => $item['_id'],
                'user_connect_id' => $userConnect['_id'],
                'user_connect_address' => $userConnect['address'],
                'principal_day' => $principalPaidDay,
                'user_address' => $userConnect['address'],
                'principal_amount' => $principalAmount,
                'user_package_amount' => $item['token_amount'],
                'created_at' => time(),
            ];
            $userPackagePrincipal = $userPackagePrincipalCollection->findOne([
                'user_package_id' => $item['_id'],
                'user_connect_id' => $userConnect['_id'],
                'principal_day' => $principalPaidDay,
            ]);
            if (!$userPackagePrincipal) {
                $userPackagePrincipalCollection->insertOne($userPackagePrincipalData);
            }
            // </editor-fold>

            // <editor-fold desc="Update User Package Info">
            $dataUpdate = [
                'principal_paid_day' => $principalPaidDay,
                'principal_amount_paid' => $item['principal_amount_paid'] + $principalAmount,
                'last_principal_at' => time(),
                'next_principal_at' => strtotime("+1 days", $item['next_principal_at'])
            ];
            $userPackageCollection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
            // </editor-fold>

            $message = 'Staking principal';
            Users::updateBalance($userConnectId, BaseCollection::WALLET_COIN, $principalAmount, BaseCollection::TYPE_STAKING_PRINCIPAL, $message);

        }
    }

    public function fundInterestAction()
    {
        $userPackageCollection = $this->mongo->selectCollection('user_package');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $userPackageInterestCollection = $this->mongo->selectCollection('user_package_interest');
        $now = time();
        $listData = $userPackageCollection->find([
            'status' => BaseCollection::STATUS_INACTIVE,
            'next_fund_interest_at' => ['$gte' => $now],
            '$where' => 'this.fund_interest_paid_times < this.fund_interest_max_times'
        ]);
        !empty($listData) && $listData = $listData->toArray();
        foreach ($listData as $item) {
            $userConnectId = $item['user_connect_id'];
            $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
            if (!$userConnect) {
                continue;
            }
            $fundInterestPaidTimes = $item['fund_interest_paid_times'] + 1;
            $fundInterestMaxTimes = $item['fund_interest_max_times'];
            $totalFundInterestAmount = $item['total_fund_interest_amount'];
            $fundInterestAmount = $totalFundInterestAmount / $fundInterestMaxTimes;
            if ($fundInterestPaidTimes == $fundInterestMaxTimes) {
                $fundInterestAmount = $totalFundInterestAmount - $item['fund_interest_amount_paid'];
            }

            // <editor-fold desc="Process User Staking Interest">
            $userPackageInterestData = [
                'user_package_id' => $item['_id'],
                'user_connect_id' => $userConnect['_id'],
                'user_address' => $userConnect['address'],
                'fund_interest_times' => $fundInterestPaidTimes,
                'type' => BaseCollection::TYPE_STAKING_FUND_INTEREST,
                'bonus_amount' => $fundInterestAmount,
                'real_amount' => $fundInterestAmount,
                'user_package_amount' => $item['token_amount'],
                'created_at' => time()
            ];
            $userPackageInterest = $userPackageInterestCollection->findOne([
                'user_package_id' => $item['_id'],
                'user_connect_id' => $userConnect['_id'],
                'fund_interest_times' => $fundInterestPaidTimes,
                'type' => BaseCollection::TYPE_STAKING_FUND_INTEREST,
            ]);
            if (!$userPackageInterest) {
                $userPackageInterestCollection->insertOne($userPackageInterestData);
            }
            // </editor-fold>

            // <editor-fold desc="Update User Package Info">
            $dataUpdate = [
                'fund_interest_paid_times' => $fundInterestPaidTimes,
                'fund_interest_amount_paid' => $item['fund_interest_amount_paid'] + $fundInterestAmount,
                'last_fund_interest_at' => time(),
                'next_fund_interest_at' => strtotime("+1 months", $item['next_fund_interest_at'])
            ];
            $dataUpdate['fund_interest_amount_pending'] = $item['total_fund_interest_amount'] - $dataUpdate['fund_interest_amount_paid'];
            $userPackageCollection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
            // </editor-fold>

            $message = 'Staking interest';
            Users::updateBalance($userConnectId, BaseCollection::WALLET_COIN, $fundInterestAmount, BaseCollection::TYPE_STAKING_FUND_INTEREST, $message, false);
            Users::updateBalance($userConnectId, BaseCollection::WALLET_INTEREST, 0 - $fundInterestAmount, BaseCollection::TYPE_STAKING_FUND_INTEREST, $message, false);
        }
    }

    /**
     * @throws Exception
     * @var BinanceWeb3 $coinInstance
     */
    public function directBonusAction()
    {
        $userPackageHistoryCollection = $this->mongo->selectCollection('user_package_history');
        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $bonusLogCollection = $this->mongo->selectCollection('bonus_log');
        $errorLogCollection = $this->mongo->selectCollection('error_log');
        $userPackage = $userPackageHistoryCollection->findOne([
            'is_direct_bonus' => BaseCollection::STATUS_INACTIVE
        ]);
        if (!$userPackage) {
            return;
        }

        $bonusPercent = 8;
        $directBonusMessage = '';
        $userConnectId = $userPackage['user_connect_id'];
        $userConnect = $userConnectCollection->findOne(['_id' => $userConnectId]);
        if ($userConnect) {
            if (!empty($userConnect['inviter_id'])) {
                $inviterId = $userConnect['inviter_id'];
                $inviter = $userConnectCollection->findOne(['_id' => $inviterId]);
                if ($inviter) {
                    $baseAmount = $userPackage['token_amount'] / 100 * $bonusPercent;
                    $bonusAmount = Users::getAvailableAmountBonus($baseAmount, $inviterId);
                    if ($bonusAmount > 0) {
                        $message = 'Direct Bonus';
                        // <editor-fold desc="Process Bonus Log">
                        $bonusLogData = [
                            'from_user_connect_id' => $userConnectId,
                            'to_user_connect_id' => $inviterId,
                            'from_user_address' => $userConnect['address'],
                            'to_user_address' => $inviter['address'],
                            'type' => BaseCollection::TYPE_DIRECT_BONUS,
                            'contract_id' => $userPackage['contract_id'],
                            'user_package_history_id' => $userPackage['_id'],
                            'user_package_id' => $userPackage['user_package_id'],
                            'from_amount' => $userPackage['token_amount'],
                            'base_amount' => $baseAmount,
                            'bonus_amount' => $bonusAmount,
                            'bonus_percent' => $bonusPercent,
                            'message' => $message,
                            'created_at' => time()
                        ];
                        $bonusLog = $bonusLogCollection->findOne([
                            'from_user_connect_id' => $userConnectId,
                            'to_user_connect_id' => $inviterId,
                            'type' => BaseCollection::TYPE_DIRECT_BONUS,
                            'contract_id' => $userPackage['contract_id'],
                            'user_package_history_id' => $userPackage['_id'],
                        ]);
                        if (!$bonusLog) {
                            $bonusLogCollection->insertOne($bonusLogData);
                        }
                        // </editor-fold>
                        Users::updateBalance($inviter['_id'], BaseCollection::WALLET_COIN, $bonusAmount, BaseCollection::TYPE_DIRECT_BONUS, $message, true);
                        $directBonusMessage = 'Paid success';
                    } else {
                        // <editor-fold desc="Process Error Log">
                        $errorLogData = [
                            'from_user_connect_id' => $userConnectId,
                            'to_user_connect_id' => $inviterId,
                            'from_user_address' => $userConnect['address'],
                            'to_user_address' => $inviter['address'],
                            'type' => BaseCollection::TYPE_DIRECT_BONUS,
                            'contract_id' => $userPackage['contract_id'],
                            'user_package_history_id' => $userPackage['_id'],
                            'user_package_id' => $userPackage['user_package_id'],
                            'from_amount' => $userPackage['token_amount'],
                            'message' => 'Reach max out',
                            'created_at' => time(),
                        ];
                        $errorLog = $errorLogCollection->findOne([
                            'from_user_connect_id' => $userConnectId,
                            'to_user_connect_id' => $inviterId,
                            'type' => BaseCollection::TYPE_DIRECT_BONUS,
                            'contract_id' => $userPackage['contract_id'],
                            'user_package_history_id' => $userPackage['_id'],
                        ]);
                        if (!$errorLog) {
                            $errorLogCollection->insertOne($errorLogData);
                        }
                        // </editor-fold>
                        $directBonusMessage = 'Reach max out';
                    }
                } else {
                    // <editor-fold desc="Process Error Log">
                    $errorLogData = [
                        'from_user_connect_id' => $userConnectId,
                        'from_user_address' => $userConnect['address'],
                        'type' => BaseCollection::TYPE_DIRECT_BONUS,
                        'contract_id' => $userPackage['contract_id'],
                        'user_package_history_id' => $userPackage['_id'],
                        'user_package_id' => $userPackage['user_package_id'],
                        'from_amount' => $userPackage['token_amount'],
                        'message' => 'Inviter not found',
                        'created_at' => time(),
                    ];
                    $errorLog = $errorLogCollection->findOne([
                        'from_user_connect_id' => $userConnectId,
                        'type' => BaseCollection::TYPE_DIRECT_BONUS,
                        'contract_id' => $userPackage['contract_id'],
                        'user_package_history_id' => $userPackage['_id'],
                    ]);
                    if (!$errorLog) {
                        $errorLogCollection->insertOne($errorLogData);
                    }
                    // </editor-fold>
                    $directBonusMessage = 'Not found inviter';
                }
            } else {
                // <editor-fold desc="Process Error Log">
                $errorLogData = [
                    'from_user_connect_id' => $userConnectId,
                    'from_user_address' => $userConnect['address'],
                    'type' => BaseCollection::TYPE_DIRECT_BONUS,
                    'contract_id' => $userPackage['contract_id'],
                    'user_package_history_id' => $userPackage['_id'],
                    'user_package_id' => $userPackage['user_package_id'],
                    'from_amount' => $userPackage['token_amount'],
                    'message' => 'No inviter',
                    'created_at' => time(),
                ];
                $errorLog = $errorLogCollection->findOne([
                    'from_user_connect_id' => $userConnectId,
                    'type' => BaseCollection::TYPE_DIRECT_BONUS,
                    'contract_id' => $userPackage['contract_id'],
                    'user_package_history_id' => $userPackage['_id'],
                ]);
                if (!$errorLog) {
                    $errorLogCollection->insertOne($errorLogData);
                }
                // </editor-fold>
                $directBonusMessage = 'No inviter';
            }
        }

        $dataUpdate = [
            'is_direct_bonus' => BaseCollection::STATUS_ACTIVE,
            'direct_bonus_at' => time(),
            'direct_bonus_message' => $directBonusMessage,
        ];
        $userPackageHistoryCollection->updateOne(['_id' => $userPackage['_id']], ['$set' => $dataUpdate]);
    }

    /**
     * Command: * * * * *
     * @throws RedisException
     */
    public function teamBonusAction()
    {
        $userPackageHistoryCollection = $this->mongo->selectCollection('user_package_history');
        $options = [
            'sort' => ['created_at' => 1]
        ];
        $userPackageHistory = $userPackageHistoryCollection->findOne([
            'is_team_bonus' => BaseCollection::STATUS_INACTIVE
        ], $options);
        if (!$userPackageHistory) {
            return;
        }

        $userConnectCollection = $this->mongo->selectCollection('user_connect');
        $bonusLogCollection = $this->mongo->selectCollection('bonus_log');
        $errorLogCollection = $this->mongo->selectCollection('error_log');

        $listUser = $userConnectCollection->find();
        !empty($listUser) && $listUser = $listUser->toArray();

        // <editor-fold desc = "Get user package history">
        $listInvest = $userPackageHistoryCollection->find([
            'created_at' => ['$lte' => $userPackageHistory['created_at']]
        ]);
        !empty($listInvest) && $listInvest = $listInvest->toArray();
        $listUserInvestAmount = [];
        $listUserInvestUsd = [];
        foreach ($listInvest as $item) {
            $userId = strval($item['user_connect_id']);
            !isset($listUserInvestAmount[$userId]) && $listUserInvestAmount[$userId] = 0;
            !isset($listUserInvestUsd[$userId]) && $listUserInvestUsd[$userId] = 0;

            $listUserInvestAmount[$userId] += doubleval($item['token_amount']);
            $listUserInvestUsd[$userId] += doubleval($item['usd_amount']);
        }
        // </editor-fold>

        $fromUser = $userConnectCollection->findOne(['_id' => $userPackageHistory['user_connect_id']]);

        // <editor-fold desc = "Init branch amount, khởi tạo giá trị nhánh cho hệ thống">
        $listUserBranch = [];
        foreach ($listUser as $user) {
            $userId = strval($user['_id']);
            $investAmount = $listUserInvestAmount[$userId] ?? 0;
            $investAmountUsd = $listUserInvestUsd[$userId] ?? 0;
            $parent = Users::getParent($userId);
            $lastBranch = $user['branch'];
            while ($parent) {
                $parentId = strval($parent['_id']);
                if (!isset($listUserBranch[$parentId])) {
                    $listUserBranch[$parentId] = [
                        'left_invest' => 0,
                        'right_invest' => 0,
                        'count_left' => 0,
                        'count_right' => 0,
                        'count_left_f1' => 0,
                        'count_right_f1' => 0,
                        'left_f1_id' => [],
                        'right_f1_id' => [],
                        'direct_system_id' => [],
                        'tree_system_id' => [],
                        'count_left_f1_invest' => 0,
                        'count_right_f1_invest' => 0,
                        'left_f1_invest_id' => [],
                        'right_f1_invest_id' => [],
                        'count_f1_invest' => 0,
                        'count_system_invest' => 0,
                        'direct_system_invest' => 0,
                        'direct_system_invest_usd' => 0,
                    ];
                }

                $listUserBranch[$parentId]['tree_system_id'][] = $userId;

                if ($lastBranch == BaseCollection::BRANCH_LEFT) {
                    $listUserBranch[$parentId]['left_invest'] += $investAmount;
                    $listUserBranch[$parentId]['count_left'] += 1;
                } elseif ($lastBranch == BaseCollection::BRANCH_RIGHT) {
                    $listUserBranch[$parentId]['right_invest'] += $investAmount;
                    $listUserBranch[$parentId]['count_right'] += 1;
                }

                // Nếu Inviter ID == Parent ID thì user là F1
                if ($user['inviter_id'] == $parent['_id']) {

                    $inviterId = strval($user['inviter_id']);
                    if ($lastBranch == BaseCollection::BRANCH_LEFT) {
                        $listUserBranch[$parentId]['count_left_f1'] += 1;
                        $listUserBranch[$parentId]['left_f1_id'][] = $userId;
                        if ($investAmount > 0) {
                            $listUserBranch[$parentId]['count_left_f1_invest'] += 1;
                            $listUserBranch[$parentId]['left_f1_invest_id'][] = new ObjectId($userId);
                        }
                    } else {
                        $listUserBranch[$parentId]['count_right_f1'] += 1;
                        $listUserBranch[$parentId]['right_f1_id'][] = $userId;
                        if ($investAmount > 0) {
                            $listUserBranch[$parentId]['count_right_f1_invest'] += 1;
                            $listUserBranch[$parentId]['right_f1_invest_id'][] = new ObjectId($userId);
                        }
                    }

                    if ($investAmount >= 0) {
                        $listUserBranch[$parentId]['count_f1_invest'] += 1;
                    }

                    $listUserBranch[$inviterId]['direct_system_id'][] = $userId;
                }

                $lastBranch = $parent['branch'];
                $parent = Users::getParent($parentId);
                unset($inviterId);
            }

            $inviter = Users::getInviter($userId);
            while ($inviter) {
                $inviterId = strval($inviter['_id']);
                if ($investAmount > 0) {
                    $listUserBranch[$inviterId]['count_system_invest'] += 1;
                    $listUserBranch[$inviterId]['direct_system_invest'] += $investAmount;
                    $listUserBranch[$inviterId]['direct_system_invest_usd'] += $investAmountUsd;
                    $inviter = Users::getInviter($inviterId);
                } else {
                    $inviter = null;
                }
            }
        }
        // </editor-fold>

        // <editor-fold desc = "Team Bonus">
        foreach ($listUser as $user) {
            $userId = strval($user['_id']);
            $investAmount = $listUserInvestAmount[$userId] ?? 0;
            $userInfo = $listUserBranch[$userId] ?? [];
            $leftInvest = doubleval($userInfo['left_invest']);
            $rightInvest = doubleval($userInfo['right_invest']);
            $userData = [];
            $userData['left_count'] = intval($userInfo['count_left']);
            $userData['right_count'] = intval($userInfo['count_right']);
            $userData['left_f1_count'] = intval($userInfo['count_left_f1']);
            $userData['right_f1_count'] = intval($userInfo['count_right_f1']);
            $userData['left_invest'] = $leftInvest;
            $userData['right_invest'] = $rightInvest;
            $userData['system_invest'] = $leftInvest + $rightInvest;
            $userData['personal_invest'] = $investAmount;
            $userData['count_f1_invest'] = intval($userInfo['count_f1_invest']);
            $userData['count_left_f1_invest'] = intval($userInfo['count_left_f1_invest']);
            $userData['count_right_f1_invest'] = intval($userInfo['count_right_f1_invest']);
            $userData['count_system_invest'] = intval($userInfo['count_system_invest']);
            $userData['direct_system_invest'] = $userInfo['direct_system_invest'];
            $userData['direct_system_invest_usd'] = $userInfo['direct_system_invest_usd'];

            $fromUserConnectId = strval($userPackageHistory['user_connect_id']);
            $listTreeSystemId = $userInfo['tree_system_id'] ?? [];
            $isInsertErrorLog = in_array($fromUserConnectId, $listTreeSystemId);

            // <editor-fold desc="Error log data & condition">
            $errorLogData = [
                'from_user_connect_id' => $userPackageHistory['user_connect_id'],
                'to_user_connect_id' => $user['_id'],
                'from_user_address' => $fromUser['address'],
                'to_user_address' => $user['address'],
                'type' => BaseCollection::TYPE_TEAM_BONUS,
                'contract_id' => $userPackageHistory['contract_id'],
                'user_package_history_id' => $userPackageHistory['_id'],
                'user_package_id' => $userPackageHistory['user_package_id'],
                'from_amount' => $userPackageHistory['token_amount'],
                'count_left_f1_invest' => $userData['count_left_f1_invest'],
                'count_right_f1_invest' => $userData['count_right_f1_invest'],
                'left_invest' => $leftInvest,
                'right_invest' => $rightInvest,
                'save_branch' => $user['save_branch'],
                'created_at' => time(),
            ];
            $errorLogCondition = [
                'from_user_connect_id' => $userPackageHistory['user_connect_id'],
                'to_user_connect_id' => $user['_id'],
                'from_user_address' => $fromUser['address'],
                'to_user_address' => $user['address'],
                'type' => BaseCollection::TYPE_TEAM_BONUS,
                'contract_id' => $userPackageHistory['contract_id'],
                'user_package_history_id' => $userPackageHistory['_id'],
            ];
            // </editor-fold>

            $conditionLeftF1Invest = $userData['count_left_f1_invest'] > 0;
            $conditionRightF1Invest = $userData['count_right_f1_invest'] > 0;
            $conditionTeamBonus = $conditionLeftF1Invest && $conditionRightF1Invest;

            $littleAmount = min($leftInvest, $rightInvest);
            $userData['level'] = $this->getLevel($littleAmount);
            $residualAmount = $littleAmount - $user['save_branch'];
            if ($conditionTeamBonus) {
                if ($residualAmount > 0) {
                    $teamBonusPercent = Helper::getTeamBonusPercent($littleAmount);
                    $baseAmount = round($residualAmount * $teamBonusPercent / 100, 2);
                    $bonusAmount = Users::getAvailableAmountBonus($baseAmount, $userId);
                    if ($bonusAmount > 0.001) {
                        $beforeSaveBranch = $user['save_branch'];
                        $userData['save_branch'] = $littleAmount;
                        $message = 'Team Bonus';

                        // <editor-fold desc="Process Bonus Log">
                        $bonusLogData = [
                            'from_user_connect_id' => $userPackageHistory['user_connect_id'],
                            'to_user_connect_id' => $user['_id'],
                            'from_user_address' => $fromUser['address'],
                            'to_user_address' => $user['address'],
                            'type' => BaseCollection::TYPE_TEAM_BONUS,
                            'contract_id' => $userPackageHistory['contract_id'],
                            'user_package_history_id' => $userPackageHistory['_id'],
                            'user_package_id' => $userPackageHistory['user_package_id'],
                            'from_amount' => $userPackageHistory['token_amount'],
                            'base_amount' => $baseAmount,
                            'bonus_amount' => $bonusAmount,
                            'before_save_branch' => $beforeSaveBranch,
                            'last_save_branch' => $userData['save_branch'],
                            'count_left_f1_invest' => $userData['count_left_f1_invest'],
                            'count_right_f1_invest' => $userData['count_right_f1_invest'],
                            'left_f1_invest_id' => $userData['left_f1_invest_id'],
                            'right_f1_invest_id' => $userData['right_f1_invest_id'],
                            'bonus_percent' => $teamBonusPercent,
                            'residual_amount' => $residualAmount,
                            'left_invest' => $leftInvest,
                            'right_invest' => $rightInvest,
                            'message' => $message,
                            'created_at' => time()
                        ];
                        $bonusLog = $bonusLogCollection->findOne([
                            'from_user_connect_id' => $userPackageHistory['user_connect_id'],
                            'to_user_connect_id' => $user['_id'],
                            'type' => BaseCollection::TYPE_TEAM_BONUS,
                            'contract_id' => $userPackageHistory['contract_id'],
                            'user_package_history_id' => $userPackageHistory['_id'],
                        ]);
                        if (!$bonusLog) {
                            $bonusLogCollection->insertOne($bonusLogData);
                        }
                        // </editor-fold>

                        Users::updateBalance($user['_id'], BaseCollection::WALLET_COIN, $bonusAmount, BaseCollection::TYPE_TEAM_BONUS, $message, true);
                        echo "User: " . $user['address'] . PHP_EOL . "Amount: " . $bonusAmount . PHP_EOL;
                        $this->processMatchingBonus($user, $userPackageHistory, $bonusAmount);
                    } else {
                        // <editor-fold desc="Process Error Log">
                        if ($isInsertErrorLog) {
                            $errorLogData['message'] = 'Reach max out';
                            $errorLog = $errorLogCollection->findOne($errorLogCondition);
                            if (!$errorLog) {
                                $errorLogCollection->insertOne($errorLogData);
                            }
                        }
                        // </editor-fold>
                    }
                } else {
                    // <editor-fold desc="Process Error Log">
                    if ($isInsertErrorLog) {
                        $errorLogData['message'] = 'Not change total invest in all branch';
                        $errorLog = $errorLogCollection->findOne($errorLogCondition);
                        if (!$errorLog) {
                            $errorLogCollection->insertOne($errorLogData);
                        }
                    }
                    // </editor-fold>
                }
            } else {
                // <editor-fold desc="Process Error Log">
                if ($isInsertErrorLog) {
                    $errorLogData['message'] = 'Not enough F1 invest in each branch';
                    $errorLog = $errorLogCollection->findOne($errorLogCondition);
                    if (!$errorLog) {
                        $errorLogCollection->insertOne($errorLogData);
                    }
                }
                // </editor-fold>
            }
            $userConnectCollection->updateOne(['_id' => $user['_id']], ['$set' => $userData]);
        }
        // </editor-fold>

        // <editor-fold desc="Update User Package History">
        $dataUpdate = [
            'is_team_bonus' => BaseCollection::STATUS_ACTIVE,
            'team_bonus_at' => time()
        ];
        $userPackageHistoryCollection->updateOne(
            ['_id' => $userPackageHistory['_id']],
            ['$set' => $dataUpdate]
        );
        // </editor-fold>

        foreach ($listUserBranch as $userId => $item) {
            $this->redis->set("system_id:direct_system_id_{$userId}", json_encode($item['direct_system_id']));
            $this->redis->set("system_id:tree_system_id_{$userId}", json_encode($item['tree_system_id']));
        }

    }

    protected function processMatchingBonus($userConnect, $userPackageHistory, $fromAmount)
    {
        $bonusLogCollection = $this->mongo->selectCollection('bonus_log');
        $errorLogCollection = $this->mongo->selectCollection('error_log');

        // <editor-fold desc="Error log data & condition">
        $errorLogData = [
            'from_user_connect_id' => $userConnect['_id'],
            'from_user_address' => $userConnect['address'],
            'type' => BaseCollection::TYPE_MATCHING_BONUS,
            'contract_id' => $userPackageHistory['contract_id'],
            'user_package_history_id' => $userPackageHistory['_id'],
            'user_package_id' => $userPackageHistory['user_package_id'],
            'from_amount' => $fromAmount,
            'created_at' => time(),
        ];
        $errorLogCondition = [
            'from_user_connect_id' => $userConnect['_id'],
            'from_user_address' => $userConnect['address'],
            'type' => BaseCollection::TYPE_MATCHING_BONUS,
            'contract_id' => $userPackageHistory['contract_id'],
            'user_package_history_id' => $userPackageHistory['_id'],
        ];
        // </editor-fold>

        $layer = 1;
        $maxLayer = 7;
        $inviter = Users::getInviter($userConnect['_id']);

        if ($inviter) {
            $minSaveBranch = 400000;
            while ($inviter != null && $layer <= $maxLayer) {
                $saveBranch = $inviter['save_branch'];
                $countLeftF1Invest = $inviter['count_left_f1_invest'];
                $countRightF1Invest = $inviter['count_right_f1_invest'];

                $errorLogData['to_user_connect_id'] = $inviter['_id'];
                $errorLogData['to_user_address'] = $inviter['address'];
                $errorLogData['save_branch'] = $saveBranch;
                $errorLogData['require_save_branch'] = $minSaveBranch;
                $errorLogData['count_left_f1_invest'] = $countLeftF1Invest;
                $errorLogData['count_right_f1_invest'] = $countRightF1Invest;
                $errorLogData['left_invest'] = $inviter['left_invest'];
                $errorLogData['right_invest'] = $inviter['right_invest'];
                $errorLogData['layer'] = $layer;

                $errorLogCondition['to_user_connect_id'] = $inviter['_id'];
                $errorLogCondition['to_user_address'] = $inviter['address'];

                if ($saveBranch >= $minSaveBranch) {
                    if ($countLeftF1Invest > 0 && $countRightF1Invest > 0) {
                        $bonusPercent = $this->getMatchingBonusPercent($layer, $saveBranch);
                        if ($bonusPercent > 0) {
                            $baseAmount = $fromAmount * $bonusPercent / 100;
                            $bonusAmount = Users::getAvailableAmountBonus($baseAmount, $inviter['_id']);
                            $message = "Matching Bonus";

                            // <editor-fold desc="Process Bonus Log">
                            $bonusLogData = [
                                'from_user_connect_id' => $userConnect['_id'],
                                'to_user_connect_id' => $inviter['_id'],
                                'from_user_address' => $userConnect['address'],
                                'to_user_address' => $inviter['address'],
                                'type' => BaseCollection::TYPE_MATCHING_BONUS,
                                'contract_id' => $userPackageHistory['contract_id'],
                                'user_package_history_id' => $userPackageHistory['_id'],
                                'user_package_id' => $userPackageHistory['user_package_id'],
                                'from_amount' => $fromAmount,
                                'base_amount' => $baseAmount,
                                'bonus_amount' => $bonusAmount,
                                'bonus_percent' => $bonusPercent,
                                'save_branch' => $saveBranch,
                                'layer' => $layer,
                                'message' => $message,
                                'created_at' => time()
                            ];
                            $bonusLog = $bonusLogCollection->findOne([
                                'from_user_connect_id' => $userConnect['_id'],
                                'to_user_connect_id' => $inviter['_id'],
                                'type' => BaseCollection::TYPE_MATCHING_BONUS,
                                'contract_id' => $userPackageHistory['contract_id'],
                                'user_package_history_id' => $userPackageHistory['_id'],
                            ]);
                            if (!$bonusLog) {
                                $bonusLogCollection->insertOne($bonusLogData);
                            }
                            // </editor-fold>

                            Users::updateBalance($inviter['_id'], BaseCollection::WALLET_COIN, $bonusAmount, BaseCollection::TYPE_MATCHING_BONUS, $message, true);
                        }
                    } else {
                        // <editor-fold desc="Process Error Log">
                        $errorLogData['message'] = 'Not enough F1 invest in each branch';
                        $errorLog = $errorLogCollection->findOne($errorLogCondition);
                        if (!$errorLog) {
                            $errorLogCollection->insertOne($errorLogData);
                        }
                        // </editor-fold>
                    }
                } else {
                    // <editor-fold desc="Process Error Log">
                    $errorLogData['message'] = 'Not enough save branch';
                    $errorLog = $errorLogCollection->findOne($errorLogCondition);
                    if (!$errorLog) {
                        $errorLogCollection->insertOne($errorLogData);
                    }
                    // </editor-fold>
                }
                $inviter = Users::getInviter($inviter['_id']);
                $layer++;
            }
        }
    }

    protected function getMatchingBonusPercent($layer = 1, $saveBranch = 0)
    {
        $bonusPercent = 0;

        if ($layer == 1) {
            $saveBranch >= 400000 && $bonusPercent = 5;
        } else if ($layer == 2) {
            $saveBranch >= 800000 && $bonusPercent = 10;
        } else if ($layer == 3) {
            $saveBranch >= 1500000 && $bonusPercent = 10;
        } else if ($layer == 4) {
            $saveBranch >= 3000000 && $bonusPercent = 15;
        } else if ($layer == 5) {
            $saveBranch >= 6000000 && $bonusPercent = 20;
        } else if ($layer == 6) {
            $saveBranch >= 10000000 && $bonusPercent = 25;
        } else if ($layer == 7) {
            $saveBranch >= 15000000 && $bonusPercent = 30;
        }
        return $bonusPercent;
    }

    /**
     * @throws ConnectionErrorException
     * @throws Exception
     * @throws Exception
     */
    public function updateCoinRateAction()
    {
        $platform = BinanceWeb3::PLATFORM;
        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY, $network);
        $functionBalanceOf = ContractLibrary::FUNCTION_BALANCE_OF;
        $functionDecimals = ContractLibrary::FUNCTION_DECIMALS;
        $functionName = ContractLibrary::FUNCTION_NAME;
        $functionSymbol = ContractLibrary::FUNCTION_SYMBOL;
        $functionToken0 = 'token0';
        $functionToken1 = 'token1';
        $abiDexPair = ContractLibrary::getAbi(ContractLibrary::DEX_PAIR);
        $abiToken = ContractLibrary::getAbi(ContractLibrary::TOKEN);
        $contractToken = new Contract($coinInstance->rpcConnector->getProvider(), $abiToken);
        $contractDexPair = new Contract($coinInstance->rpcConnector->getProvider(), $abiDexPair);

        $dataUpdate = [];

        $registryCollection = $this->mongo->selectCollection('registry');
        $registry = $registryCollection->findOne();
        if ($registry) {
            $tokenKey = "staking_setting_{$platform}_{$network}";
            $stakingSetting = $registry[$tokenKey];
            $stakingToken = $stakingSetting['staking_token'];
            $dexPair = $registry['dex_pair'];
            $dexPairAddress = $dexPair['address'];

            if ($dexPairAddress && $stakingToken) {

                $stakingTokenAddress = $stakingToken['token_address'];

                $contractDexPairInstance = $contractDexPair->at($dexPairAddress);

                // <editor-fold desc="Get Token 0 Address">
                $token0 = '';
                $contractDexPairInstance->call($functionToken0, null, function ($err, $res) use (&$token0) {
                    if ($res) {
                        $token0 = $res[0];
                    }
                });
                $token0IsAddress = $coinInstance->validAddress($token0);
                if ($token0IsAddress) {
                    $token0 = $coinInstance->toCheckSumAddress($token0);
                }
                // </editor-fold>

                // <editor-fold desc="Get Token 1 Address">
                $token1 = '';
                $contractDexPairInstance->call($functionToken1, null, function ($err, $res) use (&$token1) {
                    if ($res) {
                        $token1 = $res[0];
                    }
                });

                $token1IsAddress = $coinInstance->validAddress($token0);
                if ($token1IsAddress) {
                    $token1 = $coinInstance->toCheckSumAddress($token1);
                }
                // </editor-fold>

                $swapTokenAddress = $token0 == $stakingTokenAddress ? $token1 : $token0;

                // <editor-fold desc = "Get Balance & Decimals Swap Token">
                $swapTokenContractInstance = $contractToken->at($swapTokenAddress);
                if (!$dexPair['swap_token']) {
                    $swapTokenName = '';
                    $swapTokenContractInstance->call($functionName, null, function ($err, $res) use (&$swapTokenName) {
                        if ($res) {
                            $swapTokenName = $res[0];
                        }
                    });

                    $swapTokenSymbol = '';
                    $swapTokenContractInstance->call($functionSymbol, null, function ($err, $res) use (&$swapTokenSymbol) {
                        if ($res) {
                            $swapTokenSymbol = $res[0];
                        }
                    });

                    $swapTokenDecimals = 0;
                    $swapTokenContractInstance->call($functionDecimals, null, function ($err, $res) use (&$swapTokenDecimals) {
                        if ($res) {
                            $swapTokenDecimals = intval($res[0]->toString());
                        }
                    });
                } else {
                    $swapTokenName = $dexPair['swap_token']['token_name'];
                    $swapTokenSymbol = $dexPair['swap_token']['token_symbol'];
                    $swapTokenDecimals = $dexPair['swap_token']['token_decimals'];
                }

                $swapTokenBalance = 0;
                $swapTokenContractInstance->call($functionBalanceOf, $dexPairAddress, function ($err, $res) use (&$swapTokenBalance) {
                    if ($res) {
                        $swapTokenBalance = $res[0]->toString();
                    }
                });

                $swapTokenBalance = BigDecimal::of($swapTokenBalance)->exactlyDividedBy(pow(10, $swapTokenDecimals))->toFloat();
                // </editor-fold>

                // <editor-fold desc = "Get Balance & Decimals Staking Token">
                $stakingTokenContractInstance = $contractToken->at($stakingTokenAddress);
                $stakingTokenBalance = 0;
                $stakingTokenContractInstance->call($functionBalanceOf, $dexPairAddress, function ($err, $res) use (&$stakingTokenBalance) {
                    if ($res) {
                        $stakingTokenBalance = $res[0]->toString();
                    }
                });
                $stakingTokenDecimals = $stakingToken['token_decimals'];
                $stakingTokenBalance = BigDecimal::of($stakingTokenBalance)->exactlyDividedBy(pow(10, $stakingTokenDecimals))->toFloat();
                // </editor-fold>

                $price = $stakingTokenBalance > 0 ? $swapTokenBalance / $stakingTokenBalance : 0;

                $dexPair['swap_token'] = [
                    'token_address' => $swapTokenAddress,
                    'token_name' => $swapTokenName,
                    'token_symbol' => $swapTokenSymbol,
                    'token_decimals' => $swapTokenDecimals,
                ];
                $dexPair['staking_token'] = $stakingToken;
                $dexPair['swap_token_balance'] = $swapTokenBalance;
                $dexPair['staking_token_balance'] = $stakingTokenBalance;
                $dexPair['price'] = $price;
                $dataUpdate['dex_pair'] = $dexPair;
                $dataUpdate['coin_rate'] = $price;
            }

        }
        $priceBNB = ContractLibrary::getPriceBNB();
        if ($priceBNB > 0) {
            $dataUpdate['bnb_rate'] = $priceBNB;
        }

        if (count($dataUpdate)) {
            $registryCollection->findOneAndUpdate(['_id' => $registry['_id']], ['$set' => $dataUpdate], ['upsert' => true]);
        }
    }

    /**
     * @throws Exception
     */
    public function supportLiquidAction()
    {
        $userPackageInterestCollection = $this->mongo->selectCollection('user_package_history');
        $listData = $userPackageInterestCollection->find([
            'payment_token_type' => ContractLibrary::PAYMENT_TYPE_SWAP_TOKEN,
            'support_liquid_status' => BaseCollection::STATUS_INACTIVE
        ]);
        !empty($listData) && $listData = $listData->toArray();
        global $config;
        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $coinInstance = Adapter::getInstance('push_bsc', $network);

        foreach ($listData as $item) {
            $platform = $item['platform'];
            $network = $item['network'];
            $stakingSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::STAKING);
            $privateKey = $config->blockchain['support_liquid_private_key'];
            $fromAddress = $config->blockchain['support_liquid_address'];

            $fromAccount = new Account();
            $fromAccount->address = $fromAddress;
            $fromAccount->private_key = $privateKey;
            $toAccount = new Account();
            $toAccount->address = $stakingSettingAddress;
            $sendObject = new Send();
            $sendObject->with_nonce = true;
            $sendObject->amount = doubleval($item['token_amount']);

            $balance = $coinInstance->getBalanceAccount($fromAccount);
            if ($balance >= $sendObject->amount) {
                $sendObject = $coinInstance->send($fromAccount, $toAccount, $sendObject);
                $hash = $sendObject->hash;
                if (strlen($hash)) {
                    $dataUpdate = [
                        'support_liquid_status' => BaseCollection::STATUS_ACTIVE,
                        'support_liquid_at' => time(),
                        'support_liquid_hash' => $hash,
                        'support_liquid_message' => 'Success'
                    ];
                    Transaction::createBlockchainTransaction($fromAddress, $stakingSettingAddress, BaseCollection::ACTION_SUPPORT_LIQUID, $sendObject);
                } else {
                    $dataUpdate = [
                        'support_liquid_message' => 'Hash fail'
                    ];
                }
            } else {
                $dataUpdate = [
                    'support_liquid_message' => 'Not enough balance'
                ];
            }
            $userPackageInterestCollection->updateOne(['_id' => $item['_id']], ['$set' => $dataUpdate]);
        }
    }

    public function deleteErrorLogAction()
    {
        $errorLogCollection = $this->mongo->selectCollection('error_log');
        $errorLogCollection->deleteMany([
            'created_at' => ['$lte' => strtotime('-1 days')]
        ]);
    }

    public function getTransactionStakingAction()
    {
        $transactionCollection = $this->mongo->selectCollection('transaction');
        $platform = BinanceWeb3::PLATFORM;
        $network = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
        $stakingSettingAddress = ContractLibrary::getConfigAddress($platform, $network, ContractLibrary::STAKING);
        $dataResponse = ContractLibrary::getTransactionStaking($stakingSettingAddress);
        $listData = $dataResponse['result'] ?? [];
        $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY, $network);
        if (count($listData)) {
            foreach ($listData as $item) {
                $hash = $item['hash'];
                if ($item['txreceipt_status'] != ContractLibrary::ACTIVE) {
                    continue;
                }
                $transaction = $transactionCollection->findOne([
                    'network' => $network,
                    'platform' => $platform,
                    'hash' => $hash
                ]);

                if (!$transaction) {
                    $fromAddress = $coinInstance->toCheckSumAddress($item['from']);
                    $toAddress = $coinInstance->toCheckSumAddress($item['to']);
                    $dataTransaction = [
                        'block_hash' => $item['blockHash'],
                        'block_number' => intval($item['blockNumber']),
                        'from' => $fromAddress,
                        'to' => $toAddress,
                        'hash' => $hash,
                        'input' => $item['input'],
                        'value' => $item['value'],
                        'network' => $network,
                        'platform' => $coinInstance->platform,
                        'timestamp' => intval($item['timeStamp']),
                        'created_at' => time(),
                        'is_process' => ContractLibrary::INACTIVE,
                        'contract_type' => ContractLibrary::STAKING
                    ];
                    $transactionCollection->insertOne($dataTransaction);
                }
            }
        }

    }

    protected function getLevel($saveBranch = 0)
    {
        $level = 0;
        $saveBranch >= 400000 && $level = 1;
        $saveBranch >= 800000 && $level = 2;
        $saveBranch >= 1500000 && $level = 3;
        $saveBranch >= 3000000 && $level = 4;
        $saveBranch >= 6000000 && $level = 5;
        $saveBranch >= 10000000 && $level = 6;
        $saveBranch >= 15000000 && $level = 7;
        return $level;
    }
}