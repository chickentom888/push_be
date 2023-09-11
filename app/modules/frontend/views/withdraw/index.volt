<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Withdraw</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List User Withdraw</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

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
                                <label for="user_address">User Address</label>
                                <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_connect_id">User ID</label>
                                <input placeholder="User ID" id="user_connect_id" type="text" class="form-control" name="user_connect_id" value="{{ dataGet['user_connect_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="sort">Sort</label>
                                <select name="sort" id="sort" class="form-control">
                                    <option value="">Select</option>
                                    <option value="_id" {{ (dataGet['sort']|length AND dataGet['sort'] == '_id') ? 'selected' : '' }}>ID</option>
                                    <option value="amount" {{ (dataGet['sort']|length AND dataGet['sort'] == 'amount') ? 'selected' : '' }}>Amount</option>
                                </select>
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
                            <div>Total Amount: <b class="text-info">{{ helper.numberFormat(summaryData['amount'], 2) }}</b></div>
                            <div>Fee Amount: <b class="text-warning">{{ helper.numberFormat(summaryData['fee_amount'], 2) }}</b></div>
                            <div>Amount After Fee: <b class="text-success">{{ helper.numberFormat(summaryData['amount_after_fee'], 2) }}</b></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">User</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Status</th>
                                <th class="border-top-0">Blockchain Status</th>
                                <th class="border-top-0">Hash</th>
                                <th class="border-top-0">Date</th>
                                <th class="border-top-0">Action</th>
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
                                        <div>Amount: <b class="text-info">{{ helper.numberFormat(item['amount'], 2) }}</b></div>
                                        <div>Fee Amount: <b class="text-warning">{{ helper.numberFormat(item['fee_amount'], 2) }}</b></div>
                                        <div>Amount after fee: <b class="text-success">{{ helper.numberFormat(item['amount_after_fee'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <b class="text-{{ helper.getWithdrawStatusClass(item['status']) }}">{{ helper.getWithdrawStatusText(item['status']) }}</b>
                                    </td>

                                    <td>
                                        <b class="text-{{ helper.getWithdrawStatusClass(item['blockchain_status']) }}">{{ helper.getWithdrawBlcStatusText(item['blockchain_status']) }}</b>
                                    </td>
                                    <td>
                                        {% if item['hash']|length %}
                                            <div>Hash: <a href="{{ helper.getLinkTx(item['hash']) }}" target="_blank"><b>{{ helper.makeShortString(item['hash']) }}</b></a></div>
                                        {% endif %}
                                        <div>Message: <b>{{ item['message'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Created: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Process: <b>{{ item['process_at'] > 0 ? date('d/m/Y H:i:s', item['process_at']) :'' }}</b></div>
                                    </td>
                                    <td>
                                        {% if item['status'] == 0 %}
                                            <div>
                                                <a class="btn btn-success btn-sm need-confirm" href="/withdraw/approve/{{ item['_id'] }}">Approve</a>
                                                <a class="btn btn-danger btn-sm need-confirm" href="/withdraw/reject/{{ item['_id'] }}">Reject</a>
                                            </div>
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

</div>