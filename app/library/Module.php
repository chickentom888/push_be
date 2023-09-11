<?php

namespace Dcore\Library;

use Dcore\Collections\BaseCollection;
use Dcore\ControllerBase\ControllerBase;

class Module
{

    /**
     * @param ControllerBase|null $context
     * @return array
     */
    public static function listSidebar($context = null): array
    {
        global $config;
        $menu[] = [
            'icon' => 'fa fa-home',
            'name' => 'Dashboard',
            'link' => "/",
            'active_menu' => 'dashboard',
            "child" => [],
            'role' => BaseCollection::LIST_ROLE
        ];

        $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Staking',
            'link' => "javascript:",
            'active_menu' => 'staking',
            "child" => [
                ['icon' => '', 'name' => 'User Package', 'link' => "/staking/user_package", 'active_menu' => 'staking', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'User Package History', 'link' => "/staking/user_package_history", 'active_menu' => 'staking', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Interest', 'link' => "/staking/interest", 'active_menu' => 'staking', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Principal', 'link' => "/staking/principal", 'active_menu' => 'staking', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Setting', 'link' => "/registry/staking_setting", 'active_menu' => 'staking_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => BaseCollection::LIST_ROLE
        ];

        $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Report',
            'link' => "javascript:",
            'active_menu' => 'report',
            "child" => [
                ['icon' => '', 'name' => 'Balance Log', 'link' => "/report/balance_log", 'active_menu' => 'report_balance_log', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Bonus Log', 'link' => "/report/bonus_log", 'active_menu' => 'report_bonus_log', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Max Out Log', 'link' => "/report/max_out_log", 'active_menu' => 'report_max_out_log', 'role' => BaseCollection::LIST_ROLE],
            ],
            'role' => BaseCollection::LIST_ROLE
        ];

        $menu[] = [
            'icon' => 'fa fa-lock',
            'name' => 'Lock',
            'link' => "javascript:",
            'active_menu' => 'lock',
            "child" => [
                ['icon' => '', 'name' => 'History', 'link' => "/lock", 'active_menu' => 'airdrop', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Setting', 'link' => "/registry/lock_setting", 'active_menu' => 'lock_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'fa fa-flash',
            'name' => 'Management',
            'link' => "javascript:",
            'active_menu' => 'management',
            "child" => [
                ['icon' => '', 'name' => 'Config address', 'link' => "/config_address", 'active_menu' => 'active_menu', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Transaction', 'link' => "/index/transaction", 'active_menu' => 'active_menu', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'User Connect', 'link' => "/user_connect/index", 'active_menu' => 'user_connect', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Registry', 'link' => "/index/registry", 'active_menu' => 'registry', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Withdraw', 'link' => "/withdraw/index", 'active_menu' => 'withdraw', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Contract', 'link' => "/contract/index", 'active_menu' => 'contract', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'User', 'link' => "/user/index", 'active_menu' => 'user', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Blockchain Transaction', 'link' => "/block/transaction", 'active_menu' => 'block_transaction', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        return $menu;
    }
}
