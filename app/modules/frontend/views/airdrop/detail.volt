<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Airdrop Detail</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Airdrop Detail</h5>

                    {{ flash.output() }}

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Airdrop Info</h5>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Airdrop Contract Address</label>
                                <a class="form-control" href="{{ helper.getLinkAddress(airdrop['airdrop_contract_address'], airdrop['platform'], airdrop['network']) }}" target="_blank"><b>{{ airdrop['airdrop_contract_address'] }}</b></a>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Transaction hash</label>
                                <a class="form-control" href="{{ helper.getLinkTx(airdrop['hash'], airdrop['platform'], airdrop['network']) }}" target="_blank"><b>{{ airdrop['hash'] }}</b></a>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fee-amount">Fee amount</label>
                                <input class="form-control" type="text" value="{{ airdrop['fee_amount'] }}" id="fee-amount" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="created-at">Date</label>
                                <input class="form-control" type="text" value="{{ date('d/m/Y H:i:s', airdrop['created_at']) }}" id="created-at" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="platform">Platform</label>
                                <input class="form-control" type="text" value="{{ airdrop['platform']|upper }}" id="platform" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="network">Platform</label>
                                <input class="form-control" type="text" value="{{ airdrop['network']|upper }}" id="network" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Token address</label>
                                {% if airdrop['token_type'] == 'main' %}
                                    <a class="form-control" href="javascript:"><b>{{ airdrop['token_address'] }}</b></a>
                                {% else %}
                                    <a class="form-control" href="{{ helper.getLinkAddress(airdrop['token_address'], airdrop['platform'], airdrop['network']) }}" target="_blank"><b>{{ airdrop['token_address'] }}</b></a>
                                {% endif %}
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-name">Token name</label>
                                <input class="form-control" type="text" value="{{ airdrop['token_name'] }}" id="token-name" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-symbol">Token symbol</label>
                                <input class="form-control" type="text" value="{{ airdrop['token_symbol'] }}" id="token-symbol" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="token-decimals">Token decimals</label>
                                <input class="form-control" type="text" value="{{ airdrop['token_decimals'] }}" id="token-decimals" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="address-number">Address number</label>
                                <input class="form-control" type="text" value="{{ airdrop['list_address']|length }}" id="address-number" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="total-token-amount">Total token amount ({{ airdrop['token_symbol'] }})</label>
                                <input class="form-control" type="text" value="{{ helper.numberFormat(airdrop['total_token_amount'], 8) }}" id="total-token-amount" disabled>
                            </div>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Ix</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Amount</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for key,item in airdrop['list_address'] %}
                                <tr>
                                    <td>{{ key + 1 }}</td>
                                    <td>
                                        <div><a href="{{ helper.getLinkAddress(item, airdrop['platform'], airdrop['network']) }}" target="_blank"><b>{{ item }}</b></a></div>
                                    </td>
                                    <td>
                                        <div><b>{{ helper.numberFormat(airdrop['list_amount'][key], 8) }}</b> {{ airdrop['token_symbol'] }}</div>
                                    </td>
                                </tr>
                            {% endfor %}

                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
<!-- end row -->

</div>
