<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Token Minted</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Token Minted</h5>

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
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="address">Address</label>
                                <input placeholder="Address" id="address" type="text" class="form-control" name="address" value="{{ dataGet['address'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                                <input class="btn btn-danger" type="submit" name="export" value="Export"/>
                            </div>
                        </div>
                    </form>

                    <div class="mt-2">
                        Total: {{ helper.numberFormat(count) }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Token Address</th>
                                <th class="border-top-0">Token Info</th>
                                <th class="border-top-0">Fee Info</th>
                                <th class="border-top-0">Date</th>
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
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['contract_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['contract_address'] }}</b></a></div>
                                        <div>Hash: <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a></div>
                                        <div>Owner: <a href="{{ helper.getLinkAddress(item['hash'], item['platform'], item['user_address']) }}" target="_blank"><b>{{ item['user_address'] }}</b></a></div>
                                    </td>
                                    <td>
                                        <div>Name: <b>{{ item['name'] }}</b></div>
                                        <div>Symbol: <b>{{ item['symbol'] }}</b></div>
                                        <div>Decimals: <b>{{ item['decimals'] }}</b></div>
                                        <div>Supply: <b>{{ helper.numberFormat(item['total_supply']) }}</b></div>
                                        <div>Version: <b>{{ item['contract_version'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Creation Fee: <b>{{ item['creation_fee'] }}</b></div>
                                        <div>Token Fee: <b>{{ helper.numberFormat(item['fee_amount'], 2) }}</b></div>
                                    </td>
                                    <td>
                                        {{ date('d/m/Y H:i:s', item['created_at']) }}
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