<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Lottery Ticket</h4>
            </div>
        </div>
    </div>
</div>

{% if lottery %}
    {% include 'layouts/lottery/lottery_info.volt' %}
{% endif %}
<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Lottery Ticket</h5>

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
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="lottery_contract_id">Lottery Contract ID</label>
                                <input placeholder="Lottery Contract ID" id="lottery_contract_id" type="text" class="form-control" name="lottery_contract_id" value="{{ dataGet['lottery_contract_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_address">User Address</label>
                                <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="ticket_id">Ticket ID</label>
                                <input placeholder="Ticket ID" id="ticket_id" type="text" class="form-control" name="ticket_id" value="{{ dataGet['ticket_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_real_ticket_number">Ticket Number</label>
                                <input placeholder="Number" id="user_real_ticket_number" type="text" class="form-control" name="user_real_ticket_number" value="{{ dataGet['user_real_ticket_number'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="bracket">Bracket</label>
                                <input placeholder="Bracket" id="bracket" type="text" class="form-control" name="bracket" value="{{ dataGet['bracket'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="is_win">Win</label>
                                <select name="is_win" id="is_win" class="form-control">
                                    <option value="">Select</option>
                                    <option value="1" {{ dataGet['is_win']|length AND dataGet['is_win'] == 1 ? 'selected' : '' }}>Win</option>
                                    <option value="0" {{ dataGet['is_win']|length AND dataGet['is_win'] == 0 ? 'selected' : '' }}>Lose</option>
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="is_claim">Claimed</label>
                                <select name="is_claim" id="is_claim" class="form-control">
                                    <option value="">Select</option>
                                    <option value="1" {{ dataGet['is_claim']|length AND dataGet['is_claim'] == 1 ? 'selected' : '' }}>Claimed</option>
                                    <option value="0" {{ dataGet['is_claim']|length AND dataGet['is_claim'] == 0 ? 'selected' : '' }}>Not claim</option>
                                </select>
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
                                <th class="border-top-0">Ticket</th>
                                <th class="border-top-0">Info</th>
                                <th class="border-top-0">Status</th>
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
                                        <div>Ticket ID: <b>{{ item['ticket_id'] }}</b></div>
                                        <div>Contract Raw: <b>{{ item['contract_raw_ticket_number'] }}</b></div>
                                        <div>Contract Real: <b>{{ item['contract_real_ticket_number'] }}</b></div>
                                        <div>User Raw: <b>{{ item['user_raw_ticket_number'] }}</b></div>
                                        <div>User Real: <b>{{ item['user_real_ticket_number'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Bracket: <b>{{ item['bracket'] }}</b></div>
                                        <div>Amount Reward: <b>{{ item['amount_reward'] }}</b></div>
                                    </td>
                                    <td>
                                        <div><b class="text-{{ item['is_win'] ? 'success' : 'danger' }}">{{ item['is_win'] ? 'Win' : 'Lose' }}</b></div>
                                        <div><b class="text-{{ item['is_claim'] == 1 ? 'success' : 'danger' }}">{{ item['is_claim'] ? 'Claimed' : 'Not Claim' }}</b></div>
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