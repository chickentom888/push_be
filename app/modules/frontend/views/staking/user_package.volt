<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Staking</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List User Package</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="platform">Platform</label>
                                    <select name="platform" id="platform" class="form-control">
                                        <option value="">Platform</option>
                                        {% for key,item in listPlatform %}
                                            <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="network">Network</label>
                                    <select name="network" id="network" class="form-control">
                                        <option value="">Network</option>
                                        {% for key,item in listNetwork %}
                                            <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="lottery-status">Status</label>
                                    <select name="status" id="lottery-status" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['status']|length AND dataGet['status'] == 0) ? 'selected' : '' }}>Inactive</option>
                                        <option value="1" {{ (dataGet['status']|length AND dataGet['status'] == 1) ? 'selected' : '' }}>Active</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_address">User Address</label>
                                <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_connect_id">User ID</label>
                                <input placeholder="User ID" id="user_connect_id" type="text" class="form-control" name="user_connect_id" value="{{ dataGet['user_connect_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="code">Code</label>
                                <input placeholder="Code" id="code" type="text" class="form-control" name="code" value="{{ dataGet['code'] }}">
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
                            <div>Total: <b>{{ helper.numberFormat(summaryData['token_amount'], 2) }}</b></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Package</th>
                                <th class="border-top-0">User</th>
                                <th class="border-top-0">Interest</th>
                                <th class="border-top-0">Fund Interest</th>
                                <th class="border-top-0">Principal</th>
                                <th class="border-top-0">Amount</th>
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
                                    </td>
                                    <td>
                                        <div>ID: <b>{{ item['_id'] }}</b></div>
                                        <div>Code: <b>{{ item['code'] }}</b></div>
                                        <div>Date: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Expire: <b>{{ date('d/m/Y H:i:s', item['expired_at']) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['user_address']) }}" target="_blank"><b>{{ helper.makeShortString(item['user_address']) }}</b></a></div>
                                        <div>ID: <b>{{ item['user_connect_id'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Last pay: <b>{{ item['last_interest_at'] > 0 ? date('d/m/Y H:i:s', item['last_interest_at']) : '' }}</b></div>
                                        <div>Next pay: <b>{{ item['next_interest_at'] > 0 ? date('d/m/Y H:i:s', item['next_interest_at']) : '' }}</b></div>
                                        <div>Paid day: <b>{{ item['interest_paid_day'] }} / {{ item['interest_max_day'] }}</b></div>
                                        <div>Paid amount: <b>{{ helper.numberFormat(item['interest_amount_paid'], 3) }}</b></div>
                                    </td>

                                    <td>
                                        <div>Last pay: <b>{{ item['last_fund_interest_at'] > 0 ? date('d/m/Y H:i:s', item['last_fund_interest_at']) : '' }}</b></div>
                                        <div>Next pay: <b>{{ item['next_fund_interest_at'] > 0 ? date('d/m/Y H:i:s', item['next_fund_interest_at']) : '' }}</b></div>
                                        <div>Paid times: <b>{{ item['fund_interest_paid_times'] }} / {{ item['fund_interest_max_times'] }}</b></div>
                                        <div>Total amount: <b>{{ helper.numberFormat(item['total_fund_interest_amount'], 3) }}</b></div>
                                        <div>Paid amount: <b>{{ helper.numberFormat(item['fund_interest_amount_paid'], 3) }}</b></div>
                                        <div>Pending amount: <b>{{ helper.numberFormat(item['fund_interest_amount_pending'], 3) }}</b></div>
                                    </td>

                                    <td>
                                        <div>Last pay: <b>{{ item['last_principal_at'] > 0 ? date('d/m/Y H:i:s', item['last_principal_at']) : '' }}</b></div>
                                        <div>Next pay: <b>{{ item['next_principal_at'] > 0 ? date('d/m/Y H:i:s', item['next_principal_at']) : '' }}</b></div>
                                        <div>Paid day: <b>{{ item['principal_paid_day'] }}/ {{ item['principal_max_day'] }}</b></div>
                                        <div>Paid amount: <b>{{ helper.numberFormat(item['principal_amount_paid'], 3) }}</b></div>
                                    </td>
                                    <td>
                                        <div><b>{{ helper.numberFormat(item['token_amount'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <b class="text-{{ item['status'] == 0 ? 'danger' : 'success' }}">{{ item['status'] == 0 ? 'Inactive' : 'Active' }}</b>
                                    </td>
                                    <td>
                                        <div>
                                            <a class="btn btn-info btn-sm" href="/staking/user_package_history?user_package_id={{ item['_id'] }}">Staking</a>
                                            <a class="btn btn-success btn-sm" href="/report/bonus_log?user_package_id={{ item['_id'] }}">Bonus Log</a>
                                        </div>
                                        <div class="mt-2">
                                            <a class="btn btn-info btn-sm" href="/staking/interest?user_package_id={{ item['_id'] }}">Interest</a>
                                            <a class="btn btn-success btn-sm" href="/staking/principal?user_package_id={{ item['_id'] }}">Principal</a>
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