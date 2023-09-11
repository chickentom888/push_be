<?php

namespace Dcore\Library;

use Dcore\Collections\BaseCollection;
use Dcore\ControllerBase\ControllerBase;

class Module
{
    public static function isAcceptPermission($key)
    {
        if (count($_SESSION['permission']) <= 0) {
            $_SESSION['permission'] = [];
        }
        $tmp = explode(",", $key);
        if (count($tmp) > 0) {
            $rs = array_intersect($tmp, $_SESSION['permission']);
            if (count($rs) > 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            if (in_array($key, $_SESSION['permission'])) {
                return 1;
            } else {
                return 0;
            }
        }
    }

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
                ['icon' => '', 'name' => 'User Connect', 'link' => "/report/user_connect", 'active_menu' => 'report_user_connect', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Error Log', 'link' => "/report/error_log", 'active_menu' => 'report_error_log', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Airdrop', 'link' => "/report/airdrop", 'active_menu' => 'report_airdrop', 'role' => BaseCollection::LIST_ROLE],
            ],
            'role' => BaseCollection::LIST_ROLE
        ];

        $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Mint Token',
            'link' => "javascript:",
            'active_menu' => 'token_minted',
            "child" => [
                ['icon' => '', 'name' => 'Token Minted', 'link' => "/token_minted", 'active_menu' => 'token_minted', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Setting', 'link' => "/registry/mint_token_setting", 'active_menu' => 'mint_token_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Mint Token Generator', 'link' => "/mint_token_generator", 'active_menu' => 'mint_token_generator', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Mint Token Setting history', 'link' => "/registry_log/mint_token_setting", 'active_menu' => 'mint_token_generator', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'fa fa-first-order',
            'name' => 'Airdrop',
            'link' => "javascript:",
            'active_menu' => 'airdrop',
            "child" => [
                ['icon' => '', 'name' => 'Airdrop', 'link' => "/airdrop", 'active_menu' => 'airdrop', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Setting', 'link' => "/registry/airdrop_setting", 'active_menu' => 'airdrop_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Airdrop Setting history', 'link' => "/registry_log/airdrop_setting", 'active_menu' => 'airdrop_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'fa fa-lock',
            'name' => 'Lock',
            'link' => "javascript:",
            'active_menu' => 'lock',
            "child" => [
                ['icon' => '', 'name' => 'History', 'link' => "/lock", 'active_menu' => 'airdrop', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Setting', 'link' => "/registry/lock_setting", 'active_menu' => 'lock_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Lock setting history', 'link' => "/registry_log/lock_setting", 'active_menu' => 'lock_setting', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'ion-arrow-swap',
            'name' => 'Exchange Platform',
            'link' => "/exchange_platform",
            'active_menu' => 'exchange_platform',
            "child" => [],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Presale',
            'link' => "javascript:",
            'active_menu' => 'ico',
            "child" => [
                ['icon' => '', 'name' => 'Presale', 'link' => "/presale", 'active_menu' => 'presale_setting_bsc', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Presale Setting', 'link' => "/registry/presale_setting", 'active_menu' => 'presale_setting_eth', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Setting Address', 'link' => "/presale_setting_address/index", 'active_menu' => 'presale_setting_address', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Presale Generator', 'link' => "/presale_generator", 'active_menu' => 'presale_generator', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Presale Setting history', 'link' => "/registry_log/presale_setting", 'active_menu' => 'presale_generator', 'role' => BaseCollection::LIST_ROLE],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        /* $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Lottery',
            'link' => "javascript:",
            'active_menu' => 'lottery',
            "child" => [
                ['icon' => '', 'name' => 'List', 'link' => "/lottery", 'active_menu' => 'lottery', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Ticket', 'link' => "/lottery/ticket", 'active_menu' => 'lottery_ticket', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Cron', 'link' => "/lottery/cron", 'active_menu' => 'lottery_cron', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Setting', 'link' => "/registry/lottery_setting", 'active_menu' => 'lottery_setting', 'role' => BaseCollection::LIST_ROLE],
            ],
            'role' => BaseCollection::LIST_ROLE
        ];



        $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Sale',
            'link' => "javascript:",
            'active_menu' => 'ico',
            "child" => [
                ['icon' => '', 'name' => 'Sale', 'link' => "/sale", 'active_menu' => 'sale_setting_bsc', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Sale Setting', 'link' => "/registry/sale_setting", 'active_menu' => 'sale_setting_eth', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Setting Address', 'link' => "/sale_setting_address/index", 'active_menu' => 'sale_setting_address', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Sale Generator', 'link' => "/sale_generator", 'active_menu' => 'sale_generator', 'role' => BaseCollection::LIST_ROLE],
//                ['icon' => '', 'name' => 'Sale Setting history', 'link' => "/registry_log/sale_setting", 'active_menu' => 'sale_generator', 'role' => BaseCollection::LIST_ROLE],
            ],
            'role' => BaseCollection::LIST_ROLE
        ];

        $menu[] = [
            'icon' => 'dripicons-star',
            'name' => 'Pool',
            'link' => "javascript:",
            'active_menu' => 'pool',
            "child" => [
                ['icon' => '', 'name' => 'Pool', 'link' => "/pool", 'active_menu' => 'pool_setting_bsc', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Pool Setting', 'link' => "/registry/pool_setting", 'active_menu' => 'pool_setting_eth', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Setting Address', 'link' => "/pool_setting_address/index", 'active_menu' => 'pool_setting_address', 'role' => BaseCollection::LIST_ROLE],
                ['icon' => '', 'name' => 'Pool Generator', 'link' => "/pool_generator", 'active_menu' => 'pool_generator', 'role' => BaseCollection::LIST_ROLE],
            ],
            'role' => BaseCollection::LIST_ROLE
        ];

        */

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

        $menu[] = [
            'icon' => 'fa fa-flash',
            'name' => 'Admin Tools',
            'link' => "javascript:",
            'active_menu' => 'admin_tools',
            "child" => [
//                ['icon' => '', 'name' => 'Create Presale', 'link' => "/index/create_presale", 'active_menu' => 'create_presale', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Create Sale', 'link' => "/index/create_sale", 'active_menu' => 'create_sale', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Create Pool', 'link' => "/index/create_pool", 'active_menu' => 'create_pool', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Create Token', 'link' => "/index/create_token", 'active_menu' => 'create_token', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Airdrop', 'link' => "/index/airdrop", 'active_menu' => 'airdrop', 'role' => [BaseCollection::ROLE_ADMIN]],
//                ['icon' => '', 'name' => 'Slide management', 'link' => "/slide", 'active_menu' => 'slide', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Main token', 'link' => "/index/main_token", 'active_menu' => 'main_token', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Swap', 'link' => "/index/swap", 'active_menu' => 'Swap', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'mdi mdi-settings',
            'name' => 'Block Info',
            'link' => "javascript:void(0)",
            'active_menu' => 'block_info',
            "child" => [
                ['icon' => '', 'name' => 'Blockchain Sync', 'link' => "/block/blockchainSync", 'active_menu' => 'active_menu', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Block Task', 'link' => "/block/blockTask", 'active_menu' => 'block_block_task', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Block Info', 'link' => "/block/blockInfo", 'active_menu' => 'block_block_info', 'role' => [BaseCollection::ROLE_ADMIN]],
                ['icon' => '', 'name' => 'Scan Block', 'link' => "/block/scanMissBlock", 'active_menu' => 'block_scan_block', 'role' => [BaseCollection::ROLE_ADMIN]],
            ],
            'role' => [BaseCollection::ROLE_ADMIN]
        ];

        $menu[] = [
            'icon' => 'fa fa-circle-o',
            'name' => 'Token',
            'link' => "/token",
            'active_menu' => 'token_management',
            "child" => [],
            'role' => BaseCollection::LIST_ROLE
        ];

        return $menu;
    }
}
