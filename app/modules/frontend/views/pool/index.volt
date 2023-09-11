<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Pool</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List Pool</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="status" id="presale-status" class="form-control">
                                    <option value="">Status</option>
                                    {% for key,item in listPoolStatusWithName %}
                                        <option value="{{ item }}" {{ dataGet['status'] == item ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <input placeholder="Token or address" id="search" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                                <input class="btn btn-danger" type="submit" name="export" value="Export"/>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Token</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Time</th>
                                <th class="border-top-0">Status</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                        <div>Network: <b>{{ item['network']|upper }}</b></div>
                                        <div>Base Token Symbol: <b>{{ item['base_token_symbol'] }}</b></div>
                                        <div>Sale Token Symbol: <b>{{ item['pool_token_symbol'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>
                                            <label for="base-token-address-{{ key }}">Presale owner: </label>
                                            <a href="{{ helper.getLinkAddress(item['pool_owner_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['pool_owner_address'] }}</b></a>
                                        </div>
                                        <div>
                                            <label for="base-token-address-{{ key }}">Pool token: </label>
                                            <b>{{ item['pool_token_symbol'] }}</b>
                                            <a href="{{ helper.getLinkAddress(item['pool_token_address'], item['platform'], item['network']) }}" target="_blank">
                                                <b>{{ item['pool_token_address'] }}</b>
                                            </a>
                                        </div>
                                        <div>
                                            <label for="base-token-address-{{ key }}">Base token: </label>
                                            <b>{{ item['base_token_symbol'] }}</b>
                                            <a href="{{ helper.getLinkAddress(item['base_token_address'], item['platform'], item['network']) }}" target="_blank">
                                                <b>{{ item['base_token_address'] }}</b>
                                            </a>
                                        </div>
                                        <div>
                                            <label>Contract address: </label><a href="{{ helper.getLinkAddress(item['contract_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['contract_address'] }}</b></a>
                                        </div>
                                        <div>
                                            <label>Hash: </label><a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Sale Amount: <b>{{ helper.numberFormat(item['amount'], 4) }}</b></div>
                                        <div>Creation Fee: <b>{{ helper.numberFormat(item['creation_fee'], 4) }}</b></div>
                                        <div>Hard cap: <b>{{ helper.numberFormat(item['hard_cap'], 4) }}</b></div>
                                        <div>Sale Price: <b>{{ helper.numberFormat(item['token_price'], 4) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Created: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Start: <b>{{ date('d/m/Y H:i:s', item['start_time']) }}</b></div>
                                        <div>End: <b>{{ date('d/m/Y H:i:s', item['end_time']) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Zero Round: <b>{{ item['active_zero_round'] ? 'TRUE' : 'FALSE' }}</b></div>
                                        <div>First Round: <b>{{ item['active_first_round'] ? 'TRUE' : 'FALSE' }}</b></div>
                                        <div>Auction Round: <b>{{ item['active_auction_round'] ? 'TRUE' : 'FALSE' }}</b></div>
                                        <div>Active Claim: <b>{{ item['is_active_claim'] ? 'TRUE' : 'FALSE' }}</b></div>
                                        <div>First Round Length: <b>{{ item['first_round_length'] }}</b></div>
                                        <div>Current Round: <b>{{ item['current_round'] }}</b></div>
                                        <div>Pool Status: <b>{{ listPoolStatusWithName[item['current_status']] ? listPoolStatusWithName[item['current_status']] : null }}</b></div>
                                    </td>
                                    <td>
                                        <div>
                                            <a class="btn btn-info btn-sm" href="/pool/detail/{{ item['_id'] }}">Detail</a>
                                            <a class="btn btn-info btn-sm" href="/pool/update/{{ item['_id'] }}">Update</a>
                                        </div>
                                        <div class="mt-2">
                                            <a class="btn btn-success btn-sm" href="/pool/buy_log/{{ item['_id'] }}">Buy Log</a>
                                            <a class="btn btn-success btn-sm" href="/pool/user_log/{{ item['_id'] }}">User Log</a>
                                        </div>
                                        <div class="mt-2">
                                            <a class="btn btn-success btn-sm" href="/pool/user_zero_round/{{ item['_id'] }}">User Zero Round</a>
                                            <a class="btn btn-success btn-sm" href="/pool/whitelist_user/{{ item['_id'] }}">Whitelist User</a>
                                        </div>
                                        <div class="mt-2">
                                            {% if item['is_show'] == activeFlag %}
                                                <button class="btn btn-danger btn-sm is-show-button" data-show="1" data-url="/pool/set_show/{{ item['_id'] }}?is_show=0">Hidden Pool</button>
                                            {% else %}
                                                <button class="btn btn-success btn-sm is-show-button" data-show="0" data-url="/pool/set_show/{{ item['_id'] }}?is_show=1">Show Pool</button>
                                            {% endif %}
                                        </div>
                                    </td>
                                </tr>
                            {% endfor %}

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 mb-2">
                        {% include 'layouts/paging.volt' %}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
{% include 'common/plugins/toastr.volt' %}
<script type="text/javascript">
    $(document).ready(function () {
        $('.is-show-button').click(function () {
            let isShow = $(this).attr('data-show');
            let message = 'Are you sure to show this?'
            if (isShow == '1') {
                message = 'Are you sure to hidden this?'
            }
            let ok = confirm(message);
            let url = $(this).attr('data-url');
            if (ok) {
                window.location.href = url;
            }
        });
    });
</script>