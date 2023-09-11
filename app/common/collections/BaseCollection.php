<?php

namespace Dcore\Collections;

use MongoDB\Collection;

/**
 * Class BaseCollection
 * @package Dcore\Collections
 * @property MongoDB\Database mongo
 */
class BaseCollection extends Collection
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_UN_CONFIRMED = 3;

    const ROLE_ADMIN = 1;
    const ROLE_MEMBER = 0;
    const ROLE_REPORTER = 2;

    const LIST_ROLE = [
        self::ROLE_ADMIN,
        self::ROLE_MEMBER,
        self::ROLE_REPORTER
    ];

    const STATUS_PENDING = 0;
    const STATUS_APPROVE = 1;
    const STATUS_REJECT = 2;

    const BRANCH_LEFT = 'left';
    const BRANCH_RIGHT = 'right';
    const WALLET_INTEREST = 'interest';
    const WALLET_COIN = 'coin';

    const TYPE_DIRECT_BONUS = 'direct_bonus';
    const TYPE_TEAM_BONUS = 'team_bonus';
    const TYPE_MATCHING_BONUS = 'matching_bonus';
    const TYPE_STAKING_INTEREST = 'staking_interest';
    const TYPE_STAKING_PRINCIPAL = 'staking_principal';
    const TYPE_STAKING_FUND_INTEREST = 'staking_fund_interest';
    const TYPE_WITHDRAW = 'withdraw';
    const TYPE_WITHDRAW_REJECT = 'withdraw_reject';

    const ACTION_SUPPORT_LIQUID = 'support_liquid';
    const ACTION_WITHDRAW = 'withdraw';

    const LIST_TYPE_BONUS = [
        self::TYPE_DIRECT_BONUS,
        self::TYPE_TEAM_BONUS,
        self::TYPE_MATCHING_BONUS,
    ];

    const LIST_TYPE_INTEREST = [
        self::TYPE_STAKING_INTEREST,
        self::TYPE_STAKING_FUND_INTEREST,
    ];

    const LIST_ACTION = [
        self::ACTION_SUPPORT_LIQUID,
        self::ACTION_WITHDRAW,
    ];

    public function getNextSequence($name)
    {
        $ary = $name::find(
            [
                'limit' => 1,
                'order' => 'id DESC'
            ]
        );
        if (empty($ary->toArray())) $count = 1;
        else {
            $count = intval($ary[0]->id);
            $count++;
        }
        return $count;
    }

    public static function listWallet()
    {
        global $config;
        return [
            self::WALLET_COIN => $config->site->coin_ticker,
            self::WALLET_INTEREST => 'Interest',
        ];
    }

    public static function listBalanceLog($key = null)
    {
        $list = [
            self::TYPE_DIRECT_BONUS => 'Direct Bonus',
            self::TYPE_TEAM_BONUS => 'Team Bonus',
            self::TYPE_MATCHING_BONUS => 'Matching Bonus',
            self::TYPE_STAKING_INTEREST => 'Interest',
            self::TYPE_STAKING_PRINCIPAL => 'Principal',
            self::TYPE_STAKING_FUND_INTEREST => 'Fund Interest',
            self::TYPE_WITHDRAW => 'Withdraw',
            self::TYPE_WITHDRAW_REJECT => 'Withdraw Rejected',
        ];
        return $key != null ? $list[$key] : $list;
    }

    public static function listBonusLog($key = null)
    {
        $list = [
            self::TYPE_DIRECT_BONUS => 'Direct Bonus',
            self::TYPE_TEAM_BONUS => 'Team Bonus',
            self::TYPE_MATCHING_BONUS => 'Matching Bonus',
        ];
        return $key != null ? $list[$key] : $list;
    }

    public static function listBranch($key = null)
    {
        $list = [
            self::BRANCH_LEFT => 'Left',
            self::BRANCH_RIGHT => 'Right',
        ];
        return $key != null ? $list[$key] : $list;
    }

    public static function listAction($key = null)
    {
        $list = [
            self::ACTION_SUPPORT_LIQUID => 'Support Liquid',
            self::ACTION_WITHDRAW => 'Withdraw',
        ];
        return $key != null ? $list[$key] : $list;
    }

}
