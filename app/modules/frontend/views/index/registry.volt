<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Registry</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <form action="" method="post">
                        <h5 class="header-title mb-4 mt-0">Registry</h5>
                        <div class="general-label">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Registry Info</h5>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="min_withdraw">Min Withdraw ({{ siteCoinTicker }})</label>
                                        <input class="form-control" type="text" value="{{ registry['min_withdraw'] }}" id="min_withdraw" name="min_withdraw">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="fee_withdraw">Fee Withdraw ({{ siteCoinTicker }})</label>
                                        <input class="form-control" type="text" name="fee_withdraw" value="{{ registry['fee_withdraw'] }}" id="fee_withdraw">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="auto_withdraw_amount">Auto Withdraw Amount ({{ siteCoinTicker }})</label>
                                        <input class="form-control" type="text" name="auto_withdraw_amount" value="{{ registry['auto_withdraw_amount'] }}" id="auto_withdraw_amount">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="min_staking">Min Staking ($)</label>
                                        <input class="form-control" type="text" value="{{ registry['min_staking'] }}" id="min_staking" name="min_staking">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="dex_pair_address">Dex Pair Address</label>
                                        <a target="_blank" class="form-control" type="text" href="{{ helper.getLinkAddress(registry['dex_pair']['address']) }}" id="dex_pair_address">{{ registry['dex_pair']['address'] }}</a>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="bnb_rate">BNB rate</label>
                                        <input class="form-control" type="text" name="bnb_rate" value="{{ helper.numberFormat(registry['bnb_rate']) }}" id="bnb_rate" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="eth_rate">ETH Rate</label>
                                        <input class="form-control" type="text" name="eth_rate" value="{{ helper.numberFormat(registry['eth_rate']) }}" id="eth_rate" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Pool Info</h5>
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
                        <button type="submit" class="btn btn-success" id="btn-approve">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->