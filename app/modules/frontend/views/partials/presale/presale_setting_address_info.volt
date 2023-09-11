<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Setting Address Info</h5>

                <div class="general-label">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="base-fee-address">Base fee address</label>
                                <input value="{{ setting['setting']['base_fee_address'] }}" class="form-control" id="base-fee-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-fee-address">Token fee address</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['token_fee_address'] }}" id="token-fee-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="admin-address">Admin address</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['admin_address'] }}" id="admin-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="wrap-token-address">Main wrap token address</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['wrap_token_address'] }}" id="wrap-token-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="dex-locker-address">Dex locker address</label>
                                <input class="form-control" type="text" value="{{ setting['setting']['dex_locker_address'] }}" id="dex-locker-address">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-setting-address btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>