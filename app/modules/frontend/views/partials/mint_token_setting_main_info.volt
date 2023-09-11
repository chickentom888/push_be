<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Setting Main Info</h5>

                <div class="general-label">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="creation-fee">Creation fee ({{ mainCurrency|upper }})</label>
                                <input class="form-control" type="text" value="{{ setting['creation_fee'] }}" id="creation-fee">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="total-supply-fee-percent">Total Supply Fee (%)</label>
                                <input class="form-control" type="text" value="{{ setting['total_supply_fee_percent'] }}" id="total-supply-fee-percent">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-fee-address">Fee Address</label>
                                <input class="form-control" type="text" value="{{ setting['token_fee_address'] }}" id="token-fee-address">
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