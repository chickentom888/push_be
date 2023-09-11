<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Airdrop</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Airdrop</h5>

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
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="token_type">Token Type</label>
                                <select name="token_type" id="token_type" class="form-control">
                                    <option value="">Select</option>
                                    <option value="main" {{ dataGet['token_type'] == 'main' ? 'selected' : '' }}>Main Currency</option>
                                    <option value="erc20" {{ dataGet['token_type'] == 'erc20' ? 'selected' : '' }}>Token ERC20</option>
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <label for="token_address">Token address</label>
                                <input placeholder="Token address" id="token_address" type="text" class="form-control" name="token_address" value="{{ dataGet['token_address'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                                <button class="btn btn-danger btn-export">Export</button>
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
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Type</th>
                                <th class="border-top-0">Token Info</th>
                                <th class="border-top-0">Airdrop Info</th>
                                <th class="border-top-0">Date</th>
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
                                        <div>Airdrop contract: <a href="{{ helper.getLinkAddress(item['airdrop_contract_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['airdrop_contract_address'] }}</b></a></div>
                                        <div>User address: <a href="{{ helper.getLinkAddress(item['user_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['user_address'] }}</b></a></div>
                                        <div>Hash: <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a></div>
                                    </td>

                                    <td>{{ helper.getTypeByPlatform(item['platform'])|upper }}</td>
                                    <td>
                                        {% if item['token_type'] == 'main' %}
                                            <div>Token address: <a href="javascript:"><b>{{ item['token_address'] }}</b></a></div>
                                        {% else %}
                                            <div>Token address: <a href="{{ helper.getLinkAddress(item['token_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['token_address'] }}</b></a></div>
                                        {% endif %}
                                        <div>Token name: <b>{{ item['token_name'] }}</b></div>
                                        <div>Token symbol: <b>{{ item['token_symbol'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Address number: <b>{{ item['list_address']|length }}</b></div>
                                        <div>Total amount: <b>{{ helper.numberFormat(item['total_token_amount'], 8) }}</b></div>
                                        <div>Fee amount: <b>{{ item['fee_amount'] }}</b></div>
                                    </td>
                                    <td>{{ date('m/d/Y H:i', item['created_at']) }}</td>
                                    <td><a href="/airdrop/detail/{{ item['_id'] }}" class="btn btn-info btn-sm">Detail</a></td>
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

<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-export').click(function (e) {
            e.preventDefault()
            var url = window.location.href;
            if (url.indexOf('?') > -1) {
                url += '&export=1'
            } else {
                url += '?export=1'
            }
            window.location.replace(url);
        });
    });
</script>
