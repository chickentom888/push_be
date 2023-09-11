<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Max Out Log</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List Max Out Log</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="wallet">Wallet</label>
                                    <select name="wallet" id="wallet" class="form-control">
                                        <option value="">Select</option>
                                        {% for key, value in listWallet %}
                                            <option value="{{ key }}" {{ (dataGet['wallet']|length AND dataGet['wallet'] == key) ? 'selected' : '' }}>{{ value }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

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
                                <label for="user_address">User Address</label>
                                <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_connect_id">User ID</label>
                                <input placeholder="User ID" id="user_connect_id" type="text" class="form-control" name="user_connect_id" value="{{ dataGet['user_connect_id'] }}">
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-sm-12">
                            <div>Total: <b>{{ helper.numberFormat(summaryData['amount'], 2) }}</b></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">User</th>
                                <th class="border-top-0">Before/Last</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Type</th>
                                <th class="border-top-0">Message</th>
                                <th class="border-top-0">Date</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['user_address']) }}" target="_blank"><b>{{ helper.makeShortString(item['user_address']) }}</b></a></div>
                                        <div>ID: <b>{{ item['user_connect_id'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Before: <b class="text-warning">{{ helper.numberFormat(item['before_amount'], 2) }}</b></div>
                                        <div>Last: <b class="text-info">{{ helper.numberFormat(item['last_amount'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <div><b class="text-{{ item['amount'] > 0 ? 'success' : 'danger' }}">{{ helper.numberFormat(item['amount'], 2) }}</b></div>
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