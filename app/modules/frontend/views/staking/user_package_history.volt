<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">User Package History</h4>
            </div>
        </div>
    </div>
</div>

{% if userPackage %}
    {% include 'layouts/staking/user_package_info.volt' %}
{% endif %}

<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">User Package History</h5>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label for="user-address">Address</label>
                                <input placeholder="Address" id="user-address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="hash">Hash</label>
                                <input placeholder="Hash" id="hash" type="text" class="form-control" name="hash" value="{{ dataGet['hash'] }}">
                            </div>


                            <div class="col-sm-2 form-group">
                                <label for="contract_id">Contract ID</label>
                                <input placeholder="Contract ID" id="contract_id" type="text" class="form-control" name="contract_id" value="{{ dataGet['contract_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="code">Code</label>
                                <input placeholder="Code" id="code" type="text" class="form-control" name="code" value="{{ dataGet['code'] }}">
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="is_direct_bonus">Direct bonus</label>
                                    <select name="is_direct_bonus" id="is_direct_bonus" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['is_direct_bonus']|length AND dataGet['is_direct_bonus'] == '0') ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ (dataGet['is_direct_bonus']|length AND dataGet['is_direct_bonus'] == '1') ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="is_team_bonus">Team bonus</label>
                                    <select name="is_team_bonus" id="is_team_bonus" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['is_team_bonus']|length AND dataGet['is_team_bonus'] == '0') ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ (dataGet['is_team_bonus']|length AND dataGet['is_team_bonus'] == '1') ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="is_staking_token">Payment</label>
                                    <select name="is_staking_token" id="is_staking_token" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['is_staking_token']|length AND dataGet['is_staking_token'] == '0') ? 'selected' : '' }}>Staking Token</option>
                                        <option value="1" {{ (dataGet['is_staking_token']|length AND dataGet['is_staking_token'] == '1') ? 'selected' : '' }}>Swap Token</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="support_liquid_status">Support Liquid</label>
                                    <select name="support_liquid_status" id="support_liquid_status" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['support_liquid_status']|length AND dataGet['support_liquid_status'] == '0') ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ (dataGet['support_liquid_status']|length AND dataGet['support_liquid_status'] == '1') ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="row mt-2">
                        <div class="col-sm-12">
                            <div>Total: <b>{{ helper.numberFormat(summaryData['token_amount'], 2) }} {{ config.site.coin_ticker }}</b></div>
                            <div>Total: <b>${{ helper.numberFormat(summaryData['usd_amount'], 2) }}</b></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Info</th>
                                <th class="border-top-0">User Address</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Payment</th>
                                <th class="border-top-0">Direct Bonus</th>
                                <th class="border-top-0">Team Bonus</th>
                                <th class="border-top-0">User Package</th>
                                <th class="border-top-0">Contract ID</th>
                                <th class="border-top-0">Support Liquid</th>
                                <th class="border-top-0">Date</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>ID: <b>{{ item['_id'] }}</b></div>
                                        <div>Code: <b>{{ item['code'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['user_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ helper.makeShortString(item['user_address']) }}</b></a></div>
                                        <div>Hash: <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ helper.makeShortString(item['hash']) }}</b></a></div>
                                        <div>Contract: <a href="{{ helper.getLinkAddress(item['contract_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ helper.makeShortString(item['contract_address']) }}</b></a></div>
                                    </td>
                                    <td>
                                        <div>Token amount: <b>{{ helper.numberFormat(item['token_amount'], 4) }}</b></div>
                                        <div>USD amount: <b>{{ helper.numberFormat(item['usd_amount'], 4) }}</b></div>
                                        <div>Rate: <b>{{ helper.numberFormat(item['coin_rate'], 4) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Payment: <b>{{ helper.numberFormat(item['payment_token_amount'], 2) }} {{ item['payment_token_symbol'] }}</b></div>
                                        <div>Staking Balance: <b>{{ helper.numberFormat(item['staking_token_balance'], 2) }} </b></div>
                                        <div>Swap Balance: <b>{{ helper.numberFormat(item['swap_token_balance'], 2) }} </b></div>
                                    </td>
                                    <td>
                                        <div>Status: <b class="text-{{ item['is_direct_bonus'] == 0 ? 'warning' : 'success' }}">{{ item['is_direct_bonus'] == 0 ? 'No' : 'Yes' }}</b></div>
                                        <div>Date: <b>{{ item['direct_bonus_at'] > 0 ? date('d/m/Y H:i:s', item['direct_bonus_at']) : '' }}</b></div>
                                        <div>Message: <b>{{ item['direct_bonus_message'] }}</b></div>
                                    </td>

                                    <td>
                                        <div>Status: <b class="text-{{ item['is_team_bonus'] == 0 ? 'warning' : 'success' }}">{{ item['is_team_bonus'] == 0 ? 'No' : 'Yes' }}</b></div>
                                        <div>Date: <b>{{ item['team_bonus_at'] > 0 ? date('d/m/Y H:i:s', item['team_bonus_at']) : '' }}</b></div>
                                    </td>

                                    <td>
                                        <div>ID: <b>{{ item['user_package_id'] }}</b></div>
                                    </td>
                                    <td>{{ item['contract_id'] }}</td>
                                    <td>
                                        {% if item['support_liquid_status']|length %}
                                            <div>Status: <b class="text-{{ item['support_liquid_status'] == 0 ? 'warning' : 'success' }}">{{ item['support_liquid_status'] == 0 ? 'Pending' : 'Success' }}</b></div>
                                            <div>Date: <b>{{ item['support_liquid_at'] > 0 ? date('d/m/Y H:i:s', item['support_liquid_at']) : '' }}</b></div>
                                            <div>Hash: <a target="_blank" href="{{ helper.getLinkTx(item['support_liquid_hash']) }}"><b>{{ helper.makeShortString(item['support_liquid_hash']) }}</b></a></div>
                                            <div>Message: <b>{{ item['support_liquid_message'] }}</b></div>
                                        {% endif %}
                                    </td>

                                    <td>{{ date('d/m/Y H:i:s', item['created_at']) }}</td>
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
</div>