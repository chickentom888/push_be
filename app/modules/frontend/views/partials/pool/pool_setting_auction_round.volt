<div class="row">
    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Auction Round</h5>

                <div class="general-label">

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Token auction round</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="auction-round-token-address">Token address</label>
                                <input class="form-control" type="text" value="{{ setting['auction_round']['token_address'] }}" id="auction-round-token-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="auction-round-token-name">Token name</label>
                                <input class="form-control" type="text" value="{{ setting['auction_round']['token_name'] }}" id="auction-round-token-name" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="auction-round-token-symbol">Token symbol</label>
                                <input class="form-control" type="text" value="{{ setting['auction_round']['token_symbol'] }}" id="auction-round-token-symbol" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="auction-round-token-decimals">Token decimals</label>
                                <input class="form-control" type="text" value="{{ setting['auction_round']['token_decimals'] }}" id="auction-round-token-decimals" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="auction-round-finish-before-first-round">Finish before first round (second)</label>
                                <input class="form-control" type="text" value="{{ setting['auction_round']['finish_before_first_round'] }}" id="auction-round-finish-before-first-round">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <hr>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-set-auction-round btn-interact-sc">Update</button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>