<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Staking Setting </h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">List Platform</h5>
                    <div class="row">
                        <div class="col-sm-12">
                            {% for key, item in listPlatform %}
                                <a href="/registry/staking_setting/{{ key }}" class="btn btn-{{ platform == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
                            {% endfor %}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Staking Setting Address</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for=staking-setting-address>Staking setting address</label>
                                <a id="staking-setting-address" href="{{ helper.getLinkAddress(stakingSettingAddress, platform, network ) }}" target="_blank" class="form-control">{{ stakingSettingAddress }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="operator-address">Operator address</label>
                                <input class="form-control" type="text" value="{{ setting['operator_address'] }}" id="operator-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="dex-pair-address">Dex pair address</label>
                                <input class="form-control" type="text" value="{{ setting['dex_pair_address'] }}" id="dex-pair-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fee-staking-address">Fee staking address</label>
                                <input class="form-control" type="text" value="{{ setting['fee_staking_address'] }}" id="fee-staking-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fee-swap-address">Fee swap address</label>
                                <input class="form-control" type="text" value="{{ setting['fee_swap_address'] }}" id="fee-swap-address">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-set-address btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Staking Token</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="staking-token-address">Token address</label>
                                <input class="form-control" type="text" value="{{ setting['staking_token']['token_address'] }}" id="staking-token-address" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="staking-token-name">Token name</label>
                                <input class="form-control" type="text" value="{{ setting['staking_token']['token_name'] }}" id="staking-token-name" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="staking-token-symbol">Token symbol</label>
                                <input class="form-control" type="text" value="{{ setting['staking_token']['token_symbol'] }}" id="staking-token-symbol" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="staking-token-decimals">Token decimals</label>
                                <input class="form-control" type="text" value="{{ setting['staking_token']['token_decimals'] }}" id="staking-token-decimals" readonly>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Swap Token</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="swap-token-address">Token address</label>
                                <input class="form-control" type="text" value="{{ setting['swap_token']['token_address'] }}" id="swap-token-address" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="swap-token-name">Token name</label>
                                <input class="form-control" type="text" value="{{ setting['swap_token']['token_name'] }}" id="swap-token-name" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="swap-token-symbol">Token symbol</label>
                                <input class="form-control" type="text" value="{{ setting['swap_token']['token_symbol'] }}" id="swap-token-symbol" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="swap-token-decimals">Token decimals</label>
                                <input class="form-control" type="text" value="{{ setting['swap_token']['token_decimals'] }}" id="swap-token-decimals" readonly>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Pool Info</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Pool Info</h5>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pool-info-dex-pair-address">Pool address</label>
                                <a id="staking-setting-address" href="{{ helper.getLinkAddress(registry['dex_pair']['address'], platform, network ) }}" target="_blank" class="form-control">{{ registry['dex_pair']['address'] }}</a>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="staking_token_name">Staking Token Name</label>
                                <input class="form-control" type="text" value="{{ registry['dex_pair']['staking_token']['token_name'] }}" id="staking_token_name" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="staking_token_balance">Staking Token Balance</label>
                                <input class="form-control" type="text" value="{{ helper.numberFormat(registry['dex_pair']['staking_token_balance'], 2) }}" id="staking_token_balance" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="swap_token_name">Swap Token Name</label>
                                <input class="form-control" type="text" value="{{ registry['dex_pair']['swap_token']['token_name'] }}" id="swap_token_name" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="swap_token_balance">Swap Token Balance</label>
                                <input class="form-control" type="text" value="{{ helper.numberFormat(registry['dex_pair']['swap_token_balance'], 2) }}" id="swap_token_balance" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="price">Price ($)</label>
                                <input class="form-control" type="text" value="{{ helper.numberFormat(registry['dex_pair']['price'], 2) }}" id="price" disabled>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>


    <!-- end row -->

</div>

<input type="hidden" data-platform="{{ platform }}" data-network="{{ network }}" id="setting_platform_network">

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    async function setAddress() {
        try {
            await checkConnect();

            let stakingSettingAddress = $('#staking-setting-address').text();
            let operatorAddress = $('#operator-address').val();
            let dexPairAddress = $('#dex-pair-address').val();
            let feeStakingAddress = $('#fee-staking-address').val();
            let feeSwapAddress = $('#fee-swap-address').val();
            let stakingSettingContract = new web3.eth.Contract(ABI_STAKING, stakingSettingAddress);
            let result = await stakingSettingContract.methods.setAddress(operatorAddress, dexPairAddress, feeStakingAddress, feeSwapAddress).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                toastr["success"]("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
                toastr["success"]("Success");
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
        $('.btn-set-address').on('click', setAddress);
    });

</script>