<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Setting Main Info</h5>

                <div class="general-label">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="base-fee-percent">Base token fee (%)</label>
                                <input value="{{ setting['setting']['base_fee_percent'] }}" class="form-control" id="base-fee-percent">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-fee-percent">Sale token fee (%)</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['token_fee_percent'] }}" id="token-fee-percent">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="creation-fee">Creation fee ({{ mainCurrency|upper }})</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['creation_fee'] }}" id="creation-fee">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="first-round-length">First round length (second)</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['first_round_length'] }}" id="first-round-length">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="max-presale-length">Max presale length (second)</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['max_presale_length'] }}" id="max-presale-length">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="min-liquidity-percent">Min liquidity percent (%)</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['min_liquidity_percent'] }}" id="min-liquidity-percent">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="min-lock-period">Min lock period (second)</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['min_lock_period'] }}" id="min-lock-period">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="max-success-to-liquidity">Max success to liquidity (second)</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['max_success_to_liquidity'] }}" id="max-success-to-liquidity">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-setting-main btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>