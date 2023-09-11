<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Transaction</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Transaction</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="network">Network</label>
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="from_address">From Address</label>
                                <input placeholder="From Address" id="from_address" type="text" class="form-control" name="from_address" value="{{ dataGet['from_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="to_address">To Address</label>
                                <input placeholder="To Address" id="to_address" type="text" class="form-control" name="to_address" value="{{ dataGet['to_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="hash">Hash</label>
                                <input placeholder="Hash" id="hash" type="text" class="form-control" name="hash" value="{{ dataGet['hash'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="action">Action</label>
                                <select name="action" id="action" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listAction %}
                                        <option value="{{ key }}" {{ dataGet['action'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>


                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="id_status">Status</label>
                                    <select name="status" id="id_status" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['status']|length AND dataGet['status'] == 0) ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ (dataGet['status']|length AND dataGet['status'] == 1) ? 'selected' : '' }}>Approve</option>
                                        <option value="2" {{ (dataGet['status']|length AND dataGet['status'] == 2) ? 'selected' : '' }}>Reject</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="blockchain_status">Blockchain Status</label>
                                    <select name="blockchain_status" id="blockchain_status" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['blockchain_status']|length AND dataGet['blockchain_status'] == 0) ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ (dataGet['blockchain_status']|length AND dataGet['blockchain_status'] == 1) ? 'selected' : '' }}>Success</option>
                                        <option value="2" {{ (dataGet['blockchain_status']|length AND dataGet['blockchain_status'] == 2) ? 'selected' : '' }}>Fail</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="nonce">Nonce</label>
                                <input placeholder="Nonce" id="nonce" type="text" class="form-control" name="nonce" value="{{ dataGet['nonce'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform Info</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Type</th>
                                <th class="border-top-0">Info</th>
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
                                    </td>
                                    <td>
                                        <div>
                                            <label>From: </label>
                                            <a href="{{ helper.getLinkAddress(item['from_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['from_address'] }}</b></a>
                                        </div>
                                        <div>
                                            <label>To: </label>
                                            <a href="{{ helper.getLinkAddress(item['to_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['to_address'] }}</b></a>
                                        </div>
                                        <div>
                                            <label>Hash: </label>
                                            <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div><b>{{ listAction[item['action']] }}</b></div>
                                    </td>

                                    <td>
                                        <div>Amount: <b>{{ helper.numberFormat(item['amount'], 4) }}</b></div>
                                        <div>Nonce: <b>{{ helper.numberFormat(item['nonce']) }}</b></div>
                                        <div>Gas Limit: <b>{{ helper.numberFormat(item['gas_limit']) }}</b></div>
                                        <div>Gas Price: <b>{{ helper.numberFormat(item['gas_price']) }}</b></div>
                                    </td>

                                    <td>
                                        <div>Created at: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Timestamp: <b>{{ item['timestamp'] > 0 ? date('d/m/Y H:i:s', item['timestamp']) : '' }}</b></div>
                                    </td>
                                    <td>
                                        <div>Status: <b class="text-{{ helper.getWithdrawStatusClass(item['status']) }}">{{ helper.getWithdrawStatusText(item['status']) }}</b></div>
                                        <div>Blockchain: <b class="text-{{ helper.getWithdrawStatusClass(item['blockchain_status']) }}">{{ helper.getWithdrawBlcStatusText(item['blockchain_status']) }}</b></div>
                                    </td>
                                    <td>
                                        {% if item['blockchain_status'] != 1 %}
                                            <a href="/index/transaction_detail/{{ item['_id'] }}" class="btn btn-sm btn-info need-confirm"> Resend</a>
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
