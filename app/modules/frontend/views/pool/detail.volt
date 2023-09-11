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

                    <h5 class="header-title mb-4 mt-0">Pool Detail</h5>
                    {% if pool %}
                        <div class="general-label">
                            <form role="form" method="post" action="">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h5>Thông tin</h5>
                                        <div class="form-group">
                                            <label for="pool_token_address">Pool Token Address</label>
                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(pool['pool_token_address'], pool['platform'], pool['network']) }}"
                                               target="_blank"><b>{{ pool['pool_token_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="pool_token_name">Pool token name</label>
                                            <input class="form-control" type="text" name="pool_token_name"
                                                   value="{{ pool['pool_token_name'] }}"
                                                   id="pool_token_name" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="pool_token_symbol">Pool token symbol</label>
                                            <input class="form-control" type="text" name="pool_token_symbol"
                                                   value="{{ pool['pool_token_symbol'] }}"
                                                   id="pool_token_symbol" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="pool_token_decimals">Pool token decimals</label>
                                            <input class="form-control" type="text" name="pool_token_decimals"
                                                   value="{{ pool['pool_token_decimals'] }}" id="pool_token_decimals"
                                                   readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_address">Base Token Address</label>
                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(pool['base_token_address'], pool['platform'], pool['network']) }}"
                                               target="_blank"><b>{{ pool['base_token_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_name">Base token name</label>
                                            <input class="form-control" type="text" name="base_token_name"
                                                   value="{{ pool['base_token_name'] }}"
                                                   id="base_token_name" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_symbol">Base token symbol</label>
                                            <input class="form-control" type="text" name="base_token_symbol"
                                                   value="{{ pool['base_token_symbol'] }}"
                                                   id="base_token_symbol" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_decimals">Base token decimals</label>
                                            <input class="form-control" type="text" name="base_token_decimals"
                                                   value="{{ pool['base_token_decimals'] }}" id="base_token_decimals"
                                                   readonly>
                                        </div>

                                    </div>

                                    <div class="col-sm-6">
                                        <h5>Cấu hình</h5>

                                        <div class="form-group">
                                            <label for="pool_token_amount">{{ pool['pool_token_symbol'] }} amount
                                                pool?</label>
                                            <input class="form-control" type="text" name="pool_token_amount"
                                                   value="{{ pool['amount'] }}"
                                                   id="pool_token_amount" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="soft_cap">Soft Cap ({{ pool['base_token_symbol'] }})</label>
                                            <input class="form-control" type="text" name="soft_cap"
                                                   value="{{ pool['soft_cap'] }}" id="soft_cap"
                                                   readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="hard_cap">Hard Cap ({{ pool['base_token_symbol'] }})</label>
                                            <input class="form-control" type="text" name="hard_cap"
                                                   value="{{ pool['hard_cap'] }}" id="hard_cap"
                                                   readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="token_price">pool Rate (1 {{ pool['base_token_symbol'] }}
                                                =
                                                ? {{ pool['pool_token_symbol'] }})</label>
                                            <input class="form-control" type="text" name="token_price"
                                                   value="{{ pool['token_price'] }}"
                                                   id="token_price" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="created_at">Created at</label>
                                            <input class="form-control" type="text" name="created_at"
                                                   value="{{ date('d/m/Y H:i:s', pool['created_at']) }}"
                                                   id="created_at" readonly>
                                        </div>

                                    </div>

                                </div>

                                <div class="row">
                                    {% if pool['active_zero_round'] %}
                                        <div class="col-sm-6">
                                            <h5>Zero Round</h5>
                                            <div class="form-group">
                                                <label for="zero_round_token_address">Token Address</label>

                                                <a class="form-control zero_round_token_address" readonly
                                                   href="{{ pool['zero_round']['token_address'] ? helper.getLinkAddress(pool['zero_round']['token_address'], pool['platform'], pool['network']) : '' }}"
                                                   target="_blank"><b>{{ pool['zero_round']['token_address'] }}</b></a>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_token_name">Token Name</label>
                                                <input class="form-control" type="text" name="zero_round_token_name"
                                                       value="{{ pool['zero_round']['token_name'] }}"
                                                       id="zero_round_token_name" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_token_symbol">Token Symbol</label>
                                                <input class="form-control" type="text" name="zero_round_token_symbol"
                                                       value="{{ pool['zero_round']['token_symbol'] }}"
                                                       id="zero_round_token_symbol" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_token_amount">Token Amount</label>
                                                <input class="form-control" type="text" name="zero_round_token_amount"
                                                       value="{{ pool['zero_round']['token_amount'] }}"
                                                       id="zero_round_token_amount" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_token_decimals">Token Decimals</label>
                                                <input class="form-control" type="text" name="zero_round_token_decimals"
                                                       value="{{ pool['zero_round']['token_decimals'] }}"
                                                       id="zero_round_token_decimals" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_finish_at">Finish at</label>
                                                <input class="form-control" type="text" name="zero_round_finish_at"
                                                       value="{{ date('d/m/Y H:i:s', pool['zero_round']['finish_at']) }}"
                                                       id="zero_round_finish_at" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_finish_before_first_round">Finish before first
                                                    round</label>
                                                <input class="form-control" type="text"
                                                       name="zero_round_finish_before_first_round"
                                                       value="{{ pool['zero_round']['finish_before_first_round'] }}"
                                                       id="zero_round_finish_before_first_round" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_percent">Hard Cap Percent</label>
                                                <input class="form-control" type="text" name="zero_round_percent"
                                                       value="{{ pool['zero_round']['percent'] }}"
                                                       id="zero_round_percent" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_max_base_token_amount">Max Base Token
                                                    Amount</label>
                                                <input class="form-control" type="text"
                                                       name="zero_round_max_base_token_amount"
                                                       value="{{ pool['zero_round']['max_base_token_amount'] }}"
                                                       id="zero_round_max_base_token_amount"
                                                       readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_max_slot">Max Slot</label>
                                                <input class="form-control" type="text" name="zero_round_max_slot"
                                                       value="{{ pool['zero_round']['max_slot'] }}"
                                                       id="zero_round_max_slot" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label for="zero_round_registered_slot">Registered Slot</label>
                                                <input class="form-control" type="text"
                                                       name="zero_round_registered_slot"
                                                       value="{{ pool['zero_round']['registered_slot'] }}"
                                                       id="zero_round_registered_slot" readonly>
                                            </div>

                                        </div>
                                    {% elseif pool['active_auction_round'] %}
                                    {% endif %}
                                    <div class="col-sm-6">

                                        <h5>Address</h5>

                                        <div class="form-group">
                                            <label for="contract_address">Contract Address</label>

                                            <a class="form-control" readonly id="contract_address"
                                               href="{{ helper.getLinkAddress(pool['contract_address'], pool['platform'], pool['network']) }}"
                                               target="_blank"><b>{{ pool['contract_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="pool_generator">pool Generator</label>

                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(pool['pool_generator'], pool['platform'], pool['network']) }}"
                                               target="_blank"
                                               id="pool_generator"><b>{{ pool['pool_generator'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="pool_owner_address">pool Owner Address</label>

                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(pool['pool_owner_address'], pool['platform'], pool['network']) }}"
                                               target="_blank"
                                               id="pool_owner_address"><b>{{ pool['pool_owner_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="wrap_token_address">Wrap token address</label>
                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(pool['wrap_token_address'], pool['platform'], pool['network']) }}"
                                               target="_blank"
                                               id="wrap_token_address"><b>{{ pool['wrap_token_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="creation_fee">Creation fee</label>
                                            <input class="form-control" type="text" name="creation_fee"
                                                   value="{{ pool['creation_fee'] }}"
                                                   id="creation_fee" readonly>
                                        </div>

                                    </div>

                                    <div class="col-sm-6">
                                        <h5>General Info</h5>

                                        <div class="form-group">
                                            <label for="contract_version">Contract Version</label>
                                            <input class="form-control" type="text" name="contract_version"
                                                   value="{{ pool['contract_version'] }}"
                                                   id="contract_version" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="pool_in_main_token">pool in main token</label>
                                            <input class="form-control" type="text" name="pool_in_main_token"
                                                   value="{{ pool['pool_in_main_token'] ? 'True' : 'False' }}"
                                                   id="pool_in_main_token" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="whitelist_only">Whitelist only</label>
                                            <select name="whitelist_only" id="whitelist_only" class="form-control">
                                                <option value="1" {{ pool['whitelist_only'] == true ? 'selected' : '' }}>
                                                    True
                                                </option>
                                                <option value="0" {{ pool['whitelist_only'] == false ? 'selected' : '' }}>
                                                    False
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="force_failed">Force Failed</label>
                                            <input class="form-control" type="text" name="force_failed"
                                                   value="{{ pool['force_failed'] ? 'True' : 'False' }}"
                                                   id="force_failed" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="first_round_length">First Round Length (second)</label>
                                            <input class="form-control" type="text" name="first_round_length"
                                                   value="{{ pool['first_round_length'] }}"
                                                   id="first_round_length" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="message">Message</label>
                                            <input class="form-control" type="text" name="message"
                                                   value="{{ pool['message'] }}" id="message" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="owner_withdraw_pool_token">Owner withdraw pool token</label>
                                            <input class="form-control" type="text" name="owner_withdraw_pool_token"
                                                   value="{{ pool['owner_withdraw_pool_token'] }}"
                                                   id="owner_withdraw_pool_token" readonly>
                                        </div>

                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-sm-6">
                                        <h5>Token Sold</h5>

                                        <div class="form-group">
                                            <label for="total_base_collected">Total Base Collected</label>
                                            <input class="form-control" type="text" name="total_base_collected"
                                                   value="{{ pool['total_base_collected'] }}"
                                                   id="total_base_collected" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="total_token_sold">Total Token Sold</label>
                                            <input class="form-control" type="text" name="total_token_sold"
                                                   value="{{ pool['total_token_sold'] }}"
                                                   id="total_token_sold" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="total_base_withdrawn">Total Base Withdrawn</label>
                                            <input class="form-control" type="text" name="total_base_withdrawn"
                                                   value="{{ pool['total_base_withdrawn'] }}"
                                                   id="total_base_withdrawn" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="total_token_withdrawn">Total Token Withdrawn</label>
                                            <input class="form-control" type="text" name="total_token_withdrawn"
                                                   value="{{ pool['total_token_withdrawn'] }}"
                                                   id="total_token_withdrawn" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="current_round">Current Round</label>
                                            <input class="form-control" type="text" name="current_round"
                                                   value="{{ pool['current_round'] }}"
                                                   id="current_round" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="current_status">Current Status</label>
                                            <input class="form-control" type="text" name="current_status"
                                                   value="{{ listPoolStatusWithName[pool['current_status']] }}"
                                                   id="current_status" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="success_at">Success At</label>
                                            <input class="form-control" type="text" name="success_at"
                                                   value="{{ date('d/m/Y H:i:s', pool['success_at']) }}"
                                                   id="success_at" readonly>
                                        </div>

                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <h5>Round 0</h5>
                                            <div class="form-group">
                                                <label for="active_zero_round">Active Zero Round</label>
                                                <select name="active_zero_round" class="form-control"
                                                        id="active_zero_round"
                                                        readonly="" disabled>
                                                    <option value="0" {{ pool['active_zero_round'] == false ? 'selected' : '' }}>
                                                        No
                                                    </option>
                                                    <option value="1" {{ pool['active_zero_round'] == true ? 'selected' : '' }}>
                                                        Yes
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <h5>Round 1</h5>
                                            <div class="form-group">
                                                <label for="active_first_round">Active First Round</label>
                                                <select name="active_first_round" class="form-control"
                                                        id="active_first_round" readonly="" disabled>
                                                    <option value="0" {{ pool['active_first_round'] == false ? 'selected' : '' }}>
                                                        No
                                                    </option>
                                                    <option value="1" {{ pool['active_first_round'] == true ? 'selected' : '' }}>
                                                        Yes
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <h5>Auction Round</h5>
                                            <div class="form-group">
                                                <label for="active_auction_round">Active Auction</label>
                                                <select name="active_auction_round" class="form-control"
                                                        id="active_auction_round" readonly="" disabled>
                                                    <option value="0" {{ pool['active_auction_round'] == false ? 'selected' : '' }}>
                                                        No
                                                    </option>
                                                    <option value="1" {{ pool['active_auction_round'] == true ? 'selected' : '' }}>
                                                        Yes
                                                    </option>
                                                </select>
                                                {% if pool['active_auction_round'] %}
                                                    <div>
                                                        <label for="start_time">Start Time</label>
                                                        <input class="form-control" type="text" name="start_time"
                                                               value="{{ date('d/m/Y H:i:s', pool['auction_round']['start_time']) }}"
                                                               id="start_time" readonly>
                                                    </div>
                                                    <div>
                                                        <label for="end_time">End Time</label>
                                                        <input class="form-control" type="text" name="end_time"
                                                               value="{{ date('d/m/Y H:i:s', pool['auction_round']['end_time']) }}"
                                                               id="start_time" readonly>
                                                    </div>
                                                    <div>
                                                        <label for="registered_slot">Register Slot</label>
                                                        <input class="form-control" type="text" name="registered_slot"
                                                               value="{{ pool['auction_round']['registered_slot'] }}"
                                                               id="registered_slot" readonly>
                                                    </div>
                                                    <div>
                                                        <label for="token_name">Token Name</label>
                                                        <input class="form-control" type="text" name="token_name"
                                                               value="{{ pool['auction_round']['token_name'] }}"
                                                               id="token_name" readonly>
                                                    </div>
                                                    <div>
                                                        <label for="token_symbol">Token Symbol</label>
                                                        <input class="form-control" type="text" name="token_symbol"
                                                               value="{{ pool['auction_round']['token_symbol'] }}"
                                                               id="token_symbol" readonly>
                                                    </div>
                                                    <div>
                                                        <label for="token_address">Token Address</label>
                                                        <a class="form-control" readonly
                                                           href="{{ helper.getLinkAddress(pool['auction_round']['token_address'], pool['platform'], pool['network']) }}"
                                                           target="_blank"><b>{{ pool['auction_round']['token_address'] }}</b></a>
                                                    </div>
                                                {% endif %}
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row mt-5">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-danger" id="btn-admin-force-fail">Admin
                                                Force Fail
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    {% else %}
                        <div class="text-center">
                            <p class="m-t-10">No Record Available</p>
                        </div>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
{% include 'common/plugins/toastr.volt' %}
<script type="text/javascript">
    async function adminForceFail() {
        try {
            await checkConnect();
            let poolContractAddress = $('#contract_address').text();
            let poolContract = new web3.eth.Contract(ABI_POOL, poolContractAddress);
            let result = await poolContract.methods.forceFailByAdmin().send({
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

    async function checkConnect() {
        if (provider == null) {
            init();
            await onConnect();
            return false;
        }
    }

    $(document).ready(function () {
        $('#btn-admin-force-fail').on('click', adminForceFail);

        $("a").each(function () {
            if ($(this).attr('href') === '') {
                $(this).removeAttr("href");
                $(this).html('&nbsp;')
            }
        })
    });

</script>