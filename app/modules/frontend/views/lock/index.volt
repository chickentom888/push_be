<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Lock History</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Lock History</h5>

                    {{ flash.output() }}
                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="network">Network</label>
                                <select name="network" id="network" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="address">Address</label>
                                <input placeholder="Address lock" id="address" type="text" class="form-control" name="address" value="{{ dataGet['address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="withdraw_status">Withdraw status</label>
                                <select name="withdraw_status" id="withdraw_status" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listWithdrawnStatus %}
                                        <option value="{{ key }}" {{ (dataGet['withdraw_status']|length AND dataGet['withdraw_status'] == key) ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="address_token">Address token</label>
                                <input placeholder="Address Token" id="address_token" type="text" class="form-control" name="address_token" value="{{ dataGet['address_token'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="hash">Hash</label>
                                <input placeholder="Tx" id="hash" type="text" class="form-control" name="hash" value="{{ dataGet['hash'] }}">
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
                                <th class="border-top-0">Platform Info</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Info</th>
                                <th class="border-top-0">Token</th>
                                <th class="border-top-0">Withdraw</th>
                                <th class="border-top-0">Time</th>
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
                                            <label for="base-token-address-{{ key }}">Address lock: </label>
                                            <a href="{{ helper.getLinkAddress(item['address_lock'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['address_lock'] }}</b></a>
                                        </div>
                                        <div>
                                            <label for="base-token-address-{{ key }}">Address withdraw: </label>
                                            <a href="{{ helper.getLinkAddress(item['address_withdraw'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['address_withdraw'] }}</b></a>
                                        </div>
                                        <div>
                                            <label for="base-token-address-{{ key }}">Transaction hash: </label>
                                            <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ helper.makeShortString(item['hash']) }}</b></a>
                                        </div>
                                    </td>

                                    <td>
                                        <div>Base fee: <b>{{ item['base_fee_amount'] }}</b></div>
                                        <div>Amount: <b>{{ helper.numberFormat(item['amount'], 2) }}</b></div>
                                        <div>Real amount: <b>{{ helper.numberFormat(item['real_token_amount'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['contract_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['contract_address'] }}</b></a></div>
                                        <div>Name: <b>{{ item['contract_name'] }}</b></div>
                                        <div>Symbol: <b>{{ item['contract_symbol'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Status: <b class="text-{{ item['withdraw_status'] == withdrawnStatus ? 'success' : 'danger' }} text-xl">{{ listWithdrawnStatus[item['withdraw_status']] }}</b></div>
                                        <div>Hash: <a href="{{ helper.getLinkTx(item['withdraw_hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['withdraw_hash']|length ? helper.makeShortString(item['withdraw_hash']) : '' }}</b></a></div>
                                        <div>Date: <b>{{ item['withdraw_at'] > 0 ? date('d/m/Y H:i:s', item['withdraw_at']) : '' }}</b></div>
                                    </td>
                                    <td>
                                        <div>Created: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Updated: <b>{{ item['updated_at'] > 0 ? date('d/m/Y H:i:s', item['updated_at']) : '' }}</b></div>
                                        <div>Unlock: <b>{{ date('d/m/Y H:i:s', item['unlock_time']) }}</b></div>
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
