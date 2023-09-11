<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Pool User Log</h5>

                    {% if isSearch == true %}
                        <form action="" class="form settings-form" method="get">
                            <div class="row">
                                <div class="col-sm-2">
                                    <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address"
                                           value="{{ dataGet['user_address'] }}">
                                </div>
                                <div class="col-sm-2">
                                    <select name="withdraw_status" id="withdraw_status" class="form-control">
                                        <option value="">Withdraw Status</option>
                                        <option value="0" {{ dataGet['withdraw_status'] and dataGet['withdraw_status'] == 0 ? 'selected' : '' }}>Not
                                            Withdraw
                                        </option>
                                        <option value="1" {{ dataGet['withdraw_status'] and dataGet['withdraw_status'] == 1 ? 'selected' : '' }}>
                                            Withdraw
                                        </option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    {% endif %}

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                            <tr>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Withdraw</th>
                                <th class="border-top-0">Vesting</th>
                                <th class="border-top-0">Withdraw Status</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listPoolUserLog %}
                                <tr>
                                    <td>
                                        <div>User:
                                            <a href="{{ helper.getLinkAddress(item['user_address'], item['platform'], item['network']) }}"
                                               target="_blank"><b>{{ item['user_address'] }}</b></a>
                                        </div>
                                        <div> Pool:
                                            <a href="{{ helper.getLinkAddress(item['pool_address'], item['platform'], item['network']) }}"
                                               target="_blank"><b>{{ item['pool_address'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Base Token: <b>{{ item['base_token_amount'] }}</b></div>
                                        <div>Pool Token: <b>{{ item['pool_token_amount'] }}</b></div>
                                        {% if item['pool_token_withdraw_amount'] %}
                                            <div>Pool Token Withdraw: <b>{{ item['pool_token_withdraw_amount'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        {% if item['withdraw_token_type'] and item['withdraw_token_type'] == 'base_token' %}
                                            <div>Base Token: <b>{{ item['base_token_withdraw_amount'] }}</b></div>
                                        {% elseif item['withdraw_token_type'] and item['withdraw_token_type'] == 'pool_token' %}
                                            <div>Pool Token: <b>{{ item['pool_token_withdraw_amount'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        {% if item['list_vesting'] %}
                                            {% for vesting in item['list_vesting'] %}
                                                <div class="form-control">
                                                    <div>Number: <b>{{ vesting['vesting_number'] }}</b></div>
                                                    <div>Period: <b>{{ date('d/m/Y H:i:s', vesting['vesting_period']) }}</b></div>
                                                    <div>Percent: <b>{{ vesting['vesting_percent'] }}</b></div>
                                                    <div>Withdraw Status: <b>{{ vesting['withdraw_status'] }}</b></div>
                                                    <div>Amount: <b>{{ vesting['pool_token_withdraw_amount'] }}</b></div>
                                                    <div>Withdraw at: <b>{{ date('d/m/Y H:i:s', vesting['withdraw_at']) }}</b></div>
                                                    <div>{{ date('d/m/Y H:i:s', item['created_at']) }}</div>
                                                </div>
                                            {% endfor %}
                                        {% endif %}
                                    </td>
                                    <td>
                                        <div>Withdraw Status: {{ item['withdraw_status'] }}</div>
                                        <div>Active Vesting: {{ item['active_vesting'] }}</div>
                                        <div>Contract Type: {{ item['contract_type'] }}</div>
                                        <div>Withdraw Token Type: {{ item['withdraw_token_type'] }}</div>
                                        {% if item['withdraw_at'] %}
                                            <div>Withdraw At: {{ date('d/m/Y H:i:s', item['withdraw_at']) }}</div>
                                        {% endif %}
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
