<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Presale Buy Log</h5>

                    {% if isSearch == true %}
                        <form action="" class="form settings-form" method="get">
                            <div class="row">
                                <div class="col-sm-2">
                                    <input placeholder="Hash or Address" id="q" type="text" class="form-control" name="q"
                                           value="{{ dataGet['q'] }}">
                                </div>

                                <div class="col-sm-2">
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    {% endif %}

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                            <tr>
                                <th class="border-top-0">User Address</th>
                                <th class="border-top-0">Hash</th>
                                <th class="border-top-0">Round</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Time</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listPresaleBuyLog %}
                                <tr>
                                    <td>
                                        <a href="{{ helper.getLinkAddress(item['user_address'], presale['platform'], presale['network']) }}" target="_blank"><b>{{ item['user_address'] }}</b></a>
                                    </td>
                                    <td>
                                        <a href="{{ helper.getLinkTx(item['hash'], presale['platform'], presale['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a>
                                    </td>
                                    <td>
                                        {{ item['round'] }}
                                    </td>
                                    <td>
                                        <div>Base Token: <b>{{ item['base_token_amount'] }}</b></div>
                                        <div>Sale Token: <b>{{ item['sale_token_amount'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>{{ date('d/m/Y H:i:s', item['created_at']) }}</div>
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