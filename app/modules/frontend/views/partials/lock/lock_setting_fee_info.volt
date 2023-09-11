<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Lock Fee Info</h5>

                <div class="general-label">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="base-fee">Base Fee ({{ mainCurrency|upper }})</label>
                                <input value="{{ setting['base_fee'] }}" class="form-control" id="base-fee">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-fee-percent">Token Fee Percent (%)</label>
                                <input class="form-control" type="text" value="{{ setting['token_fee_percent'] }}" id="token-fee-percent">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="address-fee">Address Fee</label>
                                <input class="form-control" type="text" value="{{ setting['address_fee'] }}" id="address-fee">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-fee btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>