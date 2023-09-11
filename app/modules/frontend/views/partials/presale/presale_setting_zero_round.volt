<div class="row">
    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Zero Round</h5>

                <div class="general-label">

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Token zero round</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-token-address">Token address</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['token_address'] }}" id="zero-round-token-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-token-name">Token name</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['token_name'] }}" id="zero-round-token-name" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-token-symbol">Token symbol</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['token_symbol'] }}" id="zero-round-token-symbol" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-token-decimals">Token decimals</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['token_decimals'] }}" id="zero-round-token-decimals" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-token-amount">Token amount</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['token_amount'] }}" id="zero-round-token-amount">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <hr>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Zero round info</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-finish-before-first-round">Finish before first round (second)</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['finish_before_first_round'] }}" id="zero-round-finish-before-first-round">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="zero-round-percent">Hard cap percent (%)</label>
                                <input class="form-control" type="text" value="{{ setting['zero_round']['percent'] }}" id="zero-round-percent">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-set-zero-round btn-interact-sc">Update</button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>