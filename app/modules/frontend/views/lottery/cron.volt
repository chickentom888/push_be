<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Lottery Cron</h4>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Lottery Cron</h5>

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
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="contract_address">Contract Address</label>
                                <input placeholder="Contract Address" id="contract_address" type="text" class="form-control" name="contract_address" value="{{ dataGet['contract_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="hash">Hash</label>
                                <input placeholder="Hash" id="hash" type="text" class="form-control" name="hash" value="{{ dataGet['hash'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="lottery_contract_id">Lottery ID</label>
                                <input placeholder="Lottery ID" id="lottery_contract_id" type="text" class="form-control" name="lottery_contract_id" value="{{ dataGet['lottery_contract_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="action">Action</label>
                                <select name="action" id="action" class="form-control">
                                    <option value="">Select</option>
                                    <option value="startLottery" {{ dataGet['action']|length AND dataGet['action'] == 'startLottery' ? 'selected' : '' }}>Start Lottery</option>
                                    <option value="closeLottery" {{ dataGet['action']|length AND dataGet['action'] == 'closeLottery' ? 'selected' : '' }}>Close Lottery</option>
                                    <option value="calculateReward" {{ dataGet['action']|length AND dataGet['action'] == 'calculateReward' ? 'selected' : '' }}>Calculate Reward</option>
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="cron_status">Action Status</label>
                                <select name="status" id="cron_status" class="form-control">
                                    <option value="">Select</option>
                                    <option value="0" {{ dataGet['status']|length AND dataGet['status'] == 0 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ dataGet['status']|length AND dataGet['status'] == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ dataGet['status']|length AND dataGet['status'] == 2 ? 'selected' : '' }}>Success</option>
                                    <option value="3" {{ dataGet['status']|length AND dataGet['status'] == 3 ? 'selected' : '' }}>Fail</option>
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="tx_status">Tx Status</label>
                                <select name="tx_status" id="tx_status" class="form-control">
                                    <option value="">Select</option>
                                    <option value="0" {{ dataGet['tx_status']|length AND dataGet['tx_status'] == 0 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ dataGet['tx_status']|length AND dataGet['tx_status'] == 1 ? 'selected' : '' }}>Success</option>
                                    <option value="2" {{ dataGet['tx_status']|length AND dataGet['tx_status'] == 2 ? 'selected' : '' }}>Fail</option>
                                </select>
                            </div>


                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                            <tr>
                                <th class="border-top-0">Network</th>
                                <th class="border-top-0">Time</th>
                                <th class="border-top-0">Contract</th>
                                <th class="border-top-0">Action</th>
                                <th class="border-top-0">Status</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Network: <b>{{ item['network'] }}</b></div>
                                        <div>Platform: <b>{{ item['platform'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Cron time: <b>{{ date('d/m/Y H:i:s', item['cron_time']) }}</b></div>
                                        <div>Action time: <b>{{ item['action_time'] > 0 ? date('d/m/Y H:i:s', item['action_time']) : '' }}</b></div>
                                    </td>
                                    <td>
                                        <div>
                                            Contract : <b><a href="{{ helper.getLinkAddress(item['contract_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['contract_address'] }}</b></a></b>
                                        </div>
                                        <div>
                                            Lottery ID: <b>{{ item['lottery_contract_id'] }}</b>
                                        </div>
                                        <div>
                                            Hash : <b><a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a></b>
                                        </div>
                                    </td>
                                    <td>
                                        {{ item['action'] }}
                                    </td>
                                    <td>
                                        <div>Action: <b>{{ listLotteryCronStatus[item['status']] }}</b></div>
                                        <div>Tx: <b>{{ listLotteryCronTxStatus[item['tx_status']] }}</b></div>
                                    </td>
                                    <td>
                                        {% if item['tx_status'] == 2 %}
                                            <a href="/lottery/updateCron/{{ item['_id'] }}/0" class="btn btn-sm btn-info">Reset</a>
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