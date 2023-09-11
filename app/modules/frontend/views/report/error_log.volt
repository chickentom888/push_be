<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Error Log</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List Error Log</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="type">Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">Select</option>
                                        {% for key, value in listBalanceLog %}
                                            <option value="{{ key }}" {{ (dataGet['type']|length AND dataGet['type'] == key) ? 'selected' : '' }}>{{ value }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="from_user_address">From User Address</label>
                                <input placeholder="From User Address" id="from_user_address" type="text" class="form-control" name="from_user_address" value="{{ dataGet['from_user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="to_user_address">To User Address</label>
                                <input placeholder="To User Address" id="to_user_address" type="text" class="form-control" name="to_user_address" value="{{ dataGet['to_user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="from_user_connect_id">From User ID</label>
                                <input placeholder="From User ID" id="from_user_connect_id" type="text" class="form-control" name="from_user_connect_id" value="{{ dataGet['from_user_connect_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="to_user_connect_id">To User ID</label>
                                <input placeholder="To User ID" id="to_user_connect_id" type="text" class="form-control" name="to_user_connect_id" value="{{ dataGet['to_user_connect_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_package_id">User Package ID</label>
                                <input placeholder="User Package ID" id="user_package_id" type="text" class="form-control" name="user_package_id" value="{{ dataGet['user_package_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_package_history_id">User Package History ID</label>
                                <input placeholder="User Package History ID" id="user_package_history_id" type="text" class="form-control" name="user_package_history_id" value="{{ dataGet['user_package_history_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="contract_id">Contract ID</label>
                                <input placeholder="Contract ID" id="contract_id" type="text" class="form-control" name="contract_id" value="{{ dataGet['contract_id'] }}">
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">From User</th>
                                <th class="border-top-0">To User</th>
                                <th class="border-top-0">Package History</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Info</th>
                                <th class="border-top-0">Type</th>
                                <th class="border-top-0">Message</th>
                                <th class="border-top-0">Date</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                {% set userPackageHistory = helper.getUserPackageHistory(item['user_package_history_id']) %}
                                <tr>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['from_user_address']) }}" target="_blank"><b>{{ helper.makeShortString(item['from_user_address']) }}</b></a></div>
                                        <div>ID: <b>{{ item['from_user_connect_id'] }}</b></div>
                                    </td>

                                    <td>
                                        {% if item['to_user_address'] %}
                                            <div>Address: <a href="{{ helper.getLinkAddress(item['to_user_address']) }}" target="_blank"><b>{{ helper.makeShortString(item['to_user_address']) }}</b></a></div>
                                            <div>ID: <b>{{ item['to_user_connect_id'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        <div>ID: <b>{{ item['user_package_history_id'] }}</b></div>
                                        <div>Code: <b>{{ userPackageHistory['code'] }}</b></div>
                                        <div>Contract ID: <b>{{ userPackageHistory['contract_id'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>From Amount: <b class="text-info">{{ helper.numberFormat(item['from_amount'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        {% if item['type'] == 'team_bonus' %}
                                            <div>Left invest: <b class="text-info">{{ helper.numberFormat(item['left_invest'], 2) }}</b></div>
                                            <div>Right invest: <b class="text-info">{{ helper.numberFormat(item['right_invest'],2 ) }}</b></div>
                                            <div>Save branch: <b class="text-info">{{ helper.numberFormat(item['save_branch'],2 ) }}</b></div>
                                            <div>Count Left F1 Invest: <b class="text-info">{{ item['count_left_f1_invest'] }}</b></div>
                                            <div>Count Right F1 Invest: <b class="text-info">{{ item['count_right_f1_invest'] }}</b></div>
                                        {% endif %}

                                        {% if item['type'] == 'matching_bonus' %}
                                            <div>Save branch: <b class="text-warning">{{ helper.numberFormat(item['save_branch'], 2) }}</b></div>
                                            <div>Left invest: <b class="text-info">{{ helper.numberFormat(item['left_invest'], 2) }}</b></div>
                                            <div>Right invest: <b class="text-info">{{ helper.numberFormat(item['right_invest'],2 ) }}</b></div>
                                            <div>Count Left F1 Invest: <b class="text-info">{{ item['count_left_f1_invest'] }}</b></div>
                                            <div>Count Right F1 Invest: <b class="text-info">{{ item['count_right_f1_invest'] }}</b></div>
                                            <div>Layer: <b class="text-info">{{ item['layer'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>{{ listBalanceLog[item['type']] }}</td>
                                    <td>{{ item['message'] }}</td>
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