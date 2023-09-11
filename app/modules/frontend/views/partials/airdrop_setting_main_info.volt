<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Setting Main Info</h5>

                <div class="general-label">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fee-amount">Fee Amount ({{ mainCurrency|upper }})</label>
                                <input class="form-control" type="text" value="{{ setting['fee_amount'] }}" id="fee-amount">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fee-address">Fee Address</label>
                                <input class="form-control" type="text" value="{{ setting['fee_address'] }}" id="fee-address">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-airdrop-setting btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>