<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Presale User Log</h5>

                    {% if isSearch == true %}
                        <form action="" class="form settings-form" method="get">
                            <div class="row">
                                <div class="col-sm-2">
                                    <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address"
                                           value="{{ dataGet['user_address'] }}">
                                </div>
                                <div class="col-sm-2">
                                    <select name="withdraw_status" id="withdraw_status" class="form-control">
                                        <option value="">Withdraw Status</option>
                                        <option value="0" {{ dataGet['withdraw_status'] and dataGet['withdraw_status'] == 0 ? 'selected' : '' }}>
                                            Withdraw
                                        </option>
                                        <option value="1" {{ dataGet['withdraw_status'] and dataGet['withdraw_status'] == 1 ? 'selected' : '' }}>Not
                                            Withdraw
                                        </option>
                                    </select>
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
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Buy Amount</th>
                                <th class="border-top-0">Withdraw Amount</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listPresaleUserLog %}
                                <tr>
                                    <td>
                                        <div>User Address:
                                            <a
                                               href="{{ helper.getLinkAddress(item['user_address'], presale['platform'], presale['network']) }}"
                                               target="_blank"><b>{{ item['user_address'] }}</b></a>
                                        </div>
                                        <div>Presale Address:
                                            <a
                                               href="{{ helper.getLinkAddress(item['presale_address'], presale['platform'], presale['network']) }}"
                                               target="_blank"><b>{{ item['presale_address'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Base Token: <b>{{ item['base_token_amount'] }}</b></div>
                                        <div>Sale Token: <b>{{ item['sale_token_amount'] }}</b></div>
                                    </td>

                                    <td>
                                        {% if item['withdraw_token_type'] == 'base_token' %}
                                            <div>Base Token: <b>{{ item['base_token_withdraw_amount'] }}</b></div>
                                        {% else %}
                                            <div>Sale Token: <b>{{ item['sale_token_withdraw_amount'] }}</b></div>
                                        {% endif %}
                                    </td>
                                    <td>
                                        <div>Withdraw Status: <b>{{ item['withdraw_status'] }}</b></div>
                                        <div>Active vesting: <b>{{ item['active_vesting'] }}</b></div>
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