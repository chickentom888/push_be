<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Lottery User Log</h5>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2">
                                <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
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
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Buy</th>
                                <th class="border-top-0">Claim</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>
                                            <a href="{{ helper.getLinkAddress(item['user_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['user_address'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Times: <b>{{ item['buy_times'] }}</b></div>
                                        <div>Number: <b>{{ item['number_ticket'] }}</b></div>
                                        <div>Amount: <b>{{ item['total_amount'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Number: <b>{{ item['number_claim'] }}</b></div>
                                        <div>Amount: <b>{{ item['amount_claim'] }}</b></div>
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