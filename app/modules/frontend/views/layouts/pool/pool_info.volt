<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Pool Info</h5>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Token</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Time</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div>Platform: <b>{{ pool['platform']|upper }}</b></div>
                                    <div>Network: <b>{{ pool['network']|upper }}</b></div>
                                    <div>Base Token Name: <b>{{ pool['base_token_name'] }}</b></div>
                                    <div>Base Token Symbol: <b>{{ pool['base_token_symbol'] }}</b></div>
                                    <div>Pool Token Name: <b>{{ pool['pool_token_name'] }}</b></div>
                                    <div>Pool Token Symbol: <b>{{ pool['pool_token_symbol'] }}</b></div>
                                </td>
                                <td>
                                    <div>
                                        <label for="base-token-address">pool owner: </label>
                                        <a href="{{ helper.getLinkAddress(pool['pool_owner_address'], pool['platform'], pool['network']) }}"
                                           target="_blank"><b>{{ pool['pool_owner_address'] }}</b></a>
                                    </div>
                                    <div>
                                        <label for="base-token-address">Pool token: </label>
                                        <a href="{{ helper.getLinkAddress(pool['pool_token_address'], pool['platform'], pool['network']) }}"
                                           target="_blank"><b>{{ pool['pool_token_address'] }}</b></a>
                                    </div>
                                    <div>
                                        <label for="base-token-address">Base token: </label><a
                                                href="{{ helper.getLinkAddress(pool['base_token_address'], pool['platform'], pool['network']) }}"
                                                target="_blank"><b>{{ pool['base_token_address'] }}</b></a>
                                    </div>
                                    <div>
                                        <label for="contract_address">Contract: </label>
                                        <a href="{{ helper.getLinkAddress(pool['contract_address'], pool['platform'], pool['network']) }}"
                                           target="_blank" id="contract_address"><b>{{ pool['contract_address'] }}</b></a>
                                    </div>
                                </td>
                                <td>
                                    <div>Pool Amount: <b>{{ helper.numberFormat(pool['amount'], 4) }}</b></div>
                                    <div>Creation Fee: <b>{{ helper.numberFormat(pool['creation_fee'], 4) }}</b></div>
                                    <div>Hard cap: <b>{{ helper.numberFormat(pool['hard_cap'], 4) }}</b></div>
                                    <div>Pool Price: <b>{{ helper.numberFormat(pool['token_price'], 4) }}</b></div>
                                </td>
                                <td>
                                    <div>Created: <b>{{ date('d/m/Y H:i:s', pool['created_at']) }}</b></div>
                                    <div>Start: <b>{{ date('d/m/Y H:i:s', pool['start_time']) }}</b></div>
                                    <div>End: <b>{{ date('d/m/Y H:i:s', pool['end_time']) }}</b></div>
                                </td>
                                <td>
                                    <div>Zero Round: <b>{{ pool['active_zero_round'] ? 'TRUE' : 'FALSE' }}</b></div>
                                    <div>Auction Round: <b>{{ pool['active_auction_round'] ? 'TRUE' : 'FALSE' }}</b></div>
                                    <div>First Round: <b>{{ pool['active_first_round'] ? 'TRUE' : 'FALSE' }}</b></div>
                                    {% if pool['active_first_round'] %}
                                        <div>First Round Length: <b>{{ pool['first_round_length'] }}</b></div>
                                    {% endif %}
                                    <div>Active Claim: <b>{{ pool['is_active_claim'] ? 'TRUE' : 'FALSE' }}</b></div>
                                    <div>Current Round: <b>{{ pool['current_round'] }}</b></div>
                                    <div>Pool Status: <b>{{ listPoolStatusWithName[pool['current_status']] ? listPoolStatusWithName[pool['current_status']] : null }}</b></div>
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
