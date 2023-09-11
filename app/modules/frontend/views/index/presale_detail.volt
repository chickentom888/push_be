<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Instance </h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">


                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Presale Detail</h5>

                    <div class="general-label">
                        <form role="form" method="post" action="">

                            <div class="row">

                                <div class="col-sm-6">
                                    <h5>Thông tin</h5>

                                    <div class="form-group">
                                        <label for="sale_token_address">Sale Token Address</label>
                                        <input class="form-control" type="text" name="sale_token_address" value="{{ presale['sale_token_address'] }}" id="sale_token_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_name">Sale token name</label>
                                        <input class="form-control" type="text" name="sale_token_name" value="{{ presale['sale_token_name'] }}" id="sale_token_name" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_symbol">Sale token symbol</label>
                                        <input class="form-control" type="text" name="sale_token_symbol" value="{{ presale['sale_token_symbol'] }}" id="sale_token_symbol" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_decimals">Sale token decimals</label>
                                        <input class="form-control" type="text" name="sale_token_decimals" value="{{ presale['sale_token_decimals'] }}" id="sale_token_decimals" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_address">Base Token Address</label>
                                        <input class="form-control" type="text" name="base_token_address" value="{{ presale['base_token_address'] }}" id="base_token_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_name">Base token name</label>
                                        <input class="form-control" type="text" name="base_token_name" value="{{ presale['base_token_name'] }}" id="base_token_name" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_symbol">Base token symbol</label>
                                        <input class="form-control" type="text" name="base_token_symbol" value="{{ presale['base_token_symbol'] }}" id="base_token_symbol" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_decimals">Base token decimals</label>
                                        <input class="form-control" type="text" name="base_token_decimals" value="{{ presale['base_token_decimals'] }}" id="base_token_decimals" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Cấu hình</h5>

                                    <div class="form-group">
                                        <label for="sale_token_amount">{{ presale['sale_token_symbol'] }} amount sale?</label>
                                        <input class="form-control" type="text" name="sale_token_amount" value="{{ presale['amount'] }}" id="sale_token_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="soft_cap">Soft Cap ({{ presale['base_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="soft_cap" value="{{ presale['soft_cap'] }}" id="soft_cap" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="hard_cap">Hard Cap ({{ presale['base_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="hard_cap" value="{{ presale['hard_cap'] }}" id="hard_cap" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_price">Presale Rate (1 {{ presale['base_token_symbol'] }} = ? {{ presale['sale_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="token_price" value="{{ presale['token_price'] }}" id="token_price" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="listing_rate_percent">Listing Rate Percent (%)</label>
                                        <select name="listing_rate_percent" class="form-control" id="listing_rate_percent" readonly="">
                                            <option value="0" {{ presale['listing_price_percent'] == 0 ? 'selected' : '' }}>0%</option>
                                            <option value="10" {{ presale['listing_price_percent'] == 10 ? 'selected' : '' }}>10%</option>
                                            <option value="25" {{ presale['listing_price_percent'] == 25 ? 'selected' : '' }}>25%</option>
                                            <option value="50" {{ presale['listing_price_percent'] == 50 ? 'selected' : '' }}>50%</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="listing_price">Listing Rate (1 {{ presale['base_token_symbol'] }} = ? {{ presale['sale_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="listing_price" value="{{ presale['listing_price'] }}" id="listing_price" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_free_amount">Sale token free amount ({{ presale['sale_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="sale_token_free_amount" value="{{ presale['sale_token_free_amount'] }}" id="sale_token_free_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="created_at">Created at</label>
                                        <input class="form-control" type="text" name="created_at" value="{{ date('d/m/Y H:i:s', presale['created_at']) }}" id="created_at" readonly>
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Liquid</h5>

                                    <div class="form-group">
                                        <label for="liquidity_percent">Liquidity Percent (%)</label>
                                        <input class="form-control" type="text" name="liquidity_percent" value="{{ presale['liquidity_percent'] }}" id="liquidity_percent" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_liquidity_amount">{{ presale['base_token_symbol'] }} Liquid Amount</label>
                                        <input class="form-control" type="text" name="base_token_liquidity_amount" value="{{ presale['base_token_liquidity_amount'] }}" id="base_token_liquidity_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_liquidity_amount">{{ presale['sale_token_symbol'] }} Liquid Amount</label>
                                        <input class="form-control" type="text" name="sale_token_liquidity_amount" value="{{ presale['sale_token_liquidity_amount'] }}" id="sale_token_liquidity_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_fee_amount">Base Token Fee ({{ presale['base_fee_percent'] }}%) ({{ presale['base_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="base_fee_amount" value="{{ presale['base_fee_amount'] }}" id="base_fee_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_fee_amount">Sale Token Fee ({{ presale['token_fee_percent'] }}%) ({{ presale['sale_token_symbol'] }})</label>
                                        <input class="form-control" type="text" name="sale_token_fee_amount" value="{{ presale['sale_token_fee_amount'] }}" id="sale_token_fee_amount" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Buyer</h5>

                                    <div class="form-group">
                                        <label for="limit_per_buyer">Amount {{ presale['base_token_symbol'] }} / user</label>
                                        <input class="form-control" type="text" name="limit_per_buyer" value="{{ presale['limit_per_buyer'] }}" id="limit_per_buyer">
                                    </div>

                                    <div class="form-group">
                                        <label for="max_buyer">Max User</label>
                                        <input class="form-control" type="text" name="max_buyer" value="{{ presale['max_buyer'] }}" id="max_buyer" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="lock_period">Lock liquid for (months)</label>
                                        <input class="form-control" type="text" name="lock_period" value="{{ presale['lock_period'] / 60 / 60 / 24 / 30 }}" id="lock_period" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="start_time">Start Time</label>
                                        <input class="form-control" type="datetime-local" name="start_time" value="{{ date('Y-m-d\TH:i', presale['start_time']) }}" id="start_time">
                                    </div>

                                    <div class="form-group">
                                        <label for="end_time">End Time</label>
                                        <input class="form-control" type="datetime-local" name="end_time" value="{{ date('Y-m-d\TH:i', presale['end_time']) }}" id="end_time">
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Referer</h5>

                                    <div class="form-group">
                                        <label for="refer_fee_address">Referer Address</label>
                                        <input class="form-control" type="text" name="referer_fee_address" value="{{ presale['refer_fee_address'] }}" id="refer_fee_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="refer_fee_percent">Referer Fee Percent</label>
                                        <input class="form-control" type="text" name="refer_fee_percent" value="{{ presale['refer_fee_percent'] }}" id="refer_fee_percent" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="refer_fee_amount">Referer Fee Amount</label>
                                        <input class="form-control" type="text" name="refer_fee_amount" value="{{ presale['refer_fee_amount'] }}" id="refer_fee_amount" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Fee</h5>

                                    <div class="form-group">
                                        <label for="creation_fee">Creation Fee</label>
                                        <input class="form-control" type="text" name="creation_fee" value="{{ presale['creation_fee'] }}" id="creation_fee" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_fee_percent">{{ presale['base_token_symbol'] }} fee percent</label>
                                        <input class="form-control" type="text" name="base_fee_percent" value="{{ presale['base_fee_percent'] }}%" id="base_fee_percent" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_fee_percent">{{ presale['sale_token_symbol'] }} fee percent</label>
                                        <input class="form-control" type="text" name="token_fee_percent" value="{{ presale['token_fee_percent'] }}%" id="token_fee_percent" readonly>
                                    </div>

                                </div>

                            </div>


                            <div class="row">

                                <div class="col-sm-6">
                                    <h5>Zero Round</h5>

                                    <div class="form-group">
                                        <label for="zero_round_token_address">Token Address</label>
                                        <input class="form-control" type="text" name="zero_round_token_address" value="{{ presale['zero_round']['token_address'] }}" id="zero_round_token_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_token_name">Token Name</label>
                                        <input class="form-control" type="text" name="zero_round_token_name" value="{{ presale['zero_round']['token_name'] }}" id="zero_round_token_name" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_token_symbol">Token Symbol</label>
                                        <input class="form-control" type="text" name="zero_round_token_symbol" value="{{ presale['zero_round']['token_symbol'] }}" id="zero_round_token_symbol" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_token_amount">Token Amount</label>
                                        <input class="form-control" type="text" name="zero_round_token_amount" value="{{ presale['zero_round']['token_amount'] }}" id="zero_round_token_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_token_decimals">Token Decimals</label>
                                        <input class="form-control" type="text" name="zero_round_token_decimals" value="{{ presale['zero_round']['token_decimals'] }}" id="zero_round_token_decimals" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_finish_at">Finish at</label>
                                        <input class="form-control" type="text" name="zero_round_finish_at" value="{{ date('d/m/Y H:i:s', presale['zero_round']['finish_at']) }}" id="zero_round_finish_at" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_finish_before_first_round">Finish before first round</label>
                                        <input class="form-control" type="text" name="zero_round_finish_before_first_round" value="{{ presale['zero_round']['finish_before_first_round'] }}" id="zero_round_finish_before_first_round" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_percent">Hard Cap Percent</label>
                                        <input class="form-control" type="text" name="zero_round_percent" value="{{ presale['zero_round']['percent'] }}" id="zero_round_percent" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_max_base_token_amount">Max Base Token Amount</label>
                                        <input class="form-control" type="text" name="zero_round_max_base_token_amount" value="{{ presale['zero_round']['max_base_token_amount'] }}" id="zero_round_max_base_token_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_max_slot">Max Slot</label>
                                        <input class="form-control" type="text" name="zero_round_max_slot" value="{{ presale['zero_round']['max_slot'] }}" id="zero_round_max_slot" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="zero_round_registered_slot">Registered Slot</label>
                                        <input class="form-control" type="text" name="zero_round_registered_slot" value="{{ presale['zero_round']['registered_slot'] }}" id="zero_round_registered_slot" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">

                                    <h5>Address</h5>

                                    <div class="form-group">
                                        <label for="contract_address">Contract Address</label>
                                        <input class="form-control" type="text" name="contract_address" value="{{ presale['contract_address'] }}" id="contract_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="presale_generator">Presale Generator</label>
                                        <input class="form-control" type="text" name="presale_generator" value="{{ presale['presale_generator'] }}" id="presale_generator" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="presale_owner_address">Presale Owner Address</label>
                                        <input class="form-control" type="text" name="presale_owner_address" value="{{ presale['presale_owner_address'] }}" id="presale_owner_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="wrap_token_address">Wrap token address</label>
                                        <input class="form-control" type="text" name="wrap_token_address" value="{{ presale['wrap_token_address'] }}" id="wrap_token_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="dex_locker_address">Dex locker address</label>
                                        <input class="form-control" type="text" name="dex_locker_address" value="{{ presale['dex_locker_address'] }}" id="dex_locker_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="dex_factory_address">Dex factory address</label>
                                        <input class="form-control" type="text" name="dex_factory_address" value="{{ presale['dex_factory_address'] }}" id="dex_factory_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_fee_address">Base fee address</label>
                                        <input class="form-control" type="text" name="dex_factory_address" value="{{ presale['base_fee_address'] }}" id="base_fee_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_fee_address">Token fee address</label>
                                        <input class="form-control" type="text" name="token_fee_address" value="{{ presale['token_fee_address'] }}" id="token_fee_address" readonly>
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>General Info</h5>

                                    <div class="form-group">
                                        <label for="contract_version">Contract Version</label>
                                        <input class="form-control" type="text" name="contract_version" value="{{ presale['contract_version'] }}" id="contract_version" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="presale_in_main_token">Presale in main token</label>
                                        <input class="form-control" type="text" name="presale_in_main_token" value="{{ presale['presale_in_main_token'] ? 'True' : 'False' }}" id="presale_in_main_token" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="whitelist_only">Whitelist only</label>
                                        <select name="whitelist_only" id="whitelist_only" class="form-control">
                                            <option value="1" {{ presale['whitelist_only'] == true ? 'selected' : '' }}>True</option>
                                            <option value="0" {{ presale['whitelist_only'] == false ? 'selected' : '' }}>False</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="force_failed">Force Failed</label>
                                        <input class="form-control" type="text" name="force_failed" value="{{ presale['force_failed'] ? 'True' : 'False' }}" id="force_failed" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="lp_generation_complete">LP Generation Complete</label>
                                        <input class="form-control" type="text" name="force_failed" value="{{ presale['lp_generation_complete'] ? 'True' : 'False' }}" id="lp_generation_complete" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="first_round_length">First Round Length (second)</label>
                                        <input class="form-control" type="text" name="first_round_length" value="{{ presale['first_round_length'] }}" id="first_round_length" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="dex_pair_address">Dex Pair Address</label>
                                        <input class="form-control" type="text" name="dex_pair_address" value="{{ presale['dex_pair_address'] }}" id="dex_pair_address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="message">Message</label>
                                        <input class="form-control" type="text" name="message" value="{{ presale['message'] }}" id="message" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="owner_withdraw_sale_token">Owner withdraw sale token</label>
                                        <input class="form-control" type="text" name="owner_withdraw_sale_token" value="{{ presale['owner_withdraw_sale_token'] }}" id="owner_withdraw_sale_token" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Token Sold</h5>

                                    <div class="form-group">
                                        <label for="total_base_collected">Total Base Collected</label>
                                        <input class="form-control" type="text" name="total_base_collected" value="{{ presale['total_base_collected'] }}" id="total_base_collected" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="total_token_sold">Total Token Sold</label>
                                        <input class="form-control" type="text" name="total_token_sold" value="{{ presale['total_token_sold'] }}" id="total_token_sold" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="total_base_withdrawn">Total Base Withdrawn</label>
                                        <input class="form-control" type="text" name="total_base_withdrawn" value="{{ presale['total_base_withdrawn'] }}" id="total_base_withdrawn" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="total_token_withdrawn">Total Token Withdrawn</label>
                                        <input class="form-control" type="text" name="total_token_withdrawn" value="{{ presale['total_token_withdrawn'] }}" id="total_token_withdrawn" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="current_round">Current Round</label>
                                        <input class="form-control" type="text" name="current_round" value="{{ presale['current_round'] }}" id="current_round" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="current_status">Current Status</label>
                                        <input class="form-control" type="text" name="current_status" value="{{ helper.getPresaleStatusText(presale) }}" id="current_status" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="success_at">Success At</label>
                                        <input class="form-control" type="text" name="success_at" value="{{ date('d/m/Y H:i:s', presale['success_at']) }}" id="success_at" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="max_success_to_liquidity">Max Success To Liquid At</label>
                                        <input class="form-control" type="text" name="max_success_to_liquidity" value="{{ date('d/m/Y H:i:s', presale['success_at'] + registry['setting']['max_success_to_liquidity']) }}" id="max_success_to_liquidity" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="liquidity_at">Liquidity At</label>
                                        <input class="form-control" type="text" name="liquidity_at" value="{{ date('d/m/Y H:i:s', presale['liquidity_at']) }}" id="liquidity_at" readonly>
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Round 0</h5>
                                    <div class="form-group">
                                        <label for="active_zero_round">Active Zero Round</label>
                                        <select name="active_zero_round" class="form-control" id="active_zero_round" readonly="">
                                            <option value="0" {{ presale['active_zero_round'] == false ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ presale['active_zero_round'] == true ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h5>Round 1</h5>
                                    <div class="form-group">
                                        <label for="active_first_round">Active First Round</label>
                                        <select name="active_first_round" class="form-control" id="active_first_round" readonly="">
                                            <option value="0" {{ presale['active_first_round'] == false ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ presale['active_first_round'] == true ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Vesting Info</h5>
                                    <div class="form-group">
                                        <label for="active_zero_round">Active vesting</label>
                                        <select name="active_vesting" class="form-control" id="active_vesting" readonly="">
                                            <option value="0">No</option>
                                            <option value="1" {{ presale['active_vesting'] == true ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {% if presale['active_vesting'] == true %}
                                <div class="row">
                                    {% for index, vesting in presale['list_vesting_period'] %}
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="list_vesting_period">Vesting period</label>
                                                <input class="form-control" type="text" name="success_at" value="{{ date('d/m/Y H:i:s', vesting) }}" id="list_vesting_period" readonly>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="active_zero_round">Vesting percent (%)</label>
                                                <input class="form-control" type="text" name="success_at" value="{{ presale['list_vesting_percent'][index] }}" id="list_vesting_period" readonly>
                                            </div>
                                        </div>
                                    {% endfor %}
                                </div>

                            {% endif %}

                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Whitelist User</h5>
                                </div>
                                <div class="col-sm-6">

                                    <label for="whitelist_action">Action</label>
                                    <select name="whitelist_action" id="whitelist_action" class="form-control">
                                        <option value="1">Add</option>
                                        <option value="0">Remove</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="whitelist_user">Address</label>
                                    <textarea class="form-control" type="text" name="whitelist_user" id="whitelist_user" rows="15"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Buy Token</h5>
                                </div>
                                <div class="col-sm-6">
                                    <label for="amount_to_buy">Amount to buy</label>
                                    <input class="form-control" type="text" name="amount_to_buy" value="" id="amount_to_buy">
                                </div>

                                <div class="col-sm-6">
                                    <label for="amount_receive">Amount receive</label>
                                    <input class="form-control" type="text" name="amount_receive" value="" id="amount_receive" readonly>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-info" id="btn_approve">Approve Base Token</button>
                                        <button type="button" class="btn btn-success" id="btn_buy">Buy</button>
                                        <button type="button" class="btn btn-dark" id="btn_add_liquidity">Add Liquid</button>
                                        <button type="button" class="btn btn-info" id="btn_user_withdraw_sale_token">User Withdraw Sale Token</button>
                                        <button type="button" class="btn btn-danger" id="btn_user_force_fail">User Force Fail</button>
                                        <button type="button" class="btn btn-danger" id="btn_admin_force_fail">Admin Force Fail</button>
                                        <button type="button" class="btn btn-warning" id="btn_user_withdraw_base_token">User Withdraw Base Token</button>
                                        <button type="button" class="btn btn-warning" id="btn_owner_withdraw_sale_token">Owner Withdraw Sale Token</button>
                                        <button type="button" class="btn btn-info" id="btn_update_limit_per_buyer">Update Limit Per Buyer</button>
                                        <button type="button" class="btn btn-info" id="btn_update_time">Update Time</button>
                                        <button type="button" class="btn btn-info" id="btn_update_whitelist_flag">Update Whitelist Flag</button>
                                        <button type="button" class="btn btn-info" id="btn_update_whitelist_user">Update Whitelist User</button>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-info" id="btn_approve_zero_token">Approve Zero Token</button>
                                        <button type="button" class="btn btn-info" id="btn_register_zero_round">Register Zero Round</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>

{% include 'layouts/presale_user_log.volt' %}

{% include 'layouts/presale_user_zero_round.volt' %}

{% include 'layouts/presale_whitelist.volt' %}

{% include 'layouts/presale_buy_log.volt' %}

<script type="text/javascript">

    function calculateAmount() {
        let amountToBuy = parseFloat($('#amount_to_buy').val() || 0);

        let amountReceiveInput = $('#amount_receive');
        if (amountToBuy <= 0) {
            amountReceiveInput.val(0);
            return;
        }

        let tokenPrice = parseFloat($('#token_price').val() || 0);
        let amountReceive = amountToBuy * tokenPrice;
        amountReceiveInput.val(amountReceive);
    }

    async function approveToken() {
        await checkConnect();
        let amountToBuy = parseFloat($('#amount_to_buy').val() || 0);
        if (amountToBuy <= 0) {
            return;
        }

        let baseTokenDecimals = parseInt($('#base_token_decimals').val());
        amountToBuy = amountToBuy * 1000000;

        amountToBuy = (new BigNumber(amountToBuy)).mul((new BigNumber(10)).pow(baseTokenDecimals));
        amountToBuy = web3.utils.toBN(amountToBuy).toString();

        let baseTokenAddress = $('#base_token_address').val();
        let presaleContractAddress = $('#contract_address').val();
        let tokenContract = new web3.eth.Contract(ABI_TOKEN, baseTokenAddress);
        let approve = await tokenContract.methods.approve(presaleContractAddress, amountToBuy).send({
            from: selectedAccount
        }).on('transactionHash', function (hash) {
            alert("Please wait a minute to complete your transaction!");
        }).on('confirmation', function (confirmationNumber, receipt) {
        }).on('receipt', function (receipt) {
        }).on('error', function (error, receipt) {
            alert("Error");
            console.log("error", error, receipt);
        });

        $("#global_loading").hide();
        if (approve.status) {
            alert("Success");
        }

    }

    async function buyToken() {
        try {
            await checkConnect();
            let amountToBuy = parseFloat($('#amount_to_buy').val() || 0);
            if (amountToBuy <= 0) {
                return;
            }

            let baseTokenDecimals = parseInt($('#base_token_decimals').val());

            amountToBuy = (new BigNumber(amountToBuy)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            amountToBuy = web3.utils.toBN(amountToBuy).toString();

            let presaleContractAddress = $('#contract_address').val();

            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let dataSend = {from: selectedAccount};
            let presaleInMainToken = $('#presale_in_main_token').val().toLowerCase();
            if (presaleInMainToken === 'true') {
                dataSend.value = amountToBuy
            }
            let approve = await presaleContract.methods.buyToken(amountToBuy).send(dataSend).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (approve.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function addLiquidity() {
        try {
            await checkConnect();
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.addLiquidity().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function userWithdrawSaleToken() {
        try {
            await checkConnect();
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.userWithdrawSaleToken().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function userForceFail() {
        try {
            await checkConnect();
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.forceFailIfPairExists().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function adminForceFail() {
        try {
            await checkConnect();
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.forceFailByAdmin().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function userWithdrawBaseToken() {
        try {
            await checkConnect();
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.userWithdrawBaseToken().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function ownerWithdrawSaleToken() {
        try {
            await checkConnect();
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.ownerWithdrawSaleToken().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function updateLimitPerBuyer() {
        try {
            await checkConnect();

            let limitPerBuyer = parseFloat($('#limit_per_buyer').val() || 0);
            if (limitPerBuyer <= 0) {
                return;
            }

            let baseTokenDecimals = parseInt($('#base_token_decimals').val());

            limitPerBuyer = (new BigNumber(limitPerBuyer)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            limitPerBuyer = web3.utils.toBN(limitPerBuyer).toString();

            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.updateLimitPerBuyer(limitPerBuyer).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function updateTime() {
        try {
            await checkConnect();

            let startTime = $('#start_time').val();
            startTime = parseInt((new Date(startTime)).getTime() / 1000);

            let endTime = $('#end_time').val();
            endTime = parseInt((new Date(endTime)).getTime() / 1000);

            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.updateTime(startTime, endTime).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function updateWhitelistFlag() {
        try {
            await checkConnect();

            let value = parseInt($('#whitelist_only').val());
            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.setWhitelistFlag(value).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    async function editWhitelist() {

        try {
            await checkConnect();

            let address = [];
            $.each($('#whitelist_user').val().split(/\n/), function (i, line) {
                if (line.length && web3.utils.isAddress(line)) {
                    address.push(line);
                }
            });
            let action = parseInt($('#whitelist_action').val());

            if (!address.length) {
                return;
            }

            console.log(action, address);

            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.editWhitelist(address, action).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }
    }

    async function checkConnect() {
        if (provider == null) {
            init();
            await onConnect();
            return false;
        }
    }

    async function approveZeroToken() {
        await checkConnect();
        let zeroAmount = parseFloat($('#zero_round_token_amount').val() || 0);
        if (zeroAmount <= 0) {
            return;
        }

        let zeroTokenDecimals = parseInt($('#zero_round_token_decimals').val());
        zeroAmount = zeroAmount * 1000000;

        zeroAmount = (new BigNumber(zeroAmount)).mul((new BigNumber(10)).pow(zeroTokenDecimals));
        zeroAmount = web3.utils.toBN(zeroAmount).toString();

        let zeroTokenAddress = $('#zero_round_token_address').val();
        let presaleContractAddress = $('#contract_address').val();
        let tokenContract = new web3.eth.Contract(ABI_TOKEN, zeroTokenAddress);
        let approve = await tokenContract.methods.approve(presaleContractAddress, zeroAmount).send({
            from: selectedAccount
        }).on('transactionHash', function (hash) {
            alert("Please wait a minute to complete your transaction!");
        }).on('confirmation', function (confirmationNumber, receipt) {
        }).on('receipt', function (receipt) {
        }).on('error', function (error, receipt) {
            alert("Error");
            console.log("error", error, receipt);
        });

        $("#global_loading").hide();
        if (approve.status) {
            alert("Success");
        }
    }

    async function registerZeroRound() {
        try {
            await checkConnect();

            let presaleContractAddress = $('#contract_address').val();
            let presaleContract = new web3.eth.Contract(ABI_PRESALE, presaleContractAddress);
            let result = await presaleContract.methods.registerZeroRound().send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                toastr["success"]("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                toastr["error"]("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            console.log(ex);
        }

    }

    $(document).ready(function () {

        $('#amount_to_buy').on('change', calculateAmount);

        $('#btn_approve').on('click', approveToken);

        $('#btn_buy').on('click', buyToken);

        $('#btn_add_liquidity').on('click', addLiquidity);

        $('#btn_user_withdraw_sale_token').on('click', userWithdrawSaleToken);

        $('#btn_user_force_fail').on('click', userForceFail);

        $('#btn_admin_force_fail').on('click', adminForceFail);

        $('#btn_user_withdraw_base_token').on('click', userWithdrawBaseToken);

        $('#btn_owner_withdraw_sale_token').on('click', ownerWithdrawSaleToken);

        $('#btn_update_limit_per_buyer').on('click', updateLimitPerBuyer);

        $('#btn_update_time').on('click', updateTime);

        $('#btn_update_whitelist_flag').on('click', updateWhitelistFlag);

        $('#btn_update_whitelist_user').on('click', editWhitelist);

        $('#btn_approve_zero_token').on('click', approveZeroToken);

        $('#btn_register_zero_round').on('click', registerZeroRound);


    });

</script>