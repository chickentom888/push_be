<div class="container-fluid">

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Presale Info</h5>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Token</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Cap</th>
                                <th class="border-top-0">Price</th>
                                <th class="border-top-0">Time</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div>Platform: <b>{{ presale['platform']|upper }}</b></div>
                                    <div>Network: <b>{{ presale['network']|upper }}</b></div>
                                    <div>Base Token Name: <b>{{ presale['base_token_name'] }}</b></div>
                                    <div>Base Token Symbol: <b>{{ presale['base_token_symbol'] }}</b></div>
                                    <div>Sale Token Name: <b>{{ presale['sale_token_name'] }}</b></div>
                                    <div>Sale Token Symbol: <b>{{ presale['sale_token_symbol'] }}</b></div>
                                </td>
                                <td>
                                    <div>
                                        <label for="base-token-address-{{ key }}">Presale owner: </label>
                                        <a href="{{ helper.getLinkAddress(presale['presale_owner_address'], presale['platform'], presale['network']) }}"
                                           target="_blank"><b>{{ presale['presale_owner_address'] }}</b></a>
                                    </div>
                                    <div>
                                        <label for="base-token-address-{{ key }}">Sale token: </label>
                                        <a href="{{ helper.getLinkAddress(presale['sale_token_address'], presale['platform'], presale['network']) }}"
                                           target="_blank"><b>{{ presale['sale_token_address'] }}</b></a>
                                    </div>
                                    <div>
                                        <label for="base-token-address-{{ key }}">Base token: </label><a
                                                href="{{ helper.getLinkAddress(presale['base_token_address'], presale['platform'], presale['network']) }}"
                                                target="_blank"><b>{{ presale['base_token_address'] }}</b></a>
                                    </div>
                                    <div>
                                        <label for="base-token-address-{{ key }}">Dex factory: </label><a
                                                href="{{ helper.getLinkAddress(presale['dex_factory_address'], presale['platform'], presale['network']) }}"
                                                target="_blank"><b>{{ presale['dex_factory_address'] }}</b></a>
                                    </div>
                                </td>
                                <td>

                                    <div>Sale Amount: {{ helper.numberFormat(presale['amount'], 4) }}</div>
                                    <div>Sale Token Liquidity Amount: {{ helper.numberFormat(presale['sale_token_liquidity_amount'], 4) }}</div>
                                    <div>Sale Token Fee Amount: {{ helper.numberFormat(presale['sale_token_fee_amount'], 4) }}</div>

                                </td>

                                <td>

                                    <div>Hard cap: {{ helper.numberFormat(presale['hard_cap'], 4) }}</div>
                                    <div>Soft cap: {{ helper.numberFormat(presale['soft_cap'], 4) }}</div>
                                    <div>Base token fee: {{ helper.numberFormat(presale['base_fee_amount'], 4) }}</div>
                                    <div>Base liquidity fee: {{ helper.numberFormat(presale['base_token_liquidity_amount'], 4) }}</div>

                                </td>

                                <td>
                                    <div>Sale Price: {{ helper.numberFormat(presale['token_price'], 4) }}</div>
                                    <div>Listing Price: {{ helper.numberFormat(presale['listing_price'], 4) }}</div>
                                    <div>Listing Price Percent: {{ helper.numberFormat(presale['listing_price_percent'], 4) }}</div>
                                </td>
                                <td>
                                    <div>Created At: {{ date('d/m/Y H:i:s', presale['created_at']) }}</div>
                                    <div>Start: {{ date('d/m/Y H:i:s', presale['start_time']) }}</div>
                                    <div>End: {{ date('d/m/Y H:i:s', presale['end_time']) }}</div>
                                </td>
                                <td>
                                    <div>Active Zero Round: {{ presale['active_zero_round'] ? 'TRUE' : 'FALSE' }}</div>
                                    <div>Active First Round: {{ presale['active_first_round'] ? 'TRUE' : 'FALSE' }}</div>
                                    <div>First Round Length: {{ presale['first_round_length'] ? 'TRUE' : 'FALSE' }}</div>
                                    <div>Current Round: {{ helper.getCurrentRound(presale) }}</div>
                                    <div>Presale
                                        Status: {{ listPresaleStatus[presale['current_status']] ? listPresaleStatus[presale['current_status']] : null }}</div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>