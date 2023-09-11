<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Airdrop</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List Airdrop</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="id_status">Status</label>
                                    <select name="status" id="id_status" class="form-control">
                                        <option value="">Select</option>
                                        <option value="0" {{ (dataGet['status']|length AND dataGet['status'] == 0) ? 'selected' : '' }}>Not send</option>
                                        <option value="1" {{ (dataGet['status']|length AND dataGet['status'] == 1) ? 'selected' : '' }}>Sent</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="address">User Address</label>
                                <input placeholder="Address" id="address" type="text" class="form-control" name="address" value="{{ dataGet['address'] }}">
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
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Hash</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['address']) }}" target="_blank"><b>{{ item['address'] }}</b></a></div>
                                        {% if userInfo['role'] == 1 %}
                                            <div>Private Key: <b>{{ item['private_key'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        <div>Hash: <a target="_blank" href="{{ helper.getLinkTx(item['hash']) }}"><b>{{ helper.makeShortString(item['hash']) }}</b></a></div>
                                    </td>
                                    <td>
                                        <div><b class="text-success">{{ helper.numberFormat(item['amount'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        <b class="text-{{ item['status'] == 0 ? 'danger' : 'success' }}">{{ item['status'] == 0 ? 'Not send' : 'Sent' }}</b>
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