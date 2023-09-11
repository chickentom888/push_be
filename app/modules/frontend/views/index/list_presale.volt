<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Presale </h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Presale</h5>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                            <tr>
                                <th class="border-top-0">Token</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Cap</th>
                                <th class="border-top-0">Price</th>
                                <th class="border-top-0">Time</th>
                                <th class="border-top-0">Detail</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Base Token Name: {{ item['base_token_name'] }}</div>
                                        <div>Base Token Symbol: {{ item['base_token_symbol'] }}</div>
                                        <div>Sale Token Name: {{ item['sale_token_name'] }}</div>
                                        <div>Sale Token Symbol: {{ item['sale_token_symbol'] }}</div>
                                    </td>
                                    <td>

                                        <div>Sale Amount: {{ helper.numberFormat(item['amount'], 4) }}</div>
                                        <div>Sale Token Liquidity Amount: {{ helper.numberFormat(item['sale_token_liquidity_amount'], 4) }}</div>
                                        <div>Sale Token Fee Amount: {{ helper.numberFormat(item['sale_token_fee_amount'], 4) }}</div>

                                    </td>

                                    <td>

                                        <div>Hard cap: {{ helper.numberFormat(item['hard_cap'], 4) }}</div>
                                        <div>Soft cap: {{ helper.numberFormat(item['soft_cap'], 4) }}</div>
                                        <div>Base token fee: {{ helper.numberFormat(item['base_fee_amount'], 4) }}</div>
                                        <div>Base liquidity fee: {{ helper.numberFormat(item['base_token_liquidity_amount'], 4) }}</div>

                                    </td>

                                    <td>
                                        <div>Sale Price: {{ helper.numberFormat(item['token_price'], 4) }}</div>
                                        <div>Listing Price: {{ helper.numberFormat(item['listing_price'], 4) }}</div>
                                        <div>Listing Price Percent: {{ helper.numberFormat(item['listing_price_percent'], 4) }}</div>
                                    </td>
                                    <td>
                                        <div>Created At: {{ date('d/m/Y H:i:s', item['created_at']) }}</div>
                                        <div>Start: {{ date('d/m/Y H:i:s', item['start_time']) }}</div>
                                        <div>End: {{ date('d/m/Y H:i:s', item['end_time']) }}</div>
                                    </td>

                                    <td>
                                        <a class="btn btn-success btn-sm" href="/index/presale_detail/{{ item['_id'] }}">Detail</a>
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
    <!-- end row -->

</div>
