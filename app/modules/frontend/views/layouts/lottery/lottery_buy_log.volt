<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Lottery Buy Log</h5>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2">
                                <input placeholder="Hash or Address" id="q" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
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
                                <th class="border-top-0">User Address</th>
                                <th class="border-top-0">Number</th>
                                <th class="border-top-0">Ticket</th>
                                <th class="border-top-0">Date</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['user_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['user_address'] }}</b></a></div>
                                        <div>Hash: <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['hash'] }}</b></a></div>
                                    </td>
                                    <td>
                                        <div>Number: <b>{{ item['number_ticket'] }}</b></div>
                                        <div>Amount: <b>{{ item['payment_amount'] }}</b></div>
                                    </td>
                                    <td>
                                        {% for cKey, cItem in item['user_raw_ticket_number'] %}
                                            <div>Raw: <b>{{ cItem }}</b> - Real: <b>{{ item['user_real_ticket_number'][cKey] }}</b></div>
                                        {% endfor %}
                                    </td>
                                    <td>{{ date('d/m/Y H:i:s', item['created_at']) }}</td>
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