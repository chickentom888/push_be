<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">User Connect</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List User Connect</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="branch">Branch</label>
                                    <select name="branch" id="branch" class="form-control">
                                        <option value="">Select</option>
                                        {% for key, value in listBranch %}
                                            <option value="{{ key }}" {{ (dataGet['branch']|length AND dataGet['branch'] == key) ? 'selected' : '' }}>{{ value }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="branch_for_child">Child Branch</label>
                                    <select name="branch_for_child" id="branch_for_child" class="form-control">
                                        <option value="">Select</option>
                                        {% for key, value in listBranch %}
                                            <option value="{{ key }}" {{ (dataGet['branch_for_child']|length AND dataGet['branch_for_child'] == key) ? 'selected' : '' }}>{{ value }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="address">User Address</label>
                                <input placeholder="User Address" id="address" type="text" class="form-control" name="address" value="{{ dataGet['address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="id">User ID</label>
                                <input placeholder="User ID" id="id" type="text" class="form-control" name="id" value="{{ dataGet['id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="parent_id">Parent ID</label>
                                <input placeholder="Parent ID" id="parent_id" type="text" class="form-control" name="parent_id" value="{{ dataGet['parent_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="inviter_id">Inviter ID</label>
                                <input placeholder="Inviter ID" id="inviter_id" type="text" class="form-control" name="inviter_id" value="{{ dataGet['inviter_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="parent_address">Parent Address</label>
                                <input placeholder="Parent Address" id="parent_address" type="text" class="form-control" name="parent_address" value="{{ dataGet['parent_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="inviter_address">Inviter Address</label>
                                <input placeholder="Inviter Address" id="inviter_address" type="text" class="form-control" name="inviter_address" value="{{ dataGet['inviter_address'] }}">
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="sort">Sort</label>
                                    <select name="sort" id="sort" class="form-control">
                                        <option value="">Select</option>
                                        <option value="_id" {{ (dataGet['sort']|length AND dataGet['sort'] == '_id') ? 'selected' : '' }}>ID</option>
                                        <option value="coin_balance" {{ (dataGet['sort']|length AND dataGet['sort'] == 'coin_balance') ? 'selected' : '' }}>{{ siteCoinTicker }}</option>
                                        <option value="interest_balance" {{ (dataGet['sort']|length AND dataGet['sort'] == 'interest_balance') ? 'selected' : '' }}>Interest</option>
                                        <option value="diagram_date" {{ (dataGet['sort']|length AND dataGet['sort'] == 'diagram_date') ? 'selected' : '' }}>Tree Date</option>
                                        <option value="direct_system_invest" {{ (dataGet['sort']|length AND dataGet['sort'] == 'direct_system_invest') ? 'selected' : '' }}>Direct System Invest ({{ config.site.coin_ticker }})</option>
                                        <option value="direct_system_invest_usd" {{ (dataGet['sort']|length AND dataGet['sort'] == 'direct_system_invest_usd') ? 'selected' : '' }}>Direct System Invest ($)</option>
                                        <option value="level" {{ (dataGet['sort']|length AND dataGet['sort'] == 'level') ? 'selected' : '' }}>Level</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="lock_withdraw">Lock withdraw</label>
                                    <select name="lock_withdraw" id="lock_withdraw" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['lock_withdraw']|length AND dataGet['lock_withdraw'] == 0) ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ (dataGet['lock_withdraw']|length AND dataGet['lock_withdraw'] == 1) ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row mt-2">
                        <div class="col-sm-12">
                            <div>{{ siteCoinTicker }}: <b>{{ helper.numberFormat(summaryData['coin_balance'], 2) }}</b></div>
                            <div>Interest: <b>{{ helper.numberFormat(summaryData['interest_balance'], 2) }}</b></div>
                            <div>Total staking: <b>{{ helper.numberFormat(summaryData['personal_invest'], 2) }}</b></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">User</th>
                                <th class="border-top-0">Inviter</th>
                                <th class="border-top-0">Parent</th>
                                <th class="border-top-0">Branch</th>
                                <th class="border-top-0">Wallet</th>
                                <th class="border-top-0">Max Out</th>
                                <th class="border-top-0">Invest</th>
                                <th class="border-top-0">Date</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                {% set inviter = helper.getUserConnectById(item['inviter_id']) %}
                                {% set parent = helper.getUserConnectById(item['parent_id']) %}
                                <tr>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['address'], item['platform'], item['network']) }}" target="_blank"><b>{{ helper.makeShortString(item['address']) }}</b></a></div>
                                        <div>ID: <b>{{ item['_id'] }}</b></div>
                                        <div>Code: <b>{{ item['code'] }}</b></div>
                                        <div>Level: <b class="text-info">{{ number_format(item['level']) }}</b></div>
                                        <div>Lock: <b class="text-{{ item['lock_withdraw'] == 0 ? 'success' : 'danger' }}">{{ item['lock_withdraw'] == 0 ? 'No' : 'Yes' }}</b></div>
                                    </td>
                                    <td>
                                        {% if inviter %}
                                            <div>Address: <a href="{{ helper.getLinkAddress(inviter['address'], inviter['platform'], inviter['network']) }}" target="_blank"><b>{{ helper.makeShortString(inviter['address']) }}</b></a></div>
                                            <div>ID: <b>{{ inviter['_id'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        {% if parent %}
                                            <div>Address: <a href="{{ helper.getLinkAddress(parent['address'], parent['platform'], parent['network']) }}" target="_blank"><b>{{ helper.makeShortString(parent['address']) }}</b></a></div>
                                            <div>ID: <b>{{ parent['_id'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        <div>User: <b class="text-{{ item['branch'] == leftBranch ? 'warning' : 'info' }}">{{ item['branch']|capitalize }}</b></div>
                                        <div>Child: <b class="text-{{ item['branch_for_child'] == leftBranch ? 'warning' : 'info' }}">{{ item['branch_for_child']|capitalize }}</b></div>
                                    </td>
                                    <td>
                                        <div>{{ siteCoinTicker }}: <b>{{ helper.numberFormat(item['coin_balance'], 2) }}</b></div>
                                        <div>Interest: <b>{{ helper.numberFormat(item['interest_balance'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <b class="text-info">{{ helper.numberFormat(item['max_out_bonus'], 2) }}</b> / <b class="text-danger">{{ helper.numberFormat(item['personal_invest'] * 3, 2) }}</b>
                                    </td>
                                    <td>
                                        <div>Personal: <b>{{ helper.numberFormat(item['personal_invest'], 2) }}</b></div>
                                        <div>Total System: <b>{{ helper.numberFormat(item['system_invest'], 2) }}</b></div>
                                        <div>Direct System: <b>{{ helper.numberFormat(item['direct_system_invest'], 2) }} {{ config.site.coin_ticker }} / ${{ helper.numberFormat(item['direct_system_invest_usd'], 2) }}</b></div>
                                        <div>Left: <b>{{ helper.numberFormat(item['left_invest'], 2) }}</b></div>
                                        <div>Right: <b>{{ helper.numberFormat(item['right_invest'], 2) }}</b></div>
                                        <div>Save Branch: <b>{{ helper.numberFormat(item['save_branch'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Created: <b>{{ item['created_at'] > 0 ? date('d/m/Y H:i:s', item['created_at']) : '' }}</b></div>
                                        <div>Tree: <b>{{ item['diagram_date'] > 0 ? date('d/m/Y H:i:s', item['diagram_date']) : '' }}</b></div>
                                    </td>
                                    <td>
                                        <div class="mt-2">
                                            <a class="btn btn-success btn-sm" href="/report/bonus_log?to_user_connect_id={{ item['_id'] }}" target="_blank">Bonus Log</a>
                                            <a class="btn btn-info btn-sm" href="/report/balance_log?user_connect_id={{ item['_id'] }}" target="_blank">Balance Log</a>
                                        </div>
                                        <div class="mt-2">
                                            <a class="btn btn-info btn-sm" href="/report/max_out_log?user_connect_id={{ item['_id'] }}" target="_blank">Max Out Log</a>
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
</div>